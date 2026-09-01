(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		initNavigation();
		initProductSearchAutocomplete();
		initRecentlyViewed();
	});

	function initNavigation() {
		var toggle = document.querySelector('.menu-toggle');
		var navigation = document.getElementById('site-primary-navigation');

		if (!toggle || !navigation) {
			return;
		}

		navigation.querySelectorAll('.menu-item-has-children').forEach(function (item, index) {
			var link = item.querySelector(':scope > a');
			var submenu = item.querySelector(':scope > .sub-menu');

			if (!link || !submenu || item.querySelector(':scope > .submenu-toggle')) {
				return;
			}

			var button = document.createElement('button');
			var label = link.textContent.trim() || 'submenu';
			var submenuId = submenu.id || 'primary-submenu-' + index;

			submenu.id = submenuId;
			button.type = 'button';
			button.className = 'submenu-toggle';
			button.setAttribute('aria-expanded', 'false');
			button.setAttribute('aria-controls', submenuId);
			button.setAttribute('aria-label', 'Toggle ' + label + ' submenu');
			button.textContent = '+';

			button.addEventListener('click', function () {
				var isExpanded = button.getAttribute('aria-expanded') === 'true';

				button.setAttribute('aria-expanded', String(!isExpanded));
				button.textContent = isExpanded ? '+' : '-';
				item.classList.toggle('is-submenu-open', !isExpanded);
			});

			link.insertAdjacentElement('afterend', button);
		});

		toggle.addEventListener('click', function () {
			var isExpanded = toggle.getAttribute('aria-expanded') === 'true';

			toggle.setAttribute('aria-expanded', String(!isExpanded));
			navigation.classList.toggle('is-open', !isExpanded);
		});
	}

	function initProductSearchAutocomplete() {
		var settings = window.vapestoreProductSearch || {};
		var form = document.querySelector('.product-search form.woocommerce-product-search');
		var input = form ? form.querySelector('input[type="search"][name="s"], .search-field[name="s"]') : null;
		var minLength = toPositiveInteger(settings.minLength) || 2;
		var limit = Math.min(5, toPositiveInteger(settings.limit) || 5);
		var debounceTimer = null;
		var abortController = null;
		var lastQuery = '';
		var dropdown;
		var resultsList;
		var status;

		if (!form || !input || !settings.endpoint || typeof window.fetch !== 'function') {
			return;
		}

		form.classList.add('vapestore-search-autocomplete');
		input.setAttribute('autocomplete', 'off');
		input.setAttribute('aria-expanded', 'false');

		dropdown = document.createElement('div');
		dropdown.className = 'vapestore-search-autocomplete__dropdown';
		dropdown.hidden = true;

		status = document.createElement('div');
		status.className = 'vapestore-search-autocomplete__status';
		status.setAttribute('aria-live', 'polite');

		resultsList = document.createElement('div');
		resultsList.className = 'vapestore-search-autocomplete__results';

		dropdown.appendChild(status);
		dropdown.appendChild(resultsList);
		form.appendChild(dropdown);

		input.addEventListener('input', function () {
			var query = input.value.trim();

			window.clearTimeout(debounceTimer);

			if (query.length < minLength) {
				closeDropdown();
				abortSearch();
				return;
			}

			debounceTimer = window.setTimeout(function () {
				searchProducts(query);
			}, 300);
		});

		input.addEventListener('keydown', function (event) {
			if (event.key === 'Escape') {
				closeDropdown();
			}
		});

		document.addEventListener('click', function (event) {
			if (!form.contains(event.target)) {
				closeDropdown();
			}
		});

		form.addEventListener('submit', function () {
			closeDropdown();
		});

		function searchProducts(query) {
			var url = settings.endpoint + '?q=' + encodeURIComponent(query) + '&limit=' + encodeURIComponent(limit);

			abortSearch();
			lastQuery = query;
			abortController = window.AbortController ? new window.AbortController() : null;
			showStatus(settings.loading || 'Searching...');

			window.fetch(url, {
				credentials: 'same-origin',
				signal: abortController ? abortController.signal : undefined
			})
				.then(function (response) {
					if (!response.ok) {
						throw new Error('Product search request failed.');
					}

					return response.json();
				})
				.then(function (data) {
					if (query !== lastQuery || input.value.trim() !== query) {
						return;
					}

					renderResults(Array.isArray(data.products) ? data.products.slice(0, limit) : [], query);
				})
				.catch(function (error) {
					if (error && error.name === 'AbortError') {
						return;
					}

					closeDropdown();
				});
		}

		function renderResults(products, query) {
			status.textContent = '';
			resultsList.innerHTML = '';

			if (!products.length) {
				showStatus(settings.noResults || 'No products found');
				appendViewAll(query);
				return;
			}

			products.forEach(function (product) {
				var link = document.createElement('a');
				var image = document.createElement('img');
				var text = document.createElement('span');
				var name = document.createElement('span');
				var price = document.createElement('span');

				link.className = 'vapestore-search-autocomplete__item';
				link.href = product.permalink || form.action || '/';

				image.className = 'vapestore-search-autocomplete__image';
				image.alt = '';
				image.loading = 'lazy';
				image.src = product.thumbnail || '';

				text.className = 'vapestore-search-autocomplete__text';
				name.className = 'vapestore-search-autocomplete__name';
				name.textContent = product.name || '';
				price.className = 'vapestore-search-autocomplete__price';
				price.innerHTML = product.price || '';

				text.appendChild(name);
				text.appendChild(price);
				link.appendChild(image);
				link.appendChild(text);
				resultsList.appendChild(link);
			});

			appendViewAll(query);
			openDropdown();
		}

		function appendViewAll(query) {
			var link = document.createElement('a');
			var searchUrl = new URL(form.action || window.location.href, window.location.href);

			searchUrl.searchParams.set(input.name || 's', query);
			searchUrl.searchParams.set('post_type', 'product');

			link.className = 'vapestore-search-autocomplete__view-all';
			link.href = searchUrl.toString();
			link.textContent = settings.viewAll || 'View all results';
			resultsList.appendChild(link);
			openDropdown();
		}

		function showStatus(message) {
			status.textContent = message;
			resultsList.innerHTML = '';
			openDropdown();
		}

		function openDropdown() {
			dropdown.hidden = false;
			input.setAttribute('aria-expanded', 'true');
		}

		function closeDropdown() {
			dropdown.hidden = true;
			input.setAttribute('aria-expanded', 'false');
			status.textContent = '';
			resultsList.innerHTML = '';
		}

		function abortSearch() {
			if (abortController) {
				abortController.abort();
				abortController = null;
			}
		}
	}

	function initRecentlyViewed() {
		var storageKey = 'vapestore_recently_viewed';
		var settings = window.vapestoreRecentlyViewed || {};
		var currentProductId = toPositiveInteger(settings.currentProductId);
		var placeholders = document.querySelectorAll('[data-vapestore-recently-viewed]');
		var history = readHistory();

		if (currentProductId) {
			history = normalizeIds([currentProductId].concat(history)).slice(0, 8);
			writeHistory(history);
		}

		if (!placeholders.length || !settings.endpoint) {
			return;
		}

		placeholders.forEach(function (placeholder) {
			var limit = toPositiveInteger(placeholder.getAttribute('data-limit')) || 4;
			var productIds = history.filter(function (productId) {
				return !currentProductId || productId !== currentProductId;
			}).slice(0, limit);
			var target = placeholder.querySelector('[data-vapestore-recently-viewed-products]');
			var url;

			if (!productIds.length || !target || typeof window.fetch !== 'function') {
				return;
			}

			url = settings.endpoint + '?ids=' + encodeURIComponent(productIds.join(',')) + '&limit=' + encodeURIComponent(limit);

			window.fetch(url, { credentials: 'same-origin' })
				.then(function (response) {
					if (!response.ok) {
						throw new Error('Recently viewed request failed.');
					}

					return response.json();
				})
				.then(function (data) {
					if (!data || !data.html) {
						return;
					}

					target.innerHTML = data.html;

					if (target.querySelector('li.product')) {
						placeholder.hidden = false;
					}
				})
				.catch(function () {});
		});

		function readHistory() {
			var stored;

			try {
				stored = window.localStorage.getItem(storageKey);
			} catch (error) {
				return [];
			}

			if (!stored) {
				return [];
			}

			try {
				return normalizeIds(JSON.parse(stored)).slice(0, 8);
			} catch (error) {
				return [];
			}
		}

		function writeHistory(productIds) {
			try {
				window.localStorage.setItem(storageKey, JSON.stringify(productIds));
			} catch (error) {}
		}

		function normalizeIds(productIds) {
			var normalized = [];

			if (!Array.isArray(productIds)) {
				return normalized;
			}

			productIds.forEach(function (productId) {
				productId = toPositiveInteger(productId);

				if (productId && normalized.indexOf(productId) === -1) {
					normalized.push(productId);
				}
			});

			return normalized;
		}

		function toPositiveInteger(value) {
			var integer = parseInt(value, 10);

			return integer > 0 ? integer : 0;
		}
	}

	function toPositiveInteger(value) {
		var integer = parseInt(value, 10);

		return integer > 0 ? integer : 0;
	}
})();

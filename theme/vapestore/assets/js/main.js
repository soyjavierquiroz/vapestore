(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		initNavigation();
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
})();

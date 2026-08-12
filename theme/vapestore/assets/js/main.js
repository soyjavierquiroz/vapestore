(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
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
	});
})();

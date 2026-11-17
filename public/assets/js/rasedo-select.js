(function () {
    var openMenu = null;

    function labelOf(select) {
        var option = select.options[select.selectedIndex];
        return option ? option.text : '';
    }

    function closeMenu(menu) {
        if (!menu) return;
        menu.classList.remove('is-open');
        var wrap = menu.bSelectWrap;
        if (wrap) wrap.classList.remove('is-open');
        if (openMenu === menu) openMenu = null;
    }

        function placeMenu(menu, toggle) {
        var rect = toggle.getBoundingClientRect();
        var width = Math.max(rect.width, 180);
        menu.style.width = width + 'px';
        menu.classList.add('is-open');
        var height = menu.offsetHeight;
        var top = rect.bottom + 8;
        if (top + height > window.innerHeight - 8 && rect.top - height > 8) {
            top = rect.top - height - 8;
        }
        var left = rect.left;
        if (left + width > window.innerWidth - 8) left = window.innerWidth - width - 8;
        if (left < 8) left = 8;
        menu.style.top = Math.max(8, top) + 'px';
        menu.style.left = left + 'px';
    }

    function enhance(select) {
        if (!select || select.dataset.bSelect || select.multiple || (select.size && select.size > 1)) return;
        if (select.closest('.b-select')) return;
        select.dataset.bSelect = '1';

        var wrap = document.createElement('div');
        wrap.className = 'b-select';
        select.parentNode.insertBefore(wrap, select);
        wrap.appendChild(select);
        select.classList.add('b-select-native');

        var toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.className = 'b-select-toggle';
        toggle.innerHTML = '<span class="b-select-label"></span>';

        var menu = document.createElement('div');
        menu.className = 'b-select-menu';
        menu.bSelectWrap = wrap;
        menu.setAttribute('role', 'listbox');

        wrap.insertBefore(toggle, select);
        wrap.appendChild(menu);

        var label = toggle.querySelector('.b-select-label');
        var hot = -1;

        function sync() {
            label.textContent = labelOf(select);
            toggle.disabled = select.disabled;
            if (select.classList.contains('is-invalid')) toggle.classList.add('is-invalid');
        }

        function paintHot() {
            var buttons = menu.querySelectorAll('.b-select-option');
            buttons.forEach(function (button, index) {
                button.classList.toggle('is-hot', index === hot);
            });
            if (buttons[hot]) buttons[hot].scrollIntoView({ block: 'nearest' });
        }

        function rebuild() {
            menu.innerHTML = '';
            Array.prototype.forEach.call(select.options, function (option, index) {
                var button = document.createElement('button');
                button.type = 'button';
                button.className = 'b-select-option' + (index === select.selectedIndex ? ' is-selected' : '');
                button.textContent = option.text;
                button.disabled = option.disabled;
                button.setAttribute('role', 'option');
                button.addEventListener('click', function () {
                    select.selectedIndex = index;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    sync();
                    closeMenu(menu);
                });
                menu.appendChild(button);
            });
            hot = select.selectedIndex;
            sync();
        }

        function open() {
            if (toggle.disabled) return;
            if (openMenu && openMenu !== menu) closeMenu(openMenu);
            rebuild();
            document.body.appendChild(menu);
            placeMenu(menu, toggle);
            wrap.classList.add('is-open');
            openMenu = menu;
            paintHot();
        }

        toggle.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            if (menu.classList.contains('is-open')) closeMenu(menu);
            else open();
        });

        toggle.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowDown' || event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                if (!menu.classList.contains('is-open')) open();
            }
        });

        menu.addEventListener('keydown', function (event) {
            var max = select.options.length - 1;
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                hot = Math.min(max, hot + 1);
                paintHot();
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                hot = Math.max(0, hot - 1);
                paintHot();
            } else if (event.key === 'Enter') {
                event.preventDefault();
                var button = menu.querySelectorAll('.b-select-option')[hot];
                if (button && !button.disabled) button.click();
            } else if (event.key === 'Escape') {
                closeMenu(menu);
                toggle.focus();
            }
        });

        select.addEventListener('change', sync);
        new MutationObserver(rebuild).observe(select, { childList: true, subtree: true, characterData: true });
        rebuild();
    }

    function scan(root) {
        var scope = root && root.querySelectorAll ? root : document;
        if (root && root.matches && root.matches('select')) enhance(root);
        scope.querySelectorAll('select').forEach(enhance);
    }

    document.addEventListener('mousedown', function (event) {
        if (!openMenu) return;
        if (event.target.closest('.b-select-toggle') || event.target.closest('.b-select-menu')) return;
        closeMenu(openMenu);
    });

    function reposition() {
        if (!openMenu || !openMenu.bSelectWrap) return;
        placeMenu(openMenu, openMenu.bSelectWrap.querySelector('.b-select-toggle'));
    }

    window.addEventListener('resize', reposition);
    window.addEventListener('scroll', reposition, true);

    scan(document);
    new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            mutation.addedNodes.forEach(function (node) {
                if (node.nodeType === 1) scan(node);
            });
        });
    }).observe(document.body, { childList: true, subtree: true });
})();

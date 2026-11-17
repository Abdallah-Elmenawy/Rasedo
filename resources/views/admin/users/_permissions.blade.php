@php
    $icons = \App\Support\PermissionCatalog::icons();
    $actions = \App\Support\PermissionCatalog::actions();
    $selected = collect($selected)->map(fn ($id) => (int) $id);
    $grantable = collect($grantable);
    $total = $permissions->flatten()->count();
@endphp
<section class="user-perms" data-role-permissions='@json($rolePermissions)' data-role-select="#role_id">
    <header class="user-perms-head">
        <div class="user-perms-title">
            <span class="user-perms-mark"><i class="fe fe-shield"></i></span>
            <div>
                <h5>الصلاحيات</h5>
                <p>تبدأ صلاحيات المستخدم من دوره، ويمكنك تفعيل أو إيقاف أي صلاحية له هو فقط دون تغيير الدور.</p>
            </div>
        </div>
        <div class="user-perms-stats">
            <span class="user-perms-stat is-total">المفعّلة <b><span class="js-stat-total">0</span> من {{ $total }}</b></span>
            <span class="user-perms-stat is-extra"><i class="fe fe-plus"></i> إضافية <b class="js-stat-extra">0</b></span>
            <span class="user-perms-stat is-revoked"><i class="fe fe-minus"></i> مسحوبة <b class="js-stat-revoked">0</b></span>
        </div>
    </header>

    <div class="user-perms-tools">
        <label class="user-perms-search">
            <i class="fe fe-search"></i>
            <input type="search" class="js-perm-search" placeholder="ابحث عن صلاحية...">
        </label>
        <div class="user-perms-actions">
            <button type="button" class="btn btn-sm btn-light js-perm-reset"><i class="fe fe-rotate-ccw ml-1"></i> صلاحيات الدور فقط</button>
            <button type="button" class="btn btn-sm btn-light js-perm-all-on"><i class="fe fe-check-square ml-1"></i> تفعيل الكل</button>
            <button type="button" class="btn btn-sm btn-light js-perm-all-off"><i class="fe fe-square ml-1"></i> إيقاف الكل</button>
        </div>
    </div>

    <div class="user-perms-admin js-admin-note" hidden>
        <i class="fe fe-award"></i>
        <span>هذا المستخدم <strong>مدير النظام</strong> ويملك كل الصلاحيات دائماً. لتخصيص صلاحياته اختر له دوراً آخر.</span>
    </div>

    <div class="perm-board user-perms-board">
        @foreach($permissions as $module => $items)
            <article class="perm-card" data-module>
                <div class="perm-card-head">
                    <span class="perm-card-mark"><i class="fe {{ $icons[$module] ?? 'fe-shield' }}"></i></span>
                    <div class="perm-card-title">
                        <h6>{{ $modules[$module] ?? $module }}</h6>
                        <small class="perm-count"><span class="js-card-on">0</span> من {{ $items->count() }}</small>
                    </div>
                    <label class="perm-all">
                        <input type="checkbox" class="js-card-all">
                        <span>الكل</span>
                    </label>
                </div>
                <div class="perm-list">
                    @foreach($items as $p)
                        @php $canGrant = $grantable->contains($p->id); @endphp
                        <label class="perm-item" data-perm-item data-search="{{ $p->name }} {{ $modules[$module] ?? '' }}">
                            <input type="checkbox" name="permissions[]" value="{{ $p->id }}"
                                   data-grantable="{{ $canGrant ? 1 : 0 }}"
                                   @checked($selected->contains($p->id)) @disabled(! $canGrant)>
                            <span class="perm-switch" aria-hidden="true"></span>
                            <span class="perm-copy">
                                <strong>{{ $p->name }}</strong>
                                <em>{{ $actions[$p->action] ?? $p->action }}</em>
                            </span>
                            <span class="perm-tag is-role">من الدور</span>
                            <span class="perm-tag is-extra">إضافية</span>
                            <span class="perm-tag is-revoked">مسحوبة</span>
                        </label>
                    @endforeach
                </div>
            </article>
        @endforeach
    </div>
    <p class="user-perms-empty js-perm-empty" hidden>لا توجد صلاحيات مطابقة للبحث.</p>
</section>

@push('scripts')
<script>
(function () {
    var root = document.querySelector('.user-perms');
    if (!root) return;
    var map = JSON.parse(root.getAttribute('data-role-permissions') || '{}');
    var roleSelect = document.querySelector(root.getAttribute('data-role-select'));
    var boxes = Array.prototype.slice.call(root.querySelectorAll('input[name="permissions[]"]'));
    var cards = Array.prototype.slice.call(root.querySelectorAll('[data-module]'));
    var adminLocked = false;

    function currentRole() {
        return map[roleSelect ? roleSelect.value : ''] || { admin: false, ids: [] };
    }

    function inRole(box) {
        return currentRole().ids.map(String).indexOf(box.value) !== -1;
    }

    function editable(box) {
        return !adminLocked && box.dataset.grantable === '1';
    }

    function applyRole(resetToRole) {
        var role = currentRole();
        adminLocked = !!role.admin;
        root.classList.toggle('is-admin-role', adminLocked);
        root.querySelector('.js-admin-note').hidden = !adminLocked;
        root.querySelectorAll('.user-perms-actions .btn').forEach(function (btn) { btn.disabled = adminLocked; });
        boxes.forEach(function (box) {
            if (adminLocked) box.checked = true;
            else if (resetToRole && box.dataset.grantable === '1') box.checked = inRole(box);
            box.disabled = !editable(box);
        });
        refresh();
    }

    function refresh() {
        var on = 0, extra = 0, revoked = 0;
        boxes.forEach(function (box) {
            var item = box.closest('[data-perm-item]');
            var fromRole = adminLocked || inRole(box);
            var state = fromRole ? (box.checked ? 'role' : 'revoked') : (box.checked ? 'extra' : 'off');
            item.dataset.state = state;
            if (box.checked) on++;
            if (state === 'extra') extra++;
            if (state === 'revoked') revoked++;
        });
        root.querySelector('.js-stat-total').textContent = on;
        root.querySelector('.js-stat-extra').textContent = extra;
        root.querySelector('.js-stat-revoked').textContent = revoked;

        cards.forEach(function (card) {
            var list = Array.prototype.slice.call(card.querySelectorAll('input[name="permissions[]"]'));
            var open = list.filter(function (box) { return !box.disabled; });
            var all = card.querySelector('.js-card-all');
            card.querySelector('.js-card-on').textContent = list.filter(function (box) { return box.checked; }).length;
            all.disabled = open.length === 0;
            all.checked = list.length > 0 && list.every(function (box) { return box.checked; });
            all.closest('.perm-all').classList.toggle('is-disabled', all.disabled);
        });
    }

    function setMany(list, value) {
        list.forEach(function (box) {
            if (editable(box)) box.checked = typeof value === 'function' ? value(box) : value;
        });
        refresh();
    }

    boxes.forEach(function (box) { box.addEventListener('change', refresh); });

    cards.forEach(function (card) {
        var all = card.querySelector('.js-card-all');
        all.addEventListener('change', function () {
            setMany(Array.prototype.slice.call(card.querySelectorAll('input[name="permissions[]"]')), all.checked);
        });
    });

    root.querySelector('.js-perm-reset').addEventListener('click', function () { setMany(boxes, inRole); });
    root.querySelector('.js-perm-all-on').addEventListener('click', function () { setMany(boxes, true); });
    root.querySelector('.js-perm-all-off').addEventListener('click', function () { setMany(boxes, false); });

    var search = root.querySelector('.js-perm-search');
    search.addEventListener('input', function () {
        var needle = search.value.trim().toLowerCase();
        var visibleCards = 0;
        cards.forEach(function (card) {
            var shown = 0;
            card.querySelectorAll('[data-perm-item]').forEach(function (item) {
                var match = !needle || item.getAttribute('data-search').toLowerCase().indexOf(needle) !== -1;
                item.hidden = !match;
                if (match) shown++;
            });
            card.hidden = shown === 0;
            if (shown) visibleCards++;
        });
        root.querySelector('.js-perm-empty').hidden = visibleCards > 0;
    });

    if (roleSelect) roleSelect.addEventListener('change', function () { applyRole(true); });
    applyRole(false);
})();
</script>
@endpush

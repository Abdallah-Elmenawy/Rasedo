@php
    $selected = collect($selected ?? []);
    $locked = $locked ?? false;
    $grantable = collect($grantable ?? []);
    $icons = \App\Support\PermissionCatalog::icons();
    $actions = \App\Support\PermissionCatalog::actions();
    $total = $permissions->flatten()->count();
@endphp
<section class="user-perms role-perms{{ $locked ? ' is-admin-role' : '' }}">
    <header class="user-perms-head">
        <div class="user-perms-title">
            <span class="user-perms-mark"><i class="fe fe-shield"></i></span>
            <div>
                <h5>الصلاحيات</h5>
                <p>اختر الصلاحيات التي يملكها كل مستخدم على هذا الدور، ويمكنك تعديل نص اسم أي صلاحية.</p>
            </div>
        </div>
        <div class="user-perms-stats">
            <span class="user-perms-stat is-total">المفعّلة <b><span class="js-stat-total">0</span> من {{ $total }}</b></span>
        </div>
    </header>

    <div class="user-perms-tools">
        <label class="user-perms-search">
            <i class="fe fe-search"></i>
            <input type="search" class="js-perm-search" placeholder="ابحث عن صلاحية...">
        </label>
        @unless($locked)
            <div class="user-perms-actions">
                <button type="button" class="btn btn-sm btn-light js-perm-all-on"><i class="fe fe-check-square ml-1"></i> تفعيل الكل</button>
                <button type="button" class="btn btn-sm btn-light js-perm-all-off"><i class="fe fe-square ml-1"></i> إيقاف الكل</button>
            </div>
        @endunless
    </div>

    @if($locked)
        <div class="user-perms-admin">
            <i class="fe fe-award"></i>
            <span>صلاحيات <strong>مدير النظام</strong> تبقى مفعّلة كلها. تقدر تعدّل نص الجملة نفسها، مثل لوحة التحكم وباقي الأقسام.</span>
        </div>
    @endif

    <div class="perm-board user-perms-board">
        @foreach($permissions as $module => $items)
            <article class="perm-card" data-module>
                <div class="perm-card-head">
                    <span class="perm-card-mark"><i class="fe {{ $icons[$module] ?? 'fe-shield' }}"></i></span>
                    <div class="perm-card-title">
                        <h6>{{ $modules[$module] ?? $module }}</h6>
                        <small class="perm-count"><span class="js-card-on">0</span> من {{ $items->count() }}</small>
                    </div>
                    @unless($locked)
                        <label class="perm-all">
                            <input type="checkbox" class="js-card-all">
                            <span>الكل</span>
                        </label>
                    @endunless
                </div>
                <div class="perm-list">
                    @foreach($items as $p)
                        <div class="perm-item" data-perm-item data-module-name="{{ $modules[$module] ?? '' }}">
                            <label class="perm-toggle">
                                <input type="checkbox" name="permissions[]" value="{{ $p->id }}" @checked($selected->contains($p->id)) @disabled($locked || ! $grantable->contains($p->id))>
                                <span class="perm-switch" aria-hidden="true"></span>
                            </label>
                            <span class="perm-copy">
                                <input type="text" class="perm-name" name="permission_names[{{ $p->id }}]" value="{{ old('permission_names.'.$p->id, $p->name) }}" maxlength="150">
                                <em>{{ $actions[$p->action] ?? $p->action }}</em>
                            </span>
                        </div>
                    @endforeach
                </div>
            </article>
        @endforeach
    </div>
    <p class="user-perms-empty js-perm-empty" hidden>لا توجد صلاحيات مطابقة للبحث.</p>
</section>
@once
<script>
document.querySelectorAll('.role-perms').forEach(function (root) {
    var boxes = Array.prototype.slice.call(root.querySelectorAll('input[name="permissions[]"]'));
    var cards = Array.prototype.slice.call(root.querySelectorAll('[data-module]'));

    function refresh() {
        root.querySelector('.js-stat-total').textContent = boxes.filter(function (box) { return box.checked; }).length;
        cards.forEach(function (card) {
            var list = Array.prototype.slice.call(card.querySelectorAll('input[name="permissions[]"]'));
            var all = card.querySelector('.js-card-all');
            card.querySelector('.js-card-on').textContent = list.filter(function (box) { return box.checked; }).length;
            if (!all) return;
            all.disabled = !list.some(function (box) { return !box.disabled; });
            all.checked = list.length > 0 && list.every(function (box) { return box.checked; });
            all.closest('.perm-all').classList.toggle('is-disabled', all.disabled);
        });
    }

    function setMany(list, value) {
        list.forEach(function (box) { if (!box.disabled) box.checked = value; });
        refresh();
    }

    boxes.forEach(function (box) {
        box.addEventListener('change', refresh);
        box.closest('[data-perm-item]').addEventListener('click', function (e) {
            if (box.disabled || e.target.closest('.perm-name, .perm-toggle')) return;
            box.checked = !box.checked;
            refresh();
        });
    });

    cards.forEach(function (card) {
        var all = card.querySelector('.js-card-all');
        if (!all) return;
        all.addEventListener('change', function () {
            setMany(Array.prototype.slice.call(card.querySelectorAll('input[name="permissions[]"]')), all.checked);
        });
    });

    var allOn = root.querySelector('.js-perm-all-on');
    var allOff = root.querySelector('.js-perm-all-off');
    if (allOn) allOn.addEventListener('click', function () { setMany(boxes, true); });
    if (allOff) allOff.addEventListener('click', function () { setMany(boxes, false); });

    var search = root.querySelector('.js-perm-search');
    search.addEventListener('input', function () {
        var needle = search.value.trim().toLowerCase();
        var visibleCards = 0;
        cards.forEach(function (card) {
            var shown = 0;
            card.querySelectorAll('[data-perm-item]').forEach(function (item) {
                var text = (item.querySelector('.perm-name').value + ' ' + item.getAttribute('data-module-name')).toLowerCase();
                var match = !needle || text.indexOf(needle) !== -1;
                item.hidden = !match;
                if (match) shown++;
            });
            card.hidden = shown === 0;
            if (shown) visibleCards++;
        });
        root.querySelector('.js-perm-empty').hidden = visibleCards > 0;
    });

    refresh();
});
</script>
@endonce

@php
    $selected = collect($selected ?? []);
    $locked = $locked ?? false;
    $icons = [
        'dashboard' => 'fe-home',
        'customers' => 'fe-users',
        'suppliers' => 'fe-truck',
        'products' => 'fe-box',
        'inventory' => 'fe-layers',
        'pos' => 'fe-monitor',
        'sales' => 'fe-shopping-cart',
        'purchases' => 'fe-file-text',
        'accounting' => 'fe-book-open',
        'treasury' => 'fe-credit-card',
        'expenses' => 'fe-minus-circle',
        'reports' => 'fe-bar-chart-2',
        'users' => 'fe-user-check',
        'settings' => 'fe-settings',
    ];
    $actions = ['view' => 'عرض', 'create' => 'إضافة', 'update' => 'تعديل', 'delete' => 'حذف'];
@endphp
<div class="perm-board">
    @foreach($permissions as $module => $items)
        <article class="perm-card">
            <div class="perm-card-head">
                <span class="perm-card-mark"><i class="fe {{ $icons[$module] ?? 'fe-shield' }}"></i></span>
                <div class="perm-card-title">
                    <h6>{{ $modules[$module] ?? $module }}</h6>
                    <small class="perm-count" data-total="{{ $items->count() }}">0 من {{ $items->count() }}</small>
                </div>
                @unless($locked)
                    <label class="perm-all">
                        <input type="checkbox" class="js-perm-all">
                        <span>الكل</span>
                    </label>
                @endunless
            </div>
            <div class="perm-list">
                @foreach($items as $p)
                    <div class="perm-item">
                        <label class="perm-toggle">
                            <input type="checkbox" name="permissions[]" value="{{ $p->id }}" @checked($selected->contains($p->id)) @disabled($locked)>
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
@once
<script>
document.querySelectorAll('.perm-card').forEach(function (card) {
    var all = card.querySelector('.js-perm-all');
    var boxes = card.querySelectorAll('input[name="permissions[]"]');
    var count = card.querySelector('.perm-count');
    var total = count ? count.getAttribute('data-total') : String(boxes.length);
    if (!boxes.length) return;
    function refresh() {
        var on = Array.prototype.filter.call(boxes, function (box) { return box.checked; }).length;
        if (count) count.textContent = on + ' من ' + total;
        if (all) all.checked = on === boxes.length;
    }
    if (all) {
        all.addEventListener('change', function () {
            boxes.forEach(function (box) { if (!box.disabled) box.checked = all.checked; });
            refresh();
        });
    }
    boxes.forEach(function (box) { box.addEventListener('change', refresh); });
    refresh();
});
</script>
@endonce

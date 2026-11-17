@php
    preg_match_all('/\bname="([^"\[]+)/', (string) $slot, $fieldNames);
    $activeCount = collect(request()->only(array_unique($fieldNames[1])))
        ->filter(fn ($value) => $value !== null && $value !== '')
        ->count();
    $filled = $activeCount > 0;
@endphp
<div class="filter-launch-wrap">
    @if($filled)
        <a href="{{ url()->current() }}" class="filter-launch-clear" title="مسح الفلتر"><i class="fe fe-x"></i> مسح</a>
    @endif
    <button type="button" class="filter-launch {{ $filled ? 'is-active' : '' }}" data-filter-open>
        <span class="filter-launch-icon"><i class="fe fe-filter"></i></span>
        <span>فلتر</span>
        @if($filled)
            <span class="filter-launch-count">{{ $activeCount }}</span>
        @endif
    </button>
</div>
<div class="filter-drawer" data-filter-drawer>
    <div class="filter-drawer-backdrop" data-filter-close></div>
    <aside class="filter-drawer-panel" role="dialog" aria-label="فلتر حسب">
        <div class="filter-drawer-head">
            <h3>فلتر حسب :</h3>
            <button type="button" class="filter-drawer-close" data-filter-close aria-label="إغلاق">
                <i class="fe fe-x"></i>
            </button>
        </div>
        <form method="get" action="{{ url()->current() }}" class="filter-drawer-form">
            <div class="filter-drawer-body">
                {{ $slot }}
            </div>
            <div class="filter-drawer-foot">
                <button type="submit" class="btn btn-primary">تطبيق</button>
                @if($filled)
                    <a class="filter-reset" href="{{ url()->current() }}">مسح الفلتر</a>
                @endif
            </div>
        </form>
    </aside>
</div>
@once
<script>
function closeFilterDrawer(drawer) {
    if (!drawer || !drawer.classList.contains('is-open') || drawer.classList.contains('is-closing')) return;
    var panel = drawer.querySelector('.filter-drawer-panel');
    drawer.classList.add('is-closing');
    drawer.classList.remove('is-open');
    var done = false;
    function finish() {
        if (done) return;
        done = true;
        drawer.classList.remove('is-closing');
    }
    if (panel) {
        panel.addEventListener('transitionend', function (event) {
            if (event.target === panel && event.propertyName === 'transform') finish();
        });
    }
    setTimeout(finish, 320);
}
document.addEventListener('click', function (event) {
    var openBtn = event.target.closest('[data-filter-open]');
    if (openBtn) {
        var drawer = openBtn._filterDrawer || openBtn.parentElement.nextElementSibling;
        if (!drawer) return;
        if (drawer.parentElement !== document.body) {
            openBtn._filterDrawer = drawer;
            document.body.appendChild(drawer);
        }
        drawer.classList.remove('is-closing');
        drawer.classList.add('is-open');
        return;
    }
    if (event.target.closest('[data-filter-close]')) {
        closeFilterDrawer(event.target.closest('[data-filter-drawer]'));
    }
});
document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape') return;
    document.querySelectorAll('[data-filter-drawer].is-open').forEach(closeFilterDrawer);
});
document.querySelectorAll('.filter-control').forEach(function (wrap) {
    var input = wrap.querySelector('input, select');
    var clear = wrap.querySelector('.filter-clear');
    if (!input || !clear) return;
    function sync() { clear.hidden = input.value === ''; }
    input.addEventListener('input', sync);
    input.addEventListener('change', sync);
    clear.addEventListener('click', function () {
        input.value = '';
        sync();
        input.focus();
    });
    sync();
});
</script>
@endonce

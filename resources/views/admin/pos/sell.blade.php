@extends('layouts.pos')

@section('content')
@php
    $shiftOpen = is_array($shift);
    $lastPrint = session('pos_print');
    $report = session('pos_shift_report');
@endphp
<div class="pos-shell">
    <div class="pos-nav">
        @if(can('dashboard.view'))
            <a class="pos-back pos-back-home" href="{{ route('dashboard') }}">
                <span class="pos-back-icon"><i class="fe fe-home"></i></span>
                <span>الرئيسية</span>
                <i class="fe fe-chevron-left pos-back-arrow"></i>
            </a>
        @endif
        @if(can('pos.view'))
            <a class="pos-back" href="{{ route('pos.index') }}">
                <span class="pos-back-icon"><i class="fe fe-bar-chart-2"></i></span>
                <span>ملخص المبيعات</span>
                <i class="fe fe-chevron-left pos-back-arrow"></i>
            </a>
        @endif
    </div>
    @if($shiftOpen)
        <form method="post" action="{{ route('pos.shift.close') }}" class="pos-shift-bar card">
            @csrf
            <div class="pos-shift-bar-meta">
                <span class="pos-kpi-label">الخزينة</span>
                <strong>{{ $shift['treasury_name'] }}</strong>
            </div>
            <input type="number" name="actual_cash" min="0" step="0.01" class="form-control" placeholder="النقدية الفعلية في الدرج" required>
            <button type="submit" class="btn btn-primary">إغلاق الوردية</button>
        </form>
    @else
        <form method="post" action="{{ route('pos.shift.open') }}" class="pos-shift-bar card">
            @csrf
            <div class="pos-shift-bar-meta">
                <span class="pos-kpi-label">الوردية مغلقة</span>
                <strong>اختر الخزينة قبل البيع</strong>
            </div>
            <select name="treasury_id" class="form-control" required>
                <option value="">اختر الخزينة</option>
                @foreach($treasuries as $treasury)
                    <option value="{{ $treasury->id }}">{{ $treasury->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary" @disabled($treasuries->isEmpty())>فتح الوردية</button>
        </form>
    @endif
    @if($report)
        <div class="pos-report card">
            <strong>مراجعة إغلاق الوردية — {{ $report['treasury_name'] }}</strong>
            <span>فواتير {{ $report['invoices'] }}</span>
            <span>مبيعات {{ money($report['sales']) }}</span>
            <span>نقدي {{ money($report['cash']) }}</span>
            <span>إلكتروني {{ money($report['card']) }}</span>
            <span>المتوقع {{ money($report['expected_cash']) }}</span>
            <span>الفعلي {{ money($report['actual_cash']) }}</span>
            <span>الفرق {{ money($report['difference']) }}</span>
        </div>
    @endif
    <div class="pos-layout">
        <div class="pos-catalog card">
            <div class="pos-scan">
                <input type="text" id="posScan" class="form-control" placeholder="امسح سيريال العلبة أو القطعة" autocomplete="off" autofocus>
                <button type="button" id="posCamera" class="btn btn-light">الكاميرا</button>
            </div>
            <div class="pos-camera" id="posCameraBox" hidden>
                <video id="posCameraVideo" autoplay playsinline muted></video>
                <button type="button" id="posCameraClose" class="btn btn-light">إغلاق الكاميرا</button>
            </div>
            <div class="pos-search">
                <i class="fe fe-search"></i>
                <input type="search" id="posSearch" class="form-control" placeholder="ابحث بالاسم أو اختر المنتج يدوياً" autocomplete="off">
            </div>
            <div class="pos-cats" id="posCats">
                <button type="button" class="pos-chip is-active" data-cat="">الكل</button>
                @foreach($categories as $category)
                    <button type="button" class="pos-chip" data-cat="{{ $category->id }}">{{ $category->name }}</button>
                @endforeach
            </div>
            <div class="pos-grid" id="posGrid"></div>
            <div class="pos-empty" id="posEmpty" hidden>لا توجد منتجات مطابقة.</div>
            <div class="pos-pager">
                <button type="button" id="posPrev" class="pos-page-btn"><i class="fe fe-chevron-right"></i></button>
                <div id="posPages" class="pos-pages"></div>
                <button type="button" id="posNext" class="pos-page-btn"><i class="fe fe-chevron-left"></i></button>
                <span id="posRange" class="pos-range"></span>
            </div>
        </div>

        <aside class="pos-cart card">
            <div class="pos-cart-head">
                <h5>المنتجات في الفاتورة (<span id="posCartCount">0</span>)</h5>
                <button type="button" class="pos-clear" id="posClear" title="تفريغ السلة"><i class="fe fe-trash-2"></i></button>
            </div>
            <div class="pos-field">
                <i class="fe fe-user"></i>
                <select id="posCustomer" class="form-control" form="posCheckoutForm" name="customer_id" required>
                    <option value="">اختر العميل</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" @selected(old('customer_id', $customers->first()?->id) == $customer->id)>{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="pos-lines" id="posLines">
                <p class="pos-lines-empty">السلة فارغة. اضغط على منتج لإضافته.</p>
            </div>

            <div class="pos-summary">
                <div class="pos-sum-row pos-discount-row">
                    <span>خصم</span>
                    <label class="pos-percent">
                        <input type="number" min="0" max="100" step="0.01" id="posDiscount" value="0">
                        <b>%</b>
                    </label>
                </div>
                <div class="pos-sum-row"><span>إجمالي المنتجات</span><strong id="posSubtotal">0.00</strong></div>
                <div class="pos-sum-row"><span>الخصم</span><strong id="posDiscountAmount">0.00</strong></div>
                <div class="pos-sum-row"><span id="posTaxLabel">الضريبة</span><strong id="posTax">0.00</strong></div>
                <div class="pos-sum-row pos-total-row"><span>الإجمالي الكلي</span><strong id="posTotal">0.00</strong></div>
            </div>

            <div class="pos-methods" id="posOrderTypes">
                <span class="pos-methods-title">طريقة الاستلام</span>
                <div class="pos-method-row pos-order-row">
                    @foreach(\App\Support\ReceiptText::orderTypes() as $key => $label)
                        <button type="button" class="pos-method @if($key === 'in_store') is-active @endif" data-order="{{ $key }}">{{ $label }}</button>
                    @endforeach
                </div>
            </div>

            <div class="pos-methods" id="posMethods">
                <span class="pos-methods-title">طريقة الدفع</span>
                <div class="pos-method-row">
                    <button type="button" class="pos-method is-active" data-method="cash"><i class="fe fe-dollar-sign"></i>نقدي</button>
                    <button type="button" class="pos-method" data-method="visa"><i class="fe fe-credit-card"></i>فيزا</button>
                    <button type="button" class="pos-method" data-method="mada"><i class="fe fe-credit-card"></i>مدى</button>
                    <button type="button" class="pos-method" data-method="wallet"><i class="fe fe-briefcase"></i>محفظة</button>
                    <button type="button" class="pos-method" data-method="other"><i class="fe fe-more-horizontal"></i>أخرى</button>
                </div>
            </div>

            <form id="posCheckoutForm" method="POST" action="{{ route('pos.checkout') }}">
                @csrf
                <input type="hidden" name="order_type" id="posOrderType" value="in_store">
                <input type="hidden" name="payment_status" id="posPaymentStatus" value="paid">
                <input type="hidden" name="payment_method" id="posPaymentMethod" value="cash">
                <input type="hidden" name="paid_amount" id="posPaidHidden" value="0">
                <input type="hidden" name="discount" id="posDiscountHidden" value="0">
                <input type="hidden" name="submit_action" id="posSubmitAction" value="pay">
                <div id="posItemInputs"></div>
                <div class="pos-pay-row">
                    <button type="submit" class="btn btn-primary pos-pay" data-action="pay" @disabled(! $shiftOpen || $customers->isEmpty() || ! can('sales.create'))>
                        <i class="fe fe-check-circle"></i>
                        <span id="posPayLabel">دفع</span>
                        <span class="pos-pay-total" id="posPayTotal">0.00</span>
                    </button>
                </div>
            </form>

            <div class="pos-tools" id="posTools">
                <button type="button" class="pos-tool" id="posDrawer" title="فتح الدرج من غير فاتورة">
                    <i class="fe fe-inbox"></i>
                    <span>الدرج</span>
                </button>
                <button type="button" class="pos-tool" id="posReprint" title="إعادة طباعة آخر عملية" @if($lastPrint) data-print-url="{{ $lastPrint }}" @else disabled @endif>
                    <i class="fe fe-printer"></i>
                    <span>طباعة</span>
                </button>
                <button type="submit" class="pos-tool" form="posCheckoutForm" data-action="draft" title="حفظ الفاتورة بدون تحصيل" @disabled(! $shiftOpen || ! can('sales.create'))>
                    <i class="fe fe-bookmark"></i>
                    <span>مسودة</span>
                </button>
                <button type="button" class="pos-tool" id="posFreeze" title="إيقافها والرجوع لها لاحقاً">
                    <i class="fe fe-pause"></i>
                    <span id="posFreezeLabel">تجميد</span>
                </button>
                <button type="button" class="pos-tool is-danger" id="posCancel" title="تفريغ السلة الحالية">
                    <i class="fe fe-trash-2"></i>
                    <span>إلغاء</span>
                </button>
            </div>
            <p class="pos-note" id="posNote" hidden></p>
        </aside>
    </div>
</div>

@include('layouts.confirm-delete-modal')
@endsection

@section('js')
<script>
(function () {
    const products = @json($posProducts);
    const symbol = @json(company()?->currency_symbol ?? 'ج.م');
    const shiftOpen = @json($shiftOpen);
    const cart = new Map();
    const pageSize = 10;
    let categoryId = '';
    let query = '';
    let page = 1;
    let method = 'cash';
    const grid = document.getElementById('posGrid');
    const empty = document.getElementById('posEmpty');
    const lines = document.getElementById('posLines');
    const discountEl = document.getElementById('posDiscount');
    const form = document.getElementById('posCheckoutForm');
    const itemInputs = document.getElementById('posItemInputs');
    const note = document.getElementById('posNote');
    const freezeKey = 'rasedoPosFrozen';

    function money(n) {
        return Number(n || 0).toFixed(2) + ' ' + symbol;
    }
    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c];
        });
    }
    function filtered() {
        const q = query.trim();
        return products.filter(function (p) {
            const catOk = !categoryId || String(p.category_id) === String(categoryId);
            const qOk = !q || p.name.indexOf(q) !== -1 || (p.sku && String(p.sku).indexOf(q) !== -1) || (p.scan_code && String(p.scan_code).indexOf(q) !== -1);
            return catOk && qOk;
        });
    }
    function stockText(p) {
        if (p.type !== 'product') return 'خدمة';
        if (p.stock <= 0) return 'غير متوفر';
        return 'متوفر ' + p.stock;
    }
    function renderGrid() {
        const list = filtered();
        const pages = Math.max(1, Math.ceil(list.length / pageSize));
        if (page > pages) page = pages;
        const start = (page - 1) * pageSize;
        const slice = list.slice(start, start + pageSize);
        grid.innerHTML = '';
        empty.hidden = list.length > 0;
        slice.forEach(function (p) {
            const out = p.type === 'product' && p.stock <= 0;
            const card = document.createElement('button');
            card.type = 'button';
            card.className = 'pos-product' + (cart.has(p.id) ? ' is-in-cart' : '') + (out ? ' is-out' : '');
            card.disabled = out;
            card.innerHTML =
                '<span class="pos-product-photo">' + esc(p.letter || '') + '</span>' +
                '<span class="pos-product-name">' + esc(p.name) + '</span>' +
                '<span class="pos-product-foot">' +
                    '<span class="pos-product-price">' + money(p.price) + '</span>' +
                    '<span class="pos-product-stock' + (out ? ' is-out' : '') + '">' + stockText(p) + '</span>' +
                '</span>';
            card.addEventListener('click', function () { addItem(p); });
            grid.appendChild(card);
        });
        const pagesEl = document.getElementById('posPages');
        pagesEl.innerHTML = '';
        for (let i = 1; i <= pages; i++) {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'pos-page-btn' + (i === page ? ' is-active' : '');
            b.textContent = String(i);
            b.addEventListener('click', function () { page = i; renderGrid(); });
            pagesEl.appendChild(b);
        }
        const from = list.length ? start + 1 : 0;
        const to = Math.min(start + pageSize, list.length);
        document.getElementById('posRange').textContent = 'عرض ' + from + ' - ' + to + ' من ' + list.length;
        document.getElementById('posPrev').disabled = page <= 1;
        document.getElementById('posNext').disabled = page >= pages;
    }
    function serialCode(serial) { return typeof serial === 'string' ? serial : serial.code; }
    function serialPieces(serial) { return typeof serial === 'string' ? 1 : (Number(serial.pieces) || 1); }
    function addItem(p, serial, pieces) {
        const current = cart.get(p.id);
        const serials = (current && current.serials ? current.serials : []).map(function (item) {
            return typeof item === 'string' ? { code: item, pieces: 1 } : item;
        });
        const step = serial ? Math.max(1, Number(pieces) || 1) : 1;
        if (serial) {
            if (serials.some(function (item) { return item.code === serial; })) {
                showNote('هذا السيريال مضاف بالفعل.');
                return;
            }
            serials.push({ code: serial, pieces: step });
        }
        const covered = serials.reduce(function (sum, item) { return sum + item.pieces; }, 0);
        const qty = serial ? Math.max((current ? current.qty : 0) + step, covered) : ((current ? current.qty : 0) + 1);
        if (p.type === 'product' && qty > p.stock) {
            showNote('الكمية غير متوفرة.');
            return;
        }
        cart.set(p.id, { product: p, qty: qty, serials: serials });
        renderAll();
    }
    function changeQty(id, delta) {
        const line = cart.get(id);
        if (!line) return;
        const next = line.qty + delta;
        const covered = (line.serials || []).reduce(function (sum, item) { return sum + serialPieces(item); }, 0);
        if (next < covered) return;
        if (next <= 0) cart.delete(id);
        else if (line.product.type === 'product' && next > line.product.stock) return;
        else line.qty = next;
        renderAll();
    }
    function totals() {
        let sub = 0;
        let tax = 0;
        const rates = [];
        cart.forEach(function (line) {
            const lineNet = line.qty * line.product.price;
            sub += lineNet;
            tax += lineNet * ((Number(line.product.tax_rate) || 0) / 100);
            rates.push(Number(line.product.tax_rate) || 0);
        });
        const pct = Math.min(100, Math.max(0, Number(discountEl.value || 0)));
        const discount = sub * pct / 100;
        const sameRate = rates.length && rates.every(function (r) { return r === rates[0]; });
        return { sub: sub, tax: tax, discount: discount, total: Math.max(sub + tax - discount, 0), rate: sameRate ? rates[0] : null };
    }
    function renderLines() {
        let count = 0;
        cart.forEach(function (line) { count += line.qty; });
        document.getElementById('posCartCount').textContent = String(count);
        if (cart.size === 0) {
            lines.innerHTML = '<p class="pos-lines-empty">السلة فارغة. اضغط على منتج لإضافته.</p>';
            return;
        }
        lines.innerHTML = '';
        cart.forEach(function (line) {
            const p = line.product;
            const row = document.createElement('div');
            row.className = 'pos-line';
            row.innerHTML =
                '<div class="pos-line-info">' +
                    '<span class="pos-line-thumb">' + esc(p.letter || '') + '</span>' +
                    '<div><strong>' + esc(p.name) + '</strong><small>' + esc(p.unit || '') + '</small>' +
                    ((line.serials || []).length ? '<small class="pos-line-serials">' + (line.serials || []).map(function (item) { return (serialPieces(item) > 1 ? 'علبة ' : 'قطعة ') + esc(serialCode(item)); }).join('، ') + '</small>' : '') +
                    '</div>' +
                '</div>' +
                '<span class="pos-line-unit">' + Number(p.price).toFixed(2) + '</span>' +
                '<div class="pos-qty">' +
                    '<button type="button" class="pos-qty-btn is-minus" data-act="minus" aria-label="نقص">−</button>' +
                    '<span>' + line.qty + '</span>' +
                    '<button type="button" class="pos-qty-btn is-plus" data-act="plus" aria-label="زيادة">+</button>' +
                '</div>' +
                '<strong class="pos-line-price">' + money(line.qty * p.price) + '</strong>' +
                '<button type="button" class="pos-line-remove" data-act="remove" aria-label="حذف">×</button>';
            row.querySelector('[data-act="remove"]').addEventListener('click', function () { cart.delete(p.id); renderAll(); });
            row.querySelector('[data-act="minus"]').addEventListener('click', function () { changeQty(p.id, -1); });
            row.querySelector('[data-act="plus"]').addEventListener('click', function () { changeQty(p.id, 1); });
            lines.appendChild(row);
        });
    }
    function renderTotals() {
        const t = totals();
        document.getElementById('posSubtotal').textContent = money(t.sub);
        const discountAmount = document.getElementById('posDiscountAmount');
        discountAmount.textContent = (t.discount ? '-' : '') + money(t.discount);
        discountAmount.classList.toggle('is-minus', t.discount > 0);
        document.getElementById('posTax').textContent = money(t.tax);
        document.getElementById('posTaxLabel').textContent = t.rate ? 'الضريبة (' + t.rate + '%)' : 'الضريبة';
        document.getElementById('posTotal').textContent = money(t.total);
        document.getElementById('posPayTotal').textContent = money(t.total);
        document.getElementById('posDiscountHidden').value = t.discount.toFixed(2);
        document.getElementById('posPaidHidden').value = t.total.toFixed(2);
        document.getElementById('posPaymentMethod').value = method;
    }
    function renderAll() {
        renderGrid();
        renderLines();
        renderTotals();
        syncFreezeLabel();
    }
    function closeTools(done) {
        if (done) done();
    }
    function showNote(text) {
        note.hidden = false;
        note.textContent = text;
        clearTimeout(showNote.timer);
        showNote.timer = setTimeout(function () { note.hidden = true; }, 2200);
    }
    function frozenCart() {
        try { return JSON.parse(localStorage.getItem(freezeKey) || 'null'); } catch (e) { return null; }
    }
    function syncFreezeLabel() {
        const frozen = frozenCart();
        const restore = frozen && cart.size === 0;
        const hint = restore ? 'إرجاع الفاتورة الموقوفة للسلة' : 'إيقافها والرجوع لها لاحقاً';
        document.getElementById('posFreezeLabel').textContent = restore ? 'استرجاع' : 'تجميد';
        document.getElementById('posFreeze').title = hint;
    }
    function fillItems(action) {
        document.getElementById('posSubmitAction').value = action;
        document.getElementById('posPaymentStatus').value = action === 'draft' ? 'unpaid' : 'paid';
        document.getElementById('posPaidHidden').value = action === 'draft' ? '0' : totals().total.toFixed(2);
        itemInputs.innerHTML = '';
        let i = 0;
        cart.forEach(function (line) {
            const pid = document.createElement('input');
            pid.type = 'hidden';
            pid.name = 'items[' + i + '][product_id]';
            pid.value = line.product.id;
            const qty = document.createElement('input');
            qty.type = 'hidden';
            qty.name = 'items[' + i + '][qty]';
            qty.value = line.qty;
            itemInputs.appendChild(pid);
            itemInputs.appendChild(qty);
            (line.serials || []).forEach(function (serial, s) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'items[' + i + '][serials][' + s + ']';
                input.value = serialCode(serial);
                itemInputs.appendChild(input);
            });
            i += 1;
        });
    }
    document.getElementById('posSearch').addEventListener('input', function (e) {
        query = e.target.value;
        page = 1;
        renderGrid();
    });
    const scanUrl = @json(route('products.scan'));
    function clearScan() {
        const scan = document.getElementById('posScan');
        if (scan) scan.value = '';
        document.getElementById('posSearch').value = '';
        query = '';
        renderGrid();
    }
    function applyScan(code) {
        code = String(code || '').trim();
        if (!code) return;
        const local = products.find(function (p) { return p.scan_code === code || p.sku === code; });
        if (local) {
            addItem(local);
            clearScan();
            return;
        }
        fetch(scanUrl + '?code=' + encodeURIComponent(code), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (response) {
            return response.json().then(function (data) { return { ok: response.ok, data: data }; });
        }).then(function (result) {
            if (!result.ok || !result.data.product) {
                showNote((result.data && result.data.message) || 'لا يوجد صنف بهذا الكود.');
                return;
            }
            const found = result.data.product;
            let product = products.find(function (p) { return p.id === found.id; });
            if (!product) {
                products.push(found);
                product = found;
            }
            addItem(product, found.serial || null, found.serial_pieces || 1);
            clearScan();
        }).catch(function () {
            showNote('تعذر قراءة الكود.');
        });
    }
    document.getElementById('posScan').addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        applyScan(e.target.value);
    });
    document.getElementById('posSearch').addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        applyScan(query);
    });
    let cameraStream = null;
    let cameraTimer = null;
    function stopCamera() {
        if (cameraTimer) clearTimeout(cameraTimer);
        cameraTimer = null;
        if (cameraStream) cameraStream.getTracks().forEach(function (track) { track.stop(); });
        cameraStream = null;
        document.getElementById('posCameraBox').hidden = true;
    }
    document.getElementById('posCameraClose').addEventListener('click', stopCamera);
    document.getElementById('posCamera').addEventListener('click', function () {
        if (!window.BarcodeDetector || !navigator.mediaDevices) {
            showNote('الكاميرا غير مدعومة هنا. استخدم قارئ USB.');
            return;
        }
        const video = document.getElementById('posCameraVideo');
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } }).then(function (stream) {
            cameraStream = stream;
            video.srcObject = stream;
            document.getElementById('posCameraBox').hidden = false;
            const detector = new BarcodeDetector({ formats: ['qr_code', 'code_128', 'ean_13', 'code_39'] });
            const tick = function () {
                if (!cameraStream) return;
                detector.detect(video).then(function (codes) {
                    if (codes[0] && codes[0].rawValue) {
                        applyScan(codes[0].rawValue);
                        stopCamera();
                        return;
                    }
                    cameraTimer = setTimeout(tick, 250);
                }).catch(function () { cameraTimer = setTimeout(tick, 400); });
            };
            video.onloadedmetadata = function () { video.play(); tick(); };
        }).catch(function () {
            showNote('تعذر فتح الكاميرا.');
        });
    });
    document.getElementById('posCats').addEventListener('click', function (e) {
        const btn = e.target.closest('.pos-chip');
        if (!btn) return;
        document.querySelectorAll('.pos-chip').forEach(function (el) { el.classList.remove('is-active'); });
        btn.classList.add('is-active');
        categoryId = btn.getAttribute('data-cat') || '';
        page = 1;
        renderGrid();
    });
    document.getElementById('posOrderTypes').addEventListener('click', function (e) {
        const btn = e.target.closest('[data-order]');
        if (!btn) return;
        document.querySelectorAll('#posOrderTypes [data-order]').forEach(function (el) { el.classList.remove('is-active'); });
        btn.classList.add('is-active');
        document.getElementById('posOrderType').value = btn.getAttribute('data-order');
    });
    document.getElementById('posMethods').addEventListener('click', function (e) {
        const btn = e.target.closest('.pos-method');
        if (!btn) return;
        document.querySelectorAll('#posMethods [data-method]').forEach(function (el) { el.classList.remove('is-active'); });
        btn.classList.add('is-active');
        method = btn.getAttribute('data-method');
        document.getElementById('posPaymentMethod').value = method;
    });
    document.getElementById('posPrev').addEventListener('click', function () { if (page > 1) { page -= 1; renderGrid(); } });
    document.getElementById('posNext').addEventListener('click', function () { page += 1; renderGrid(); });
    discountEl.addEventListener('input', renderTotals);
    document.getElementById('posClear').addEventListener('click', function () {
        cart.clear();
        discountEl.value = '0';
        renderAll();
    });
    document.getElementById('posCancel').addEventListener('click', function () {
        if (!cart.size) {
            closeTools();
            return;
        }
        closeTools(function () {
            document.getElementById('appConfirmModalTitle').textContent = 'هل تريد الاستمرار؟';
            document.getElementById('appConfirmModalMessage').textContent = 'هل أنت متأكد أنك تريد إلغاء الفاتورة الحالية؟';
            document.getElementById('appConfirmOkBtn').textContent = 'تأكيد';
            window.jQuery('#appConfirmModal').modal('show');
            window.jQuery('#appConfirmOkBtn').one('click.posCancel', function () {
                cart.clear();
                discountEl.value = '0';
                renderAll();
                window.jQuery('#appConfirmModal').modal('hide');
            });
        });
    });
    document.getElementById('posDrawer').addEventListener('click', function () {
        closeTools(function () { showNote('تم إرسال أمر فتح درج النقدية.'); });
    });
    document.getElementById('posReprint').addEventListener('click', function () {
        closeTools();
    });
    document.getElementById('posFreeze').addEventListener('click', function () {
        const frozen = frozenCart();
        if (cart.size === 0 && frozen) {
            discountEl.value = frozen.discount || '0';
            (frozen.items || []).forEach(function (row) {
                const product = products.find(function (p) { return p.id === row.id; });
                if (product) cart.set(product.id, { product: product, qty: row.qty });
            });
            localStorage.removeItem(freezeKey);
            renderAll();
            closeTools(function () { showNote('تم استرجاع الفاتورة المجمدة.'); });
            return;
        }
        if (!cart.size) {
            closeTools();
            return;
        }
        const items = [];
        cart.forEach(function (line) { items.push({ id: line.product.id, qty: line.qty }); });
        localStorage.setItem(freezeKey, JSON.stringify({ items: items, discount: discountEl.value }));
        cart.clear();
        discountEl.value = '0';
        renderAll();
        closeTools(function () { showNote('تم تجميد الفاتورة. يمكنك استرجاعها لاحقاً.'); });
    });
    form.addEventListener('submit', function (e) {
        const action = (e.submitter && e.submitter.getAttribute('data-action')) || 'pay';
        if (action === 'draft') closeTools();
        if (!shiftOpen || cart.size === 0) {
            e.preventDefault();
            showNote(shiftOpen ? 'أضف منتجاً قبل الدفع.' : 'افتح الوردية أولاً.');
            return;
        }
        fillItems(action);
    });

    renderAll();
    @if($lastPrint)
    document.querySelector('[data-print-url]')?.click();
    @endif
})();
</script>
@endsection

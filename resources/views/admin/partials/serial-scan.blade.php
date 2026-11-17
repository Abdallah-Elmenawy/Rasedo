@php
    $scanContext = $scanContext ?? 'sale';
    $scanPrice = $scanPrice ?? 'unit_price';
    $scanProducts = collect($products ?? [])->map(fn ($product) => [
        'id' => $product->id,
        'name' => $product->name,
        'price' => (float) ($scanPrice === 'unit_cost' ? $product->cost_price : $product->sale_price),
        'pieces' => (int) ($product->pieces_per_box ?: 0),
    ])->values();
@endphp
<div class="serial-scan" data-context="{{ $scanContext }}" data-table="{{ $itemsTableId ?? 'sale-items' }}" data-price="{{ $scanPrice }}" data-url="{{ route('products.scan') }}">
    <p class="serial-scan-note" hidden></p>
    <script type="application/json" class="serial-scan-products">@json($scanProducts)</script>
</div>
<script>
if (!window.serialScanBound) {
    window.serialScanBound = true;
    function scanNote(box, text) {
        var note = box.querySelector('.serial-scan-note');
        note.hidden = false;
        note.textContent = text;
        clearTimeout(box._noteTimer);
        box._noteTimer = setTimeout(function () { note.hidden = true; }, 4000);
    }
    window.lineUnitHtml = function () {
        return '<div class="line-unit" role="group" aria-label="وحدة البند"><button type="button" data-kind="piece" class="is-active">قطعة</button><button type="button" data-kind="box">علبة</button></div>';
    };
    function rowKind(row) {
        var active = row && row.querySelector('.line-unit .is-active');
        return active ? active.dataset.kind : 'piece';
    }
    function scanProducts(box) {
        if (!box._products) box._products = JSON.parse(box.querySelector('.serial-scan-products').textContent || '[]');
        return box._products;
    }
    function scanRows(box) {
        var table = document.getElementById(box.dataset.table);
        return table ? Array.prototype.slice.call(table.querySelectorAll('tbody tr')) : [];
    }
    function serialBox(row) {
        var cell = row.querySelector('td:nth-child(2)');
        var holder = cell.querySelector('.line-serials');
        if (!holder) {
            holder = document.createElement('div');
            holder.className = 'line-serials';
            cell.appendChild(holder);
        }
        return holder;
    }
    function markRow(box, row) {
        scanRows(box).forEach(function (item) { item.classList.remove('is-scan-line'); });
        row.classList.add('is-scan-line');
    }
    function addRow(box, product) {
        var table = document.getElementById(box.dataset.table);
        var tbody = table.querySelector('tbody');
        var index = tbody.rows.length;
        var tr = document.createElement('tr');
        var select = document.createElement('select');
        select.name = 'items[' + index + '][product_id]';
        select.className = 'form-control prod';
        scanProducts(box).forEach(function (item) {
            var option = document.createElement('option');
            option.value = item.id;
            option.dataset.price = item.price;
            option.dataset.pieces = item.pieces;
            option.textContent = item.name;
            if (String(item.id) === String(product.id)) option.selected = true;
            select.appendChild(option);
        });
        var productCell = document.createElement('td');
        productCell.appendChild(select);
        var lineScan = document.createElement('input');
        lineScan.type = 'text';
        lineScan.className = 'form-control line-scan';
        lineScan.placeholder = 'امسح السيريال';
        lineScan.setAttribute('autocomplete', 'off');
        productCell.appendChild(lineScan);
        var serials = document.createElement('div');
        serials.className = 'line-serials';
        productCell.appendChild(serials);
        var number = document.createElement('td');
        number.className = 'col-serial';
        number.textContent = String(index + 1);
        var unitCell = document.createElement('td');
        unitCell.className = 'col-unit';
        unitCell.innerHTML = window.lineUnitHtml();
        tr.appendChild(number);
        tr.appendChild(productCell);
        tr.appendChild(unitCell);
        ['qty', box.dataset.price, 'discount', 'tax'].forEach(function (field, i) {
            var td = document.createElement('td');
            var input = document.createElement('input');
            input.name = 'items[' + index + '][' + field + ']';
            input.type = 'number';
            input.step = field === 'qty' ? '0.001' : '0.01';
            input.className = 'form-control' + (i === 1 ? ' price' : '');
            input.value = field === 'qty' ? '1' : (field === box.dataset.price ? String(product.price || 0) : '0');
            td.appendChild(input);
            tr.appendChild(td);
        });
        var actions = document.createElement('td');
        actions.className = 'col-actions';
        actions.innerHTML = '<div class="btn-actions"><button type="button" class="btn btn-icon-action btn-icon-edit line-edit" title="تعديل البند"><i class="fe fe-edit-2"></i></button><button type="button" class="btn btn-icon-action btn-icon-delete del" title="حذف البند"><i class="fe fe-trash-2"></i></button></div>';
        tr.appendChild(actions);
        tbody.appendChild(tr);
        return tr;
    }
    function fillProduct(row, product) {
        var select = row.querySelector('select.prod');
        if (!select || !product) return;
        select.value = String(product.id);
        var price = row.querySelector('.price');
        if (price) price.value = product.price || 0;
    }
    function scanTarget(box, explicitRow, product) {
        var row = explicitRow || box.closest('form').querySelector('#' + box.dataset.table + ' tr.is-scan-line');
        if (!row) {
            var rows = scanRows(box);
            row = rows.length ? rows[rows.length - 1] : null;
        }
        if (!row) return addRow(box, product);
        var select = row.querySelector('select.prod');
        var hasSerials = serialBox(row).children.length > 0;
        if (product && hasSerials && select && String(select.value) !== String(product.id)) {
            return addRow(box, product);
        }
        if (product) fillProduct(row, product);
        return row;
    }
    function knownSerial(box, code) {
        return !!box.closest('form').querySelector('.line-serial input[value="' + CSS.escape(code) + '"]');
    }
    function addSerial(box, row, code, kind, pieces) {
        if (knownSerial(box, code)) {
            scanNote(box, 'هذا السيريال مضاف بالفعل.');
            return;
        }
        var holder = serialBox(row);
        var empty = holder.children.length === 0;
        var chip = document.createElement('span');
        chip.className = 'line-serial';
        chip.dataset.pieces = String(pieces);
        var index = row.rowIndex;
        var count = holder.children.length;
        chip.innerHTML = (kind === 'box' ? 'علبة ' : 'قطعة ') + code + ' <button type="button" class="line-serial-x" aria-label="حذف">×</button>';
        var codeInput = document.createElement('input');
        codeInput.type = 'hidden';
        codeInput.name = 'items[' + (index - 1) + '][serials][' + count + '][code]';
        codeInput.value = code;
        codeInput.setAttribute('value', code);
        var kindInput = document.createElement('input');
        kindInput.type = 'hidden';
        kindInput.name = 'items[' + (index - 1) + '][serials][' + count + '][kind]';
        kindInput.value = kind;
        chip.appendChild(codeInput);
        chip.appendChild(kindInput);
        holder.appendChild(chip);
        var qty = row.querySelector('input[name$="[qty]"]');
        var current = Number(qty.value) || 0;
        qty.value = String(empty && current <= 1 ? pieces : current + pieces);
        qty.dispatchEvent(new Event('input', { bubbles: true }));
        markRow(box, row);
        scanNote(box, (kind === 'box' ? 'علبة ' : 'قطعة ') + code + ' اتضافت.');
    }
    document.addEventListener('click', function (e) {
        var unit = e.target.closest('.line-unit button');
        if (unit) {
            unit.parentNode.querySelectorAll('button').forEach(function (button) { button.classList.remove('is-active'); });
            unit.classList.add('is-active');
            var row = unit.closest('tr');
            var scan = unit.closest('form') && unit.closest('form').querySelector('.serial-scan');
            if (scan && row) markRow(scan, row);
            return;
        }
        var remove = e.target.closest('.line-serial-x');
        if (!remove) return;
        var chip = remove.closest('.line-serial');
        var row = chip.closest('tr');
        var qty = row.querySelector('input[name$="[qty]"]');
        qty.value = String(Math.max(0, (Number(qty.value) || 0) - (Number(chip.dataset.pieces) || 1)));
        qty.dispatchEvent(new Event('input', { bubbles: true }));
        chip.remove();
    });
    document.addEventListener('keydown', function (e) {
        var input = e.target.closest('.serial-scan-input, .line-scan');
        if (!input || e.key !== 'Enter') return;
        e.preventDefault();
        var explicitRow = input.classList.contains('line-scan') ? input.closest('tr') : null;
        var box = input.closest('.serial-scan') || (input.closest('form') && input.closest('form').querySelector('.serial-scan'));
        if (!box) return;
        var code = input.value.trim();
        input.value = '';
        if (!code) return;
        var kind = 'piece';
        fetch(box.dataset.url + '?code=' + encodeURIComponent(code) + '&context=' + box.dataset.context, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (response) {
            return response.json().then(function (data) { return { ok: response.ok, data: data }; });
        }).then(function (result) {
            if (!result.ok) {
                scanNote(box, (result.data && result.data.message) || 'تعذر قراءة الكود.');
                return;
            }
            var data = result.data;
            if (data.mode === 'new_serial') {
                var active = explicitRow || box.closest('form').querySelector('#' + box.dataset.table + ' tr.is-scan-line') || scanRows(box).slice(-1)[0];
                if (!active || !active.querySelector('select.prod')) {
                    scanNote(box, 'اختار المنتج في البند، وبعدين امسح السيريال.');
                    return;
                }
                kind = rowKind(active);
                var selected = active.querySelector('select.prod').selectedOptions[0];
                var pieces = kind === 'box' ? Number(selected.dataset.pieces || 0) : 1;
                if (kind === 'box' && pieces < 2) {
                    scanNote(box, 'حدد عدد القطع داخل العلبة من بيانات المنتج.');
                    return;
                }
                addSerial(box, active, data.code, kind, pieces);
                return;
            }
            var product = scanProducts(box).find(function (item) { return String(item.id) === String(data.product.id); }) || {
                id: data.product.id, name: data.product.name, price: box.dataset.price === 'unit_cost' ? data.product.price : data.product.price, pieces: data.product.pieces_per_box || 0
            };
            var row = scanTarget(box, explicitRow, product);
            markRow(box, row);
            if (data.mode === 'serial') {
                addSerial(box, row, data.product.serial, data.kind, data.pieces || 1);
                var next = row.querySelector('.line-scan');
                if (next) next.focus();
                return;
            }
            scanNote(box, 'تم اختيار ' + data.product.name + '. امسح السيريال في نفس البند.');
            var lineScan = row.querySelector('.line-scan');
            if (lineScan) lineScan.focus();
        }).catch(function () {
            scanNote(box, 'تعذر قراءة الكود.');
        });
    });
    document.addEventListener('focusin', function (e) {
        if (!e.target.classList) return;
        if (e.target.classList.contains('serial-scan-input') || e.target.classList.contains('line-scan')) e.target.select();
        if (e.target.classList.contains('line-scan')) {
            var row = e.target.closest('tr');
            var scan = e.target.closest('form') && e.target.closest('form').querySelector('.serial-scan');
            if (scan && row) markRow(scan, row);
        }
    });
}
</script>

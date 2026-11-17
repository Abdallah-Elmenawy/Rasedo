@php
    $partyList = $formSuppliers ?? $suppliers;
    $loadedItems = $invoice->items;
    $oldItems = old('items', ($loadedItems && $loadedItems->isNotEmpty()) ? $loadedItems->toArray() : [['qty' => 1]]);
@endphp
<div class="row">
    <div class="col-md-4 form-group">
        <label for="{{ $fieldId ?? 'supplier_id' }}">المورد</label>
        <select id="{{ $fieldId ?? 'supplier_id' }}" name="supplier_id" class="form-control @error('supplier_id') is-invalid @enderror" required>
            <option value="">اختر المورد</option>
            @foreach($partyList as $s)
                <option value="{{ $s->id }}" @selected(old('supplier_id', $invoice->supplier_id) == $s->id)>{{ $s->name }}</option>
            @endforeach
        </select>
        @error('supplier_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4 form-group">
        <label>تاريخ الفاتورة</label>
        <input type="date" name="invoice_date" class="form-control @error('invoice_date') is-invalid @enderror" required value="{{ old('invoice_date', optional($invoice->invoice_date)->format('Y-m-d') ?? now()->toDateString()) }}">
        @error('invoice_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4 form-group">
        <label>تاريخ الاستحقاق</label>
        <input type="date" name="due_date" class="form-control" value="{{ old('due_date', optional($invoice->due_date)->format('Y-m-d')) }}">
    </div>
</div>
<div class="invoice-lines">
@include('admin.partials.serial-scan', ['scanContext' => 'purchase', 'scanPrice' => 'unit_cost', 'itemsTableId' => $itemsTableId ?? 'purchase-items'])
<div class="table-responsive">
    <table class="table" id="{{ $itemsTableId ?? 'purchase-items' }}">
        <thead>
            <tr>
                <th class="col-serial">#</th>
                <th>المنتج</th>
                <th class="col-unit">الوحدة</th>
                <th>الكمية</th>
                <th>السعر</th>
                <th>خصم</th>
                <th>ضريبة</th>
                <th class="col-actions">الإجراءات</th>
            </tr>
        </thead>
        <tbody>
        @foreach($oldItems as $i => $item)
            <tr>
                <td class="col-serial">{{ $loop->iteration }}</td>
                <td>
                    <select name="items[{{ $i }}][product_id]" class="form-control prod" required>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" data-price="{{ $p->cost_price }}" data-pieces="{{ (int) ($p->pieces_per_box ?: 0) }}" @selected(($item['product_id'] ?? null) == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                    <input type="text" class="form-control line-scan" placeholder="امسح السيريال" autocomplete="off">
                    <div class="line-serials">
                        @foreach(($item['serials'] ?? []) as $s => $serial)
                            @php $serialCode = is_array($serial) ? ($serial['code'] ?? '') : $serial; $serialKind = is_array($serial) && ($serial['kind'] ?? '') === 'box' ? 'box' : 'piece'; @endphp
                            @if($serialCode !== '')
                                <span class="line-serial" data-pieces="1">{{ $serialKind === 'box' ? 'علبة' : 'قطعة' }} {{ $serialCode }}
                                    <button type="button" class="line-serial-x" aria-label="حذف">×</button>
                                    <input type="hidden" name="items[{{ $i }}][serials][{{ $s }}][code]" value="{{ $serialCode }}">
                                    <input type="hidden" name="items[{{ $i }}][serials][{{ $s }}][kind]" value="{{ $serialKind }}">
                                </span>
                            @endif
                        @endforeach
                    </div>
                </td>
                <td class="col-unit">
                    <div class="line-unit" role="group" aria-label="وحدة البند">
                        <button type="button" data-kind="piece" class="is-active">قطعة</button>
                        <button type="button" data-kind="box">علبة</button>
                    </div>
                </td>
                <td><input name="items[{{ $i }}][qty]" class="form-control" type="number" step="0.001" value="{{ $item['qty'] ?? 1 }}"></td>
                <td><input name="items[{{ $i }}][unit_cost]" class="form-control price" type="number" step="0.01" value="{{ $item['unit_cost'] ?? $item['unit_price'] ?? 0 }}"></td>
                <td><input name="items[{{ $i }}][discount]" class="form-control" type="number" step="0.01" value="{{ $item['discount'] ?? 0 }}"></td>
                <td><input name="items[{{ $i }}][tax]" class="form-control" type="number" step="0.01" value="{{ $item['tax'] ?? 0 }}"></td>
                <td class="col-actions">
                    <div class="btn-actions">
                        <button type="button" class="btn btn-icon-action btn-icon-edit line-edit" title="تعديل البند"><i class="fe fe-edit-2"></i></button>
                        <button type="button" class="btn btn-icon-action btn-icon-delete del" title="حذف البند"><i class="fe fe-trash-2"></i></button>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<div class="invoice-lines-foot">
    <button type="button" class="btn btn-outline-primary js-add-item" data-table="{{ $itemsTableId ?? 'purchase-items' }}">إضافة بند</button>
</div>
</div>
<div class="row">
    <div class="col-md-4 form-group">
        <label>خصم الفاتورة</label>
        <input name="discount" type="number" step="0.01" class="form-control" value="{{ old('discount', $invoice->discount) }}">
    </div>
    <div class="col-md-8 form-group">
        <label>ملاحظات</label>
        <input name="notes" class="form-control" value="{{ old('notes', $invoice->notes) }}">
    </div>
</div>
<div class="row">
    <div class="col-md-8 form-group">
        <label for="purchase_attachment">مرفق الفاتورة</label>
        <x-file-input name="attachment" id="purchase_attachment" accept=".pdf,.jpg,.jpeg,.png,.webp" placeholder="اختر ملفاً (PDF أو صورة)" />
        <small class="text-muted d-block mt-1">PDF أو صورة. يتحفظ باسم رقم الفاتورة، ويمكن استبداله أو حذفه لاحقاً.</small>
        @error('attachment')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        @if($invoice->exists && $invoice->attachmentUrl())
            <div class="mt-2">
                <a href="{{ $invoice->attachmentUrl() }}" target="_blank" rel="noopener">المرفق الحالي: {{ $invoice->number }}</a>
                <label class="d-block mt-1 mb-0">
                    <input type="checkbox" name="remove_attachment" value="1" @checked(old('remove_attachment'))>
                    حذف المرفق
                </label>
            </div>
        @endif
    </div>
</div>
@include('admin.partials.invoice-payment-fields', ['paidLabel' => 'المبلغ المدفوع للمورد'])
@error('items')<div class="text-danger mb-2">{{ $message }}</div>@enderror

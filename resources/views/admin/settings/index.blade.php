@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', ['title' => 'الإعدادات'])
@endsection
@section('content')
@php
    $tabs = [
        'company' => 'الشركة',
        'customers' => 'العملاء',
        'invoices' => 'الفواتير',
        'accounts' => 'الحسابات',
    ];
    $activeTab = old('_settings_tab', session('settings_tab', 'company'));
    if (! isset($tabs[$activeTab])) {
        $activeTab = 'company';
    }
    $posted = old('_settings_tab') !== null;
    $field = function (string $name, $default = '') use ($setting) {
        return old($name, $setting->{$name} ?? $default);
    };
@endphp
<div class="settings-page">
    <div class="row">
        <div class="col-lg-3 col-md-4 mb-3">
            <div class="card settings-side-card">
                <div class="card-body p-3">
                    <button type="button" class="settings-nav-btn {{ $activeTab === 'company' ? 'active' : '' }}" data-settings-tab="company">
                        <i class="fe fe-briefcase"></i><span>الشركة</span>
                    </button>
                    <button type="button" class="settings-nav-btn {{ $activeTab === 'customers' ? 'active' : '' }}" data-settings-tab="customers">
                        <i class="fe fe-users"></i><span>العملاء</span>
                    </button>
                    <button type="button" class="settings-nav-btn {{ $activeTab === 'invoices' ? 'active' : '' }}" data-settings-tab="invoices">
                        <i class="fe fe-file-text"></i><span>الفواتير</span>
                    </button>
                    <button type="button" class="settings-nav-btn {{ $activeTab === 'accounts' ? 'active' : '' }}" data-settings-tab="accounts">
                        <i class="fe fe-book-open"></i><span>الحسابات</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="col-lg-9 col-md-8 mb-3">
            <div class="card settings-main-card">
                <div class="card-body p-4">
                    <form method="post" action="{{ route('settings.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_settings_tab" id="settingsTabInput" value="{{ $activeTab }}">

                        <div class="settings-panel {{ $activeTab === 'company' ? 'active' : '' }}" id="settings-panel-company">
                            <h5 class="settings-panel-title">الشركة</h5>
                            <p class="settings-panel-hint">بيانات المنشأة التي تظهر على الفواتير والإيصالات.</p>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>اسم الشركة</label>
                                    <input name="name" class="form-control @error('name') is-invalid @enderror" value="{{ $field('name') }}">
                                    @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>الاسم القانوني</label>
                                    <input name="legal_name" class="form-control @error('legal_name') is-invalid @enderror" value="{{ $field('legal_name') }}">
                                    @error('legal_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>البريد</label>
                                    <input name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ $field('email') }}">
                                    @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>الهاتف</label>
                                    <input name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ $field('phone') }}">
                                    @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>الرقم الضريبي</label>
                                    <input name="tax_number" class="form-control @error('tax_number') is-invalid @enderror" value="{{ $field('tax_number') }}">
                                    @error('tax_number')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>العملة</label>
                                    <input name="currency" class="form-control @error('currency') is-invalid @enderror" value="{{ $field('currency') }}">
                                    @error('currency')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>رمز العملة</label>
                                    <input name="currency_symbol" class="form-control @error('currency_symbol') is-invalid @enderror" value="{{ $field('currency_symbol') }}">
                                    @error('currency_symbol')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>اللغة</label>
                                    <select name="language" class="form-control @error('language') is-invalid @enderror">
                                        <option value="ar" @selected($field('language') === 'ar')>العربية</option>
                                        <option value="en" @selected($field('language') === 'en')>English</option>
                                    </select>
                                    @error('language')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-8 form-group">
                                    <label>العنوان</label>
                                    <input name="address" class="form-control @error('address') is-invalid @enderror" value="{{ $field('address') }}">
                                    @error('address')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>لوجو الشركة</label>
                                    <input name="logo" type="file" accept="image/*" class="form-control @error('logo') is-invalid @enderror">
                                    @error('logo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    @if(company_logo_url())
                                        <img src="{{ company_logo_url() }}" alt="اللوجو" class="settings-logo">
                                    @endif
                                </div>
                                <div class="col-md-12 form-group">
                                    <div class="text-muted small mb-2">اختار ما يظهر في الشريط الجانبي وعلى الفاتورة. تقدر تفعّل الاسم أو اللوجو أو الاتنين.</div>
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <label class="settings-check"><input type="checkbox" name="show_brand_name" value="1" @checked($posted ? (bool) old('show_brand_name') : ($setting->show_brand_name ?? true))> إظهار اسم الشركة</label>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label class="settings-check"><input type="checkbox" name="show_brand_logo" value="1" @checked($posted ? (bool) old('show_brand_logo') : ($setting->show_brand_logo ?? false))> إظهار اللوجو</label>
                                        </div>
                                    </div>
                                    @php $logoSide = old('brand_logo_side', $setting->brand_logo_side ?? 'left'); @endphp
                                    <div class="text-muted small mb-2">مكان اللوجو بالنسبة لاسم الشركة.</div>
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <label class="settings-check"><input type="radio" name="brand_logo_side" value="left" @checked($logoSide === 'left')> اللوجو شمال والاسم يمين</label>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label class="settings-check"><input type="radio" name="brand_logo_side" value="right" @checked($logoSide === 'right')> اللوجو يمين والاسم شمال</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="settings-panel {{ $activeTab === 'customers' ? 'active' : '' }}" id="settings-panel-customers">
                            <h5 class="settings-panel-title">العملاء</h5>
                            <p class="settings-panel-hint">حساب العملاء، التحصيل، والأسماء التي يختارها العميل عند الاستلام.</p>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>حساب العملاء</label>
                                    <select name="customers_account_id" class="form-control @error('customers_account_id') is-invalid @enderror">
                                        <option value="">—</option>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}" @selected((string) $field('customers_account_id') === (string) $account->id)>{{ $account->code }} — {{ $account->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('customers_account_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>بادئة التحصيل</label>
                                    <input name="receipt_prefix" class="form-control @error('receipt_prefix') is-invalid @enderror" value="{{ $field('receipt_prefix') }}">
                                    @error('receipt_prefix')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-12 form-group mb-1">
                                    <label class="mb-1">طريقة الاستلام</label>
                                    <div class="text-muted small">الاسم الذي يظهر في نقطة البيع وعلى الإيصال. اتركه عاماً لأي نشاط، أو غيّره لمطعم أو محل.</div>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>في المكان</label>
                                    <input name="receipt_order_in_store" class="form-control @error('receipt_order_in_store') is-invalid @enderror" value="{{ $field('receipt_order_in_store', 'من المحل') }}" maxlength="40">
                                    @error('receipt_order_in_store')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>استلام العميل</label>
                                    <input name="receipt_order_pickup" class="form-control @error('receipt_order_pickup') is-invalid @enderror" value="{{ $field('receipt_order_pickup', 'استلام') }}" maxlength="40">
                                    @error('receipt_order_pickup')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>التوصيل للعميل</label>
                                    <input name="receipt_order_delivery" class="form-control @error('receipt_order_delivery') is-invalid @enderror" value="{{ $field('receipt_order_delivery', 'توصيل') }}" maxlength="40">
                                    @error('receipt_order_delivery')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="settings-panel {{ $activeTab === 'invoices' ? 'active' : '' }}" id="settings-panel-invoices">
                            <h5 class="settings-panel-title">الفواتير</h5>
                            <p class="settings-panel-hint">شكل الطباعة، ترقيم المستندات، والضريبة والخصم.</p>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>نوع الطابعة</label>
                                    <select name="print_format" class="form-control @error('print_format') is-invalid @enderror">
                                        @foreach(\App\Support\InvoicePrintFormat::all() as $key => $format)
                                            <option value="{{ $key }}" @selected($field('print_format', 'a4') === $key)>{{ $format['label'] }} — {{ $format['paper'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('print_format')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>سطر المبيعات على الإيصال</label>
                                    <input name="receipt_sales_title" class="form-control @error('receipt_sales_title') is-invalid @enderror" value="{{ $field('receipt_sales_title', 'المبيعات') }}" maxlength="40">
                                    @error('receipt_sales_title')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                @foreach(['sale_prefix' => 'بادئة المبيعات', 'purchase_prefix' => 'بادئة المشتريات', 'payment_prefix' => 'بادئة الصرف', 'journal_prefix' => 'بادئة القيود'] as $name => $label)
                                    <div class="col-md-3 form-group">
                                        <label>{{ $label }}</label>
                                        <input name="{{ $name }}" class="form-control @error($name) is-invalid @enderror" value="{{ $field($name) }}">
                                        @error($name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                @endforeach
                                <div class="col-md-4 form-group">
                                    <label>نسبة الضريبة</label>
                                    <input name="default_tax_rate" class="form-control @error('default_tax_rate') is-invalid @enderror" value="{{ $field('default_tax_rate') }}">
                                    @error('default_tax_rate')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 form-group">
                                    <label class="settings-check"><input type="checkbox" name="tax_enabled" value="1" @checked($posted ? (bool) old('tax_enabled') : $setting->tax_enabled)> تفعيل الضريبة</label>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label class="settings-check"><input type="checkbox" name="discount_enabled" value="1" @checked($posted ? (bool) old('discount_enabled') : $setting->discount_enabled)> تفعيل الخصم</label>
                                </div>
                            </div>
                        </div>

                        <div class="settings-panel {{ $activeTab === 'accounts' ? 'active' : '' }}" id="settings-panel-accounts">
                            <h5 class="settings-panel-title">الحسابات</h5>
                            <p class="settings-panel-hint">ربط العمليات بدليل الحسابات. حساب العملاء موجود في بند العملاء.</p>
                            <div class="row">
                                @foreach([
                                    'sales_account_id' => 'حساب المبيعات',
                                    'purchases_account_id' => 'حساب المشتريات',
                                    'inventory_account_id' => 'حساب المخزون',
                                    'cogs_account_id' => 'تكلفة المبيعات',
                                    'suppliers_account_id' => 'حساب الموردين',
                                    'tax_account_id' => 'حساب الضريبة',
                                    'sales_discount_account_id' => 'خصم مسموح به',
                                ] as $name => $label)
                                    <div class="col-md-6 form-group">
                                        <label>{{ $label }}</label>
                                        <select name="{{ $name }}" class="form-control @error($name) is-invalid @enderror">
                                            <option value="">—</option>
                                            @foreach($accounts as $account)
                                                <option value="{{ $account->id }}" @selected((string) $field($name) === (string) $account->id)>{{ $account->code }} — {{ $account->name }}</option>
                                            @endforeach
                                        </select>
                                        @error($name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="form-actions">
                            <button class="btn btn-primary" type="submit">حفظ الإعدادات</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('js')
<script>
(function ($) {
    function showSettingsTab(tab) {
        $('.settings-nav-btn').removeClass('active');
        $('.settings-nav-btn[data-settings-tab="' + tab + '"]').addClass('active');
        $('.settings-panel').removeClass('active');
        $('#settings-panel-' + tab).addClass('active');
        $('#settingsTabInput').val(tab);
    }

    $(document).on('click', '.settings-nav-btn', function () {
        showSettingsTab($(this).data('settings-tab'));
    });

    var invalid = $('.settings-panel .is-invalid, .settings-panel .invalid-feedback').first();
    if (invalid.length) {
        showSettingsTab(invalid.closest('.settings-panel').attr('id').replace('settings-panel-', ''));
    }
})(window.jQuery);
</script>
@endsection

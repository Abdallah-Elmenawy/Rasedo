@php
    $brandName = optional(company())->name ?: 'Rasedo';
    $brandLogo = company_shows_logo() ? company_logo_url() : null;
@endphp
<span class="brand-mark {{ company_logo_on_right() ? 'is-logo-right' : 'is-logo-left' }}">
    @if($brandLogo)
        <img src="{{ $brandLogo }}" alt="{{ $brandName }}" class="brand-logo">
    @endif
    @if(company_shows_name())
        <span class="main-logo tx-20 font-weight-bold">{{ $brandName }}</span>
    @endif
</span>

@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', ['title' => 'الملف الشخصي'])
@endsection
@section('css')
<style>
    .profile-page .profile-side-card,
    .profile-page .profile-main-card {
        border-radius: 18px !important;
        border: none !important;
        box-shadow: 0 4px 20px rgba(45, 62, 80, 0.06) !important;
    }
    .profile-page .profile-label {
        display: block;
        text-align: right;
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 0.45rem;
        font-size: 15px;
    }
    .profile-page .profile-input {
        background: #F7FAFE !important;
        border: 1px solid #E6EEF8 !important;
        border-radius: 8px !important;
        height: 48px;
        padding: 0.65rem 1rem;
        color: #2D3E50;
        font-size: 14px;
        font-weight: 400;
        box-shadow: none !important;
    }
    .profile-page .profile-nav-btn {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 10px;
        width: 100%;
        padding: 0.9rem 1.1rem;
        margin-bottom: 0.75rem;
        border-radius: 12px;
        border: 1px solid #E5E7EB;
        background: #fff;
        color: #1F2937;
        font-weight: 600;
        font-size: 14px;
        text-align: right;
        transition: all 0.2s ease;
        cursor: pointer;
        outline: none !important;
        box-shadow: none;
    }
    .profile-page .profile-nav-btn:hover,
    .profile-page .profile-nav-btn:focus,
    .profile-page .profile-nav-btn:active {
        outline: none !important;
        box-shadow: none !important;
    }
    .profile-page .profile-nav-btn i {
        font-size: 18px;
        color: #6B7280;
    }
    .profile-page .profile-nav-btn:hover {
        border-color: transparent;
        background: rgba(107, 92, 231, 0.08);
        color: #1E4E9B;
    }
    .profile-page .profile-nav-btn:hover i {
        color: #1E4E9B;
    }
    .profile-page .profile-nav-btn.active,
    .profile-page .profile-nav-btn.active:hover,
    .profile-page .profile-nav-btn.active:focus,
    .profile-page .profile-nav-btn.active:active {
        background: linear-gradient(135deg, #5B92D4 0%, #1E4E9B 100%);
        border-color: transparent !important;
        color: #fff;
        outline: none !important;
        box-shadow: 0 6px 16px rgba(107, 92, 231, 0.28) !important;
    }
    .profile-page .profile-nav-btn.active i {
        color: #fff;
    }
    .profile-page .profile-avatar-wrap {
        text-align: center;
        margin-bottom: 2rem;
    }
    .profile-page .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        background: #EEF1F6;
        border: 4px solid #fff;
        box-shadow: 0 6px 18px rgba(45, 62, 80, 0.1);
        margin: 0 auto 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #1E4E9B;
        font-size: 42px;
        font-weight: 700;
        background-image: linear-gradient(135deg, rgba(74, 144, 226, 0.12), rgba(155, 81, 224, 0.18));
    }
    .profile-page .profile-avatar-wrap {
        padding-bottom: 1.75rem;
        border-bottom: 1px solid #EEF2F8;
    }
    .profile-page .profile-avatar-frame {
        position: relative;
        width: 120px;
        margin: 0 auto 0.75rem;
    }
    .profile-page .profile-avatar-frame .profile-avatar {
        margin: 0;
        transition: filter 0.2s ease, opacity 0.2s ease;
    }
    .profile-page .profile-avatar-cam {
        position: absolute;
        bottom: 4px;
        left: 4px;
        width: 36px;
        height: 36px;
        margin: 0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #5B92D4 0%, #1E4E9B 100%);
        color: #fff;
        border: 3px solid #fff;
        box-shadow: 0 4px 10px rgba(30, 78, 155, 0.3);
        cursor: pointer;
        font-size: 15px;
        transition: transform 0.15s ease;
    }
    .profile-page .profile-avatar-cam:hover { transform: scale(1.08); }
    .profile-page .profile-avatar-name {
        font-weight: 700;
        font-size: 16px;
        color: #1F2937;
    }
    .profile-page .profile-avatar-hint {
        font-size: 12px;
        color: #8A96A8;
        margin-top: 2px;
    }
    .profile-page .is-removing .profile-avatar { opacity: 0.55; filter: grayscale(1); }
    .profile-page .is-removing .profile-avatar-hint { color: #DC2626; font-weight: 600; }
    .profile-page .profile-avatar-actions {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 14px;
    }
    .profile-page .profile-avatar-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        height: 36px;
        padding: 0 16px;
        margin: 0;
        border-radius: 10px;
        border: 1px solid #DCE6F3;
        background: #fff;
        color: #4B5563;
        font-size: 13px !important;
        font-weight: 700;
        line-height: 1;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .profile-page .profile-avatar-btn:hover { background: #F7FAFE; }
    .profile-page .profile-avatar-btn.is-primary {
        background: #EEF4FC;
        border-color: #B9D2EE;
        color: #1E4E9B;
    }
    .profile-page .profile-avatar-btn.is-primary:hover { background: #E1ECF9; }
    .profile-page .profile-avatar-btn.is-danger {
        background: #FEF2F2;
        border-color: #FBD5D5;
        color: #DC2626;
    }
    .profile-page .profile-avatar-btn.is-danger:hover { background: #FDE5E5; }
    .profile-page .profile-input:focus {
        background: #fff !important;
        border: 1.5px solid #1E4E9B !important;
        box-shadow: 0 0 0 4px rgba(91, 146, 212, 0.22) !important;
        outline: none !important;
    }
    .profile-page .profile-actions {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-top: 1.5rem;
        width: 100%;
    }
    .profile-page .btn-profile-save {
        min-width: 150px;
        height: 46px;
        border-radius: 12px !important;
        font-weight: 700;
        border: none !important;
        color: #fff !important;
        background-image: linear-gradient(135deg, #5B92D4 0%, #1E4E9B 100%) !important;
        box-shadow: 0 6px 16px rgba(107, 92, 231, 0.28);
    }
    .profile-page .btn-profile-save:hover {
        filter: brightness(1.05);
        color: #fff !important;
    }
    .profile-page .btn-profile-back {
        min-width: 110px;
        height: 46px;
        border-radius: 12px !important;
        font-weight: 700;
        background: #fff !important;
        border: 1.5px solid #B7D4F2 !important;
        color: #1E4E9B !important;
    }
    .profile-page .btn-profile-back:hover {
        background: rgba(107, 92, 231, 0.08) !important;
        color: #173E7C !important;
    }
    .profile-page .profile-panel { display: none; }
    .profile-page .profile-panel.active { display: block; }
</style>
@endsection
@section('content')
@php
    $activeTab = old('_profile_tab', request('tab', 'basic'));
@endphp
<div class="profile-page">
    <div class="row">
        <div class="col-lg-3 col-md-4 mb-3">
            <div class="card profile-side-card">
                <div class="card-body p-3">
                    <button type="button" class="profile-nav-btn {{ $activeTab === 'basic' ? 'active' : '' }}" data-profile-tab="basic">
                        <i class="fe fe-settings"></i>
                        <span>البيانات الأساسية</span>
                    </button>
                    <button type="button" class="profile-nav-btn {{ $activeTab === 'password' ? 'active' : '' }}" data-profile-tab="password">
                        <i class="fe fe-edit-2"></i>
                        <span>تعديل كلمة السر</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="col-lg-9 col-md-8 mb-3">
            <div class="card profile-main-card">
                <div class="card-body p-4 p-md-5">
                    <div class="profile-panel {{ $activeTab === 'basic' ? 'active' : '' }}" id="profile-panel-basic">
                        <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="_profile_tab" value="basic">
                            <div class="profile-avatar-wrap" id="profileAvatarBox"
                                 data-original="{{ user_avatar($user) }}"
                                 data-default="{{ asset('assets/img/brand/avatar-default.svg') }}">
                                <div class="profile-avatar-frame">
                                    <img src="{{ user_avatar($user) }}" alt="{{ $user->name }}" class="profile-avatar" id="profileAvatarPreview">
                                    <label for="profileAvatar" class="profile-avatar-cam" title="تغيير الصورة"><i class="fe fe-camera"></i></label>
                                </div>
                                <div class="profile-avatar-name">{{ $user->name }}</div>
                                <div class="profile-avatar-hint" id="profileAvatarHint">الصيغ المسموحة: PNG ، JPG ، WEBP</div>
                                <input id="profileAvatar" name="avatar" type="file" accept="image/png,image/jpeg,image/webp,image/gif" class="d-none">
                                <input type="hidden" name="remove_avatar" value="0" id="profileAvatarRemove">
                                <div class="profile-avatar-actions">
                                    <label for="profileAvatar" class="profile-avatar-btn is-primary"><i class="fe fe-upload"></i> تغيير الصورة</label>
                                    <button type="button" class="profile-avatar-btn is-danger {{ $user->avatar ? '' : 'd-none' }}" id="profileAvatarDelete"><i class="fe fe-trash-2"></i> حذف</button>
                                    <button type="button" class="profile-avatar-btn d-none" id="profileAvatarUndo"><i class="fe fe-rotate-ccw"></i> تراجع</button>
                                </div>
                                @error('avatar')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="profile-label" for="name">الاسم</label>
                                    <input id="name" name="name" class="form-control profile-input @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                                    @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="profile-label" for="phone">رقم الهاتف</label>
                                    <input id="phone" name="phone" class="form-control profile-input @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" placeholder="رقم الهاتف">
                                    @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="profile-label" for="email">البريد الالكتروني</label>
                                    <input id="email" name="email" type="email" class="form-control profile-input @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                                    @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="profile-actions">
                                <button type="submit" class="btn btn-profile-save">حفظ التغييرات</button>
                                <a href="{{ route('dashboard') }}" class="btn btn-profile-back">رجوع</a>
                            </div>
                        </form>
                    </div>

                    <div class="profile-panel {{ $activeTab === 'password' ? 'active' : '' }}" id="profile-panel-password">
                        <form method="post" action="{{ route('profile.password') }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="_profile_tab" value="password">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="profile-label" for="current_password">كلمة المرور الحالية</label>
                                    <input id="current_password" name="current_password" type="password" class="form-control profile-input @error('current_password') is-invalid @enderror" required>
                                    @error('current_password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="profile-label" for="password">كلمة المرور الجديدة</label>
                                    <input id="password" name="password" type="password" class="form-control profile-input @error('password') is-invalid @enderror" required>
                                    @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="profile-label" for="password_confirmation">تأكيد كلمة المرور</label>
                                    <input id="password_confirmation" name="password_confirmation" type="password" class="form-control profile-input" required>
                                </div>
                            </div>
                            <div class="profile-actions">
                                <button type="submit" class="btn btn-profile-save">تغيير كلمة المرور</button>
                                <a href="{{ route('dashboard') }}" class="btn btn-profile-back">رجوع</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('js')
<script>
(function ($) {
    function showProfileTab(tab) {
        $('.profile-nav-btn').removeClass('active');
        $('.profile-nav-btn[data-profile-tab="' + tab + '"]').addClass('active');
        $('.profile-panel').removeClass('active');
        $('#profile-panel-' + tab).addClass('active');
    }

    $(document).on('click', '.profile-nav-btn', function () {
        showProfileTab($(this).data('profile-tab'));
    });

    var $box = $('#profileAvatarBox');
    var $hint = $('#profileAvatarHint');
    var hintText = $hint.text();
    var hasAvatar = !$('#profileAvatarDelete').hasClass('d-none');

    function setAvatarState(state, src) {
        $('#profileAvatarPreview').attr('src', src);
        $box.toggleClass('is-removing', state === 'removing');
        $('#profileAvatarRemove').val(state === 'removing' ? '1' : '0');
        $('#profileAvatarDelete').toggleClass('d-none', state !== 'original' || !hasAvatar);
        $('#profileAvatarUndo').toggleClass('d-none', state === 'original');
        $hint.text(state === 'removing' ? 'سيتم حذف الصورة عند الحفظ' : (state === 'new' ? 'صورة جديدة — اضغط حفظ للتطبيق' : hintText));
    }

    $(document).on('change', '#profileAvatar', function () {
        var file = this.files && this.files[0];
        if (!file) return;
        setAvatarState('new', URL.createObjectURL(file));
    });

    $(document).on('click', '#profileAvatarDelete', function () {
        $('#profileAvatar').val('');
        setAvatarState('removing', $box.data('default'));
    });

    $(document).on('click', '#profileAvatarUndo', function () {
        $('#profileAvatar').val('');
        setAvatarState('original', $box.data('original'));
    });
})(window.jQuery);
</script>
@endsection

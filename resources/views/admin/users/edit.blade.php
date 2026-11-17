@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', ['title' => 'تعديل المستخدم'])
@endsection
@section('css')
<style>
    .user-perms-board .perm-item { cursor: pointer; }
    .user-perms-board .perm-copy strong { font-size: 13px; }
    .user-perms-board .perm-tag {
        display: none; align-items: center; gap: 4px; flex-shrink: 0;
        padding: 3px 8px; border-radius: 999px; font-size: 11px; font-weight: 800;
    }
    .user-perms-board .perm-tag.is-role { background: #fff; border: 1px solid #D3E6F8; color: #1E4E9B; }
    .user-perms-board .perm-tag.is-extra { background: var(--b-gradient, linear-gradient(135deg, #5B92D4 0%, #1E4E9B 100%)); color: #fff; }
    .user-perms-board .perm-tag.is-revoked { background: #F4F7FB; border: 1px solid #E3E8F0; color: #6B7280; }
    .user-perms-board .perm-item[data-state="role"] .perm-tag.is-role,
    .user-perms-board .perm-item[data-state="extra"] .perm-tag.is-extra,
    .user-perms-board .perm-item[data-state="revoked"] .perm-tag.is-revoked { display: inline-flex; }
    .user-perms-board .perm-item[data-state="extra"] { border-color: #5B92D4; }
    .user-perms-board .perm-item[data-state="revoked"] { border: 1px dashed #D5DCE6; }
    .user-perms-board .perm-item[data-state="revoked"] .perm-copy strong { color: #6B7280; text-decoration: line-through; text-decoration-color: #B8C2D0; }
    .user-perms-board .perm-item:has(input:disabled) { cursor: not-allowed; }
    .user-perms:not(.is-admin-role) .perm-item:has(input:disabled) { opacity: .55; }
    .user-perms-board .perm-all.is-disabled { opacity: .45; cursor: not-allowed; }
    .user-perms-empty { margin: 1rem 0 0; text-align: center; color: #9CA3AF; font-weight: 700; }
    .user-edit-page .otp-check {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #F0F1F3;
        border-radius: 12px;
        padding: 0.85rem 1rem;
        min-height: 48px;
        font-weight: 700;
        color: #2D3E50;
        width: 100%;
    }
</style>
@endsection
@section('content')
<div class="user-edit-page">
    <div class="card">
        <div class="card-body p-4 p-md-5">
            <form method="post" action="{{ route('users.update', $user) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    @include('admin.users._avatar-field', ['user' => $user])
                </div>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label for="name">الاسم</label>
                        <input id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="username">اسم المستخدم</label>
                        <input id="username" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $user->username) }}">
                        @error('username')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="email">البريد الالكتروني</label>
                        <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                        @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="phone">الهاتف</label>
                        <input id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}">
                        @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="password">كلمة مرور جديدة</label>
                        <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" placeholder="اتركها فارغة للإبقاء على الحالية">
                        @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="role_id">الدور</label>
                        <select id="role_id" name="role_id" class="form-control @error('role_id') is-invalid @enderror">
                            @foreach($roles as $r)
                                @continue($r->id !== $user->role_id && ! \App\Support\PermissionCatalog::canAssignRole(auth()->user(), $r))
                                <option value="{{ $r->id }}" @selected(old('role_id', $user->role_id) == $r->id)>{{ $r->name }}</option>
                            @endforeach
                        </select>
                        @error('role_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="status">الحالة</label>
                        <select id="status" name="status" class="form-control">
                            @foreach(['active' => 'نشط', 'disabled' => 'معطّل', 'suspended' => 'موقوف'] as $k => $v)
                                <option value="{{ $k }}" @selected(old('status', $user->status) === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                        @error('status')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label>التحقق بخطوتين</label>
                        <label class="otp-check mb-0">
                            <input type="checkbox" name="two_factor_enabled" value="1" @checked(old('two_factor_enabled', $user->two_factor_enabled))>
                            تفعيل OTP
                        </label>
                    </div>
                </div>

                @include('admin.users._permissions', [
                    'selected' => old('permissions', $user->permissionIds()),
                ])

                <div class="form-actions mt-4">
                    <button type="submit" class="btn btn-primary">حفظ</button>
                    <a href="{{ route('users.index') }}" class="btn btn-light">رجوع</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@extends('layouts.master')
@php
    $statusLabels = ['active' => 'نشط', 'disabled' => 'معطّل', 'suspended' => 'موقوف'];
    $statusBadges = ['active' => 'success', 'disabled' => 'secondary', 'suspended' => 'danger'];
@endphp
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => $user->name,
        'subtitle' => 'مستخدم',
        'action' => '<a href="'.route('users.index').'" class="btn btn-light"><i class="fe fe-arrow-right ml-1"></i> كل المستخدمين</a>'
            .(can('users.update') && (! $user->isAdmin() || auth()->user()->isAdmin()) ? '<a href="'.route('users.edit', $user).'" class="btn btn-primary"><i class="fe fe-edit-2 ml-1"></i> تعديل</a>' : ''),
    ])
@endsection
@section('content')
<div class="card user-profile-card">
    <div class="card-body">
        <div class="user-profile-top">
            <img src="{{ user_avatar($user) }}" alt="{{ $user->name }}" class="user-profile-photo">
            <div class="user-profile-id">
                <h3>{{ $user->name }}</h3>
                <div class="user-profile-badges">
                    <span class="badge badge-light">{{ $user->role->name ?? 'بدون دور' }}</span>
                    <span class="badge badge-{{ $statusBadges[$user->status] ?? 'light' }}">{{ $statusLabels[$user->status] ?? $user->status }}</span>
                    <span class="badge badge-{{ $user->two_factor_enabled ? 'success' : 'light' }}">{{ $user->two_factor_enabled ? 'التحقق بخطوتين مفعّل' : 'بدون تحقق بخطوتين' }}</span>
                </div>
            </div>
        </div>

        <div class="user-profile-grid">
            <div><span>الاسم</span><strong>{{ $user->name }}</strong></div>
            <div><span>البريد</span><strong dir="ltr">{{ $user->email }}</strong></div>
            <div><span>الهاتف</span><strong dir="ltr">{{ $user->phone ?: '—' }}</strong></div>
            <div><span>الدور</span><strong>{{ $user->role->name ?? '—' }}</strong></div>
            <div><span>الحالة</span><strong>{{ $statusLabels[$user->status] ?? $user->status }}</strong></div>
            <div><span>التحقق بخطوتين</span><strong>{{ $user->two_factor_enabled ? 'مفعّل' : 'غير مفعّل' }}</strong></div>
            <div><span>آخر دخول</span><strong dir="ltr">{{ optional($user->last_login_at)->format('d-m-Y  H:i') ?: '—' }}</strong></div>
        </div>
    </div>
</div>
@endsection

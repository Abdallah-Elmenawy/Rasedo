<div class="user-avatar-field">
    <img src="{{ isset($user) ? user_avatar($user) : asset('assets/img/brand/avatar-default.svg') }}" alt="صورة المستخدم" class="user-avatar-preview">
    <div class="flex-fill">
        <label class="mb-1">الصورة الشخصية</label>
        <input type="file" name="avatar" accept="image/png,image/jpeg,image/webp,image/gif" class="form-control user-avatar-input">
        <div class="text-muted small mt-1">تظهر في الشريط الجانبي وأعلى الصفحة لهذا المستخدم.</div>
        @if(! empty($user?->avatar))
            <label class="mt-2 mb-0 d-block"><input type="checkbox" name="remove_avatar" value="1"> حذف الصورة والرجوع للافتراضية</label>
        @endif
        @error('avatar')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
</div>
<script>
document.querySelectorAll('.user-avatar-input').forEach(function (input) {
    if (input.dataset.bound) return;
    input.dataset.bound = '1';
    input.addEventListener('change', function () {
        var file = this.files && this.files[0];
        var img = this.closest('.user-avatar-field')?.querySelector('.user-avatar-preview');
        if (!file || !img) return;
        img.src = URL.createObjectURL(file);
    });
});
</script>

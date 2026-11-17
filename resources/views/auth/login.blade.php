@extends('layouts.master2')

@section('content')
    @php
        $companyName = optional(company())->name ?? 'Rasedo';
    @endphp

    <div class="auth-screen">
        <div class="auth-card">
            <aside class="auth-brand">
                <div class="auth-brand-mark">
                    <span>{{ $companyName }}</span>
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="currentColor" d="M7 3h7l5 5v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1zm7 1.5V9h4.5L14 4.5zM8 12h8v1.4H8V12zm0 3h8v1.4H8V15zm0 3h5v1.4H8V18z"/>
                    </svg>
                </div>
                <h2>{{ $companyName }}</h2>
                <p>نظام محاسبي</p>
                <div class="auth-pills">
                    <span>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2 4.5 5v6.2c0 5 3.2 9.6 7.5 10.8 4.3-1.2 7.5-5.8 7.5-10.8V5L12 2zm-1.1 13.2-3.2-3.2 1.3-1.3 1.9 1.9 4.1-4.1 1.3 1.3-5.4 5.4z"/></svg>
                        آمن ومحمي
                    </span>
                    <span>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M13 2 4 14h7l-1 8 10-14h-7l0-6z"/></svg>
                        سريع وفعال
                    </span>
                </div>
            </aside>

            <section class="auth-panel">
                <x-auth-session-status class="auth-status" :status="session('status')" />

                <h2>مرحبا لعوتك لبرنامج حساباتك !</h2>
                <p class="auth-lead">من فضلك سجل الدخول للمتابعة.</p>

                <form method="POST" action="{{ route('login') }}" novalidate>
                    @csrf

                    <div class="auth-field">
                        <label for="email">البريد أو اسم المستخدم</label>
                        <div class="auth-control">
                            <input id="email" class="form-control" placeholder="اسم المستخدم أو البريد الإلكتروني" type="text"
                                name="email" value="{{ old('email') }}" autofocus autocomplete="username">
                            <svg class="auth-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill="currentColor" d="M3 6.5A2.5 2.5 0 0 1 5.5 4h13A2.5 2.5 0 0 1 21 6.5v11a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 17.5v-11zm1.6.3 7.4 4.7 7.4-4.7a1 1 0 0 0-1-.8h-13a1 1 0 0 0-.8.8z"/>
                            </svg>
                            <p class="auth-required" hidden>
                                <span>من فضلك املأ هذا الحقل.</span>
                                <i aria-hidden="true">!</i>
                            </p>
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="auth-error" />
                    </div>

                    <div class="auth-field is-secret">
                        <label for="password">كلمة المرور</label>
                        <div class="auth-control">
                            <input id="password" class="form-control" placeholder="كلمة المرور" type="password"
                                name="password" autocomplete="current-password">
                            <svg class="auth-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill="currentColor" d="M17 9h-1V7a4 4 0 0 0-8 0v2H7a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2zm-3 0h-4V7a2 2 0 1 1 4 0v2z"/>
                            </svg>
                            <button type="button" class="auth-eye" id="togglePassword" aria-label="إظهار كلمة المرور">
                                <svg class="is-on" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill="currentColor" d="M12 5c5.5 0 9.5 4.4 10.8 6.3a1.2 1.2 0 0 1 0 1.4C21.5 14.6 17.5 19 12 19S2.5 14.6 1.2 12.7a1.2 1.2 0 0 1 0-1.4C2.5 9.4 6.5 5 12 5zm0 2C8.2 7 5 10.2 3.5 12 5 13.8 8.2 17 12 17s7-3.2 8.5-5C19 10.2 15.8 7 12 7zm0 2.2A2.8 2.8 0 1 1 12 15a2.8 2.8 0 0 1 0-5.6z"/>
                                </svg>
                                <svg class="is-off" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill="currentColor" d="M12 5c5.5 0 9.5 4.4 10.8 6.3a1.2 1.2 0 0 1 0 1.4C21.5 14.6 17.5 19 12 19S2.5 14.6 1.2 12.7a1.2 1.2 0 0 1 0-1.4C2.5 9.4 6.5 5 12 5zm0 2C8.2 7 5 10.2 3.5 12 5 13.8 8.2 17 12 17s7-3.2 8.5-5C19 10.2 15.8 7 12 7zm0 2.2A2.8 2.8 0 1 1 12 15a2.8 2.8 0 0 1 0-5.6z"/>
                                    <path fill="none" stroke="#f3f5fb" stroke-width="3.6" stroke-linecap="round" d="M4.2 19.2 19.2 4.2"/>
                                    <path fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" d="M4.2 19.2 19.2 4.2"/>
                                </svg>
                            </button>
                            <p class="auth-required" hidden>
                                <span>من فضلك املأ هذا الحقل.</span>
                                <i aria-hidden="true">!</i>
                            </p>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="auth-error" />
                    </div>

                    <label class="auth-remember" for="remember_me">
                        <input type="checkbox" name="remember" id="remember_me">
                        <span>تذكرني</span>
                    </label>

                    <button type="submit" class="auth-submit">
                        <span>سجل الدخول</span>
                        <svg class="auth-spin" viewBox="0 0 128 128" aria-hidden="true">
                            <g fill="currentColor">
                                <path d="M40.73 13.1l-4.04-7A63.67 63.67 0 0 1 61.5.06v8a55.83 55.83 0 0 0-20.77 5.05z" opacity="1"/>
                                <path d="M40.73 13.1l-4.04-7A63.67 63.67 0 0 1 61.5.06v8a55.83 55.83 0 0 0-20.77 5.05z" opacity=".08" transform="rotate(30 64 64)"/>
                                <path d="M40.73 13.1l-4.04-7A63.67 63.67 0 0 1 61.5.06v8a55.83 55.83 0 0 0-20.77 5.05z" opacity=".16" transform="rotate(60 64 64)"/>
                                <path d="M40.73 13.1l-4.04-7A63.67 63.67 0 0 1 61.5.06v8a55.83 55.83 0 0 0-20.77 5.05z" opacity=".24" transform="rotate(90 64 64)"/>
                                <path d="M40.73 13.1l-4.04-7A63.67 63.67 0 0 1 61.5.06v8a55.83 55.83 0 0 0-20.77 5.05z" opacity=".32" transform="rotate(120 64 64)"/>
                                <path d="M40.73 13.1l-4.04-7A63.67 63.67 0 0 1 61.5.06v8a55.83 55.83 0 0 0-20.77 5.05z" opacity=".4" transform="rotate(150 64 64)"/>
                                <path d="M40.73 13.1l-4.04-7A63.67 63.67 0 0 1 61.5.06v8a55.83 55.83 0 0 0-20.77 5.05z" opacity=".5" transform="rotate(180 64 64)"/>
                                <path d="M40.73 13.1l-4.04-7A63.67 63.67 0 0 1 61.5.06v8a55.83 55.83 0 0 0-20.77 5.05z" opacity=".6" transform="rotate(210 64 64)"/>
                                <path d="M40.73 13.1l-4.04-7A63.67 63.67 0 0 1 61.5.06v8a55.83 55.83 0 0 0-20.77 5.05z" opacity=".7" transform="rotate(240 64 64)"/>
                                <path d="M40.73 13.1l-4.04-7A63.67 63.67 0 0 1 61.5.06v8a55.83 55.83 0 0 0-20.77 5.05z" opacity=".8" transform="rotate(270 64 64)"/>
                                <path d="M40.73 13.1l-4.04-7A63.67 63.67 0 0 1 61.5.06v8a55.83 55.83 0 0 0-20.77 5.05z" opacity=".9" transform="rotate(300 64 64)"/>
                                <path d="M40.73 13.1l-4.04-7A63.67 63.67 0 0 1 61.5.06v8a55.83 55.83 0 0 0-20.77 5.05z" opacity=".96" transform="rotate(330 64 64)"/>
                            </g>
                        </svg>
                        <svg class="auth-check" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5 9.2 16.8 19 7"/></svg>
                    </button>
                </form>

                <div class="auth-links">
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}">نسيت كلمة المرور؟</a>
                    @endif
                </div>
            </section>
        </div>
    </div>
@endsection

@section('js')
    <script>
        document.getElementById('togglePassword')?.addEventListener('click', function () {
            var input = document.getElementById('password');
            if (!input) return;
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            this.classList.toggle('is-revealed', show);
            this.setAttribute('aria-label', show ? 'إخفاء كلمة المرور' : 'إظهار كلمة المرور');
        });

        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                window.location.reload();
            }
        });

        document.querySelector('.auth-panel form')?.addEventListener('submit', function (event) {
            var form = this;
            var button = form.querySelector('.auth-submit');
            var fields = [document.getElementById('email'), document.getElementById('password')];
            var empty = null;
            fields.forEach(function (input) {
                var bubble = input && input.closest('.auth-control').querySelector('.auth-required');
                if (bubble) bubble.hidden = true;
                if (!empty && input && !input.value.trim()) empty = input;
            });
            if (empty) {
                event.preventDefault();
                var bubble = empty.closest('.auth-control').querySelector('.auth-required');
                if (bubble) bubble.hidden = false;
                empty.focus();
                return;
            }
            if (!button || button.classList.contains('is-loading')) return;
            button.classList.add('is-loading');
            button.disabled = true;
        });

        ['email', 'password'].forEach(function (id) {
            var input = document.getElementById(id);
            if (!input) return;
            input.addEventListener('input', function () {
                var bubble = input.closest('.auth-control').querySelector('.auth-required');
                if (bubble && input.value.trim()) bubble.hidden = true;
            });
        });
    </script>
@endsection

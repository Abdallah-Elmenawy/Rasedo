@extends('layouts.master2')

@section('content')
    @php
        $user = auth()->user();
        $secondsLeft = 60;
        if ($user?->expires_at) {
            $secondsLeft = max(0, $user->expires_at->getTimestamp() - now()->getTimestamp());
        }
        $oldCode = preg_replace('/\D/', '', (string) old('code'));
    @endphp

    <div class="auth-screen">
        <div class="auth-card is-plain">
            <section class="auth-panel">
                <x-auth-session-status class="auth-status" :status="session('status')" />

                <h2>رمز التحقق</h2>
                <p class="auth-lead">أدخل رمز التحقق المرسل إلى بريدك. الرمز صالح لمدة 60 ثانية.</p>

                <form method="POST" action="{{ route('verify.store') }}" id="verifyForm">
                    @csrf
                    <input type="hidden" name="code" id="code" value="{{ $oldCode }}">

                    <div class="auth-otp" dir="ltr">
                        @for ($i = 0; $i < 4; $i++)
                            <input class="auth-otp-box" type="text" inputmode="numeric" maxlength="1" autocomplete="one-time-code"
                                aria-label="الخانة {{ $i + 1 }}" value="{{ $oldCode[$i] ?? '' }}" @if ($i === 0) autofocus @endif>
                        @endfor
                    </div>

                    <div class="auth-timer" id="cd">{{ sprintf('%02d:%02d', intdiv($secondsLeft, 60), $secondsLeft % 60) }}</div>
                    <x-input-error :messages="$errors->get('code')" class="auth-error" />
                    <p class="auth-error" id="verifyError" hidden></p>

                    <button type="submit" class="auth-submit">
                        <span>تأكيد</span>
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

                <form method="POST" action="{{ route('verify.resend') }}" class="auth-resend-wrap">
                    @csrf
                    <button type="submit" id="resendBtn" class="auth-resend" @disabled($secondsLeft > 0)>إعادة الإرسال</button>
                </form>
            </section>
        </div>
    </div>
@endsection

@section('js')
    <script>
        (function () {
            var boxes = Array.prototype.slice.call(document.querySelectorAll('.auth-otp-box'));
            var hidden = document.getElementById('code');
            var form = document.getElementById('verifyForm');
            var seconds = {{ (int) $secondsLeft }};
            var label = document.getElementById('cd');
            var resend = document.getElementById('resendBtn');

            function sync() {
                hidden.value = boxes.map(function (box) { return box.value.replace(/\D/g, ''); }).join('');
            }

            function paint(value) {
                var digits = String(value || '').replace(/\D/g, '').slice(0, 4).split('');
                boxes.forEach(function (box, index) {
                    box.value = digits[index] || '';
                });
                sync();
            }

            boxes.forEach(function (box, index) {
                box.addEventListener('input', function () {
                    var digit = box.value.replace(/\D/g, '');
                    if (digit.length > 1) {
                        paint(boxes.slice(0, index).map(function (item) { return item.value; }).join('') + digit);
                        var next = boxes[Math.min(index + digit.length, boxes.length - 1)];
                        if (next) next.focus();
                        return;
                    }
                    box.value = digit;
                    sync();
                    if (digit && boxes[index + 1]) boxes[index + 1].focus();
                });

                box.addEventListener('keydown', function (event) {
                    if (event.key === 'Backspace' && !box.value && boxes[index - 1]) {
                        boxes[index - 1].focus();
                        boxes[index - 1].value = '';
                        sync();
                    }
                });

                box.addEventListener('paste', function (event) {
                    event.preventDefault();
                    var text = (event.clipboardData || window.clipboardData).getData('text');
                    paint(text);
                    var filled = Math.min(text.replace(/\D/g, '').length, boxes.length) - 1;
                    if (boxes[Math.max(filled, 0)]) boxes[Math.max(filled, 0)].focus();
                });
            });

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                sync();
                var button = form.querySelector('.auth-submit');
                var error = document.getElementById('verifyError');
                if (hidden.value.length < 4) {
                    boxes[0].focus();
                    return;
                }
                if (!button || button.classList.contains('is-loading') || button.classList.contains('is-done')) return;
                if (error) {
                    error.hidden = true;
                    error.textContent = '';
                }
                button.classList.remove('is-done');
                button.classList.add('is-loading');
                button.disabled = true;
                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new FormData(form)
                }).then(function (response) {
                    return response.json().catch(function () { return {}; }).then(function (data) {
                        return { ok: response.ok, data: data };
                    });
                }).then(function (result) {
                    if (!result.ok) {
                        button.classList.remove('is-loading');
                        button.disabled = false;
                        if (error) {
                            error.hidden = false;
                            error.textContent = (result.data && result.data.message) || 'رمز التحقق غير صحيح.';
                        }
                        return;
                    }
                    button.classList.remove('is-loading');
                    button.classList.add('is-done');
                    button.disabled = true;
                    if (window.showAppToast) {
                        window.showAppToast('success', (result.data && result.data.message) || 'تم تسجيل الدخول بنجاح');
                    }
                    window.setTimeout(function () {
                        window.location.href = (result.data && result.data.redirect) || '/dashboard';
                    }, 900);
                }).catch(function () {
                    button.classList.remove('is-loading');
                    button.disabled = false;
                    if (error) {
                        error.hidden = false;
                        error.textContent = 'تعذر إتمام الدخول. حاول مرة أخرى.';
                    }
                });
            });

            function clock(value) {
                var minutes = Math.floor(value / 60);
                var rest = value % 60;
                return String(minutes).padStart(2, '0') + ':' + String(rest).padStart(2, '0');
            }

            function tick() {
                label.textContent = clock(seconds);
                if (seconds <= 0) {
                    resend.disabled = false;
                    return;
                }
                resend.disabled = true;
                seconds -= 1;
                window.setTimeout(tick, 1000);
            }

            tick();
        })();
    </script>
@endsection

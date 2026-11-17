<?php

namespace App\Http\Controllers;

use App\Notifications\TwoFoctorCode;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class TwoFactorController extends Controller
{
    public function index()
    {
        return view('auth.verify');
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }
        if ($user->isOtpLocked()) {
            return $this->otpError($request, 'تم إيقاف التحقق مؤقتاً لمدة 5 دقائق.');
        }
        if ($user->expires_at && $user->expires_at->isPast()) {
            return $this->otpError($request, 'انتهت صلاحية رمز التحقق. أعد إرساله.');
        }
        if ($request->input('code') == $user->code) {
            $user->resetCode();
            $user->forceFill(['last_login_at' => now()])->save();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'تم تسجيل الدخول بنجاح',
                    'redirect' => home_route(),
                ]);
            }

            return redirect(home_route())->with('success', 'تم تسجيل الدخول بنجاح');
        }
        $user->increment('otp_attempts');
        if ($user->otp_attempts >= 3) {
            $user->forceFill(['otp_locked_until' => now()->addMinutes(5)])->save();

            return $this->otpError($request, 'تجاوزت عدد المحاولات. حاول بعد 5 دقائق.');
        }

        return $this->otpError($request, 'رمز التحقق غير صحيح.');
    }

    private function otpError(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 422);
        }

        return back()->withErrors(['code' => $message]);
    }

    public function resend()
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }
        if ($user->expires_at && $user->expires_at->isFuture()) {
            return back()->withErrors(['code' => 'انتظر انتهاء العداد قبل إعادة الإرسال.']);
        }
        $user->generateCode();
        $user->notify(new TwoFoctorCode);

        return back()->with('status', 'تم إعادة إرسال رمز التحقق.');
    }
}

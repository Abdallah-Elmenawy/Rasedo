<?php

namespace App\Http\Controllers\Admin\PublicSettings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PublicSettings\UpdatePasswordRequest;
use App\Http\Requests\Admin\PublicSettings\UpdateProfileRequest;
use App\Services\PublicSettings\UserService;

class ProfileController extends Controller
{
    public function __construct(private readonly UserService $users) {}

    public function index()
    {
        return view('admin.profile', ['user' => auth()->user()]);
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $data = $request->safe()->except(['avatar', 'remove_avatar']);
        if ($request->hasFile('avatar')) {
            $data['avatar'] = $this->users->replaceAvatar($user, $request->file('avatar'));
        } elseif ($request->boolean('remove_avatar')) {
            $this->users->clearAvatar($user);
            $data['avatar'] = null;
        }
        $this->users->updateProfile($user, $data);

        return back()->with('success', 'تم تحديث الملف الشخصي.');
    }

    public function password(UpdatePasswordRequest $request)
    {
        $this->users->changePassword($request->user(), $request->password);

        return redirect()->route('profile.edit', ['tab' => 'password'])->with('success', 'تم تغيير كلمة المرور.');
    }
}

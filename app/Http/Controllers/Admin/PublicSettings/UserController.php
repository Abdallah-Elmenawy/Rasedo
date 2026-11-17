<?php

namespace App\Http\Controllers\Admin\PublicSettings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PublicSettings\StoreUserRequest;
use App\Http\Requests\Admin\PublicSettings\UpdateUserRequest;
use App\Models\AllUsers\User;
use App\Models\PublicSettings\ActivityLog;
use App\Services\PublicSettings\UserService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private readonly UserService $users) {}

    public function index()
    {
        return view('admin.users.index', $this->users->page());
    }

    public function show(User $user)
    {
        $user->load('role');

        return view('admin.users.show', ['user' => $user]);
    }

    public function edit(User $user)
    {
        $this->ensureManageable($user);

        return view('admin.users.edit', $this->users->editor($user));
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->safe()->except(['avatar']);
        $user = $this->users->create($data, $request->boolean('two_factor_enabled', true));
        if ($request->hasFile('avatar')) {
            $user->update(['avatar' => $this->users->replaceAvatar($user, $request->file('avatar'))]);
        }

        return back()->with('success', 'تم إضافة المستخدم.');
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->ensureManageable($user);
        $data = $request->safe()->except(['avatar', 'remove_avatar']);
        $this->applyAvatar($request, $user, $data);
        $this->users->update(
            $user,
            $data,
            $request->boolean('two_factor_enabled'),
            $request->input('permissions', []),
        );

        return redirect()->route('users.show', $user)->with('success', 'تم تحديث المستخدم.');
    }

    public function updateAvatar(Request $request, User $user)
    {
        $this->ensureManageable($user);
        $request->validate([
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'remove_avatar' => ['nullable', 'boolean'],
        ]);
        $this->applyAvatar($request, $user);
        ActivityLog::record('users', 'update', "تحديث صورة المستخدم {$user->name}", $user);

        return back()->with('success', 'تم تحديث صورة المستخدم.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'لا يمكنك حذف حسابك الحالي.');
        }
        $this->ensureManageable($user);

        try {
            $name = $user->name;
            $user->delete();
            ActivityLog::record('users', 'delete', "حذف مستخدم {$name}");
        } catch (QueryException) {
            return back()->with('error', 'لا يمكن حذف المستخدم لارتباطه بعمليات في النظام.');
        }

        return back()->with('success', 'تم حذف المستخدم.');
    }

    private function ensureManageable(User $user): void
    {
        abort_if($user->isAdmin() && ! auth()->user()?->isAdmin(), 403, 'حساب مدير النظام يعدّله مدير النظام فقط.');
    }

    private function applyAvatar(Request $request, User $user, ?array &$data = null): void
    {
        if ($request->hasFile('avatar')) {
            $path = $this->users->replaceAvatar($user, $request->file('avatar'));
        } elseif ($request->boolean('remove_avatar')) {
            $this->users->clearAvatar($user);
            $path = null;
        } else {
            return;
        }

        if ($data !== null) {
            $data['avatar'] = $path;

            return;
        }

        $user->update(['avatar' => $path]);
    }
}

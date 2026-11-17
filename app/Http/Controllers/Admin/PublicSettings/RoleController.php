<?php

namespace App\Http\Controllers\Admin\PublicSettings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PublicSettings\StoreRoleRequest;
use App\Models\PublicSettings\Role;
use App\Services\PublicSettings\RoleService;
use RuntimeException;

class RoleController extends Controller
{
    public function __construct(private readonly RoleService $roles) {}

    public function index()
    {
        return view('admin.roles.index', $this->roles->page());
    }

    public function edit(Role $role)
    {
        return view('admin.roles.edit', $this->roles->editor($role));
    }

    public function store(StoreRoleRequest $request)
    {
        $this->roles->create(
            $request->safe()->only(['name', 'description']),
            $request->input('permissions', []),
            $request->input('permission_names', []),
        );

        return back()->with('success', 'تم إنشاء الدور. يمكنك الآن إنشاء مستخدم بهذا الدور.');
    }

    public function update(StoreRoleRequest $request, Role $role)
    {
        $this->roles->update(
            $role,
            $request->safe()->only(['name', 'description']),
            $request->input('permissions', []),
            $request->input('permission_names', []),
        );

        return redirect()->route('roles.index')->with('success', 'تم تحديث الدور.');
    }

    public function destroy(Role $role)
    {
        try {
            $this->roles->delete($role);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم حذف الدور.');
    }
}

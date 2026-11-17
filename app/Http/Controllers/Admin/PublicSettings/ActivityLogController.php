<?php

namespace App\Http\Controllers\Admin\PublicSettings;

use App\Http\Controllers\Controller;
use App\Models\PublicSettings\ActivityLog;
use App\Repositories\PublicSettings\Contracts\ActivityLogRepositoryInterface;
use App\Support\GridQuery;

class ActivityLogController extends Controller
{
    public function __construct(private readonly ActivityLogRepositoryInterface $logs) {}

    public function index()
    {
        $modules = [
            'customers' => 'العملاء',
            'suppliers' => 'الموردون',
            'products' => 'المنتجات',
            'sales' => 'المبيعات',
            'purchases' => 'المشتريات',
            'treasury' => 'الخزينة',
            'expenses' => 'المصروفات',
            'inventory' => 'المخزون',
            'users' => 'المستخدمون',
            'accounting' => 'الحسابات',
        ];
        $actions = [
            'create' => 'إضافة',
            'update' => 'تعديل',
            'delete' => 'حذف',
            'confirm' => 'تأكيد',
            'receipt' => 'تحصيل',
            'payment' => 'صرف',
            'transfer' => 'تحويل',
            'return' => 'مرتجع',
            'in' => 'إضافة مخزون',
            'out' => 'صرف مخزون',
            'adjust' => 'تسوية',
            'expense' => 'مصروف',
            'revenue' => 'إيراد',
        ];

        return view('admin.logs.index', [
            'logs' => $this->logs->paginateLatest(),
            'filters' => [
                'modules' => GridQuery::labeled(ActivityLog::query(), 'module', $modules),
                'actions' => GridQuery::labeled(ActivityLog::query(), 'action', $actions),
                'users' => GridQuery::users(ActivityLog::query(), 'user_id'),
            ],
        ]);
    }
}

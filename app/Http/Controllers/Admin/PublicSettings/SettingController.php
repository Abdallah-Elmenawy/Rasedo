<?php

namespace App\Http\Controllers\Admin\PublicSettings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PublicSettings\UpdateSettingRequest;
use App\Services\PublicSettings\SettingService;
use Illuminate\Http\UploadedFile;

class SettingController extends Controller
{
    public function __construct(private readonly SettingService $settings) {}

    public function index()
    {
        return view('admin.settings.index', $this->settings->page());
    }

    public function update(UpdateSettingRequest $request)
    {
        $data = $request->safe()->except(['logo']);
        $data['show_brand_name'] = $request->boolean('show_brand_name');
        $data['show_brand_logo'] = $request->boolean('show_brand_logo');
        if ($request->hasFile('logo')) {
            $data['logo'] = $this->storeCompanyImage($request->file('logo'), 'company-logo');
        }
        $this->settings->update(
            $data,
            $request->boolean('tax_enabled'),
            $request->boolean('discount_enabled'),
        );

        $tab = $request->input('_settings_tab', 'company');
        if (! in_array($tab, ['company', 'customers', 'invoices', 'accounts'], true)) {
            $tab = 'company';
        }

        return back()->with('success', 'تم حفظ الإعدادات.')->with('settings_tab', $tab);
    }

    private function storeCompanyImage(UploadedFile $file, string $basename): string
    {
        $dir = public_path('uploads/company');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        foreach (glob($dir.DIRECTORY_SEPARATOR.$basename.'.*') ?: [] as $old) {
            if (is_file($old)) {
                unlink($old);
            }
        }
        $name = $basename.'.'.strtolower($file->extension());
        $file->move($dir, $name);

        return 'uploads/company/'.$name;
    }
}

<?php

namespace App\Services\PublicSettings;

use App\Models\PublicSettings\CompanySetting;
use App\Repositories\Accounting\Contracts\AccountRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    public function __construct(private readonly AccountRepositoryInterface $accounts) {}

    public function page(): array
    {
        return [
            'setting' => CompanySetting::current(),
            'accounts' => $this->accounts->ordered(),
        ];
    }

    public function update(array $data, bool $taxEnabled, bool $discountEnabled): void
    {
        $data['tax_enabled'] = $taxEnabled;
        $data['discount_enabled'] = $discountEnabled;
        CompanySetting::current()->update($data);
        Cache::forget('company_settings');
    }
}

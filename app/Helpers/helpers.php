<?php

use App\Models\PublicSettings\CompanySetting;

if (! function_exists('company')) {
    function company(): ?CompanySetting
    {
        try {
            return CompanySetting::current();
        } catch (Throwable) {
            return null;
        }
    }
}

if (! function_exists('user_avatar')) {
    function user_avatar(?\App\Models\AllUsers\User $user = null): string
    {
        $path = $user?->avatar;
        if (is_string($path) && $path !== '' && ! str_contains($path, '..')) {
            $file = public_path($path);
            if (is_file($file)) {
                return asset($path).'?v='.filemtime($file);
            }
        }

        return asset('assets/img/brand/avatar-default.svg');
    }
}

if (! function_exists('company_logo_url')) {
    function company_logo_url(): ?string
    {
        $path = company()?->logo;
        if (! is_string($path) || $path === '' || str_contains($path, '..')) {
            return null;
        }
        if (str_starts_with($path, 'http')) {
            return $path;
        }
        $file = public_path($path);
        if (! is_file($file)) {
            return null;
        }

        return asset($path).'?v='.filemtime($file);
    }
}

if (! function_exists('company_shows_name')) {
    function company_shows_name(): bool
    {
        $company = company();
        $showName = (bool) ($company->show_brand_name ?? true);
        $showLogo = (bool) ($company->show_brand_logo ?? false) && company_logo_url() !== null;
        if (! $showName && ! $showLogo) {
            return true;
        }

        return $showName;
    }
}

if (! function_exists('company_logo_on_right')) {
    function company_logo_on_right(): bool
    {
        return (company()?->brand_logo_side ?? 'left') === 'right';
    }
}

if (! function_exists('company_shows_logo')) {
    function company_shows_logo(): bool
    {
        return (bool) (company()?->show_brand_logo ?? false) && company_logo_url() !== null;
    }
}

if (! function_exists('company_avatar')) {
    function company_avatar(): string
    {
        $user = auth()->user();

        return user_avatar($user instanceof \App\Models\AllUsers\User ? $user : null);
    }
}

if (! function_exists('money')) {
    function money(float|int|string|null $amount): string
    {
        $symbol = company()?->currency_symbol ?? 'ج.م';

        return number_format((float) $amount, 2).' '."\u{202D}".$symbol."\u{202C}";
    }
}

if (! function_exists('qty')) {
    function qty(float|int|string|null $amount): string
    {
        if ($amount === null || $amount === '') {
            return '0';
        }

        $formatted = number_format((float) $amount, 3, '.', '');
        $trimmed = rtrim(rtrim($formatted, '0'), '.');

        return $trimmed === '' || $trimmed === '-' ? '0' : $trimmed;
    }
}

if (! function_exists('invoice_status_label')) {
    function invoice_status_label(?string $status): string
    {
        return match ($status) {
            'draft' => 'مسودة',
            'confirmed' => 'غير مدفوعة',
            'partial' => 'مدفوعة جزئياً',
            'paid' => 'مدفوعة',
            'cancelled' => 'ملغاة',
            default => $status ?? '-',
        };
    }
}

if (! function_exists('invoice_status_badge')) {
    function invoice_status_badge(?string $status): string
    {
        return match ($status) {
            'draft' => 'secondary',
            'confirmed' => 'info',
            'partial' => 'warning',
            'paid' => 'success',
            'cancelled' => 'danger',
            default => 'light',
        };
    }
}

if (! function_exists('can')) {
    function can(string $permission): bool
    {
        $user = auth()->user();

        return $user ? $user->hasPermission($permission) : false;
    }
}

if (! function_exists('home_route')) {
    function home_route(): string
    {
        $targets = [
            'dashboard.view' => 'dashboard',
            'pos.create' => 'pos.sell',
            'pos.view' => 'pos.index',
            'customers.view' => 'customers.index',
            'suppliers.view' => 'suppliers.index',
            'products.view' => 'products.index',
            'inventory.view' => 'inventory.index',
            'sales.view' => 'sales.index',
            'purchases.view' => 'purchases.index',
            'accounting.view' => 'accounts.index',
            'treasury.view' => 'treasury.index',
            'expenses.view' => 'expenses.index',
            'reports.view' => 'reports.index',
            'users.view' => 'users.index',
            'settings.update' => 'settings.index',
        ];

        foreach ($targets as $permission => $name) {
            if (can($permission)) {
                return route($name);
            }
        }

        return route('profile.edit');
    }
}

if (! function_exists('paginate_collection')) {
    function paginate_collection(mixed $items, int $perPage = 15, string $pageName = 'page'): \Illuminate\Pagination\LengthAwarePaginator
    {
        $items = $items instanceof \Illuminate\Support\Collection ? $items->values() : collect($items)->values();
        $page = max(1, (int) request()->query($pageName, 1));
        $total = $items->count();
        $slice = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return new \Illuminate\Pagination\LengthAwarePaginator($slice, $total, $perPage, $page, [
            'path' => request()->url(),
            'pageName' => $pageName,
            'query' => request()->query(),
        ]);
    }
}

if (! function_exists('row_no')) {
    function row_no(mixed $items, $loop): int
    {
        if (is_object($items) && method_exists($items, 'firstItem') && $items->firstItem()) {
            return (int) $items->firstItem() + $loop->index;
        }

        return (int) $loop->iteration;
    }
}

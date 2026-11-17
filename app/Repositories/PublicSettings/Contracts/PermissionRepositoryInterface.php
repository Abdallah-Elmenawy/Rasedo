<?php

namespace App\Repositories\PublicSettings\Contracts;

use Illuminate\Support\Collection;

interface PermissionRepositoryInterface
{
    public function groupedByModule(): Collection;

    public function allIds(): Collection;

    public function all(): Collection;
}

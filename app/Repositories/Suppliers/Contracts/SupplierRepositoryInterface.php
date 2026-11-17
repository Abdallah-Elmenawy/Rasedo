<?php

namespace App\Repositories\Suppliers\Contracts;

use App\Models\Suppliers\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface SupplierRepositoryInterface
{
    public function paginateFiltered(?string $search, ?string $status, int $perPage = 15): LengthAwarePaginator;

    public function paginateAccounts(int $perPage = 15): LengthAwarePaginator;

    public function nextCode(): string;

    public function create(array $data): Supplier;

    public function update(Supplier $supplier, array $data): Supplier;

    public function delete(Supplier $supplier): void;

    public function loadProfile(Supplier $supplier): Supplier;

    public function orderedByName(): Collection;

    public function activeOrdered(): Collection;
}

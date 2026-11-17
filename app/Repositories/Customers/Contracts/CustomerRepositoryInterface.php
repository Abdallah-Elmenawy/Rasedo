<?php

namespace App\Repositories\Customers\Contracts;

use App\Models\Customers\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CustomerRepositoryInterface
{
    public function paginateFiltered(?string $search, ?string $status, int $perPage = 15): LengthAwarePaginator;

    public function paginateAccounts(int $perPage = 15): LengthAwarePaginator;

    public function nextCode(): string;

    public function create(array $data): Customer;

    public function update(Customer $customer, array $data): Customer;

    public function delete(Customer $customer): void;

    public function loadProfile(Customer $customer): Customer;

    public function orderedByName(): Collection;

    public function activeOrdered(): Collection;
}

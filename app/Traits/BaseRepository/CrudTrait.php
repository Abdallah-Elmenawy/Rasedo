<?php

namespace App\Traits\BaseRepository;

use Illuminate\Database\Eloquent\Model;

trait CrudTrait
{
    abstract protected function modelClass(): string;

    public function findOrFail(int $id): Model
    {
        return $this->modelClass()::query()->findOrFail($id);
    }

    public function create(array $data): Model
    {
        return $this->modelClass()::query()->create($data);
    }

    public function updateModel(Model $model, array $data): Model
    {
        $model->update($data);

        return $model;
    }

    public function deleteModel(Model $model): void
    {
        $model->delete();
    }
}

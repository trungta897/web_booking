<?php

namespace App\Repositories;

use App\Contracts\Repositories\RepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements RepositoryInterface
{
    protected $model;

    protected $query;

    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->resetQuery();
    }

    /**
     * Get a fresh query builder.
     */
    protected function query(): Builder
    {
        return $this->model->newQuery();
    }

    /**
     * Reset the query builder.
     */
    protected function resetQuery(): void
    {
        $this->query = $this->model->newQuery();
    }

    /**
     * Get all records.
     */
    public function all(): Collection
    {
        return $this->query()->get();
    }

    /**
     * Find a record by ID.
     */
    public function findById(int $id): ?Model
    {
        return $this->query()->find($id);
    }

    /**
     * Find a record by ID or fail.
     */
    public function findByIdOrFail(int $id): Model
    {
        return $this->query()->findOrFail($id);
    }

    /**
     * Create a new record.
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update a record.
     */
    public function update(int $id, array $data): bool
    {
        $model = $this->findById($id);

        return $model ? $model->update($data) : false;
    }

    /**
     * Delete a record.
     */
    public function delete(int $id): bool
    {
        $model = $this->findById($id);

        return $model ? $model->delete() : false;
    }

    /**
     * Get paginated records.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        $result = $this->query->paginate($perPage);
        $this->resetQuery();

        return $result;
    }

    /**
     * Get records with relationships.
     */
    public function with(array $relations): self
    {
        $this->query = $this->query->with($relations);

        return $this;
    }

    /**
     * Get records where condition.
     */
    public function where(string $column, $operator = null, $value = null): self
    {
        $this->query = $this->query->where($column, $operator, $value);

        return $this;
    }

    /**
     * Order by column.
     */
    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $this->query = $this->query->orderBy($column, $direction);

        return $this;
    }

    /**
     * Get the first record.
     */
    public function first(): ?Model
    {
        $result = $this->query->first();
        $this->resetQuery();

        return $result;
    }

    /**
     * Get records count.
     */
    public function count(): int
    {
        $result = $this->query->count();
        $this->resetQuery();

        return $result;
    }

    /**
     * Find records by multiple IDs.
     */
    public function findMany(array $ids): Collection
    {
        return $this->query()->whereIn($this->model->getKeyName(), $ids)->get();
    }

    /**
     * Find by field value.
     */
    public function findBy(string $column, $value): Collection
    {
        return $this->query()->where($column, $value)->get();
    }

    /**
     * Find first by field value.
     */
    public function findFirstBy(string $field, $value): ?Model
    {
        return $this->query()->where($field, $value)->first();
    }

    /**
     * Check if record exists.
     */
    public function exists(string $column, $value): bool
    {
        return $this->query()->where($column, $value)->exists();
    }

    /**
     * Get latest records.
     */
    public function latest(string $column = 'created_at'): self
    {
        $this->query = $this->query->latest($column);

        return $this;
    }

    /**
     * Get oldest records.
     */
    public function oldest(string $column = 'created_at'): self
    {
        $this->query = $this->query->oldest($column);

        return $this;
    }

    // Helper methods for existing repositories

    /**
     * Update a model instance.
     */
    public function updateModel(Model $model, array $data): bool
    {
        return $model->update($data);
    }

    /**
     * Delete a model instance.
     */
    public function deleteModel(Model $model): bool
    {
        return $model->delete();
    }
}

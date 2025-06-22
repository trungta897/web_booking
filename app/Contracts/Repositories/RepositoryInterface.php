<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface RepositoryInterface
{
    /**
     * Get all records
     */
    public function all(): Collection;

    /**
     * Find record by ID
     */
    public function findById(int $id): ?Model;

    /**
     * Create new record
     */
    public function create(array $data): Model;

    /**
     * Update record
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete record
     */
    public function delete(int $id): bool;

    /**
     * Get paginated results
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Find records with relationships
     */
    public function with(array $relations): self;

    /**
     * Add where condition
     */
    public function where(string $column, $operator = null, $value = null): self;

    /**
     * Add order by condition
     */
    public function orderBy(string $column, string $direction = 'asc'): self;

    /**
     * Get first record
     */
    public function first(): ?Model;

    /**
     * Count records
     */
    public function count(): int;

    /**
     * Find multiple records by IDs
     */
    public function findMany(array $ids): Collection;

    /**
     * Find by specific criteria
     */
    public function findBy(string $column, $value): Collection;

    /**
     * Check if record exists
     */
    public function exists(string $column, $value): bool;

    /**
     * Get latest records
     */
    public function latest(string $column = 'created_at'): self;

    /**
     * Get oldest records
     */
    public function oldest(string $column = 'created_at'): self;
}

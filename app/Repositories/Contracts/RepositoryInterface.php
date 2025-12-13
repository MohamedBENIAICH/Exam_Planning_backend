<?php

namespace App\Repositories\Contracts;

interface RepositoryInterface
{
    public function all();

    public function find($id);

    public function findOrFail($id);

    public function create(array $data);

    public function update($id, array $data);

    public function delete($id);

    public function count();

    public function paginate($perPage = 15);

    public function with($relations);

    public function where($column, $operator = null, $value = null);
}

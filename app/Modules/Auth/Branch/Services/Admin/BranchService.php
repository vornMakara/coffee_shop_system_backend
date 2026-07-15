<?php

namespace App\Modules\Auth\Branch\Services\Admin;

use App\Modules\Auth\Branch\Models\Branch;

class BranchService
{
    /**
     * Get a paginated list of branches.
     */
    public function getPaginated(int $perPage = 10)
    {
        return Branch::paginate($perPage);
    }

    /**
     * Create a new branch.
     */
    public function createBranch(array $data): Branch
    {
        return Branch::create($data);
    }

    /**
     * Update an existing branch.
     */
    public function updateBranch(Branch $branch, array $data): Branch
    {
        $branch->update($data);
        return $branch;
    }

    /**
     * Delete a branch.
     */
    public function deleteBranch(Branch $branch): bool
    {
        return $branch->delete();
    }
}

<?php

namespace App\Modules\POS\Table\Services\Admin;

use App\Modules\POS\Table\Models\Table;

class TableService
{
    public function getPaginated(int $perPage = 15)
    {
        return Table::with('branch')->paginate($perPage);
    }

    public function createTable(array $data)
    {
        return Table::create($data)->load('branch');
    }

    public function updateTable(Table $table, array $data)
    {
        $table->update($data);
        return $table->load('branch');
    }

    public function deleteTable(Table $table)
    {
        return $table->delete();
    }
}

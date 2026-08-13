<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\Auth\User\Models\User;
use App\Modules\Auth\Branch\Models\Branch;

class TableApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_table()
    {
        $user = User::factory()->create();
        
        // Create a branch so the branch_id exists in the DB
        $branch = Branch::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'name' => 'Test Branch', 
            'code' => 'TB01', 
            'address' => 'Test'
        ]);

        $payload = [
            'branch_id' => $branch->id,
            'table_number' => 'T-01',
            'seating_capacity' => 4,
            'status' => 'available'
        ];

        // Ensure we send Accept: application/json
        $response = $this->actingAs($user, 'api')->json('POST', '/api/v1/admin/tables', $payload);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'id',
                'table_number',
                'seating_capacity',
                'status',
                'branch'
            ]
        ]);
        
        $this->assertDatabaseHas('tables', [
            'number' => 'T-01',
            'capacity' => 4
        ]);
    }
}

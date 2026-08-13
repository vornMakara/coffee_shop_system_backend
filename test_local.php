<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$branch = \App\Modules\Auth\Branch\Models\Branch::first();
if (!$branch) {
    $branch = \App\Modules\Auth\Branch\Models\Branch::create([
        'id' => \Illuminate\Support\Str::uuid()->toString(),
        'name' => 'Test Branch', 
        'code' => 'TB01', 
        'address' => 'Test'
    ]);
}

$user = \App\Models\User::first();
if (!$user) {
    $user = \App\Models\User::factory()->create();
}

echo "Testing Table Creation API Locally...\n";
echo "Using Branch ID: " . $branch->id . "\n\n";

$request = Illuminate\Http\Request::create('/api/v1/admin/tables', 'POST', [
    'branch_id' => $branch->id,
    'table_number' => 'T-99',
    'seating_capacity' => 4,
    'status' => 'available'
]);
$request->headers->set('Accept', 'application/json');

// Authenticate as user
$app->make('auth')->guard('api')->setUser($user);
$app['auth']->shouldUse('api');

$response = $kernel->handle($request);

echo "HTTP Status Code: " . $response->getStatusCode() . "\n";
echo "Response Body:\n";
echo json_encode(json_decode($response->getContent()), JSON_PRETTY_PRINT) . "\n";

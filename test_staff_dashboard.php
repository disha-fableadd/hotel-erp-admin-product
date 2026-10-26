<?php
use App\Models\User;
use Illuminate\Support\Facades\Auth;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/staff-dashboard', 'GET');
$app->instance('request', $request);

$user = User::where('role', 'staff')->first();
if (!$user) {
    echo "No staff user found\n";
    exit(1);
}

Auth::login($user);
try {
    $response = app(App\Http\Controllers\admin\AuthController::class)->staffDashboard();
    echo "Type: " . get_class($response) . "\n";
    echo "Content length: " . strlen($response->render()) . "\n";
    echo "Success!\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

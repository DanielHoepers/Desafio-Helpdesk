<?php
declare(strict_types=1);

$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header('Access-Control-Allow-Origin: ' . $origin);
header('Vary: Origin');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, X-HTTP-Method-Override');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS'){
    http_response_code(204); 
    exit; 
}

use App\Core\Router;
use App\Controllers\ChamadoController;
use App\Controllers\TarefaController;

$root = dirname(__DIR__);
$appDir = $root.'/app';               

spl_autoload_register(function($class) use ($appDir) {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) 
        return;

    $rel = substr($class, strlen($prefix));
    $file = $appDir . '/' . str_replace('\\', '/', $rel) . '.php';
    if (is_file($file)) 
        require $file;
});

require $root.'/../config/db.php';

$httpHelper = $appDir . '/Helpers/Http.php';
if (is_file($httpHelper)) 
    require $httpHelper;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!empty($_POST['_method'])) {
        $_SERVER['REQUEST_METHOD'] = strtoupper((string)$_POST['_method']);
    } elseif (!empty($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
        $_SERVER['REQUEST_METHOD'] = strtoupper((string)$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
    }
}

$router = new Router();

$router->get('/api/chamados', [ChamadoController::class, 'index']);
$router->get('/api/chamados/{id}', [ChamadoController::class, 'show']);
$router->post('/api/chamados', [ChamadoController::class, 'insert']);
$router->put('/api/chamados/{id}', [ChamadoController::class, 'update']);
$router->delete('/api/chamados/{id}', [ChamadoController::class, 'delete']);

$router->get('/api/chamados/{id}/tarefas', [TarefaController::class, 'listByChamado']);
$router->post('/api/tarefas', [TarefaController::class, 'insert']);
$router->put('/api/tarefas/{id}', [TarefaController::class, 'update']);
$router->delete('/api/tarefas/{id}', [TarefaController::class, 'delete']);

$router->options('/api/chamados', fn() => null);
$router->options('/api/chamados/{id}', fn() => null);
$router->options('/api/chamados/{id}/tarefas', fn() => null);
$router->options('/api/tarefas', fn() => null);
$router->options('/api/tarefas/{id}', fn() => null);

$router->dispatch();

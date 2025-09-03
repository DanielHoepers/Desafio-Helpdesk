<?php
namespace App\Core;

class BaseController{
    protected function json(mixed $data, int $status=200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
    }

    protected function body(): array {
        $raw = file_get_contents('php://input') ?: '';
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            $this->json(['Erro' => 'JSON Invalido'], 400);
            exit;
        }
        return $data;
    }
}

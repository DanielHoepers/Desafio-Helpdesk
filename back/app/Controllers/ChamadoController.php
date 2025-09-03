<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Chamado;
use App\Models\Tarefa;

class ChamadoController extends BaseController{
    public function index(): void{
        $pdo = \db();
        $this->json((new Chamado($pdo))->all());
    }

    public function show(array $params = []): void{
        $id = (int) ($params['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['error' => 'ID inválido'], 400);
            return;
        }

        $pdo = \db();
        $c   = (new Chamado($pdo))->select($id);

        if (!$c) {
            $this->json(['error' => 'Chamado não encontrado'], 404);
            return;
        }

        $this->json($c);
    }

    public function insert(): void{
        $d         = $this->body();
        $titulo    = trim($d['titulo'] ?? '');
        $descricao = trim($d['descricao'] ?? '');

        if ($titulo === '' || $descricao === '') {
            $this->json(['error' => 'Título e descrição são obrigatórios'], 422);
            return;
        }

        try {
            $pdo = \db();
            $id  = (new Chamado($pdo))->insert($titulo, $descricao);
            $this->json(['id' => $id, 'message' => 'Chamado criado'], 201);
        } catch (\Throwable $e) {
            $this->json([
                'error'  => 'Erro ao criar chamado',
                'detail' => $e->getMessage()
            ], 500);
        }
    }

    public function update(array $params = []): void{
        $id = (int) ($params['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['error' => 'ID inválido'], 400);
            return;
        }

        $pdo   = \db();
        $repo  = new Chamado($pdo);
        $atual = $repo->select($id);

        if (!$atual) {
            $this->json(['error' => 'Chamado não encontrado'], 404);
            return;
        }

        $d         = $this->body();
        $titulo    = array_key_exists('titulo', $d) ? trim((string) $d['titulo']) : $atual['titulo'];
        $descricao = array_key_exists('descricao', $d) ? trim((string) $d['descricao']) : $atual['descricao'];
        $status    = array_key_exists('status', $d) ? trim((string) $d['status']) : $atual['status'];

        if ($titulo === '' || $descricao === '' || $status === '') {
            $this->json(['error' => 'Dados inválidos'], 400);
            return;
        }

        $repo2 = new Tarefa($pdo);
        $novo = mb_strtolower(trim((string)$status));
        if ($novo === 'finalizado' && !$repo2->allFinalizadas($id)) {
            $this->json(['error' => 'Existem tarefas pendentes para este chamado'], 409);
            return;
        }


        try {
            $ok = $repo->update($id, $titulo, $descricao, $status);
            $this->json(['ok' => (bool) $ok]);
        } catch (\Throwable $e) {
            $this->json([
                'error'  => 'Erro ao atualizar',
                'detail' => $e->getMessage()
            ], 500);
        }
    }

    public function delete(array $params = []): void{
        $id = (int) ($params['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['error' => 'ID inválido'], 400);
            return;
        }

        $pdo     = \db();
        $tarefas = (new Tarefa($pdo))->allByChamado($id);

        if (count($tarefas) > 0) {
            $this->json(['error' => 'Existem tarefas associadas a este chamado'], 409);
            return;
        }

        try {
            (new Chamado($pdo))->delete($id);
            http_response_code(204);
        } catch (\Throwable $e) {
            $this->json([
                'error'  => 'Erro ao excluir chamado',
                'detail' => $e->getMessage()
            ], 500);
        }
    }
}

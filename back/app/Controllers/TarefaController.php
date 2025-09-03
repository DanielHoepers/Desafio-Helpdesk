<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Tarefa;

class TarefaController extends BaseController{
    public function listByChamado(array $params = []): void{
        $chamadoId = (int) ($params['id'] ?? 0);
        if ($chamadoId <= 0) {
            $this->json(['error' => 'Chamado inválido'], 400);
            return;
        }

        $pdo = \db();
        $this->json((new Tarefa($pdo))->allByChamado($chamadoId));
    }

    public function insert(): void{
        $d           = $this->body();
        $chamadoId   = (int) ($d['chamado_id'] ?? 0);
        $descricao   = trim($d['descricao'] ?? '');
        $responsavel = trim($d['responsavel'] ?? '');

        if ($chamadoId <= 0 || $descricao === '' || $responsavel === '') {
            $this->json(['error' => 'Preencha todos os campos'], 422);
            return;
        }

        try {
            $pdo = \db();
            $id  = (new Tarefa($pdo))->insert($chamadoId, $descricao, $responsavel);
            $this->json(['id' => $id, 'message' => 'Tarefa criada'], 201);
        } catch (\Throwable $e) {
            $this->json([
                'error'  => 'Erro ao criar tarefa',
                'detail' => $e->getMessage()
            ], 500);
        }
    }

    public function update(array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['error' => 'ID inválido'], 400);
            return;
        }

        $pdo   = \db();
        $repo  = new Tarefa($pdo);
        $atual = $repo->select($id);

        if (!$atual) {
            $this->json(['error' => 'Tarefa não encontrada'], 404);
            return;
        }

        $d           = $this->body();
        $descricao   = array_key_exists('descricao', $d) ? trim((string) $d['descricao']) : $atual['descricao'];
        $responsavel = array_key_exists('responsavel', $d) ? trim((string) $d['responsavel']) : $atual['responsavel'];
        $status      = strtoupper(array_key_exists('status', $d) ? trim((string) $d['status']) : $atual['status']);

        if ($descricao === '' || $responsavel === '' || $status === '') {
            $this->json(['error' => 'Dados inválidos'], 400);
            return;
        }

        try {
            $ok = $repo->update($id, $descricao, $responsavel, $status);
            $this->json(['ok' => (bool) $ok]);
        } catch (\Throwable $e) {
            $this->json([
                'error'  => 'Erro ao atualizar tarefa',
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

        $pdo  = \db();
        $repo = new Tarefa($pdo);
        $t    = $repo->select($id);

        if (!$t) {
            $this->json(['error' => 'Tarefa não encontrada'], 404);
            return;
        }

        try {
            $ok = $repo->delete($id);
            if ($ok) {
                http_response_code(204);
                return;
            }
            $this->json(['error' => 'Não foi possível excluir'], 400);
        } catch (\Throwable $e) {
            $this->json([
                'error'  => 'Erro ao excluir tarefa',
                'detail' => $e->getMessage()
            ], 500);
        }
    }
}

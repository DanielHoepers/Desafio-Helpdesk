<?php
namespace App\Models;

class Tarefa{
    private \PDO $pdo;

    public function __construct(\PDO $pdo){
        $this->pdo = $pdo;
    }

    public function allByChamado(int $chamadoId): array{
        $sql = $this->pdo->prepare("
            SELECT id,
                   chamado_id,
                   descricao,
                   responsavel,
                   status,
                   created_at
              FROM tarefas
             WHERE chamado_id = :cid
          ORDER BY id DESC");
        $sql->execute(['cid' => $chamadoId]);
        return $sql->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function allFinalizadas(int $chamadoId): bool {
        $sql = $this->pdo->prepare("
            SELECT COUNT(id)
              FROM tarefas
             WHERE chamado_id = :cid
               AND LOWER(TRIM(status)) <> 'concluida'");
        $sql->execute(['cid' => $chamadoId]);
        $qtdPendentes = (int) $sql->fetchColumn();
        return $qtdPendentes === 0;
    }


    public function select(int $id): array|false{
        $sql = $this->pdo->prepare("
            SELECT id,
                   chamado_id,
                   descricao,
                   responsavel,
                   status,
                   created_at
              FROM tarefas
             WHERE id = :id");
        $sql->execute(['id' => $id]);
        return $sql->fetch(\PDO::FETCH_ASSOC);
    }

    public function insert(int $chamadoId, string $descricao, string $responsavel): int{
        $this->pdo->beginTransaction();
        try {
            $sql = $this->pdo->prepare("
                INSERT INTO tarefas (chamado_id,
                                     descricao,
                                     responsavel,
                                     status)
                     VALUES (:cid,
                             :d,
                             :r,
                             :s)
                  RETURNING id");
            $sql->execute([
                'cid' => $chamadoId,
                'd'   => $descricao,
                'r'   => $responsavel,
                's'   => 'Pendente'
            ]);
            $id = (int) $sql->fetchColumn();
            $this->pdo->commit();
            return $id;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }

    public function update(int $id, string $descricao, string $responsavel, string $status): bool{
        $this->pdo->beginTransaction();
        try {
            $sql = $this->pdo->prepare("
                UPDATE tarefas
                   SET descricao   = :d,
                       responsavel = :r,
                       status      = :s
                 WHERE id = :id");
            $ok = $sql->execute([
                'd'   => $descricao,
                'r'   => $responsavel,
                's'   => $status,
                'id'  => $id
            ]);
            $this->pdo->commit();
            return (bool) $ok;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }

    public function delete(int $id): bool{
        $this->pdo->beginTransaction();
        try {
            $sql = $this->pdo->prepare("
                DELETE FROM tarefas
                      WHERE id = :id");
            $ok = $sql->execute(['id' => $id]);
            $this->pdo->commit();
            return (bool) $ok;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }
}

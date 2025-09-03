<?php
namespace App\Models;

class Chamado{
    private \PDO $pdo;

    public function __construct(\PDO $pdo){
        $this->pdo = $pdo;
    }

    public function all(): array{
        $sql = $this->pdo->query("
            SELECT id,
                   titulo,
                   descricao,
                   status,
                   created_at
              FROM chamados
          ORDER BY id DESC");
        return $sql ? $sql->fetchAll(\PDO::FETCH_ASSOC) : [];
    }

    public function select(int $id): array|false{
        $sql = $this->pdo->prepare("
            SELECT id,
                   titulo,
                   descricao,
                   status,
                   created_at
              FROM chamados
             WHERE id = :id");
        $sql->execute(['id' => $id]);
        return $sql->fetch(\PDO::FETCH_ASSOC);
    }

    public function insert(string $titulo, string $descricao): int{
        $this->pdo->beginTransaction();
        try {
            $sql = $this->pdo->prepare("
                INSERT INTO chamados (titulo,
                                      descricao,
                                      status)
                     VALUES (:t,
                             :d,
                             :s)
                  RETURNING id");
            $sql->execute([
                't' => $titulo,
                'd' => $descricao,
                's' => 'Aberto'
            ]);
            $id = (int) $sql->fetchColumn();
            $this->pdo->commit();
            return $id;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) 
                $this->pdo->rollBack();
            throw $e;
        }
    }

    public function update(int $id, string $titulo, string $descricao, string $status): bool{
        $this->pdo->beginTransaction();
        try {
            $sql = $this->pdo->prepare("
                UPDATE chamados
                   SET titulo    = :t,
                       descricao = :d,
                       status    = :s
                 WHERE id = :id");
            $ok = $sql->execute([
                't'  => $titulo,
                'd'  => $descricao,
                's'  => $status,
                'id' => $id
            ]);
            $this->pdo->commit();
            return (bool) $ok;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) 
                    $this->pdo->rollBack();
            throw $e;
        }
    }

    public function delete(int $id): bool{
        $this->pdo->beginTransaction();
        try {
            $sql = $this->pdo->prepare("
                DELETE FROM chamados
                      WHERE id = :id");
            $ok = $sql->execute(['id' => $id]);
            $this->pdo->commit();
            return (bool) $ok;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) 
                $this->pdo->rollBack();
            throw $e;
        }
    }
}

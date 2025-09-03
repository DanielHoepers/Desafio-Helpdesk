CREATE TABLE chamados (
  id SERIAL PRIMARY KEY,
  titulo     TEXT NOT NULL,
  descricao  TEXT NOT NULL,
  status     VARCHAR(30)  NOT NULL,
  created_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE tarefas (
  id SERIAL PRIMARY KEY,
  chamado_id INT NOT NULL REFERENCES chamados(id) ON DELETE RESTRICT,
  descricao   TEXT NOT NULL,
  responsavel TEXT NOT NULL,
  status      VARCHAR(30)  NOT NULL,
  created_at  TIMESTAMPTZ DEFAULT now()
);
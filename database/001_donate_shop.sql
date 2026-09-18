-- WYD CDK - contrato local do Donate Shop.
-- Aplicar somente no banco de contas correto, depois de revisar o schema atual.
-- Este arquivo nao contem credenciais e nao deve ser executado automaticamente em producao.

ALTER TABLE accounts
    ADD COLUMN IF NOT EXISTS donate INT NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS pix_key CHAR(5) NULL;

CREATE TABLE IF NOT EXISTS donate_shop_catalog (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    store TINYINT UNSIGNED NOT NULL,
    page TINYINT UNSIGNED NOT NULL,
    item_position TINYINT UNSIGNED NOT NULL,
    item_index SMALLINT UNSIGNED NOT NULL,
    price INT UNSIGNED NOT NULL DEFAULT 0,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    enabled TINYINT(1) NOT NULL DEFAULT 1,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_donate_shop_slot (store, page, item_position),
    KEY ix_donate_shop_enabled (enabled, store, page, item_position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A matriz wire legada possui exatamente 3 lojas, 5 paginas e 15 posicoes.
-- O seed de itens/precos fica separado para nao inventar IDs antes da tabela final.

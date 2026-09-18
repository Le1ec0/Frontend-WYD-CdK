-- WYD CDK - estado de conta/personagem por mundo.
-- O blob preserva os 7945 bytes do STRUCT_ACCOUNTFILE confirmado pelo port.
-- Aplicar somente no banco de contas revisado; nao contem credenciais.

CREATE TABLE IF NOT EXISTS wyd_world_account_state (
    account_name VARCHAR(15) NOT NULL,
    world_key VARCHAR(8) NOT NULL,
    account_blob MEDIUMBLOB NOT NULL,
    version BIGINT UNSIGNED NOT NULL DEFAULT 0,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (account_name, world_key),
    CONSTRAINT ck_wyd_world_key CHECK (world_key IN ('UP', 'PVP'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS wyd_character_names (
    world_key VARCHAR(8) NOT NULL,
    character_name VARCHAR(15) NOT NULL,
    account_name VARCHAR(15) NOT NULL,
    slot_index TINYINT UNSIGNED NULL,
    reserved_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (world_key, character_name),
    KEY ix_wyd_character_account (world_key, account_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS wyd_account_security (
    account_name VARCHAR(15) NOT NULL,
    numeric_token VARBINARY(6) NOT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (account_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

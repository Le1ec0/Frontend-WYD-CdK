-- WYD CDK - estado canonico de ofertas de autotrade.
-- Nao altera o blob legado de personagem; aplicar somente no banco revisado.

CREATE TABLE IF NOT EXISTS wyd_autotrade_state (
    world_key VARCHAR(8) NOT NULL,
    account_name VARCHAR(15) NOT NULL,
    character_slot TINYINT UNSIGNED NOT NULL,
    listing_json JSON NOT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (world_key, account_name, character_slot),
    CONSTRAINT ck_wyd_autotrade_world_key CHECK (world_key IN ('UP', 'PVP')),
    CONSTRAINT ck_wyd_autotrade_character_slot CHECK (character_slot < 4)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

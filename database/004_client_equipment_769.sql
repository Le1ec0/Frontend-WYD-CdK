-- Estado adicional do cliente 7.69/7670: 18 equipamentos de 8 bytes (144 bytes).
-- Os 16 slots legados continuam no blob de personagem; os slots 16/17 ficam aqui.
CREATE TABLE IF NOT EXISTS wyd_client_equipment_769 (
    world_key       VARCHAR(8) NOT NULL,
    account_name    VARCHAR(15) NOT NULL,
    character_slot  TINYINT UNSIGNED NOT NULL,
    equipment_blob  VARBINARY(144) NOT NULL,
    version         BIGINT UNSIGNED NOT NULL DEFAULT 0,
    created_at      DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    updated_at      DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6)
                    ON UPDATE CURRENT_TIMESTAMP(6),
    PRIMARY KEY (world_key, account_name, character_slot),
    KEY ix_client_equipment_account (account_name, world_key),
    CONSTRAINT chk_client_equipment_world CHECK (world_key IN ('UP', 'PVP')),
    CONSTRAINT chk_client_equipment_slot CHECK (character_slot < 4),
    CONSTRAINT chk_client_equipment_size CHECK (OCTET_LENGTH(equipment_blob) = 144)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

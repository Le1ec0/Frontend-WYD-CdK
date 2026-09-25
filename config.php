<?php
/**
 * WYD CDK - Configurações Gerais do Website (release 7.69 / cliente 7670)
 */

// Informações Principais do Servidor
$SERVER_NAME      = "WYD CDK";
$SERVER_SLOGAN    = "With Your Destiny - Release 7.69 / Cliente 7670";
$SERVER_VERSION   = "7.69 / 7670";
$SERVER_TYPE      = "UP / PvP Balanceado";
$EXP_RATE         = "Média / Hard (Clássico)";
$DROP_RATE        = "Dinâmico";

// Links Úteis e Redes
$LINK_DISCORD          = "https://discord.gg/wydcdk";
$LINK_WHATSAPP         = "https://chat.whatsapp.com/";
$LINK_CLIENT_DIRECT    = "downloads/WYD_CDK_Client_769.zip";
$LINK_CLIENT_MEGA      = "https://mega.nz/";
$LINK_CLIENT_MEDIAFIRE = "https://www.mediafire.com/";
$LINK_CLIENT_DRIVE     = "https://drive.google.com/";
$LINK_PATCH_DIRECT     = "downloads/WYD_CDK_Patch_769.zip";
$LINK_PATCH_MEGA       = "https://mega.nz/";
$LINK_DIRECTX          = "https://www.microsoft.com/download/details.aspx?id=35";
$LINK_VCREDIST         = "https://aka.ms/vs/17/release/vc_redist.x86.exe";

// Status dos Canais (Alterne para false caso queira simular Offline)
$CHANNELS = [
    [
        "nome"   => "Canal 1 (Novatos / UP)",
        "tipo"   => "PvE / UP",
        "status" => "online", // online, busy, offline
        "lotacao"=> "Normal"
    ],
    [
        "nome"   => "Canal 2 (Guerra / Noatun)",
        "tipo"   => "PvP / Guerra",
        "status" => "online",
        "lotacao"=> "Lotado"
    ]
];

// Dados da Manutenção Semanal
$MAINTENANCE_DAY  = "Terça-Feira";
$MAINTENANCE_TIME = "Das 08h00 às 12h00 (Horário de Brasília)";

// Configuração do Banco de Dados de Contas (Opcional / PDO)
$DB_ENABLED = false;
$DB_HOST    = "127.0.0.1";
$DB_PORT    = 3306;
$DB_NAME    = "wyd_cdk";
$DB_USER    = "root";
$DB_PASS    = "";

function getDbConnection() {
    global $DB_ENABLED, $DB_HOST, $DB_PORT, $DB_NAME, $DB_USER, $DB_PASS;
    if (!$DB_ENABLED) return null;
    try {
        $dsn = "mysql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME};charset=utf8mb4";
        return new PDO($dsn, $DB_USER, $DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    } catch (Exception $e) {
        return null;
    }
}

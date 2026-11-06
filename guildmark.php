<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'site-security.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'guild-mark.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'db.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'config.php';

site_bootstrap('guildmark', 60, 60);

error_reporting(0);
ini_set('display_errors', '0');

function readGuildLeaders(PDO $pdo, string $accountName, string $world): array
{
    $query = $pdo->prepare('SELECT account_blob FROM wyd_world_account_state WHERE account_name = ? AND world_key = ? LIMIT 1');
    $query->execute([$accountName, $world]);
    $blob = $query->fetchColumn();
    if (!is_string($blob) || strlen($blob) < 7945) {
        return [];
    }

    $leaders = [];
    for ($slot = 0; $slot < 4; $slot++) {
        $offset = 216 + ($slot * 816);
        $name = trim((string)strtok(substr($blob, $offset, 16), "\0"));
        $guildBytes = substr($blob, $offset + 18, 2);
        $guildId = strlen($guildBytes) === 2 ? unpack('v', $guildBytes)[1] : 0;
        $guildLevel = ord($blob[$offset + 800] ?? "\0");
        if ($name !== '' && $guildId > 0 && $guildLevel >= 9) {
            $leaders[(string)$guildId] = ['id' => $guildId, 'name' => $name, 'slot' => $slot];
        }
    }
    return array_values($leaders);
}

function listGuildMarkCatalog(string $directory): array
{
    if (!is_dir($directory)) {
        return [];
    }

    $catalog = [];
    foreach (glob($directory . DIRECTORY_SEPARATOR . '*.bmp') ?: [] as $path) {
        if (guild_mark_validate_bmp($path)) {
            $catalog[] = basename($path);
        }
    }
    sort($catalog, SORT_NATURAL | SORT_FLAG_CASE);
    return $catalog;
}

function copyGuildMarkVariants(string $source, string $directory, int $guildId): void
{
    if (!guild_mark_validate_bmp($source)) {
        throw new RuntimeException('A marca selecionada não é um BMP WYD válido (16x12, 24 bits).');
    }
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('Não foi possível criar o diretório de destino.');
    }

    // Grava no diretório alvo e também na raiz de guilds para compatibilidade retro
    $dirs = [$directory];
    $rootDir = dirname($directory);
    if ($rootDir !== $directory && is_dir($rootDir)) {
        $dirs[] = $rootDir;
    }

    foreach ($dirs as $targetDir) {
        foreach ([1000000, 2000000, 3000000] as $prefix) {
            $destination = $targetDir . DIRECTORY_SEPARATOR . 'b0' . (string)($prefix + $guildId) . '.bmp';
            $temporary = tempnam($targetDir, 'guildmark-out-');
            if ($temporary === false || !copy($source, $temporary) || !rename($temporary, $destination)) {
                if ($temporary !== false) {
                    @unlink($temporary);
                }
                throw new RuntimeException('Não foi possível salvar todas as variantes da marca.');
            }
        }
    }
}

$message = '';
$success = false;
$database = null;
$accountName = (string)($_SESSION['guildmark_account'] ?? '');
$world = (string)($_SESSION['guildmark_world'] ?? 'UP');
if (!in_array($world, ['UP', 'PVP'], true)) {
    $world = 'UP';
}

$catalogDir = __DIR__ . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'guilds' . DIRECTORY_SEPARATOR . 'catalog';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    site_rate_limit('guildmark-post', 15, 60);
    $csrf = (string)($_POST['csrf'] ?? '');
    $action = (string)($_POST['action'] ?? '');

    if (!site_validate_csrf($csrf, 'guildmark_csrf')) {
        $message = 'Sessão expirada. Recarregue a página e tente novamente.';
    } elseif ($action === 'login') {
        $login = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $loginWorld = (string)($_POST['world'] ?? 'UP');
        if (!preg_match('/^(?=.*[A-Za-z])[A-Za-z0-9]{6,12}$/', $login) || strlen($password) > 128 || !in_array($loginWorld, ['UP', 'PVP'], true)) {
            $message = 'Dados de acesso inválidos. Verifique seu login e senha.';
        } else {
            try {
                $database = OpenGuildDatabase();
                if ($database !== null) {
                    $select = $database->prepare('SELECT password_hash, password_scheme FROM accounts WHERE account_name = ? LIMIT 1');
                    $select->execute([$login]);
                    $row = $select->fetch(PDO::FETCH_ASSOC);
                    if (!is_array($row) || ($row['password_scheme'] ?? '') !== 'password_hash' || !password_verify($password, (string)($row['password_hash'] ?? ''))) {
                        $message = 'Usuário ou senha incorretos.';
                    } else {
                        session_regenerate_id(true);
                        $_SESSION['guildmark_account'] = $login;
                        $_SESSION['guildmark_world'] = $loginWorld;
                        $accountName = $login;
                        $world = $loginWorld;
                        $message = 'Login realizado com sucesso! Selecione a guilda que você lidera.';
                        $success = true;
                    }
                } else {
                    $message = 'O banco de dados está temporariamente indisponível.';
                }
            } catch (Throwable $e) {
                $message = 'O banco de dados está temporariamente indisponível.';
            }
        }
    } elseif ($action === 'logout') {
        unset($_SESSION['guildmark_account'], $_SESSION['guildmark_world']);
        $accountName = '';
        $message = 'Sessão encerrada com sucesso.';
        $success = true;
    } elseif ($action === 'save') {
        $selectedWorld = (string)($_POST['world'] ?? $world);
        $selectedGuild = filter_var($_POST['guild_id'] ?? null, FILTER_VALIDATE_INT);

        if (!in_array($selectedWorld, ['UP', 'PVP'], true) || $selectedGuild === false || $selectedGuild < 1 || $selectedGuild > 65535) {
            $message = 'Guild ID inválido (deve ser um número entre 1 e 65535).';
        } else {
            try {
                if ($accountName === '') {
                    throw new RuntimeException('Faça login para atualizar uma marca de guilda.');
                }

                $database ??= OpenGuildDatabase();
                if ($database === null) {
                    throw new RuntimeException('O banco de dados está temporariamente indisponível.');
                }

                $leaders = readGuildLeaders($database, $accountName, $selectedWorld);
                if (!guild_mark_account_leads_guild($leaders, (int)$selectedGuild)) {
                    $message = 'Somente o líder da guilda pode alterar o brasão.';
                } else {
                    $guildDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'guilds' . DIRECTORY_SEPARATOR . $selectedWorld;
                    $source = null;
                    $temporaryUpload = null;

                    // 1. Arquivo enviado via upload (qualquer formato convertido automaticamente)
                    if (isset($_FILES['arquivo']) && (int)($_FILES['arquivo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                        if (!is_dir($guildDirectory) && !mkdir($guildDirectory, 0755, true) && !is_dir($guildDirectory)) {
                            throw new RuntimeException('Não foi possível preparar o diretório de guildas.');
                        }
                        $temporaryUpload = tempnam($guildDirectory, 'guildmark-up-');
                        if ($temporaryUpload === false) {
                            throw new RuntimeException('Não foi possível criar o arquivo temporário de upload.');
                        }
                        guild_mark_convert_upload($_FILES['arquivo'], $temporaryUpload);
                        $source = $temporaryUpload;

                        // Salva também no catálogo geral para reaproveitamento
                        $catalogCopy = $catalogDir . DIRECTORY_SEPARATOR . 'custom_' . time() . '_' . (int)$selectedGuild . '.bmp';
                        if (is_dir($catalogDir)) {
                            @copy($temporaryUpload, $catalogCopy);
                        }
                    }

                    // 2. Marca escolhida do catálogo
                    $selectedCatalog = basename((string)($_POST['catalog_file'] ?? ''));
                    if ($source === null && $selectedCatalog !== '' && preg_match('/^[A-Za-z0-9._-]+\.bmp$/i', $selectedCatalog)) {
                        $candidate = realpath($catalogDir . DIRECTORY_SEPARATOR . $selectedCatalog);
                        $catalogRoot = realpath($catalogDir);
                        if ($candidate !== false && $catalogRoot !== false && str_starts_with(strtolower($candidate), strtolower($catalogRoot . DIRECTORY_SEPARATOR))) {
                            $source = $candidate;
                        }
                    }

                    if ($source === null) {
                        throw new RuntimeException('Selecione uma marca do catálogo ou envie uma imagem pelo formulário.');
                    }

                    copyGuildMarkVariants($source, $guildDirectory, (int)$selectedGuild);

                    if ($temporaryUpload !== null && is_string($temporaryUpload)) {
                        @unlink($temporaryUpload);
                    }

                    $world = $selectedWorld;
                    $success = true;
                    $message = "Marca da Guilda #{$selectedGuild} atualizada com sucesso no mundo {$selectedWorld}!";
                }
            } catch (Throwable $exception) {
                if (isset($temporaryUpload) && is_string($temporaryUpload)) {
                    @unlink($temporaryUpload);
                }
                $message = $exception->getMessage();
            }
        }
    }
}

try {
    $database ??= ($accountName !== '') ? OpenGuildDatabase() : null;
    $leaders = ($database === null || $accountName === '') ? [] : readGuildLeaders($database, $accountName, $world);
} catch (Throwable) {
    $leaders = [];
}

$catalog = listGuildMarkCatalog($catalogDir);
$csrf = site_csrf_token('guildmark_csrf');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Guildmark - <?php echo htmlspecialchars($SERVER_NAME); ?></title>
    <link rel="icon" type="image/png" href="images/logo.png">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .catalog_grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(64px, 1fr));
            gap: 8px;
            max-height: 220px;
            overflow-y: auto;
            background: #110e0b;
            padding: 10px;
            border: 1px solid #382b1c;
            border-radius: 3px;
        }
        .catalog_item {
            background: #1a1510;
            border: 1px solid #4a3a29;
            padding: 6px;
            text-align: center;
            border-radius: 2px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .catalog_item:hover {
            border-color: #e69d30;
            background: #2b1f14;
        }
        .catalog_item.selected {
            border-color: #2e7d32;
            background: #143818;
            box-shadow: 0 0 6px rgba(46, 125, 50, 0.6);
        }
        .catalog_item img {
            image-rendering: pixelated;
            width: 32px;
            height: 24px;
            border: 1px solid #54402a;
        }
        .catalog_item span {
            font-size: 8px;
            color: #bfae97;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 50px;
        }
        .guild_info_badge {
            display: inline-block;
            background: #251b11;
            border: 1px solid #5c432a;
            color: #ffd891;
            padding: 4px 8px;
            border-radius: 2px;
            font-size: 11px;
            margin-right: 6px;
        }
    </style>
</head>
<body>

    <!-- Top Bar -->
    <div id="top_util_bar">
        <div class="ongame_network_links">
            <span style="color:#e69d30; font-weight:bold; margin-right:10px;"><?php echo htmlspecialchars($SERVER_NAME); ?>:</span>
            <a href="index.php">INÍCIO</a>
            <a href="account.php">PAINEL DA CONTA</a>
            <a href="guildmark.php" style="color:#ffd27d;">GUILDMARK</a>
            <a href="ranking.php">RANKING</a>
            <a href="download.php">DOWNLOADS</a>
            <a href="<?php echo htmlspecialchars($LINK_DISCORD); ?>" target="_blank">DISCORD OFICIAL</a>
        </div>
        <div class="audio_player_ctrl">
            <audio id="bgm_audio" src="audio/Login.mp3" loop preload="auto"></audio>
            <button id="btn_toggle_bgm" class="audio_btn">▶ Tocar BGM Clássica</button>
        </div>
    </div>

    <div id="wrapper">
        <header id="header">
            <div class="logo_area">
                <a href="index.php"><img src="images/logo.png" alt="<?php echo htmlspecialchars($SERVER_NAME); ?> Logo" class="logo_wyd"></a>
                <div class="logo_text">
                    <h1><?php echo htmlspecialchars($SERVER_NAME); ?></h1>
                    <p>Registro & Conversor de Guildmark (16x12 BMP) • Versão <?php echo htmlspecialchars($SERVER_VERSION); ?></p>
                </div>
            </div>

            <nav id="main_nav">
                <a href="index.php" class="nav_item">Início</a>
                <a href="account.php" class="nav_item">Painel da Conta</a>
                <a href="guildmark.php" class="nav_item active">Guildmark</a>
                <a href="ranking.php" class="nav_item">Ranking Top</a>
                <a href="download.php" class="nav_item">Downloads</a>
                <a href="<?php echo htmlspecialchars($LINK_DISCORD); ?>" target="_blank" class="nav_item">Discord</a>
            </nav>
        </header>

        <div id="main_container" style="justify-content: center;">
            <div style="width: 720px; margin: 20px 0; display:flex; flex-direction:column; gap:16px;">

                <?php if ($message): ?>
                    <div class="modal_msg <?php echo $success ? 'success' : 'error'; ?>" style="display:block;">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Box 1: Status da Sessão / Login de Líder -->
                <div class="retro_box">
                    <div class="box_header">
                        <h3>👑 Autenticação de Líder de Guilda</h3>
                        <span class="box_badge"><?php echo $accountName !== '' ? 'CONECTADO' : 'LOGIN NECESSÁRIO'; ?></span>
                    </div>
                    <div class="box_content" style="padding: 16px;">
                        <?php if ($accountName !== ''): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <span class="guild_info_badge">Líder: <strong><?php echo htmlspecialchars($accountName); ?></strong></span>
                                    <span class="guild_info_badge">Mundo: <strong><?php echo htmlspecialchars($world); ?></strong></span>
                                </div>
                                <form method="POST" action="guildmark.php" style="margin: 0;">
                                    <input type="hidden" name="action" value="logout">
                                    <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
                                    <button type="submit" class="dl_btn dl_btn_secondary" style="padding: 4px 10px; font-size: 10px;">Desconectar</button>
                                </form>
                            </div>
                        <?php else: ?>
                            <p style="font-size:11px; color:#c7b69f; margin-bottom:12px; line-height:1.5;">
                                Para gerenciar o brasão da sua guilda com segurança, faça o login com a conta que possui o personagem Líder de Guilda.
                            </p>
                            <form method="POST" action="guildmark.php" style="display: grid; grid-template-columns: 1fr 1fr 100px 120px; gap: 10px; align-items: flex-end;">
                                <input type="hidden" name="action" value="login">
                                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">

                                <div class="modal_row" style="margin: 0;">
                                    <label for="login_user">Usuário:</label>
                                    <input type="text" name="username" id="login_user" maxlength="12" placeholder="Conta" required>
                                </div>

                                <div class="modal_row" style="margin: 0;">
                                    <label for="login_pass">Senha:</label>
                                    <input type="password" name="password" id="login_pass" maxlength="10" placeholder="••••••••" required>
                                </div>

                                <div class="modal_row" style="margin: 0;">
                                    <label for="login_world">Mundo:</label>
                                    <select name="world" id="login_world" style="background:#0d0a08; border:1px solid #3d2f20; color:#e0d0b8; padding:6px; border-radius:2px; width:100%;">
                                        <option value="UP">UP</option>
                                        <option value="PVP">PVP</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn_action" style="margin: 0; padding: 7px 0; width:100%;">ENTRAR</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Box 2: Envio e Aplicação da Marca -->
                <div class="retro_box">
                    <div class="box_header">
                        <h3>🛡️ Selecionar ou Enviar Guildmark</h3>
                        <span class="box_badge">PADRÃO 16x12 BMP</span>
                    </div>
                    <div class="box_content" style="padding: 16px;">
                        <form method="POST" action="guildmark.php" enctype="multipart/form-data" id="form_guildmark">
                            <input type="hidden" name="action" value="save">
                            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
                            <input type="hidden" name="catalog_file" id="selected_catalog_input" value="">

                            <!-- Identificação da Guilda -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                                <div>
                                    <label style="display:block; font-size:11px; color:#d1c1a9; font-weight:bold; margin-bottom:4px;">Mundo:</label>
                                    <select name="world" id="save_world" style="width:100%; background:#0d0a08; border:1px solid #3d2f20; color:#e0d0b8; padding:6px; border-radius:2px;">
                                        <option value="UP" <?php echo $world === 'UP' ? 'selected' : ''; ?>>UP</option>
                                        <option value="PVP" <?php echo $world === 'PVP' ? 'selected' : ''; ?>>PVP</option>
                                    </select>
                                </div>

                                <div>
                                    <label style="display:block; font-size:11px; color:#d1c1a9; font-weight:bold; margin-bottom:4px;">Guilda Liderada ou Guild ID:</label>
                                    <?php if (!empty($leaders)): ?>
                                        <select name="guild_id" style="width:100%; background:#0d0a08; border:1px solid #3d2f20; color:#e0d0b8; padding:6px; border-radius:2px;" required>
                                            <?php foreach ($leaders as $leader): ?>
                                                <option value="<?php echo (int)$leader['id']; ?>">
                                                    Guild #<?php echo (int)$leader['id']; ?> - Líder: <?php echo htmlspecialchars($leader['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php else: ?>
                                        <input type="number" name="guild_id" min="1" max="65535" placeholder="Digite o ID da Guilda (Ex: 15)" style="width:100%; background:#0d0a08; border:1px solid #3d2f20; color:#e0d0b8; padding:6px; border-radius:2px;" required>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Opção A: Upload de Imagem Customizada -->
                            <div style="background: #140f0a; border: 1px solid #3a2a1b; padding: 12px; border-radius: 3px; margin-bottom: 14px;">
                                <strong style="color: #ffd891; font-size: 11px; display:block; margin-bottom: 6px;">Opção A: Enviar Imagem Personalizada (Conversão Automática)</strong>
                                <p style="font-size: 10px; color: #a3927d; margin-bottom: 8px;">
                                    Envie seu arquivo em <strong>PNG, JPG, BMP ou WEBP</strong>. O sistema redimensiona e converte com perfeição para o padrão WYD (16x12 pixels 24-bit).
                                </p>
                                <input type="file" name="arquivo" id="input_arquivo" accept="image/*" style="width:100%; background:#0d0a08; border:1px solid #3d2f20; color:#e0d0b8; padding:6px; border-radius:2px;">
                            </div>

                            <!-- Opção B: Catálogo de Brasões Prontos -->
                            <div style="background: #140f0a; border: 1px solid #3a2a1b; padding: 12px; border-radius: 3px; margin-bottom: 14px;">
                                <strong style="color: #ffd891; font-size: 11px; display:block; margin-bottom: 6px;">Opção B: Escolher do Catálogo de Brasões Oficiais (<?php echo count($catalog); ?> disponíveis)</strong>
                                <p style="font-size: 10px; color: #a3927d; margin-bottom: 8px;">
                                    Clique em uma das guildmarks abaixo para selecioná-la como brasão da sua guilda:
                                </p>

                                <div class="catalog_grid">
                                    <?php foreach ($catalog as $bmp): ?>
                                        <div class="catalog_item" data-filename="<?php echo htmlspecialchars($bmp); ?>" onclick="selectCatalogItem(this, '<?php echo htmlspecialchars($bmp); ?>')">
                                            <img src="img/guilds/catalog/<?php echo htmlspecialchars($bmp); ?>" alt="<?php echo htmlspecialchars($bmp); ?>">
                                            <span><?php echo htmlspecialchars($bmp); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div id="selected_feedback" style="margin-top: 6px; font-size: 10px; color: #ffaa33; display: none;"></div>
                            </div>

                            <button type="submit" class="btn_action" style="width:100%; font-size: 12px; padding: 10px 0;">ATUALIZAR MARCA DA GUILDA</button>
                        </form>

                        <div style="margin-top: 14px; border-top: 1px dashed #3a2e20; padding-top: 10px; font-size: 10px; color: #8f806e; line-height: 1.5;">
                            <strong>Como verificar o ID da sua Guilda no jogo:</strong><br>
                            Conecte com seu personagem Líder de Guilda no jogo e digite o comando <code>/guild</code> no chat para visualizar o número de identificação da guilda.
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <footer id="footer">
            <div class="footer_inner">
                <div class="footer_left">
                    <div class="og_logo_badge">CDK</div>
                    <div>
                        <div class="footer_copy">
                            <?php echo htmlspecialchars($SERVER_NAME); ?> • Sistema de Guildmarks 7.54.<br>
                            With Your Destiny é marca registrada da JoyImpact Co., Ltd.
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <script>
        function selectCatalogItem(element, filename) {
            document.querySelectorAll('.catalog_item').forEach(function(item) {
                item.classList.remove('selected');
            });
            element.classList.add('selected');
            document.getElementById('selected_catalog_input').value = filename;
            var feedback = document.getElementById('selected_feedback');
            feedback.style.display = 'block';
            feedback.innerHTML = '✔ Brasão selecionado do catálogo: <strong>' + filename + '</strong>';
        }

        document.getElementById('input_arquivo').addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                document.querySelectorAll('.catalog_item').forEach(function(item) {
                    item.classList.remove('selected');
                });
                document.getElementById('selected_catalog_input').value = '';
                var feedback = document.getElementById('selected_feedback');
                feedback.style.display = 'block';
                feedback.innerHTML = '📁 Arquivo selecionado para conversão: <strong>' + this.files[0].name + '</strong>';
            }
        });
    </script>
</body>
</html>

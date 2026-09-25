<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'site-security.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'db.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'config.php';

site_bootstrap('ranking');

error_reporting(0);
ini_set('display_errors', '0');

// Dados padrão das Cidades e Donos do Castelo
$cidades = [
    ["cidade" => "ARMIA",  "guild" => "REVOLUTION", "mark" => "img/guilds/catalog/b01000014.bmp"],
    ["cidade" => "AZRAN",  "guild" => "ALLIANCE",   "mark" => "img/guilds/catalog/b01000029.bmp"],
    ["cidade" => "ERION",  "guild" => "REDDRAGON",  "mark" => "img/guilds/catalog/b01000055.bmp"],
    ["cidade" => "GELO",   "guild" => "SUPREMACIA", "mark" => "img/guilds/catalog/b01000066.bmp"],
    ["cidade" => "NOATUN", "guild" => "REDTHRONE",  "mark" => "img/guilds/catalog/b01000088.bmp"]
];

// Dados dos Top Players (Mock e DB fallback)
$topPlayers = [
    ["rank" => 1, "name" => "Focker-", "class" => "FM", "level" => 200, "evo" => "ARCH", "guild" => "REVOLUTION"],
    ["rank" => 2, "name" => "LordKersef", "class" => "TK", "level" => 200, "evo" => "ARCH", "guild" => "REDTHRONE"],
    ["rank" => 3, "name" => "ThorGeralt", "class" => "BM", "level" => 200, "evo" => "S.CELESTIAL", "guild" => "SUPREMACIA"],
    ["rank" => 4, "name" => "SagitariusBR", "class" => "HT", "level" => 200, "evo" => "MORTAL", "guild" => "ALLIANCE"],
    ["rank" => 5, "name" => "Zeus_Brasil", "class" => "BM", "level" => 200, "evo" => "CELESTIAL", "guild" => "REDDRAGON"],
    ["rank" => 6, "name" => "Atena_Heal", "class" => "FM", "level" => 200, "evo" => "MORTAL", "guild" => "REVOLUTION"]
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking & Guerras de Cidades - <?php echo htmlspecialchars($SERVER_NAME); ?></title>
    <link rel="icon" type="image/png" href="images/logo.png">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .city_table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        .city_table th {
            background: #241b12;
            color: #dfb26b;
            padding: 8px;
            text-align: center;
            border-bottom: 1px solid #4a3a29;
            font-weight: bold;
        }
        .city_table td {
            padding: 8px;
            border-bottom: 1px solid #241b12;
            text-align: center;
            color: #e0d5c1;
        }
        .city_table tr:hover td {
            background: #1c150e;
        }
        .guild_mark_img {
            image-rendering: pixelated;
            width: 24px;
            height: 18px;
            border: 1px solid #54402a;
            vertical-align: middle;
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
            <a href="guildmark.php">GUILDMARK</a>
            <a href="ranking.php">RANKING</a>
            <a href="download.php">DOWNLOADS</a>
            <a href="<?php echo htmlspecialchars($LINK_DISCORD); ?>" target="_blank">DISCORD</a>
        </div>
        <div class="audio_player_ctrl">
            <audio id="bgm_audio" src="audio/Login.mp3" loop preload="auto"></audio>
            <button id="btn_toggle_bgm" class="audio_btn">🔈 Tocar BGM Clássica</button>
        </div>
    </div>

    <div id="wrapper">
        <header id="header">
            <div class="logo_area">
                <img src="images/logo.png" alt="<?php echo htmlspecialchars($SERVER_NAME); ?> Logo" class="logo_wyd">
                <div class="logo_text">
                    <h1><?php echo htmlspecialchars($SERVER_NAME); ?></h1>
                    <p>Salão da Fama & Disputa Territorial de Noatun • Versão <?php echo htmlspecialchars($SERVER_VERSION); ?></p>
                </div>
            </div>

            <nav id="main_nav">
                <a href="index.php" class="nav_item">Início</a>
                <a href="account.php" class="nav_item">Painel da Conta</a>
                <a href="guildmark.php" class="nav_item">Guildmark</a>
                <a href="ranking.php" class="nav_item active">Ranking Top</a>
                <a href="download.php" class="nav_item">Downloads</a>
                <a href="<?php echo htmlspecialchars($LINK_DISCORD); ?>" target="_blank" class="nav_item">Discord</a>
            </nav>
        </header>

        <div id="main_container">

            <!-- Coluna Central: Guerras de Cidades & Top Players -->
            <div style="flex: 1; display:flex; flex-direction:column; gap:16px;">

                <!-- Guerras de Cidades (Donos Atuais) -->
                <div class="retro_box">
                    <div class="box_header">
                        <h3>🏰 Guerras de Cidades & Castelo de Noatun</h3>
                        <span class="box_badge">DOMÍNIO ATUAL</span>
                    </div>
                    <div class="box_content" style="padding:0;">
                        <table class="city_table">
                            <thead>
                                <tr>
                                    <th>CIDADE</th>
                                    <th>GUILDA DOMINANTE</th>
                                    <th>BRASÃO</th>
                                    <th>TAXA RECOLHIDA</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cidades as $c): ?>
                                <tr>
                                    <td><strong style="color:#ffd073;"><?php echo htmlspecialchars($c['cidade']); ?></strong></td>
                                    <td><span style="font-weight:bold; color:#fff;"><?php echo htmlspecialchars($c['guild']); ?></span></td>
                                    <td>
                                        <?php if (file_exists(__DIR__ . '/' . $c['mark'])): ?>
                                            <img src="<?php echo htmlspecialchars($c['mark']); ?>" alt="Guildmark" class="guild_mark_img">
                                        <?php else: ?>
                                            <span style="font-size:10px; color:#8c7b66;">[Sem Marca]</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span style="color:#2ecc71; font-weight:bold;">10% Gold</span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Ranking Top Players Completo -->
                <div class="retro_box">
                    <div class="box_header">
                        <h3>🏆 Top Players Kersef</h3>
                        <span class="box_badge">ATUALIZADO</span>
                    </div>
                    <div class="box_content" style="padding:0;">
                        <table class="ranking_table" style="font-size:11px;">
                            <thead>
                                <tr>
                                    <th class="rank_pos">#</th>
                                    <th>Personagem</th>
                                    <th>Classe</th>
                                    <th>Level</th>
                                    <th>Evolução</th>
                                    <th>Guilda</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topPlayers as $p): ?>
                                <tr>
                                    <td class="rank_pos <?php echo $p['rank'] === 1 ? 'first' : ($p['rank'] === 2 ? 'second' : ($p['rank'] === 3 ? 'third' : '')); ?>">
                                        <?php echo $p['rank']; ?>º
                                    </td>
                                    <td><strong style="color:#fff;"><?php echo htmlspecialchars($p['name']); ?></strong></td>
                                    <td>
                                        <span class="rank_class class_<?php echo strtolower($p['class']); ?>">
                                            <?php echo htmlspecialchars($p['class']); ?>
                                        </span>
                                    </td>
                                    <td><span style="color:#ffd073; font-weight:bold;"><?php echo $p['level']; ?></span></td>
                                    <td><span style="color:#dfb26b; font-weight:bold;"><?php echo htmlspecialchars($p['evo']); ?></span></td>
                                    <td><?php echo htmlspecialchars($p['guild']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- Coluna Lateral de Apoio -->
            <aside style="width: 260px; display:flex; flex-direction:column; gap:12px;">
                <div class="retro_box">
                    <div class="box_header">
                        <h3>Horários de Guerras</h3>
                    </div>
                    <div class="box_content" style="font-size:11px; line-height:1.6; color:#baa992;">
                        <p><strong style="color:#ffd073;">⚔️ Guerra de Noatun:</strong><br>Todo Domingo às 20h00</p>
                        <div style="border-top:1px dashed #3a2e20; margin:6px 0;"></div>
                        <p><strong style="color:#ffd073;">🛡️ Guerra de Cidades:</strong><br>Todo Domingo às 21h00</p>
                        <div style="border-top:1px dashed #3a2e20; margin:6px 0;"></div>
                        <p><strong style="color:#ffd073;">🗼 Guerra de Torres:</strong><br>Diariamente às 22h00</p>
                    </div>
                </div>

                <div class="cash_shop_promo">
                    <h4>💎 Quer ver sua Guild aqui?</h4>
                    <p>Suba de level, junte seus aliados e registre a marca da sua Guild agora mesmo!</p>
                    <a href="guildmark.php" class="btn_cash">ENVIAR GUILDMARK</a>
                </div>
            </aside>

        </div>

        <footer id="footer">
            <div class="footer_inner">
                <div class="footer_left">
                    <div class="og_logo_badge">CDK</div>
                    <div>
                        <div class="footer_copy">
                            <?php echo htmlspecialchars($SERVER_NAME); ?> • Ranking Oficial e Disputas Territoriais.<br>
                            With Your Destiny é marca registrada da JoyImpact Co., Ltd.
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <script src="js/main.js"></script>
</body>
</html>

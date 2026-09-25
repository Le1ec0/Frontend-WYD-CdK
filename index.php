<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'site-security.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'db.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'config.php';

site_bootstrap('index');

error_reporting(0);
ini_set('display_errors', '0');

$Msg = '';
$Status = false;
$AccCreate = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cadastro') {
    if (!site_validate_csrf((string)($_POST['csrf'] ?? ''))) {
        $Status = true;
        $Msg = 'Sessão expirada. Recarregue a página e tente novamente.<br>';
    } else {
        $Login = trim((string)($_POST['username'] ?? ''));
        $Senha = (string)($_POST['password'] ?? '');
        $cSenha = (string)($_POST['confirm'] ?? '');
        $Email = trim((string)($_POST['email'] ?? ''));
        $Captcha = trim((string)($_POST['captcha'] ?? ''));
        $IPAddress = site_client_ip();

        if (strlen($Login) < 6 || strlen($Login) > 12 || !preg_match('/^(?=.*[A-Za-z])[A-Za-z0-9]+$/', $Login)) {
            $Status = true;
            $Msg .= 'O usuário deve ter entre 6 e 12 caracteres alfanuméricos e conter ao menos uma letra.<br>';
        }
        if (strlen($Senha) < 6 || strlen($Senha) > 10 || !preg_match('/^[A-Za-z0-9]+$/', $Senha)) {
            $Status = true;
            $Msg .= 'A senha deve ter entre 6 e 10 caracteres alfanuméricos.<br>';
        }
        if ($Senha !== $cSenha) {
            $Status = true;
            $Msg .= 'A confirmação da senha deve ser igual à senha.<br>';
        }
        if (!filter_var($Email, FILTER_VALIDATE_EMAIL)) {
            $Status = true;
            $Msg .= 'Por favor, informe um e-mail válido.<br>';
        }
        if (empty($_SESSION['captcha']) || strtolower((string)$_SESSION['captcha']) !== strtolower($Captcha)) {
            $Status = true;
            $Msg .= 'O código de verificação está incorreto.<br>';
        }

        if (!$Status) {
            $pdo = null;
            try {
                $pdo = OpenAccountDatabase();
                if ($pdo !== null) {
                    $checkAccount = $pdo->prepare('SELECT id FROM accounts WHERE account_name = ? LIMIT 1');
                    $checkAccount->execute([$Login]);
                    if ($checkAccount->fetchColumn() !== false) {
                        $Status = true;
                        $Msg = 'O nome de usuário já existe. Escolha outro.<br>';
                    } else {
                        $insertAccount = $pdo->prepare('INSERT INTO accounts (account_name, password_hash, password_scheme, numeric_token, email, registration_ip) VALUES (?, ?, ?, ?, ?, ?)');
                        $insertAccount->execute([
                            $Login,
                            password_hash($Senha, PASSWORD_DEFAULT),
                            'password_hash',
                            hex2bin('FFFFFFFFFFFF'),
                            $Email,
                            $IPAddress
                        ]);
                        $AccCreate = true;
                        $Msg = "Conta [<strong>{$Login}</strong>] criada com sucesso! Bem-vindo ao {$SERVER_NAME}.";
                    }
                } else {
                    // Fallback para quando o banco não estiver acessível localmente
                    $AccCreate = true;
                    $Msg = "Conta [<strong>{$Login}</strong>] registrada com sucesso! (Modo de teste local)";
                }
            } catch (Throwable $e) {
                $Status = true;
                $Msg = 'Não foi possível registrar a conta no momento. Tente novamente mais tarde.<br>';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($SERVER_NAME . " - " . $SERVER_SLOGAN); ?></title>
    <link rel="icon" type="image/png" href="images/logo.png">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Top Bar de Utilidades -->
    <div id="top_util_bar">
        <div class="ongame_network_links">
            <span style="color:#e69d30; font-weight:bold; margin-right:10px;"><?php echo htmlspecialchars($SERVER_NAME); ?>:</span>
            <a href="index.php">INÍCIO</a>
            <a href="account.php">PAINEL DA CONTA</a>
            <a href="guildmark.php">GUILDMARK</a>
            <a href="ranking.php">RANKING & CIDADES</a>
            <a href="download.php">DOWNLOADS</a>
            <a href="<?php echo htmlspecialchars($LINK_DISCORD); ?>" target="_blank">DISCORD</a>
        </div>
        <div class="audio_player_ctrl">
            <audio id="bgm_audio" src="audio/Login.mp3" loop preload="auto"></audio>
            <button id="btn_toggle_bgm" class="audio_btn">🔈 Tocar BGM Clássica</button>
        </div>
    </div>

    <!-- Wrapper Principal -->
    <div id="wrapper">

        <!-- Topo & Banner Clássico (Auge 2008 Ongame) -->
        <header id="header">
            <div class="logo_area">
                <img src="images/logo.png" alt="<?php echo htmlspecialchars($SERVER_NAME); ?> Logo" class="logo_wyd">
                <div class="logo_text">
                    <h1><?php echo htmlspecialchars($SERVER_NAME); ?></h1>
                    <p><?php echo htmlspecialchars($SERVER_SLOGAN); ?> • Versão <?php echo htmlspecialchars($SERVER_VERSION); ?></p>
                </div>
            </div>

            <!-- Navegação Superior Gótica Metálica -->
            <nav id="main_nav">
                <a href="index.php" class="nav_item active">Início</a>
                <a href="#cadastro" class="nav_item open_modal_cadastro">Criar Conta</a>
                <a href="account.php" class="nav_item">Painel da Conta</a>
                <a href="guildmark.php" class="nav_item">Guildmark</a>
                <a href="ranking.php" class="nav_item">Ranking Top</a>
                <a href="download.php" class="nav_item">Downloads</a>
                <a href="<?php echo htmlspecialchars($LINK_DISCORD); ?>" target="_blank" class="nav_item">Comunidade</a>
            </nav>
        </header>

        <!-- Container do Conteúdo com 3 Colunas -->
        <div id="main_container">

            <!-- Coluna da Esquerda (Login, Teclado Virtual, Menus) -->
            <aside id="sidebar_left">

                <!-- Box de Login Ongame Clássico -->
                <div class="retro_box" id="login_box">
                    <div class="box_header">
                        <h3>Acesso à Conta</h3>
                        <span class="box_badge">CDK ID</span>
                    </div>
                    <div class="box_content">
                        <form id="form_login_ongame" class="login_form" action="account.php" method="GET">
                            <div class="login_row">
                                <label for="login_user">Usuário:</label>
                                <input type="text" name="user" id="login_user" placeholder="Login" maxlength="12" required>
                            </div>
                            <div class="login_row">
                                <label for="login_pass">Senha:</label>
                                <input type="password" name="pass" id="login_pass" placeholder="Senha" maxlength="10" required>
                            </div>
                            <div class="login_actions">
                                <button type="submit" class="btn_login">ENTRAR</button>
                                <button type="button" class="btn_login open_modal_cadastro" style="background:#3a2817; border-color:#634b30;">REGISTRAR</button>
                            </div>

                            <div class="login_virtual_kb_toggle">
                                <span>Teclado Virtual Antifraude</span>
                                <span id="open_virtual_kb">[Abrir Teclado]</span>
                            </div>

                            <div class="login_links">
                                <a href="account.php">» Alterar minha senha</a>
                                <a href="#!" class="open_modal_cadastro">» Criar conta no servidor</a>
                                <a href="guildmark.php">» Registrar marca de Guild</a>
                                <a href="<?php echo htmlspecialchars($LINK_DISCORD); ?>" target="_blank">» Atendimento Discord</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Teclado Virtual Flutuante -->
                <div id="virtual_keyboard">
                    <div class="vk_header">
                        <span>⌨ Teclado Virtual Seguro (Anti-Keylogger)</span>
                        <span class="vk_close" id="close_virtual_kb">✕</span>
                    </div>
                    <div class="vk_keys" id="vk_keys_container">
                        <!-- Gerado dinamicamente via JS -->
                    </div>
                </div>

                <!-- Menu Rápido -->
                <div class="retro_box" id="quick_menu_box">
                    <div class="box_header">
                        <h3>Menu Rápido</h3>
                    </div>
                    <div class="box_content" style="padding: 4px 0;">
                        <ul class="quick_menu_list">
                            <li><a href="#cadastro" class="open_modal_cadastro"><span>⚡</span> Ativar Conta Nova</a></li>
                            <li><a href="account.php"><span>🔑</span> Gerenciar Senha</a></li>
                            <li><a href="guildmark.php"><span>🛡️</span> Enviar Guildmark (16x12)</a></li>
                            <li><a href="ranking.php"><span>🏆</span> Salão da Fama & Guerras</a></li>
                            <li><a href="download.php"><span>📥</span> Cliente 7670 & Patch 7.69</a></li>
                            <li><a href="<?php echo htmlspecialchars($LINK_DISCORD); ?>" target="_blank"><span>💬</span> Discord Oficial</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Status dos Servidores CDK -->
                <div class="retro_box">
                    <div class="box_header">
                        <h3>Servidores <?php echo htmlspecialchars($SERVER_NAME); ?></h3>
                        <span class="box_badge">ONLINE</span>
                    </div>
                    <div class="box_content">
                        <div class="server_status_list">
                            <?php foreach ($CHANNELS as $canal): ?>
                            <div class="server_item">
                                <span class="server_name"><?php echo htmlspecialchars($canal['nome']); ?></span>
                                <span class="server_state">
                                    <span class="status_dot <?php echo $canal['status']; ?>"></span>
                                    <?php echo htmlspecialchars($canal['lotacao']); ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Manutenção Semanal -->
                <div class="retro_box">
                    <div class="box_header">
                        <h3>Manutenção</h3>
                    </div>
                    <div class="box_content">
                        <div class="maintenance_box">
                            <strong><?php echo htmlspecialchars($MAINTENANCE_DAY); ?></strong>
                            <?php echo htmlspecialchars($MAINTENANCE_TIME); ?>
                        </div>
                    </div>
                </div>

            </aside>

            <!-- Coluna Central (Destaques, Notícias, Mídia) -->
            <main id="main_content">

                <!-- Mensagem de Feedback de Cadastro -->
                <?php if ($Msg): ?>
                    <div class="retro_box" style="margin-bottom: 12px; border-color: <?php echo $AccCreate ? '#2e7d32' : '#c62828'; ?>;">
                        <div class="box_header" style="background: <?php echo $AccCreate ? '#143818' : '#3e1616'; ?>;">
                            <h3 style="color: <?php echo $AccCreate ? '#81c784' : '#ef9a9a'; ?>;">
                                <?php echo $AccCreate ? '✔ SUCESSO' : '✖ ATENÇÃO'; ?>
                            </h3>
                        </div>
                        <div class="box_content" style="font-size:12px; line-height:1.5;">
                            <?php echo $Msg; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Banner Principal Destaque -->
                <section class="featured_banner" id="inicio">
                    <div class="featured_overlay">
                        <span class="featured_tag">Servidor Oficial 7.69 / 7670</span>
                        <h2 class="featured_title"><?php echo htmlspecialchars($SERVER_NAME); ?>: A Experiência da Release 7.69!</h2>
                        <p class="featured_desc">Taxas equilibradas (EXP: <?php echo htmlspecialchars($EXP_RATE); ?>), Guerra de Noatun aos domingos, Guerra de Torres, montarias míticas e PvP balanceado sem pay-to-win.</p>
                    </div>
                </section>

                <!-- Seção de Notícias & Eventos -->
                <section class="retro_box" id="noticias">
                    <div class="news_tabs">
                        <div class="news_tab active" data-cat="todas">Todas</div>
                        <div class="news_tab" data-cat="noticia">Notícias</div>
                        <div class="news_tab" data-cat="evento">Eventos</div>
                        <div class="news_tab" data-cat="manut">Manutenções</div>
                        <div class="news_tab" data-cat="gmblog">GM Blog</div>
                    </div>

                    <div class="news_list">
                        <article class="news_item" data-cat="evento">
                            <div class="news_item_title">
                                <span class="badge_news evento">Evento</span>
                                <span class="news_title_text">[Inauguração] Grande Guerra de Noatun com Bonificação de Cash!</span>
                            </div>
                            <span class="news_item_date">Domingo 20:00</span>
                        </article>

                        <article class="news_item" data-cat="noticia">
                            <div class="news_item_title">
                                <span class="badge_news noticia">Notícia</span>
                                <span class="news_title_text">[Cliente] Lançamento do Cliente 7670 e Patch 7.69</span>
                            </div>
                            <span class="news_item_date">Recente</span>
                        </article>

                        <article class="news_item" data-cat="evento">
                            <div class="news_item_title">
                                <span class="badge_news evento">Evento</span>
                                <span class="news_title_text">[Big Sale] Pacote Inicial para Guildas e Líderes</span>
                            </div>
                            <span class="news_item_date">Ativo</span>
                        </article>

                        <article class="news_item" data-cat="manut">
                            <div class="news_item_title">
                                <span class="badge_news manut">Manut</span>
                                <span class="news_title_text">[Manutenção] Otimização das Zonas Elementais da Água e Kefra</span>
                            </div>
                            <span class="news_item_date">Finalizada</span>
                        </article>

                        <article class="news_item" data-cat="gmblog">
                            <div class="news_item_title">
                                <span class="badge_news gmblog">GM Blog</span>
                                <span class="news_title_text">[GM Blog] Cronograma de Invasões e Balanceamento das Classes</span>
                            </div>
                            <span class="news_item_date">Destaque</span>
                        </article>
                    </div>
                </section>

                <!-- Galeria de Imagens de Batalha -->
                <section class="retro_box">
                    <div class="box_header">
                        <h3>Galeria de Kersef & Batalhas Épicas</h3>
                    </div>
                    <div class="media_grid">
                        <div class="media_card">
                            <img src="images/launcher_original.png" alt="Launcher Oficial <?php echo htmlspecialchars($SERVER_NAME); ?>">
                            <div class="media_caption">Cliente oficial 7670</div>
                        </div>
                        <div class="media_card">
                            <img src="images/background.jpg" alt="Campos de Armia">
                            <div class="media_caption">Campos de Armia</div>
                        </div>
                        <div class="media_card">
                            <img src="images/main_frame.png" alt="Castelo de Noatun">
                            <div class="media_caption">Guerra de Noatun</div>
                        </div>
                    </div>
                </section>

                <!-- Guia de Classes Rápido -->
                <section class="retro_box" id="classes">
                    <div class="box_header">
                        <h3>Guia de Classes Clássicas</h3>
                    </div>
                    <div class="box_content" style="display:grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
                        <div style="background:#15110d; padding:8px; border:1px solid #33261a; border-radius:2px;">
                            <strong style="color:#fca5a5;">⚔️ TransKnight (TK)</strong>
                            <p style="font-size:10px; color:#a89984; margin-top:3px;">Mestres da espada e da fé divina. Defesa impenetrável e habilidades devastadoras corpo a corpo.</p>
                        </div>
                        <div style="background:#15110d; padding:8px; border:1px solid #33261a; border-radius:2px;">
                            <strong style="color:#93c5fd;">🔮 Foema (FM)</strong>
                            <p style="font-size:10px; color:#a89984; margin-top:3px;">Mestras da magia arcana e da cura divina. Destroem hordas inteiras e ressuscitam aliados.</p>
                        </div>
                        <div style="background:#15110d; padding:8px; border:1px solid #33261a; border-radius:2px;">
                            <strong style="color:#86efac;">🐾 BeastMaster (BM)</strong>
                            <p style="font-size:10px; color:#a89984; margin-top:3px;">Comandantes da natureza e das feras. Invocadores implacáveis e mestres da transmutação.</p>
                        </div>
                        <div style="background:#15110d; padding:8px; border:1px solid #33261a; border-radius:2px;">
                            <strong style="color:#fde047;">🏹 Huntress (HT)</strong>
                            <p style="font-size:10px; color:#a89984; margin-top:3px;">Ágeis caçadoras de elite. Especialistas no arco longo, lâminas duplas e armadilhas venenosas.</p>
                        </div>
                    </div>
                </section>

            </main>

            <!-- Coluna da Direita (Download, Ranking, Loja de Cash) -->
            <aside id="sidebar_right">

                <!-- Botão de Download Gigante -->
                <div id="download">
                    <a href="download.php" class="btn_download_large">
                        <span class="dl_main_text">DOWNLOAD DO JOGO</span>
                        <span class="dl_sub_text">Cliente Completo <?php echo htmlspecialchars($SERVER_NAME); ?> 7670</span>
                    </a>
                </div>

                <!-- Box de Ranking Top Players -->
                <div class="retro_box" id="ranking">
                    <div class="box_header">
                        <h3>Top Ranking</h3>
                        <a href="ranking.php" style="color:#e0c18d; font-size:9px; text-decoration:none;">[Ver Completo]</a>
                    </div>
                    <div class="box_content" style="padding:0;">
                        <table class="ranking_table">
                            <thead>
                                <tr>
                                    <th class="rank_pos">#</th>
                                    <th>Nick</th>
                                    <th>Classe</th>
                                    <th>Evolução</th>
                                </tr>
                            </thead>
                            <tbody id="ranking_tbody">
                                <!-- Preenchido dinamicamente via JS ou banco -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Loja de Doações / Cash -->
                <div class="cash_shop_promo" id="doacoes">
                    <h4>💎 Apoie o Servidor</h4>
                    <p>Adquira pacotes de Cash automatizados com liberação imediata para apoiar os custos do servidor!</p>
                    <a href="<?php echo htmlspecialchars($LINK_DISCORD); ?>" target="_blank" class="btn_cash">SISTEMA DE CASH</a>
                </div>

                <!-- Painel da Guilda -->
                <div class="retro_box">
                    <div class="box_header">
                        <h3>Marcas de Guild</h3>
                    </div>
                    <div class="box_content" style="font-size:10px; line-height:1.5; text-align:center;">
                        <p>É líder de Guild? Registre ou atualize o brasão 16x12 BMP da sua guilda.</p>
                        <a href="guildmark.php" class="btn_secondary" style="margin-top:8px; display:inline-block; border-color:#e08328; color:#ffcc66;">GERENCIAR GUILDMARK</a>
                    </div>
                </div>

            </aside>

        </div>

    </div>

    <!-- Rodapé Clássico Estilizado para o CDK -->
    <footer id="footer">
        <div class="footer_inner">
            <div class="footer_left">
                <div class="og_logo_badge">CDK</div>
                <div>
                    <div class="footer_links">
                        <a href="index.php">Início</a> |
                        <a href="account.php">Alterar Senha</a> |
                        <a href="guildmark.php">Guildmark</a> |
                        <a href="ranking.php">Ranking</a> |
                        <a href="download.php">Download</a> |
                        <a href="<?php echo htmlspecialchars($LINK_DISCORD); ?>" target="_blank">Discord</a>
                    </div>
                    <div class="footer_copy">
                        <?php echo htmlspecialchars($SERVER_NAME); ?> • Servidor Privado de With Your Destiny para fins de entretenimento.<br>
                        With Your Destiny é marca registrada da JoyImpact Co., Ltd.
                    </div>
                </div>
            </div>
            <div class="footer_rating">
                CLASSIFICAÇÃO INDICATIVA<br>
                <span style="font-size:14px; color:#ff6600;">14 ANOS</span><br>
                PvP & Estratégia
            </div>
        </div>
    </footer>

    <!-- Modal de Cadastro / Criar Conta (Com Captcha e CSRF) -->
    <div class="modal_backdrop" id="modal_cadastro">
        <div class="modal_window">
            <div class="modal_header">
                <span>⚔️ Criar Conta - <?php echo htmlspecialchars($SERVER_NAME); ?></span>
                <span class="modal_close" id="close_modal_cadastro">✕</span>
            </div>
            <div class="modal_body">
                <div id="modal_msg" class="modal_msg"></div>

                <form id="form_cadastro_modal" method="POST" action="index.php">
                    <input type="hidden" name="action" value="cadastro">
                    <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(site_csrf_token()); ?>">

                    <div class="modal_row">
                        <label for="cad_user">Nome de Usuário (6 a 12 caracteres):</label>
                        <input type="text" name="username" id="cad_user" maxlength="12" placeholder="Ex: Guerreiro99" required>
                    </div>
                    <div class="modal_row">
                        <label for="cad_email">E-mail Principal:</label>
                        <input type="email" name="email" id="cad_email" placeholder="seuemail@provedor.com" required>
                    </div>
                    <div class="modal_row">
                        <label for="cad_pass">Senha do Jogo (6 a 10 caracteres):</label>
                        <input type="password" name="password" id="cad_pass" maxlength="10" placeholder="••••••••" required>
                    </div>
                    <div class="modal_row">
                        <label for="cad_pass_conf">Confirmar Senha:</label>
                        <input type="password" name="confirm" id="cad_pass_conf" maxlength="10" placeholder="••••••••" required>
                    </div>
                    <div class="modal_row">
                        <label for="captcha">Código de Verificação:</label>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <input type="text" name="captcha" id="captcha" maxlength="4" style="width:100px;" placeholder="Código" required>
                            <img src="./misc/CaptchaImg.php" id="img_captcha" alt="Captcha" height="28" style="border:1px solid #4a3a29; border-radius:2px;">
                            <button type="button" onclick="document.getElementById('img_captcha').src='./misc/CaptchaImg.php?'+Math.random();" style="background:#2b1f14; border:1px solid #54402a; color:#e0c18d; padding:5px 8px; font-size:10px; cursor:pointer; border-radius:2px;">↻ Atualizar</button>
                        </div>
                    </div>
                    <div class="modal_row" style="font-size:10px; color:#9c8c76;">
                        <input type="checkbox" id="cad_termos" checked required>
                        <label for="cad_termos" style="display:inline; font-weight:normal;">Li e concordo com as Regras do Servidor.</label>
                    </div>
                    <button type="submit" class="btn_modal_submit">CONCLUIR CADASTRO</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts do Site -->
    <script src="js/main.js"></script>
</body>
</html>

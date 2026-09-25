<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'site-security.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'config.php';

site_bootstrap('download', 60, 60);

error_reporting(0);
ini_set('display_errors', '0');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Downloads & Requisitos - <?php echo htmlspecialchars($SERVER_NAME); ?> (v<?php echo htmlspecialchars($SERVER_VERSION); ?>)</title>
    <link rel="icon" type="image/png" href="images/logo.png">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Top Bar de Anúncio / Sistema de Áudio -->
    <div id="top_util_bar">
        <div class="ongame_network_links">
            <span style="color:#e69d30; font-weight:bold; margin-right:10px;"><?php echo htmlspecialchars($SERVER_NAME); ?>:</span>
            <a href="index.php">INÍCIO</a>
            <a href="account.php">PAINEL DA CONTA</a>
            <a href="guildmark.php">GUILDMARK</a>
            <a href="ranking.php">RANKING</a>
            <a href="download.php" style="color:#ffd27d;">DOWNLOADS</a>
            <a href="<?php echo htmlspecialchars($LINK_DISCORD); ?>" target="_blank">DISCORD OFICIAL</a>
        </div>
        <div class="audio_player_ctrl">
            <audio id="bgm_audio" src="audio/Login.mp3" loop preload="auto"></audio>
            <button id="btn_toggle_bgm" class="audio_btn">▶ Tocar BGM Clássica</button>
        </div>
    </div>

    <div id="wrapper">
        <!-- Cabeçalho Principal -->
        <header id="header">
            <div class="logo_area">
                <a href="index.php"><img src="images/logo.png" alt="<?php echo htmlspecialchars($SERVER_NAME); ?> Logo" class="logo_wyd"></a>
                <div class="logo_text">
                    <h1><?php echo htmlspecialchars($SERVER_NAME); ?></h1>
                    <p><?php echo htmlspecialchars($SERVER_SLOGAN); ?></p>
                </div>
            </div>

            <nav id="main_nav">
                <a href="index.php" class="nav_item">Início</a>
                <a href="account.php" class="nav_item">Painel da Conta</a>
                <a href="guildmark.php" class="nav_item">Guildmark</a>
                <a href="ranking.php" class="nav_item">Ranking Top</a>
                <a href="download.php" class="nav_item active">Downloads</a>
                <a href="<?php echo htmlspecialchars($LINK_DISCORD); ?>" target="_blank" class="nav_item">Discord</a>
            </nav>
        </header>

        <!-- Banner com Estilo 2008 Ongame -->
        <div class="main_banner_container">
            <div class="banner_title_gold">CENTRAL DE DOWNLOADS & INSTALAÇÃO</div>
            <div class="banner_sub">Baixe o cliente oficial do <?php echo htmlspecialchars($SERVER_NAME); ?> v<?php echo htmlspecialchars($SERVER_VERSION); ?> e comece sua jornada em Kersef!</div>
        </div>

        <div id="main_container">
            <!-- Coluna Principal (Conteúdo de Downloads) -->
            <div id="left_column" style="width: 710px;">

                <!-- Box 1: Cliente Completo -->
                <div class="retro_box">
                    <div class="box_header">
                        <h3>⚔️ Cliente Completo do Jogo (Versão <?php echo htmlspecialchars($SERVER_VERSION); ?>)</h3>
                        <span class="box_badge">RECOMENDADO</span>
                    </div>
                    <div class="box_content">
                        <p style="color: #d8c7af; font-size: 11px; line-height: 1.5; margin-bottom: 12px;">
                            O cliente completo contém os arquivos essenciais para jogar o <?php echo htmlspecialchars($SERVER_NAME); ?>: modelos 3D, mapas, texturas, efeitos e músicas da release 7.69. O pacote usa o <strong>WYD.exe 7670</strong> compilado para o protocolo atual.
                        </p>

                        <div class="dl_grid">
                            <!-- Mirror 1: Servidor Direto -->
                            <div class="dl_card">
                                <div>
                                    <div class="dl_card_header">
                                        <div class="dl_card_title">⚡ Servidor Direto</div>
                                        <span class="dl_card_badge">ALTA VELOCIDADE</span>
                                    </div>
                                    <div class="dl_card_meta">
                                        <span>📁 ~680 MB</span>
                                        <span>📦 Formato .ZIP</span>
                                    </div>
                                    <div class="dl_card_info">
                                        Download direto sem limites do nosso servidor de arquivos dedicado.
                                    </div>
                                </div>
                                <a href="<?php echo htmlspecialchars($LINK_CLIENT_DIRECT); ?>" class="dl_btn dl_btn_primary" download>
                                    BAIXAR CLIENTE DIRETO
                                </a>
                            </div>

                            <!-- Mirror 2: Google Drive -->
                            <div class="dl_card">
                                <div>
                                    <div class="dl_card_header">
                                        <div class="dl_card_title">☁️ Google Drive</div>
                                        <span class="dl_card_badge drive">ESPELHO 1</span>
                                    </div>
                                    <div class="dl_card_meta">
                                        <span>📁 ~680 MB</span>
                                        <span>📦 Formato .ZIP</span>
                                    </div>
                                    <div class="dl_card_info">
                                        Download rápido e seguro pelos servidores em nuvem do Google.
                                    </div>
                                </div>
                                <a href="<?php echo htmlspecialchars($LINK_CLIENT_DRIVE); ?>" target="_blank" rel="noopener" class="dl_btn dl_btn_secondary">
                                    BAIXAR VIA GOOGLE DRIVE
                                </a>
                            </div>

                            <!-- Mirror 3: Mega.nz -->
                            <div class="dl_card">
                                <div>
                                    <div class="dl_card_header">
                                        <div class="dl_card_title">🔴 Mega.nz</div>
                                        <span class="dl_card_badge mega">ESPELHO 2</span>
                                    </div>
                                    <div class="dl_card_meta">
                                        <span>📁 ~680 MB</span>
                                        <span>📦 Formato .ZIP</span>
                                    </div>
                                    <div class="dl_card_info">
                                        Espelho de backup na nuvem com taxa de download acelerada.
                                    </div>
                                </div>
                                <a href="<?php echo htmlspecialchars($LINK_CLIENT_MEGA); ?>" target="_blank" rel="noopener" class="dl_btn dl_btn_secondary">
                                    BAIXAR VIA MEGA.NZ
                                </a>
                            </div>

                            <!-- Mirror 4: MediaFire -->
                            <div class="dl_card">
                                <div>
                                    <div class="dl_card_header">
                                        <div class="dl_card_title">🔥 MediaFire</div>
                                        <span class="dl_card_badge mediafire">ESPELHO 3</span>
                                    </div>
                                    <div class="dl_card_meta">
                                        <span>📁 ~680 MB</span>
                                        <span>📦 Formato .ZIP</span>
                                    </div>
                                    <div class="dl_card_info">
                                        Download direto pelo navegador sem necessidade de cadastro.
                                    </div>
                                </div>
                                <a href="<?php echo htmlspecialchars($LINK_CLIENT_MEDIAFIRE); ?>" target="_blank" rel="noopener" class="dl_btn dl_btn_secondary">
                                    BAIXAR VIA MEDIAFIRE
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Box 2: Patch Manual -->
                <div class="retro_box" style="margin-top: 15px;">
                    <div class="box_header">
                        <h3>🔧 Patch Manual de Atualização</h3>
                        <span class="box_badge">OPCIONAL</span>
                    </div>
                    <div class="box_content">
                        <p style="color: #d8c7af; font-size: 11px; line-height: 1.5; margin-bottom: 12px;">
                            Utilize o Patch Manual caso você já possua uma instalação 7.69/7670 ou precise atualizar somente os arquivos do cliente. Basta descompactar os arquivos na raiz da pasta do jogo, substituindo quando solicitado.
                        </p>

                        <div class="dl_grid">
                            <div class="dl_card">
                                <div>
                                    <div class="dl_card_header">
                                        <div class="dl_card_title">📦 Patch Manual Direto</div>
                                        <span class="dl_card_badge">ATUALIZAÇÃO</span>
                                    </div>
                                    <div class="dl_card_meta">
                                        <span>📁 ~15 MB</span>
                                        <span>📦 Formato .ZIP</span>
                                    </div>
                                    <div class="dl_card_info">
                                        Contém os arquivos complementares do cliente 7670 e as configurações da release 7.69.
                                    </div>
                                </div>
                                <a href="<?php echo htmlspecialchars($LINK_PATCH_DIRECT); ?>" class="dl_btn dl_btn_primary" download>
                                    BAIXAR PATCH DIRETO
                                </a>
                            </div>

                            <div class="dl_card">
                                <div>
                                    <div class="dl_card_header">
                                        <div class="dl_card_title">🔴 Patch Manual (Mega)</div>
                                        <span class="dl_card_badge mega">ESPELHO</span>
                                    </div>
                                    <div class="dl_card_meta">
                                        <span>📁 ~15 MB</span>
                                        <span>📦 Formato .ZIP</span>
                                    </div>
                                    <div class="dl_card_info">
                                        Mirror alternativo para download do patch de arquivos.
                                    </div>
                                </div>
                                <a href="<?php echo htmlspecialchars($LINK_PATCH_MEGA); ?>" target="_blank" rel="noopener" class="dl_btn dl_btn_secondary">
                                    BAIXAR PATCH VIA MEGA
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Box 3: Requisitos de Sistema -->
                <div class="retro_box" style="margin-top: 15px;">
                    <div class="box_header">
                        <h3>💻 Requisitos de Sistema (Hardware & Software)</h3>
                        <span class="box_badge">ESPECIFICAÇÕES</span>
                    </div>
                    <div class="box_content">
                        <p style="color: #bfa682; font-size: 10px; margin-bottom: 8px;">
                            Verifique se seu computador atende aos requisitos abaixo para garantir o melhor desempenho e estabilidade durante as guerras e caçadas.
                        </p>

                        <table class="req_table">
                            <thead>
                                <tr>
                                    <th style="width: 25%;">Componente</th>
                                    <th style="width: 37%;">Requisitos Mínimos</th>
                                    <th style="width: 38%;">Requisitos Recomendados</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Sistema Operacional</strong></td>
                                    <td>Windows XP / Vista / 7 / 8 / 10 / 11</td>
                                    <td>Windows 10 ou 11 (64-bit)</td>
                                </tr>
                                <tr>
                                    <td><strong>Processador (CPU)</strong></td>
                                    <td>Intel Pentium III 800 MHz ou AMD equivalente</td>
                                    <td>Intel Core i3 / AMD Ryzen 3 ou superior</td>
                                </tr>
                                <tr>
                                    <td><strong>Memória RAM</strong></td>
                                    <td>512 MB de RAM</td>
                                    <td>2 GB de RAM ou superior</td>
                                </tr>
                                <tr>
                                    <td><strong>Placa de Vídeo (GPU)</strong></td>
                                    <td>32 MB compatível com DirectX 9.0c</td>
                                    <td>128 MB ou superior (NVIDIA / AMD / Intel HD)</td>
                                </tr>
                                <tr>
                                    <td><strong>Espaço em Disco</strong></td>
                                    <td>2.0 GB de espaço livre em disco</td>
                                    <td>5.0 GB de espaço livre (SSD recomendado)</td>
                                </tr>
                                <tr>
                                    <td><strong>DirectX</strong></td>
                                    <td>DirectX 9.0c End-User Runtime</td>
                                    <td>DirectX 9.0c atualizado</td>
                                </tr>
                                <tr>
                                    <td><strong>Conexão com a Internet</strong></td>
                                    <td>Banda Larga de 1 Mbps</td>
                                    <td>Banda Larga de 10 Mbps ou superior (Baixa latência)</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Box 4: Softwares Adicionais e Drivers -->
                <div class="retro_box" style="margin-top: 15px;">
                    <div class="box_header">
                        <h3>🛠️ Softwares e Drivers Essenciais</h3>
                        <span class="box_badge">COMPATIBILIDADE</span>
                    </div>
                    <div class="box_content">
                        <p style="color: #bfa682; font-size: 11px; margin-bottom: 12px;">
                            Se você encontrar erros como tela preta, travamento no carregamento ou DLLs ausentes ao iniciar o jogo, instale os componentes abaixo:
                        </p>

                        <div class="dl_grid">
                            <div class="dl_card">
                                <div>
                                    <div class="dl_card_header">
                                        <div class="dl_card_title">🎮 DirectX 9.0c Runtimes</div>
                                        <span class="dl_card_badge">MICROSOFT</span>
                                    </div>
                                    <div class="dl_card_info">
                                        Indispensável para a renderização gráfica dos efeitos, magias e texturas do WYD no Windows 10 e 11.
                                    </div>
                                </div>
                                <a href="<?php echo htmlspecialchars($LINK_DIRECTX); ?>" target="_blank" rel="noopener" class="dl_btn dl_btn_secondary">
                                    BAIXAR DIRECTX OFICIAL
                                </a>
                            </div>

                            <div class="dl_card">
                                <div>
                                    <div class="dl_card_header">
                                        <div class="dl_card_title">🧩 Visual C++ Redist (x86)</div>
                                        <span class="dl_card_badge">MICROSOFT</span>
                                    </div>
                                    <div class="dl_card_info">
                                        Corrige erros comuns de bibliotecas C++ ausentes (como `MSVCR100.dll` ou `VCRUNTIME140.dll`).
                                    </div>
                                </div>
                                <a href="<?php echo htmlspecialchars($LINK_VCREDIST); ?>" target="_blank" rel="noopener" class="dl_btn dl_btn_secondary">
                                    BAIXAR VISUAL C++ (x86)
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Box 5: Guia de Instalação e Dicas de Solução de Problemas -->
                <div class="retro_box" style="margin-top: 15px; margin-bottom: 20px;">
                    <div class="box_header">
                        <h3>📖 Guia Passo a Passo de Instalação</h3>
                        <span class="box_badge">PASSO A PASSO</span>
                    </div>
                    <div class="box_content">
                        <ul class="install_steps">
                            <li class="install_step_item">
                                <div class="step_num">1</div>
                                <div class="step_content">
                                    <strong>Baixe o Cliente Completo</strong>
                                    <p>Escolha um dos links acima (Servidor Direto, Google Drive, Mega ou MediaFire) e salve o arquivo compactado no seu computador.</p>
                                </div>
                            </li>
                            <li class="install_step_item">
                                <div class="step_num">2</div>
                                <div class="step_content">
                                    <strong>Extraia os Arquivos</strong>
                                    <p>Utilize um programa como WinRAR ou 7-Zip para extrair a pasta do jogo em um local acessível (recomendamos pastas como <code>C:\Jogos\WYD CDK</code>).</p>
                                </div>
                            </li>
                            <li class="install_step_item">
                                <div class="step_num">3</div>
                                <div class="step_content">
                                    <strong>Configuração de Administrador & Compatibilidade</strong>
                                    <p>Clique com o botão direito no arquivo <code>WYD.exe</code>, selecione <em>Propriedades &gt; Compatibilidade</em> se necessário e salve.</p>
                                </div>
                            </li>
                            <li class="install_step_item">
                                <div class="step_num">4</div>
                                <div class="step_content">
                                    <strong>Abra o Launcher e Conecte-se!</strong>
                                    <p>Abra o <code>WYD.exe</code> da pasta do cliente 7670 e conecte-se ao servidor. Bom jogo em Kersef!</p>
                                </div>
                            </li>
                        </ul>

                        <div class="notice_box_info">
                            <strong>Dica Importante para Windows 10 e Windows 11:</strong><br>
                            Caso o jogo feche ao abrir ou apresente tela preta, abra as propriedades do arquivo <code>WYD.exe</code>, marque a opção <em>"Modo de Compatibilidade: Windows 7"</em> ou <em>"Windows XP (Service Pack 3)"</em> e certifique-se de que a resolução no arquivo <code>Config.exe</code> seja condizente com o seu monitor.
                        </div>
                    </div>
                </div>

            </div>

            <!-- Coluna da Direita (Painel Rápido, Status, Discord) -->
            <div id="right_column" style="width: 270px;">

                <!-- Box Criar Conta / Painel -->
                <div class="retro_box">
                    <div class="box_header">
                        <h3>👤 Acesso Rápido</h3>
                        <span class="box_badge">CONTA</span>
                    </div>
                    <div class="box_content" style="text-align: center;">
                        <p style="font-size: 11px; color: #d4c2a7; margin-bottom: 12px; line-height: 1.4;">
                            Ainda não tem conta no servidor? Crie sua conta gratuitamente em menos de 1 minuto!
                        </p>
                        <a href="index.php" class="btn_action" style="display: block; width: 100%; text-decoration: none; margin-bottom: 8px;">
                            CRIAR CONTA AGORA
                        </a>
                        <a href="account.php" class="btn_action" style="display: block; width: 100%; background: linear-gradient(to bottom, #2d2319 0%, #17110c 100%); border-color: #4a3826; text-decoration: none;">
                            PAINEL DA CONTA
                        </a>
                    </div>
                </div>

                <!-- Status dos Canais -->
                <div class="retro_box" style="margin-top: 15px;">
                    <div class="box_header">
                        <h3>🌐 Status dos Canais</h3>
                        <span class="box_badge">ONLINE</span>
                    </div>
                    <div class="box_content">
                        <ul class="channel_list">
                            <?php foreach ($CHANNELS as $channel): ?>
                                <li class="channel_item">
                                    <div class="channel_name">
                                        <span class="status_led <?php echo htmlspecialchars($channel['status']); ?>"></span>
                                        <strong><?php echo htmlspecialchars($channel['nome']); ?></strong>
                                    </div>
                                    <div class="channel_meta">
                                        <span class="channel_type"><?php echo htmlspecialchars($channel['tipo']); ?></span>
                                        <span class="channel_load"><?php echo htmlspecialchars($channel['lotacao']); ?></span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <div class="maint_info">
                            <strong>Manutenção Semanal:</strong><br>
                            <?php echo htmlspecialchars($MAINTENANCE_DAY); ?> - <?php echo htmlspecialchars($MAINTENANCE_TIME); ?>
                        </div>
                    </div>
                </div>

                <!-- Box Comunidade Discord -->
                <div class="retro_box" style="margin-top: 15px;">
                    <div class="box_header">
                        <h3>💬 Comunidade Oficial</h3>
                        <span class="box_badge">DISCORD</span>
                    </div>
                    <div class="box_content" style="text-align: center;">
                        <p style="font-size: 11px; color: #bfa682; margin-bottom: 10px; line-height: 1.4;">
                            Precisa de suporte com o download ou instalação? Entre no nosso Discord oficial e fale diretamente com a equipe e jogadores!
                        </p>
                        <a href="<?php echo htmlspecialchars($LINK_DISCORD); ?>" target="_blank" rel="noopener" class="dl_btn dl_btn_primary" style="font-size: 11px;">
                            ENTRAR NO DISCORD
                        </a>
                    </div>
                </div>

            </div>
        </div>

        <!-- Rodapé Ongame Clássico -->
        <footer id="footer">
            <div class="footer_inner">
                <div class="footer_left">
                    <div class="og_logo_badge">ONGAME</div>
                    <div>
                        <div class="footer_links">
                            <a href="index.php">Início</a> |
                            <a href="account.php">Painel da Conta</a> |
                            <a href="guildmark.php">Guildmark</a> |
                            <a href="ranking.php">Ranking</a> |
                            <a href="download.php">Download</a> |
                            <a href="<?php echo htmlspecialchars($LINK_DISCORD); ?>" target="_blank">Discord Oficial</a>
                        </div>
                        <div class="footer_copy">
                            Copyright &copy; 2005-2026 JoyImpact Co., Ltd. &amp; <?php echo htmlspecialchars($SERVER_NAME); ?>. Todos os direitos reservados.<br>
                            With Your Destiny &eacute; uma marca registrada da HanbitSoft / JoyImpact. Interface clássica adaptada para a release 7.69.
                        </div>
                    </div>
                </div>
                <div class="footer_rating">
                    <span>CLASSIFICAÇÃO</span><br>
                    <strong>LIVRE</strong>
                </div>
            </div>
        </footer>
    </div>

    <!-- Script de BGM Clássica -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var audio = document.getElementById('bgm_audio');
            var btn = document.getElementById('btn_toggle_bgm');
            if (audio && btn) {
                btn.addEventListener('click', function() {
                    if (audio.paused) {
                        audio.play().then(function() {
                            btn.textContent = '⏸ Pausar BGM';
                            btn.classList.add('playing');
                        }).catch(function(e) {
                            console.log('Autoplay prevenido:', e);
                        });
                    } else {
                        audio.pause();
                        btn.textContent = '▶ Tocar BGM Clássica';
                        btn.classList.remove('playing');
                    }
                });
            }
        });
    </script>
</body>
</html>

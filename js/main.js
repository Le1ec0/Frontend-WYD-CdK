/* ==========================================================================
   WYD (With Your Destiny) - Ongame Retrô Interactivity
   ========================================================================== */

document.addEventListener('DOMContentLoaded', () => {
    initAudioPlayer();
    initVirtualKeyboard();
    initNewsTabs();
    initRankingTabs();
    initModalCadastro();
    initSmoothScroll();
});

/* --------------------------------------------------------------------------
   1. Retro BGM Audio Player (Login.mp3)
   -------------------------------------------------------------------------- */
function initAudioPlayer() {
    const audio = document.getElementById('bgm_audio');
    const playBtn = document.getElementById('btn_toggle_bgm');
    if (!audio || !playBtn) return;

    audio.volume = 0.35;

    playBtn.addEventListener('click', () => {
        if (audio.paused) {
            audio.play().then(() => {
                playBtn.textContent = '🔊 Pausar BGM';
                playBtn.style.color = '#ffcc66';
            }).catch(e => {
                console.log('Autoplay blocked:', e);
            });
        } else {
            audio.pause();
            playBtn.textContent = '🔈 Tocar BGM';
            playBtn.style.color = '#dfb26b';
        }
    });
}

/* --------------------------------------------------------------------------
   2. Teclado Virtual Ongame Clássico
   -------------------------------------------------------------------------- */
let activeInput = null;

function initVirtualKeyboard() {
    const vkModal = document.getElementById('virtual_keyboard');
    const openBtn = document.getElementById('open_virtual_kb');
    const closeBtn = document.getElementById('close_virtual_kb');
    const keysContainer = document.getElementById('vk_keys_container');
    const loginInput = document.getElementById('login_user');
    const passInput = document.getElementById('login_pass');

    if (!vkModal || !openBtn || !closeBtn || !keysContainer) return;

    // Track which input is active
    if (loginInput) {
        loginInput.addEventListener('focus', () => { activeInput = loginInput; });
    }
    if (passInput) {
        passInput.addEventListener('focus', () => { activeInput = passInput; });
    }
    activeInput = loginInput;

    // Open/Close
    openBtn.addEventListener('click', (e) => {
        e.preventDefault();
        vkModal.style.display = vkModal.style.display === 'block' ? 'none' : 'block';
    });

    closeBtn.addEventListener('click', () => {
        vkModal.style.display = 'none';
    });

    // Make Draggable
    const header = document.querySelector('.vk_header');
    let isDragging = false, startX, startY, initialLeft, initialTop;

    if (header) {
        header.addEventListener('mousedown', (e) => {
            if (e.target === closeBtn) return;
            isDragging = true;
            startX = e.clientX;
            startY = e.clientY;
            initialLeft = vkModal.offsetLeft;
            initialTop = vkModal.offsetTop;
            document.addEventListener('mousemove', onMouseMove);
            document.addEventListener('mouseup', onMouseUp);
        });
    }

    function onMouseMove(e) {
        if (!isDragging) return;
        vkModal.style.left = (initialLeft + (e.clientX - startX)) + 'px';
        vkModal.style.top = (initialTop + (e.clientY - startY)) + 'px';
    }

    function onMouseUp() {
        isDragging = false;
        document.removeEventListener('mousemove', onMouseMove);
        document.removeEventListener('mouseup', onMouseUp);
    }

    // Build Classic Keys (Numbers, Letters, Backspace, Clear)
    const keyChars = [
        '1','2','3','4','5','6','7','8','9','0',
        'q','w','e','r','t','y','u','i','o','p',
        'a','s','d','f','g','h','j','k','l','ç',
        'z','x','c','v','b','n','m','_','-','.',
        'CAPS', 'LIMPAR', 'APAGAR', 'ENTER'
    ];

    let isCaps = false;

    keysContainer.innerHTML = '';
    keyChars.forEach(ch => {
        const btn = document.createElement('div');
        btn.className = 'vk_key';
        btn.textContent = ch;

        if (ch === 'CAPS' || ch === 'LIMPAR' || ch === 'APAGAR' || ch === 'ENTER') {
            btn.classList.add('wide');
        }

        btn.addEventListener('click', () => {
            if (!activeInput) activeInput = loginInput;

            if (ch === 'APAGAR') {
                activeInput.value = activeInput.value.slice(0, -1);
            } else if (ch === 'LIMPAR') {
                activeInput.value = '';
            } else if (ch === 'CAPS') {
                isCaps = !isCaps;
                updateCaps();
            } else if (ch === 'ENTER') {
                vkModal.style.display = 'none';
                if (activeInput === loginInput && passInput) {
                    passInput.focus();
                } else {
                    document.getElementById('form_login_ongame').requestSubmit();
                }
            } else {
                activeInput.value += isCaps ? ch.toUpperCase() : ch.toLowerCase();
            }
            activeInput.focus();
        });

        keysContainer.appendChild(btn);
    });

    function updateCaps() {
        const allKeys = keysContainer.querySelectorAll('.vk_key');
        allKeys.forEach(k => {
            const txt = k.textContent;
            if (txt.length === 1 && /[a-z]/i.test(txt)) {
                k.textContent = isCaps ? txt.toUpperCase() : txt.toLowerCase();
            }
        });
    }
}

/* --------------------------------------------------------------------------
   3. Abas de Notícias
   -------------------------------------------------------------------------- */
function initNewsTabs() {
    const tabs = document.querySelectorAll('.news_tab');
    const items = document.querySelectorAll('.news_item');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            const category = tab.getAttribute('data-cat');
            items.forEach(item => {
                if (category === 'todas' || item.getAttribute('data-cat') === category) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
}

/* --------------------------------------------------------------------------
   4. Ranking dos Servidores (Mizar, Alper, Sephira)
   -------------------------------------------------------------------------- */
const rankingData = {
    mizar: [
        { rank: 1, name: 'xX_Kersef_Xx', classType: 'TK', level: 'Celestial 200', guild: 'RedDragons' },
        { rank: 2, name: 'LadyMorgana', classType: 'FM', level: 'Celestial 198', guild: 'Arcanes' },
        { rank: 3, name: 'ThorGeralt', classType: 'BM', level: 'Celestial 195', guild: 'NoatunWar' },
        { rank: 4, name: 'SagitariusBR', classType: 'HT', level: 'Celestial 191', guild: 'Snipers' },
        { rank: 5, name: 'LordKersef', classType: 'TK', level: 'Celestial 189', guild: 'RedDragons' },
        { rank: 6, name: 'Atena_Heal', classType: 'FM', level: 'Celestial 186', guild: 'Arcanes' },
        { rank: 7, name: 'BeastSlayer', classType: 'BM', level: 'Celestial 184', guild: 'NoatunWar' },
        { rank: 8, name: 'DeathShot', classType: 'HT', level: 'Sub-Cel 180', guild: 'Snipers' },
        { rank: 9, name: 'ArchAngel_X', classType: 'TK', level: 'Sub-Cel 175', guild: 'HolyKnights' },
        { rank: 10, name: 'DarkSorceress', classType: 'FM', level: 'Sub-Cel 170', guild: 'Arcanes' }
    ],
    alper: [
        { rank: 1, name: 'Zeus_Brasil', classType: 'BM', level: 'Celestial 199', guild: 'Olimpo' },
        { rank: 2, name: 'ShadowTK', classType: 'TK', level: 'Celestial 197', guild: 'Dominio' },
        { rank: 3, name: 'Valkyrie_HT', classType: 'HT', level: 'Celestial 196', guild: 'Valhalla' },
        { rank: 4, name: 'Gelo_Puro', classType: 'FM', level: 'Celestial 193', guild: 'Olimpo' },
        { rank: 5, name: 'KersefLord', classType: 'TK', level: 'Celestial 190', guild: 'Dominio' },
        { rank: 6, name: 'FenrirSummon', classType: 'BM', level: 'Sub-Cel 188', guild: 'Valhalla' },
        { rank: 7, name: 'SniperGirl', classType: 'HT', level: 'Sub-Cel 185', guild: 'Valhalla' },
        { rank: 8, name: 'CurandeiraBR', classType: 'FM', level: 'Sub-Cel 182', guild: 'Olimpo' },
        { rank: 9, name: 'BladeMaster', classType: 'TK', level: 'Sub-Cel 179', guild: 'Dominio' },
        { rank: 10, name: 'LoboCinza', classType: 'BM', level: 'Sub-Cel 177', guild: 'Olimpo' }
    ],
    sephira: [
        { rank: 1, name: 'SephiraKing', classType: 'TK', level: 'Celestial 200', guild: 'Imperium' },
        { rank: 2, name: 'HolyHuntress', classType: 'HT', level: 'Celestial 198', guild: 'Amazonas' },
        { rank: 3, name: 'ArcanoMaior', classType: 'FM', level: 'Celestial 194', guild: 'Misticos' },
        { rank: 4, name: 'TitanNature', classType: 'BM', level: 'Celestial 192', guild: 'Imperium' },
        { rank: 5, name: 'Paladino_Kersef', classType: 'TK', level: 'Celestial 189', guild: 'Imperium' },
        { rank: 6, name: 'RaioSolar', classType: 'FM', level: 'Sub-Cel 185', guild: 'Misticos' },
        { rank: 7, name: 'ArqueiroReal', classType: 'HT', level: 'Sub-Cel 181', guild: 'Amazonas' },
        { rank: 8, name: 'GarraSelvagem', classType: 'BM', level: 'Sub-Cel 178', guild: 'Imperium' },
        { rank: 9, name: 'GuerreiroNegro', classType: 'TK', level: 'Sub-Cel 174', guild: 'Misticos' },
        { rank: 10, name: 'BruxaDoPântano', classType: 'FM', level: 'Sub-Cel 172', guild: 'Amazonas' }
    ]
};

function initRankingTabs() {
    const selector = document.getElementById('ranking_server_select');
    const tableBody = document.getElementById('ranking_tbody');
    if (!selector || !tableBody) return;

    function renderRanking(server) {
        const data = rankingData[server] || rankingData.mizar;
        tableBody.innerHTML = '';
        data.forEach(p => {
            const tr = document.createElement('tr');
            
            let posClass = '';
            if (p.rank === 1) posClass = 'first';
            else if (p.rank === 2) posClass = 'second';
            else if (p.rank === 3) posClass = 'third';

            let classBadge = `class_${p.classType.toLowerCase()}`;

            tr.innerHTML = `
                <td class="rank_pos ${posClass}">${p.rank}</td>
                <td><strong>${p.name}</strong></td>
                <td><span class="rank_class ${classBadge}">${p.classType}</span></td>
                <td>${p.level}</td>
                <td>${p.guild}</td>
            `;
            tableBody.appendChild(tr);
        });
    }

    selector.addEventListener('change', (e) => {
        renderRanking(e.target.value);
    });

    renderRanking('mizar');
}

/* --------------------------------------------------------------------------
   5. Modal de Cadastro (Criar Conta) & Login
   -------------------------------------------------------------------------- */
function initModalCadastro() {
    const modal = document.getElementById('modal_cadastro');
    const openBtns = document.querySelectorAll('.open_modal_cadastro');
    const closeBtn = document.getElementById('close_modal_cadastro');
    const form = document.getElementById('form_cadastro_modal');
    const msgBox = document.getElementById('modal_msg');

    if (!modal) return;

    openBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            modal.classList.add('active');
            if (msgBox) msgBox.style.display = 'none';
        });
    });

    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            modal.classList.remove('active');
        });
    }

    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.remove('active');
        }
    });

    if (form) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const user = document.getElementById('cad_user').value.trim();
            const email = document.getElementById('cad_email').value.trim();
            const pass = document.getElementById('cad_pass').value;
            const passConf = document.getElementById('cad_pass_conf').value;

            if (user.length < 4 || user.length > 12) {
                showMsg('O usuário deve ter entre 4 e 12 caracteres.', 'error');
                return;
            }
            if (pass.length < 6) {
                showMsg('A senha deve ter no mínimo 6 caracteres.', 'error');
                return;
            }
            if (pass !== passConf) {
                showMsg('A confirmação da senha não confere.', 'error');
                return;
            }

            // Simulação de criação com sucesso
            showMsg(`Conta [${user}] criada com sucesso no Kersef! Guarde suas credenciais com segurança.`, 'success');
            form.reset();

            setTimeout(() => {
                modal.classList.remove('active');
            }, 3500);
        });
    }

    function showMsg(txt, type) {
        if (!msgBox) return;
        msgBox.textContent = txt;
        msgBox.className = `modal_msg ${type}`;
        msgBox.style.display = 'block';
    }

    // Login Form
    const loginForm = document.getElementById('form_login_ongame');
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const user = document.getElementById('login_user').value.trim();
            if (!user) {
                alert('Por favor, informe seu usuário Ongame.');
                return;
            }
            alert(`Bem-vindo, ${user}! Conectado ao servidor de Kersef.`);
        });
    }
}

/* --------------------------------------------------------------------------
   6. Rolagem Suave
   -------------------------------------------------------------------------- */
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href === '#' || href.startsWith('#!')) return;
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
}

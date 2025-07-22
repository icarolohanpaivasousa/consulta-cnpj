<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function validaCNPJ($cnpj) {
    $cnpj = preg_replace('/\D/', '', $cnpj);
    if (strlen($cnpj) != 14) return false;
    return preg_match('/^\d{14}$/', $cnpj) === 1;
}

function limparCNPJ($cnpj) {
    return preg_replace('/\D/', '', $cnpj);
}

function consultarCNPJ($cnpj) {
    $token = 'rh3pn2VK8bRcMxcU8bBi1n2rN6iQmbxEGUkrTm2SI5DwOl8BjrglAtZOMQKD';
    $url = "https://api.cnpj.biz/v1/cnpj/$cnpj";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token"
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        return ['error' => 'Erro ao consultar CNPJ. Código: ' . $httpCode];
    }

    $data = json_decode($response, true);
    if (!$data || isset($data['message'])) {
        return ['error' => 'CNPJ não encontrado ou inválido.'];
    }

    return [
        'Nome' => $data['razao_social'] ?? 'Não informado',
        'Fantasia' => $data['nome_fantasia'] ?? 'Não informado',
        'Situacao' => $data['situacao_cadastral'] ?? 'Desconhecida',
        'Telefones' => array_filter([
            $data['ddd_telefone_1'] ?? null,
            $data['ddd_telefone_2'] ?? null
        ]),
        'Email' => $data['email'] ?? 'Não informado',
        'Socios' => isset($data['qsa']) && is_array($data['qsa']) ? 
            array_map(function($socio) {
                return $socio['nome'] . ' (' . $socio['qualificacao'] . ')';
            }, $data['qsa']) : []
    ];
}

$cnpjsInvalidos = [];
$consultaDados = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cnpjs'])) {
    $linhas = preg_split('/\r\n|\r|\n/', trim($_POST['cnpjs']));
    foreach ($linhas as $linha) {
        $cnpjLimpo = limparCNPJ($linha);
        if (!validaCNPJ($cnpjLimpo)) {
            $cnpjsInvalidos[] = $linha;
            continue;
        }
        $consultaDados[$cnpjLimpo] = consultarCNPJ($cnpjLimpo);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Consulta Avançada de CNPJs</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
        :root {
            --cor-primaria: #007bff;
            --cor-verde: #28a745;
            --cor-vermelha: #dc3545;
            --cor-cinza-claro: #f8f9fa;
            --cor-cinza-escuro: #343a40;
            --cor-fundo: #f4f6f8;
            --cor-texto: #222;
            --cor-texto-claro: #666;
            --max-width: 1200px;
            --espaco: 12px;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--cor-fundo);
            margin: 0; padding: 20px;
            color: var(--cor-texto);
        }
        h1 {
            text-align: center;
            margin-bottom: 24px;
            color: var(--cor-primaria);
        }
        main {
            max-width: var(--max-width);
            margin: auto;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        section#consulta, section#historico {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgb(0 0 0 / 0.1);
            padding: var(--espaco);
            flex: 1 1 480px;
            min-width: 320px;
            max-height: 85vh;
            overflow-y: auto;
        }
        form textarea {
            width: 100%;
            height: 130px;
            font-size: 1rem;
            padding: 10px;
            border: 2px solid #ccc;
            border-radius: 6px;
            resize: vertical;
            font-family: monospace;
        }
        form label {
            font-weight: 600;
            margin-bottom: 6px;
            display: block;
        }
        form button {
            margin-top: 12px;
            background: var(--cor-primaria);
            border: none;
            color: white;
            font-weight: 700;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        form button:disabled {
            background: #999;
            cursor: not-allowed;
        }
        form button:hover:not(:disabled) {
            background: #0056b3;
        }
        .contadores {
            margin-top: 6px;
            font-size: 0.9rem;
            color: var(--cor-texto-claro);
            user-select: none;
        }
        .resultado {
            background: var(--cor-cinza-claro);
            border-left: 5px solid var(--cor-primaria);
            padding: 12px 16px;
            margin-top: 20px;
            border-radius: 6px;
        }
        .resultado.error {
            border-color: var(--cor-vermelha);
            color: var(--cor-vermelha);
        }
        .campo {
            margin-bottom: 10px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
        }
        .campo strong {
            min-width: 110px;
        }
        .campo .valor {
            background: white;
            padding: 5px 10px;
            border-radius: 4px;
            flex-grow: 1;
            user-select: text;
            font-family: monospace;
            word-break: break-word;
        }
        .btn-copiar {
            background: var(--cor-primaria);
            border: none;
            color: white;
            padding: 6px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 700;
            transition: background 0.3s ease;
        }
        .btn-copiar.copiado {
            background: var(--cor-verde);
        }
        .btn-copiar:hover {
            background: #0056b3;
        }
        ul.socios-lista {
            margin: 0; padding-left: 130px;
        }
        ul.socios-lista li {
            margin-bottom: 6px;
        }
        /* Histórico */
        #historico-header {
            display: flex;
            gap: 8px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }
        #historico-header input[type="search"] {
            flex-grow: 1;
            padding: 8px 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 1rem;
            min-width: 160px;
        }
        #historico-header button {
            background: var(--cor-primaria);
            border: none;
            color: white;
            font-weight: 700;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s ease;
            min-width: 90px;
        }
        #historico-header button:hover {
            background: #0056b3;
        }
        #historicoLista {
            max-height: 70vh;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 8px 12px;
            list-style: none;
            margin: 0;
            font-family: monospace;
            font-size: 0.9rem;
            user-select: none;
        }
        #historicoLista li {
            padding: 6px 8px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        #historicoLista li:last-child {
            border-bottom: none;
        }
        #historicoLista li:hover {
            background-color: var(--cor-cinza-claro);
        }
        #historicoLista li[aria-selected="true"] {
            background-color: var(--cor-primaria);
            color: white;
            font-weight: 700;
        }
        .historico-item-text {
            flex-grow: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            padding-right: 12px;
        }
        .btn-excluir-historico {
            background: transparent;
            border: none;
            color: var(--cor-vermelha);
            font-weight: 700;
            cursor: pointer;
            font-size: 1.1rem;
            line-height: 1;
            padding: 0 4px;
            user-select: none;
        }
        .btn-excluir-historico:hover {
            color: #a00;
        }
        #historicoCount {
            margin-top: 8px;
            font-size: 0.9rem;
            color: var(--cor-texto-claro);
            user-select: none;
        }
        /* Toast notifications */
        #toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--cor-primaria);
            color: white;
            padding: 14px 20px;
            border-radius: 6px;
            opacity: 0;
            pointer-events: none;
            transform: translateY(20px);
            transition: opacity 0.3s ease, transform 0.3s ease;
            z-index: 9999;
            font-weight: 700;
        }
        #toast.show {
            opacity: 1;
            pointer-events: auto;
            transform: translateY(0);
        }
        /* Tema escuro */
        body.dark {
            background: #121212;
            color: #e0e0e0;
        }
        body.dark section#consulta, body.dark section#historico {
            background: #1e1e1e;
            box-shadow: none;
        }
        body.dark form textarea {
            background: #222;
            color: #eee;
            border-color: #555;
        }
        body.dark .resultado {
            background: #222;
            border-left-color: #0d6efd;
            color: #ddd;
        }
        body.dark .campo .valor {
            background: #333;
            color: #eee;
        }
        body.dark .btn-copiar {
            background: #0d6efd;
        }
        body.dark .btn-copiar:hover {
            background: #0047ab;
        }
        body.dark #historico-header input[type="search"] {
            background: #222;
            color: #eee;
            border-color: #555;
        }
        body.dark #historico-header button {
            background: #0d6efd;
        }
        body.dark #historico-header button:hover {
            background: #0047ab;
        }
        body.dark #historicoLista {
            background: #222;
            border-color: #555;
            color: #eee;
        }
        body.dark #historicoLista li:hover {
            background-color: #333;
        }
        body.dark #toast {
            background: #0d6efd;
        }
        /* Responsivo */
        @media (max-width: 900px) {
            main {
                flex-direction: column;
                max-width: 100%;
            }
            section#consulta, section#historico {
                max-height: none;
                min-width: auto;
            }
        }
    </style>
</head>
<body>
    <h1>Consulta Avançada de CNPJs</h1>
    <main>
        <section id="consulta" aria-label="Área de consulta de CNPJs">
            <form id="formConsulta" method="post" novalidate>
                <label for="txtCNPJs">Cole os CNPJs (um por linha):</label>
                <textarea id="txtCNPJs" name="cnpjs" placeholder="Ex: 27.865.757/0001-02 ou 27865757000102" aria-describedby="contadores"><?= isset($_POST['cnpjs']) ? htmlspecialchars($_POST['cnpjs']) : '' ?></textarea>
                <div class="contadores" id="contadores">
                    <span id="validosCount">Válidos: 0</span> | 
                    <span id="invalidosCount">Inválidos: 0</span>
                </div>
                <button type="submit" id="btnConsultar" disabled>Consultar</button>
            </form>

            <div id="resultados" aria-live="polite" style="margin-top:24px;">
                <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                    <?php if (count($cnpjsInvalidos) > 0): ?>
                        <div class="resultado error" role="alert" tabindex="0">
                            <strong>Lista de CNPJs inválidos:</strong>
                            <ul>
                                <?php foreach ($cnpjsInvalidos as $inv): ?>
                                    <li><?=htmlspecialchars($inv)?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php foreach ($consultaDados as $cnpj => $d): ?>
                        <article class="resultado <?= isset($d['error']) ? 'error' : '' ?>" tabindex="0" aria-label="Resultado para o CNPJ <?=htmlspecialchars($cnpj)?>">
                            <div class="campo">
                                <strong>CNPJ:</strong> 
                                <span class="valor"><?=htmlspecialchars(preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpj))?></span>
                                <button class="btn-copiar" data-text="<?=htmlspecialchars(preg_replace('/\D/', '', $cnpj))?>" aria-label="Copiar CNPJ">📋</button>
                            </div>
                            <?php if (isset($d['error'])): ?>
                                <div class="campo"><strong>Erro:</strong> <span class="valor"><?=htmlspecialchars($d['error'])?></span></div>
                            <?php else: ?>
                                <div class="campo">
                                    <strong>Nome:</strong> 
                                    <span class="valor"><?=htmlspecialchars($d['Nome'])?></span>
                                    <button class="btn-copiar" data-text="<?=htmlspecialchars($d['Nome'])?>" aria-label="Copiar Nome">📋</button>
                                </div>
                                <div class="campo">
                                    <strong>Fantasia:</strong> 
                                    <span class="valor"><?=htmlspecialchars($d['Fantasia'])?></span>
                                    <button class="btn-copiar" data-text="<?=htmlspecialchars($d['Fantasia'])?>" aria-label="Copiar Fantasia">📋</button>
                                </div>
                                <div class="campo">
                                    <strong>Situação:</strong> 
                                    <span class="valor"><?=htmlspecialchars($d['Situacao'])?></span>
                                    <button class="btn-copiar" data-text="<?=htmlspecialchars($d['Situacao'])?>" aria-label="Copiar Situação">📋</button>
                                </div>
                                <div class="campo" style="flex-wrap: wrap; align-items: center;">
                                    <strong>Telefone(s):</strong> 
                                    <?php foreach($d['Telefones'] as $idx => $tel): ?>
                                        <span class="valor" style="min-width: 110px; margin-right: 8px;"><?=htmlspecialchars($tel)?></span>
                                        <button class="btn-copiar" data-text="<?=htmlspecialchars($tel)?>" aria-label="Copiar Telefone <?=($idx+1)?>" tabindex="0">📋</button>
                                    <?php endforeach; ?>
                                </div>
                                <div class="campo">
                                    <strong>Email:</strong> 
                                    <span class="valor"><?=htmlspecialchars($d['Email'])?></span>
                                    <button class="btn-copiar" data-text="<?=htmlspecialchars($d['Email'])?>" aria-label="Copiar Email">📋</button>
                                </div>
                                <?php if(count($d['Socios']) > 0): ?>
                                <div>
                                    <strong>Sócios:</strong>
                                    <ul class="socios-lista">
                                        <?php foreach ($d['Socios'] as $socio): ?>
                                            <li><?=htmlspecialchars($socio)?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section id="historico" aria-label="Histórico de consultas">
            <div id="historico-header">
                <input type="search" id="buscaHistorico" placeholder="Buscar no histórico..." aria-label="Buscar no histórico" />
                <button id="btnExportarTXT" title="Exportar TXT">TXT</button>
                <button id="btnExportarCSV" title="Exportar CSV">CSV</button>
                <button id="btnExportarJSON" title="Exportar JSON">JSON</button>
                <button id="btnLimparHistorico" title="Limpar histórico">🗑 Limpar</button>
                <button id="btnTema" title="Alternar tema">🌙 / ☀️</button>
            </div>
            <ul id="historicoLista" role="listbox" tabindex="0" aria-label="Lista do histórico de consultas">
                <!-- Itens via JS -->
            </ul>
            <div id="historicoCount" aria-live="polite"></div>
        </section>
    </main>

    <div id="toast" role="alert" aria-live="assertive" aria-atomic="true" style="display:none;">Mensagem</div>

<script>
(() => {
    const txtCNPJs = document.getElementById('txtCNPJs');
    const btnConsultar = document.getElementById('btnConsultar');
    const validosCount = document.getElementById('validosCount');
    const invalidosCount = document.getElementById('invalidosCount');
    const historicoLista = document.getElementById('historicoLista');
    const buscaHistorico = document.getElementById('buscaHistorico');
    const historicoCount = document.getElementById('historicoCount');
    const btnLimparHistorico = document.getElementById('btnLimparHistorico');
    const btnExportarTXT = document.getElementById('btnExportarTXT');
    const btnExportarCSV = document.getElementById('btnExportarCSV');
    const btnExportarJSON = document.getElementById('btnExportarJSON');
    const btnTema = document.getElementById('btnTema');
    const toast = document.getElementById('toast');

    function validaCNPJJS(cnpj) {
        cnpj = cnpj.replace(/\D/g,'');
        return cnpj.length === 14;
    }

    function contaCNPJs(text) {
        const linhas = text.split('\n').map(l => l.trim()).filter(l => l.length > 0);
        let validos = 0, invalidos = 0;
        for (const linha of linhas) {
            if (validaCNPJJS(linha)) validos++;
            else invalidos++;
        }
        return {validos, invalidos};
    }

    function atualizarContadores() {
        const {validos, invalidos} = contaCNPJs(txtCNPJs.value);
        validosCount.textContent = `Válidos: ${validos}`;
        invalidosCount.textContent = `Inválidos: ${invalidos}`;
        btnConsultar.disabled = validos === 0;
    }

    function showToast(msg, dur=2500) {
        toast.textContent = msg;
        toast.style.display = 'block';
        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.remove('show');
            toast.style.display = 'none';
        }, dur);
    }

    // Copiar texto da linha
    document.body.addEventListener('click', e => {
        if (e.target.classList.contains('btn-copiar')) {
            const txt = e.target.getAttribute('data-text');
            navigator.clipboard.writeText(txt).then(() => {
                e.target.classList.add('copiado');
                showToast('Copiado: ' + txt);
                setTimeout(() => e.target.classList.remove('copiado'), 1500);
            }).catch(() => {
                alert('Erro ao copiar');
            });
        }
    });

    // Histórico localStorage
    const STORAGE_KEY = 'cnpjConsultaHistorico';

    function carregarHistorico() {
        let data = localStorage.getItem(STORAGE_KEY);
        if (!data) return [];
        try {
            return JSON.parse(data);
        } catch {
            localStorage.removeItem(STORAGE_KEY);
            return [];
        }
    }
    function salvarHistorico(arr) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(arr));
    }

    function atualizarHistoricoLista(filtro = '') {
        const dados = carregarHistorico();
        let filtrados = dados;
        if (filtro.trim()) {
            const f = filtro.toLowerCase();
            filtrados = dados.filter(item => item.toLowerCase().includes(f));
        }

        historicoLista.innerHTML = '';
        filtrados.forEach((item, i) => {
            const li = document.createElement('li');
            li.textContent = item;
            li.setAttribute('role', 'option');
            li.setAttribute('tabindex', '-1');
            li.className = '';
            const btnExcluir = document.createElement('button');
            btnExcluir.className = 'btn-excluir-historico';
            btnExcluir.setAttribute('aria-label', 'Excluir ' + item);
            btnExcluir.textContent = '×';
            btnExcluir.addEventListener('click', (e) => {
                e.stopPropagation();
                removerDoHistorico(i);
            });
            li.appendChild(btnExcluir);
            li.addEventListener('click', () => {
                txtCNPJs.value = item;
                atualizarContadores();
                txtCNPJs.focus();
            });
            historicoLista.appendChild(li);
        });
        historicoCount.textContent = `Total no histórico: ${filtrados.length}`;
    }

    function adicionarNoHistorico(texto) {
        if (!texto.trim()) return;
        let dados = carregarHistorico();
        if (!dados.includes(texto)) {
            dados.unshift(texto);
            if (dados.length > 50) dados.pop(); // limita 50 itens
            salvarHistorico(dados);
            atualizarHistoricoLista(buscaHistorico.value);
        }
    }

    function removerDoHistorico(index) {
        let dados = carregarHistorico();
        if (index >= 0 && index < dados.length) {
            dados.splice(index, 1);
            salvarHistorico(dados);
            atualizarHistoricoLista(buscaHistorico.value);
            showToast('Entrada removida do histórico');
        }
    }

    function limparHistorico() {
        if(confirm('Confirma limpar todo o histórico?')) {
            localStorage.removeItem(STORAGE_KEY);
            atualizarHistoricoLista();
            showToast('Histórico limpo');
        }
    }

    function exportarTexto(tipo) {
        const dados = carregarHistorico();
        if (dados.length === 0) {
            alert('Histórico vazio.');
            return;
        }
        let conteudo = '';
        let filename = `historico_cnpjs.${tipo}`;
        if (tipo === 'txt') {
            conteudo = dados.join("\n");
        } else if (tipo === 'csv') {
            conteudo = dados.map(item => `"${item.replace(/"/g, '""')}"`).join("\n");
        } else if (tipo === 'json') {
            conteudo = JSON.stringify(dados, null, 2);
        }
        const blob = new Blob([conteudo], {type: 'text/plain;charset=utf-8'});
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = filename;
        link.click();
        URL.revokeObjectURL(link.href);
        showToast(`Exportado arquivo ${filename}`);
    }

    // Eventos
    txtCNPJs.addEventListener('input', () => {
        atualizarContadores();
    });

    document.getElementById('formConsulta').addEventListener('submit', (e) => {
        const val = txtCNPJs.value.trim();
        if (val === '') {
            e.preventDefault();
            alert('Insira pelo menos um CNPJ válido para consultar.');
            return;
        }
        const {validos} = contaCNPJs(val);
        if (validos === 0) {
            e.preventDefault();
            alert('Nenhum CNPJ válido para consulta.');
            return;
        }
        adicionarNoHistorico(val);
    });

    buscaHistorico.addEventListener('input', () => {
        atualizarHistoricoLista(buscaHistorico.value);
    });

    btnLimparHistorico.addEventListener('click', () => limparHistorico());

    btnExportarTXT.addEventListener('click', () => exportarTexto('txt'));
    btnExportarCSV.addEventListener('click', () => exportarTexto('csv'));
    btnExportarJSON.addEventListener('click', () => exportarTexto('json'));

    btnTema.addEventListener('click', () => {
        document.body.classList.toggle('dark');
        localStorage.setItem('temaEscuro', document.body.classList.contains('dark') ? '1' : '0');
    });

    // Inicializa
    atualizarContadores();
    atualizarHistoricoLista();
    if(localStorage.getItem('temaEscuro') === '1') {
        document.body.classList.add('dark');
    }
})();
</script>
</body>
</html>

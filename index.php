<?php if (!empty($mensagemErro)): ?>
    <div style="color: red"><?= $mensagemErro ?></div>
<?php elseif (!empty($dados)): ?>
    <div>
        <?php foreach ($dados as $campo => $valor): ?>
            <p>
                <strong><?= $campo ?>:</strong><br>
                <?= nl2br(formatarTexto($valor)) ?>
                <button onclick="copiarTexto(this)" aria-label="Copiar <?= $campo ?>">📋</button>
            </p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
function copiarTexto(botao) {
    const texto = botao.previousSibling.textContent || botao.previousSibling.innerText;
    navigator.clipboard.writeText(texto.trim()).then(() => {
        botao.innerText = "✅";
        setTimeout(() => botao.innerText = "📋", 1500);
    });
}
</script>

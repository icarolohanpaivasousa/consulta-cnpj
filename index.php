<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("consulta_cnpjs.php");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Consulta CNPJ</title>
</head>
<body>
    <form method="post">
        <label for="cnpj">Cole o CNPJ:</label><br>
        <input type="text" name="cnpj" id="cnpj" value="<?= htmlspecialchars($_POST["cnpj"] ?? "") ?>"><br>
        <button type="submit">Consultar</button>
    </form>

    <?php if (!empty($mensagemErro)): ?>
        <div style="color: red"><?= $mensagemErro ?></div>
    <?php elseif (!empty($dados)): ?>
        <div>
            <?php foreach ($dados as $campo => $valor): ?>
                <p>
                    <strong><?= $campo ?>:</strong><br>
                    <?= nl2br(htmlspecialchars($valor ?? '')) ?>
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
</body>
</html>

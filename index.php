<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("consulta_cnpjs.php");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Consulta Avançada de CNPJs</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        label, input, textarea, button { display: block; margin-top: 10px; }
        .campo { margin-bottom: 20px; }
        .erro { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Consulta Avançada de CNPJs</h1>

    <form method="POST">
        <label for="cnpj">Cole o CNPJ:</label>
        <input type="text" name="cnpj" id="cnpj" value="<?= htmlspecialchars($_POST["cnpj"] ?? "") ?>">
        <button type="submit">Consultar</button>
    </form>

    <?php if (!empty($mensagemErro)): ?>
        <div class="erro"><?= $mensagemErro ?></div>
    <?php elseif (!empty($dados)): ?>
        <div class="campo">
            <strong>Nome:</strong>
            <?= htmlspecialchars($dados["Nome"] ?? 'Não informado') ?>
        </div>

        <div class="campo">
            <strong>Fantasia:</strong>
            <?= htmlspecialchars($dados["Fantasia"] ?? 'Não informado') ?>
        </div>

        <div class="campo">
            <strong>Situação:</strong>
            <?= htmlspecialchars($dados["Situacao"] ?? 'Não informado') ?>
        </div>

        <div class="campo">
            <strong>Telefone(s):</strong><br>
            <?php
            if (!empty($dados["Telefones"]) && is_array($dados["Telefones"])) {
                foreach ($dados["Telefones"] as $telefone) {
                    echo htmlspecialchars($telefone) . "<br>";
                }
            } else {
                echo "Não informado";
            }
            ?>
        </div>

        <div class="campo">
            <strong>Email:</strong>
            <?= htmlspecialchars($dados["Email"] ?? 'Não informado') ?>
        </div>
    <?php endif; ?>
</body>
</html>

<?php
session_start();
require_once 'src/funcoes.php';
require_once 'config.php';

$erro = '';
$popup_inativo = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login']);
    $senha = $_POST['senha'];
    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE (usuario = ? OR email = ?) LIMIT 1');
    $stmt->execute([$login, $login]);
    $usuario = $stmt->fetch();
    if ($usuario && password_verify($senha, $usuario['senha'])) {
        if ($usuario['oculto'] === 'T') {
            $popup_inativo = true;
        } else {
            // Login OK
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_tipo'] = $usuario['tipo'];
            header('Location: index.php');
            exit;
        }
    } else {
        $erro = 'Usuário ou senha inválidos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Gestão de Clínicas</title>
    <link rel="stylesheet" href="css/style.php">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-header">
            <h2>Login</h2>
            <p style="color: var(--cor-cinza-escuro); font-size: 1.1rem;">Sistema de Gestão de Clínicas</p>
        </div>
        <?php if ($popup_inativo): ?>
            <div class="popup-inativo">Seu usuário está inativo. Entre em contato com o administrador do sistema.</div>
        <?php endif; ?>
        <?php if ($erro): ?>
            <div class="login-erro"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <div class="form-group">
                <label for="login">Usuário ou E-mail</label>
                <input type="text" name="login" id="login" class="form-control" required autofocus value="<?= isset($_POST['login']) ? htmlspecialchars($_POST['login']) : '' ?>">
            </div>
            <div class="form-group">
                <label for="senha">Senha</label>
                <div class="input-group">
                    <input type="password" name="senha" id="senha" class="form-control" required>
                    <div class="input-group-append">
                        <button type="button" class="btn btn-outline-secondary" tabindex="-1" onclick="toggleSenha('senha', this)"><span class="fa fa-eye"></span></button>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-login">Entrar</button>
        </form>
    </div>
</body>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
<script>
function toggleSenha(id, btn) {
    var input = document.getElementById(id);
    var icon = btn.querySelector('span');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
</html> 
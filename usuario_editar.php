<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'src/funcoes.php';
require_once 'config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: usuarios.php');
    exit;
}
$id = (int)$_GET['id'];

$stmt = $pdo->prepare('SELECT funcao_id FROM usuarios WHERE id = ?');
$stmt->execute([$_SESSION['usuario_id']]);
$funcao_id_logado = $stmt->fetchColumn();
$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
$stmt->execute([$id]);
$usuario = $stmt->fetch();
if (!$usuario) {
    echo '<p>Usuário não encontrado.</p><a href="usuarios.php">Voltar</a>';
    exit;
}
if ($funcao_id_logado == 2 && $usuario['funcao_id'] == 1) { // Gerente tentando editar Desenvolvedor
    echo '<div style="max-width:500px;margin:40px auto;font-family:sans-serif;text-align:center;padding:2rem;border-radius:12px;background:#fff3cd;color:#856404;border:1px solid #ffeeba;">Acesso negado. Gerente não pode editar o perfil de um Desenvolvedor.<br><a href="usuarios.php">Voltar</a></div>';
    exit;
}

$funcoes = $pdo->query('SELECT id, nome FROM funcoes_usuario')->fetchAll();
$erros = [];
$sucesso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $usuario_nome = trim($_POST['usuario']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $funcao_id = intval($_POST['funcao_id']);
    $oculto = isset($_POST['oculto']) ? 'T' : 'F';

    if (!$nome) $erros[] = 'Nome é obrigatório.';
    if (!$usuario_nome) $erros[] = 'Usuário é obrigatório.';
    if (!$email) $erros[] = 'E-mail é obrigatório.';
    if (!$funcao_id) $erros[] = 'Função é obrigatória.';

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM usuarios WHERE (usuario = ? OR email = ?) AND id != ?');
    $stmt->execute([$usuario_nome, $email, $id]);
    if ($stmt->fetchColumn() > 0) {
        $erros[] = 'Usuário ou e-mail já cadastrado.';
    }

    if (empty($erros)) {
        $sql = 'UPDATE usuarios SET nome=?, usuario=?, email=?, funcao_id=?, oculto=?';
        $params = [$nome, $usuario_nome, $email, $funcao_id, $oculto];
        if (!empty($senha)) {
            $senha_hash = password_hash($senha, PASSWORD_BCRYPT);
            $sql .= ', senha=?';
            $params[] = $senha_hash;
        }
        $sql .= ' WHERE id=?';
        $params[] = $id;
        $stmt = $pdo->prepare($sql);
        $ok = $stmt->execute($params);
        if ($ok) {
            $sucesso = true;
            $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
            $stmt->execute([$id]);
            $usuario = $stmt->fetch();
        } else {
            $erros[] = 'Erro ao atualizar usuário.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário</title>
    <link rel="stylesheet" href="css/style.php">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
    .form-container { max-width: 500px; margin: 40px auto; }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <div class="form-container">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2>Editar Usuário</h2>
                <a href="usuarios.php" class="btn btn-secondary">← Voltar</a>
            </div>
            <div style="height: 20px;"></div>
            <?php if ($erros): ?>
                <div class="alert alert-error">
                    <strong>Erros encontrados:</strong>
                    <ul style="margin-top: 10px; margin-left: 20px;">
                        <?php foreach ($erros as $erro): ?>
                            <li><?= htmlspecialchars($erro) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php elseif ($sucesso): ?>
                <div class="alert alert-success">Usuário atualizado com sucesso!</div>
            <?php endif; ?>
            <form method="post" autocomplete="off">
                <div class="form-group">
                    <label for="nome">Nome</label>
                    <input type="text" name="nome" id="nome" class="form-control" required value="<?= isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : htmlspecialchars($usuario['nome']) ?>">
                </div>
                <div class="form-group">
                    <label for="usuario">Usuário</label>
                    <input type="text" name="usuario" id="usuario" class="form-control" required value="<?= isset($_POST['usuario']) ? htmlspecialchars($_POST['usuario']) : htmlspecialchars($usuario['usuario']) ?>">
                </div>
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" name="email" id="email" class="form-control" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : htmlspecialchars($usuario['email']) ?>">
                </div>
                <div class="form-group">
                    <label for="senha">Senha <small>(deixe em branco para não alterar)</small></label>
                    <div class="input-group">
                        <input type="password" name="senha" id="senha" class="form-control">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary" tabindex="-1" onclick="toggleSenha('senha', this)"><span class="fa fa-eye"></span></button>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="funcao_id">Função</label>
                    <select name="funcao_id" id="funcao_id" class="form-control" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($funcoes as $f): ?>
                            <option value="<?= $f['id'] ?>" <?= ((isset($_POST['funcao_id']) ? $_POST['funcao_id'] : $usuario['funcao_id']) == $f['id']) ? 'selected' : '' ?>><?= htmlspecialchars($f['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group form-check">
                    <input type="checkbox" name="oculto" id="oculto" class="form-check-input" <?= ((isset($_POST['oculto']) ? $_POST['oculto'] : $usuario['oculto']) == 'T') ? 'checked' : '' ?> >
                    <label for="oculto" class="form-check-label">Usuário oculto</label>
                </div>
                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            </form>
        </div>
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
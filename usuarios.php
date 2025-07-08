<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'src/funcoes.php';
require_once 'config.php';

// Buscar função do usuário logado
$stmt = $pdo->prepare('SELECT funcao_id FROM usuarios WHERE id = ?');
$stmt->execute([$_SESSION['usuario_id']]);
$funcao_id = $stmt->fetchColumn();
if ($funcao_id != 1 && $funcao_id != 2) { // 1 = Desenvolvedor, 2 = Gerente
    echo '<div style="max-width:500px;margin:40px auto;font-family:sans-serif;text-align:center;padding:2rem;border-radius:12px;background:#fff3cd;color:#856404;border:1px solid #ffeeba;">Acesso restrito. Apenas gerente ou desenvolvedor podem acessar esta página.<br><a href="index.php">Voltar</a></div>';
    exit;
}

$mensagem = '';
// Ocultar usuário via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ocultar_id'])) {
    $ocultar_id = (int)$_POST['ocultar_id'];
    $stmt = $pdo->prepare('SELECT funcao_id FROM usuarios WHERE id = ?');
    $stmt->execute([$ocultar_id]);
    $ocultar_funcao_id = $stmt->fetchColumn();
    if ($ocultar_id === $_SESSION['usuario_id']) {
        $mensagem = 'Você não pode ocultar seu próprio usuário!';
    } elseif ($funcao_id == 2 && $ocultar_funcao_id == 1) { // Gerente tentando ocultar Desenvolvedor
        $mensagem = 'Você não tem permissão para ocultar um usuário Desenvolvedor!';
    } else {
        $stmt = $pdo->prepare('UPDATE usuarios SET oculto = "T" WHERE id = ?');
        $stmt->execute([$ocultar_id]);
        $mensagem = 'Usuário ocultado com sucesso!';
    }
}
// Reativar usuário via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reativar_id'])) {
    $reativar_id = (int)$_POST['reativar_id'];
    $stmt = $pdo->prepare('SELECT funcao_id FROM usuarios WHERE id = ?');
    $stmt->execute([$reativar_id]);
    $reativar_funcao_id = $stmt->fetchColumn();
    if ($funcao_id == 2 && $reativar_funcao_id == 1) { // Gerente tentando reativar Desenvolvedor
        $mensagem = 'Você não tem permissão para reativar um usuário Desenvolvedor!';
    } else {
        $stmt = $pdo->prepare('UPDATE usuarios SET oculto = "F" WHERE id = ?');
        $stmt->execute([$reativar_id]);
        $mensagem = 'Usuário reativado com sucesso!';
    }
}

// Buscar todos os usuários e suas funções
$stmt = $pdo->query('SELECT u.*, f.nome AS funcao_nome, f.descricao AS funcao_desc FROM usuarios u JOIN funcoes_usuario f ON u.funcao_id = f.id');
$usuarios = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.php">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
    .usuarios-table th, .usuarios-table td { vertical-align: middle; }
    .usuarios-table th { background: var(--cor-secundaria); color: var(--cor-branco); }
    .usuarios-table tr.oculto { background: var(--cor-erro-bg, #f8d7da); color: var(--cor-erro-texto, var(--cor-erro)); }
    .btn-ocultar { background: var(--cor-erro); color: var(--cor-branco); }
    .btn-ocultar:hover { background: var(--cor-erro-hover,rgb(177, 3, 0)); }
    .btn-reativar { background: var(--cor-sucesso); color: var(--cor-branco); }
    .btn-reativar:hover { background: var(--cor-sucesso-hover,rgb(32, 153, 52)); }
    .btn-warning i {
        color: var(--cor-cinza-escuro) !important;
    }
    a.btn, button.btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 10px !important;
        min-width: 32px;
        min-height: 32px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        transition: background 0.2s;
        border: none;
    }
    .btn-ocultar i, .btn-reativar i, .btn-warning i {
        font-size: 1.2em;
        vertical-align: middle;
        margin: 0;
        color: var(--cor-branco) !important;
        display: inline-block;
    }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <div class="form-container">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2>Gerenciar Usuários</h2>
                <div class="d-flex gap-2">
                    <a href="usuario_criar.php" class="btn btn-primary mr-2">Adicionar Novo Usuário</a>
                    <a href="index.php" class="btn btn-secondary">← Voltar</a>
                </div>
            </div>
            <div style="height: 20px;"></div>
            <?php if ($mensagem): ?>
                <div class="alert alert-success"><?= htmlspecialchars($mensagem) ?></div>
            <?php endif; ?>
            <table class="table usuarios-table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Usuário</th>
                        <th>Email</th>
                        <th>Função</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr class="<?= $usuario['oculto'] === 'T' ? 'oculto' : '' ?>">
                        <td><?= htmlspecialchars($usuario['nome']) ?></td>
                        <td><?= htmlspecialchars($usuario['usuario']) ?></td>
                        <td><?= htmlspecialchars($usuario['email']) ?></td>
                        <td title="<?= htmlspecialchars($usuario['funcao_desc']) ?>"><?= htmlspecialchars($usuario['funcao_nome']) ?></td>
                        <td>
                            <a href="usuario_editar.php?id=<?= $usuario['id'] ?>" class="btn btn-warning btn-sm" title="Editar" <?= ($funcao_id == 2 && $usuario['funcao_id'] == 1) ? 'disabled title=\'Gerente não pode editar Desenvolvedor\'' : '' ?>>
                                <i class="fa-solid fa-pencil"></i>
                            </a>
                            <?php if ($usuario['oculto'] === 'F'): ?>
                                <form method="post" style="display:inline;" onsubmit="return confirm('Ocultar este usuário?');">
                                    <input type="hidden" name="ocultar_id" value="<?= $usuario['id'] ?>">
                                    <button type="submit" class="btn btn-ocultar btn-sm" title="Ocultar" <?= ($usuario['id'] == $_SESSION['usuario_id'] || ($funcao_id == 2 && $usuario['funcao_id'] == 1)) ? 'disabled title=\'Gerente não pode ocultar Desenvolvedor\'' : '' ?>>
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="reativar_id" value="<?= $usuario['id'] ?>">
                                    <button type="submit" class="btn btn-reativar btn-sm" title="Reativar" <?= ($funcao_id == 2 && $usuario['funcao_id'] == 1) ? 'disabled title=\'Gerente não pode reativar Desenvolvedor\'' : '' ?>>
                                        <i class="fa-solid fa-eye-slash"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html> 
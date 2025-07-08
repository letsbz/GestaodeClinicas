<?php
session_start();
if (isset($_GET['param']) && $_GET['param'] === 'get_empresas') {
    require_once __DIR__ . '/src/get_empresas.php';
    exit;
}
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'src/funcoes.php';

$stmt = $pdo->prepare('SELECT funcao_id FROM usuarios WHERE id = ?');
$stmt->execute([$_SESSION['usuario_id']]);
$funcao_id = $stmt->fetchColumn();

$clinicas = listarClinicas($pdo);
$clinicas_ocultas = listarClinicasOcultas($pdo);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestão de Clínicas</title>
    <link rel="stylesheet" href="css/style.php">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="card">
            <div class="card-header d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between">
                <h2 class="mb-2 mb-sm-0">Clínicas Cadastradas</h2>
                <div class="d-flex flex-column flex-sm-row gap-2">
                    <?php if ($funcao_id == 1 || $funcao_id == 2): ?>
                        <a href="usuarios.php" class="btn btn-secondary mr-0 mr-sm-2 mb-2 mb-sm-0">Gerenciar Usuários</a>
                    <?php endif; ?>
                    <a href="clinica_cadastrar.php" class="btn btn-primary mb-2 mb-sm-0">
                        <i class="fa-solid fa-plus"></i> Adicionar Nova Clínica
                    </a>
                </div>
            </div>
            <div class="mt-3 mb-3">
                <input type="text" id="busca-clinica" class="form-control" placeholder="Buscar por nome, CNPJ, município ou estado...">
            </div>
            <ul class="nav nav-tabs mt-2" id="clinicasTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="ativas-tab" data-toggle="tab" href="#ativas" role="tab" aria-controls="ativas" aria-selected="true">Ativas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="excluidas-tab" data-toggle="tab" href="#excluidas" role="tab" aria-controls="excluidas" aria-selected="false">Clínicas Excluídas</a>
                </li>
            </ul>
            <div class="tab-content p-4" id="clinicasTabContent">
                <div class="tab-pane fade show active" id="ativas" role="tabpanel" aria-labelledby="ativas-tab">
                    <?php if (empty($clinicas)): ?>
                        <div class="alert alert-warning">
                            <strong>Nenhuma clínica encontrada.</strong> Clique em "Adicionar Nova Clínica" para começar.
                        </div>
                    <?php else: ?>
                        <div class="clinicas-grid">
                            <?php foreach ($clinicas as $clinica): ?>
                                <div class="clinica-card">
                                    <div class="card-actions">
                                        <a href="clinica_ver.php?id=<?= $clinica['id'] ?>" class="btn btn-secondary" title="Visualizar" style="padding: 6px 10px; font-size: 13px;"><i class="fa-solid fa-plus"></i></a>
                                        <a href="clinica_editar.php?id=<?= $clinica['id'] ?>" class="btn btn-warning" title="Editar" style="padding: 6px 10px; font-size: 13px;"><i class="fa-solid fa-pencil"></i></a>
                                        <a href="src/funcoes.php?action=ocultar&id=<?= $clinica['id'] ?>" onclick="return confirm('Tem certeza que deseja excluir a clínica \'<?= htmlspecialchars($clinica['nome']) ?>\'?')" class="btn btn-danger" title="Excluir" style="padding: 6px 10px; font-size: 13px;"><i class="fa-solid fa-trash"></i></a>
                                    </div>
                                    <div class="clinica-info">
                                        <div class="clinica-nome"><strong><?= htmlspecialchars($clinica['nome']) ?></strong></div>
                                        <div class="clinica-cnpj">CNPJ: <?= htmlspecialchars($clinica['cnpj']) ?></div>
                                        <div class="clinica-local">Município/UF: <?= htmlspecialchars($clinica['municipio']) ?>/<?= htmlspecialchars($clinica['estado']) ?></div>
                                        <div class="clinica-tel">Telefone: <?= htmlspecialchars($clinica['telefone']) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="tab-pane fade" id="excluidas" role="tabpanel" aria-labelledby="excluidas-tab">
                    <?php if (empty($clinicas_ocultas)): ?>
                        <div class="alert alert-warning">
                            <strong>Nenhuma clínica excluída.</strong>
                        </div>
                    <?php else: ?>
                        <div class="clinicas-grid">
                            <?php foreach ($clinicas_ocultas as $clinica): ?>
                                <div class="clinica-card">
                                    <div class="card-actions">
                                        <form method="post" action="src/funcoes.php" style="display:inline;">
                                            <input type="hidden" name="action" value="reativar">
                                            <input type="hidden" name="id" value="<?= $clinica['id'] ?>">
                                            <button type="submit" class="btn btn-success" title="Reativar" style="padding: 6px 10px; font-size: 13px;"><i class="fa-solid fa-undo"></i> Reativar</button>
                                        </form>
                                    </div>
                                    <div class="clinica-info">
                                        <div class="clinica-nome"><strong><?= htmlspecialchars($clinica['nome']) ?></strong></div>
                                        <div class="clinica-cnpj">CNPJ: <?= htmlspecialchars($clinica['cnpj']) ?></div>
                                        <div class="clinica-local">Município/UF: <?= htmlspecialchars($clinica['municipio']) ?>/<?= htmlspecialchars($clinica['estado']) ?></div>
                                        <div class="clinica-tel">Telefone: <?= htmlspecialchars($clinica['telefone']) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('busca-clinica').addEventListener('input', function() {
            var termo = this.value.toLowerCase();
            document.querySelectorAll('.clinicas-grid .clinica-card').forEach(function(card) {
                var texto = card.textContent.toLowerCase();
                card.style.display = texto.includes(termo) ? '' : 'none';
            });
        });
    </script>
</body>

</html>
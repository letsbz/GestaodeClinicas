<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'src/funcoes.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = (int)$_GET['id'];
$clinica = buscarClinica($pdo, $id);

if (!$clinica) {
    echo '<p>Clínica não encontrada.</p><a href="index.php">Voltar</a>';
    exit;
}

$exames_clinicos = buscarExamesClinicosClinica($pdo, $id);
$exames_complementares = buscarExamesComplementaresClinica($pdo, $id);

$exames_clinicos_disponiveis = buscarExamesClinicosDisponiveis($pdo, $id);
$exames_complementares_disponiveis = buscarExamesComplementaresDisponiveis($pdo, $id);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes da Clínica - Sistema de Gestão</title>
    <link rel="stylesheet" href="css/style.php">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="detalhes">
            <div class="card-header">
                <h2>Detalhes da Clínica</h2>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <dl>
                        <dt>Nome da Clínica</dt>
                        <dd><strong><?= htmlspecialchars($clinica['nome']) ?></strong></dd>
                        <dt>CNPJ</dt>
                        <dd><?= htmlspecialchars($clinica['cnpj']) ?></dd>
                        <dt>Telefone</dt>
                        <dd><?= htmlspecialchars($clinica['telefone']) ?></dd>
                        <dt>Horário de Atendimento</dt>
                        <dd><?= htmlspecialchars($clinica['horario_atendimento']) ?></dd>
                        <dt>Tipo de Atendimento</dt>
                        <dd><?= htmlspecialchars($clinica['tipo_atendimento'] ?? '') ?></dd>
                    </dl>
                </div>
                <div class="col-md-6">
                    <dl>
                        <dt>Endereço</dt>
                        <dd><?= htmlspecialchars($clinica['endereco']) ?></dd>
                        <dt>Estado</dt>
                        <dd><?= htmlspecialchars($clinica['estado']) ?></dd>
                        <dt>Município</dt>
                        <dd><?= htmlspecialchars($clinica['municipio']) ?></dd>
                        <dt>Email (Contratual)</dt>
                        <dd><a href="mailto:<?= htmlspecialchars($clinica['email_contratual']) ?>"><?= htmlspecialchars($clinica['email_contratual']) ?></a></dd>
                        <dt>Email (Marcação de Exames)</dt>
                        <dd><a href="mailto:<?= htmlspecialchars($clinica['email_marcacao_exames']) ?>"><?= htmlspecialchars($clinica['email_marcacao_exames']) ?></a></dd>
                        <dt>Forma de Pagamento</dt>
                        <dd><?= htmlspecialchars($clinica['forma_pagamento']) ?></dd>
                    </dl>
                </div>
            </div>
            
            <div class="exames-duas-colunas">
                <div class="exames-section col-md-6">
                    <h3>Exames Clínicos</h3>
                    <div class="exames-list">
                        <?php if (empty($exames_clinicos)): ?>
                            <p style="color: var(--cor-cinza-escuro); font-style: italic;">Nenhum exame clínico associado a esta clínica.</p>
                        <?php else: ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Exame Clínico</th>
                                        <th style="width: 120px;">Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($exames_clinicos as $exame): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($exame['exameclinico']) ?></td>
                                            <td>R$ <?= number_format($exame['valor'] ?? 0, 2, ',', '.') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="exames-section col-md-6">
                    <h3>Exames Complementares</h3>
                    
                    <div class="exames-list">
                        <?php if (empty($exames_complementares)): ?>
                            <p style="color: var(--cor-cinza-escuro); font-style: italic;">Nenhum exame complementar associado a esta clínica.</p>
                        <?php else: ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Exame Complementar</th>
                                        <th style="width: 120px;">Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($exames_complementares as $exame): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($exame['examecomplementar']) ?></td>
                                            <td>R$ <?= number_format($exame['valor'] ?? 0, 2, ',', '.') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="actions">
                <a href="clinica_editar.php?id=<?= $clinica['id'] ?>" class="btn btn-warning">Editar Clínica</a>
                <a href="src/funcoes.php?action=ocultar&id=<?= $clinica['id'] ?>" 
                   onclick="return confirm('⚠️ Tem certeza que deseja excluir a clínica \'<?= htmlspecialchars($clinica['nome']) ?>\'?')" 
                   class="btn btn-danger">Excluir Clínica</a>
            </div>
        </div>
    </div>
</body>
</html> 
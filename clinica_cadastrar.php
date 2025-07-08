<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'src/funcoes.php';

$estado = listarEstados($pdo);
$exames_clinicos = listarExamesClinicos($pdo);
$exames_complementares = listarExamesComplementares($pdo);
$erros = [];
$sucesso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $campos_obrigatorios = [
        'cnpj',
        'nome',
        'telefone',
        'horario_atendimento',
        'endereco',
        'estado',
        'municipio',
        'email_contratual',
        'email_marcacao_exames',
        'forma_pagamento'
    ];
    foreach ($campos_obrigatorios as $campo) {
        if (empty($_POST[$campo])) {
            $erros[] = "O campo '" . $campo . "' é obrigatório.";
        }
    }
    // Validação de CNPJ
    if (!empty($_POST['cnpj']) && !validarCNPJ($_POST['cnpj'])) {
        $erros[] = "CNPJ inválido.";
    }
    if (empty($erros)) {
        $dados = [
            'cnpj' => formatarCNPJ($_POST['cnpj']),
            'nome' => $_POST['nome'],
            'telefone' => formatarTelefone($_POST['telefone']),
            'horario_atendimento' => $_POST['horario_atendimento'],
            'endereco' => $_POST['endereco'],
            'estado' => $_POST['estado'],
            'municipio' => $_POST['municipio'],
            'email_contratual' => $_POST['email_contratual'],
            'email_marcacao_exames' => $_POST['email_marcacao_exames'],
            'forma_pagamento' => $_POST['forma_pagamento']
        ];
        if (criarClinica($pdo, $dados)) {
            $clinica_id = $pdo->lastInsertId();

            if (isset($_POST['exames_clinicos']) && is_array($_POST['exames_clinicos'])) {
                associarExamesClinicos($pdo, $clinica_id, $_POST['exames_clinicos']);
            }

            if (isset($_POST['exames_complementares']) && is_array($_POST['exames_complementares'])) {
                associarExamesComplementares($pdo, $clinica_id, $_POST['exames_complementares']);
            }

            echo '<div id="modal-sucesso" class="modal-sucesso-overlay" style="display:flex;">
                    <div class="modal-sucesso-content alert-success">
                        <h4>Clínica cadastrada com sucesso!</h4>
                        <button class="btn btn-primary" onclick="document.getElementById(\'modal-sucesso\').style.display=\'none\'; window.location.href=\'clinica_editar.php\';">OK</button>
                    </div>
                  </div>';
        } else {
            $erros[] = 'Erro ao cadastrar clínica.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Clínica - Sistema de Gestão</title>
    <link rel="stylesheet" href="css/style.php">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
</head>

<body>
<script>
function mascaraCNPJ(campo) {
    let v = campo.value.replace(/\D/g, '');
    v = v.replace(/(\d{2})(\d)/, '$1.$2');
    v = v.replace(/(\d{3})(\d)/, '$1.$2');
    v = v.replace(/(\d{3})(\d)/, '$1/$2');
    v = v.replace(/(\d{4})(\d{1,2})$/, '$1-$2');
    campo.value = v;
}
function mascaraTelefone(campo) {
    let v = campo.value.replace(/\D/g, '');
    if (v.length > 10) {
        v = v.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
    } else {
        v = v.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
    }
    campo.value = v;
}
</script>
<?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($erros) && isset($dados) && isset($clinica_id)) : ?>
    <div id="modal-sucesso" class="modal-sucesso-overlay" style="display:flex;">
        <div class="modal-sucesso-content alert-success">
            <h4>Clínica cadastrada com sucesso!</h4>
            <button class="btn btn-primary" onclick="window.location.href='clinica_editar.php?id=<?= $clinica_id ?>';">OK</button>
        </div>
    </div>
<?php endif; ?>
<?php include 'header.php'; ?>
    <div class="container">
        <div class="form-container">
            <div class="card-header">
                <h2>Cadastrar Nova Clínica</h2>
            </div>
            <div style="height: 40px;"></div>
    <?php if ($erros): ?>
                <div class="alert alert-error">
                    <strong>Erros encontrados:</strong>
                    <ul style="margin-top: 10px; margin-left: 20px;">
                <?php foreach ($erros as $erro): ?>
                    <li><?= htmlspecialchars($erro) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
            <form method="post" id="form-cadastrar-clinica">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nome da Clínica *</label>
                            <input type="text" name="nome" class="form-control" required value="<?= isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : '' ?>">
                        </div>
                        <div class="form-group">
                            <label>CNPJ *</label>
                            <input type="text" name="cnpj" class="form-control" required maxlength="18" placeholder="00.000.000/0000-00" value="<?= isset($_POST['cnpj']) ? formatarCNPJ(htmlspecialchars($_POST['cnpj'])) : '' ?>" oninput="mascaraCNPJ(this)">
                        </div>
                        <div class="form-group">
                            <label>Telefone *</label>
                            <input type="text" name="telefone" class="form-control" required maxlength="15" placeholder="(99) 99999-9999" value="<?= isset($_POST['telefone']) ? formatarTelefone(htmlspecialchars($_POST['telefone'])) : '' ?>" oninput="mascaraTelefone(this)">
                        </div>
                        <div class="form-group">
                            <label>Horário de Atendimento *</label>
                            <input type="text" name="horario_atendimento" class="form-control" required placeholder="08:00 às 18:00" value="<?= isset($_POST['horario_atendimento']) ? htmlspecialchars($_POST['horario_atendimento']) : '' ?>">
                        </div>
                        <div class="form-group">
                            <label>Email (Contratual) *</label>
                            <input type="text" name="email_contratual" class="form-control" required pattern="^([\w\.-]+@[\w\.-]+\.[a-zA-Z]{2,}(;[\w\.-]+@[\w\.-]+\.[a-zA-Z]{2,})*)$" title="Separe múltiplos e-mails com ponto e vírgula (;)" value="<?= isset($_POST['email_contratual']) ? htmlspecialchars($_POST['email_contratual']) : '' ?>">
                            <small class="form-text text-muted">Separe múltiplos e-mails com ponto e vírgula (;)</small>
                        </div>
                        <div class="form-group">
                            <label>Tipo de Atendimento *</label>
                            <div class="d-flex align-items-center gap-3 mt-2">
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input" type="radio" id="agendado" name="tipo_atendimento" value="Horário Agendado" <?= (isset($_POST['tipo_atendimento']) && $_POST['tipo_atendimento'] == 'Horário Agendado') ? 'checked' : '' ?> required>
                                    <label class="form-check-label" for="agendado">Horário Agendado</label>
                                </div>
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input" type="radio" id="ordem" name="tipo_atendimento" value="Ordem de Chegada" <?= (isset($_POST['tipo_atendimento']) && $_POST['tipo_atendimento'] == 'Ordem de Chegada') ? 'checked' : '' ?> required>
                                    <label class="form-check-label" for="ordem">Ordem de Chegada</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Endereço *</label>
                            <input type="text" name="endereco" class="form-control" required value="<?= isset($_POST['endereco']) ? htmlspecialchars($_POST['endereco']) : '' ?>">
                        </div>
                        <div class="form-group">
                            <label>Estado (UF) *</label>
                            <select name="estado" id="estado" class="form-control" required>
                                <option value="">Selecione o estado</option>
                                <?php foreach ($estado as $estado): ?>
                                    <option value="<?= $estado['sigla'] ?>" <?= (isset($_POST['estado']) && $_POST['estado'] == $estado['sigla']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($estado['estado']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Município *</label>
                            <select name="municipio" id="municipio" class="form-control" required>
                                <option value="">Selecione primeiro o estado</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Forma de Pagamento *</label>
                            <select name="forma_pagamento" class="form-control" required>
                                <option value="">Selecione a forma de pagamento</option>
                                <option value="BOLETO" <?= (isset($_POST['forma_pagamento']) && $_POST['forma_pagamento'] == 'BOLETO') ? 'selected' : '' ?>>BOLETO</option>
                                <option value="CARTÃO CRÉDITO" <?= (isset($_POST['forma_pagamento']) && $_POST['forma_pagamento'] == 'CARTÃO CRÉDITO') ? 'selected' : '' ?>>CARTÃO CRÉDITO</option>
                                <option value="CARTÃO DÉBITO" <?= (isset($_POST['forma_pagamento']) && $_POST['forma_pagamento'] == 'CARTÃO DÉBITO') ? 'selected' : '' ?>>CARTÃO DÉBITO</option>
                                <option value="CHEQUE" <?= (isset($_POST['forma_pagamento']) && $_POST['forma_pagamento'] == 'CHEQUE') ? 'selected' : '' ?>>CHEQUE</option>
                                <option value="DEPÓSITO BANCÁRIO" <?= (isset($_POST['forma_pagamento']) && $_POST['forma_pagamento'] == 'DEPÓSITO BANCÁRIO') ? 'selected' : '' ?>>DEPÓSITO BANCÁRIO</option>
                                <option value="DINHEIRO" <?= (isset($_POST['forma_pagamento']) && $_POST['forma_pagamento'] == 'DINHEIRO') ? 'selected' : '' ?>>DINHEIRO</option>
                                <option value="FATURAMENTO" <?= (isset($_POST['forma_pagamento']) && $_POST['forma_pagamento'] == 'FATURAMENTO') ? 'selected' : '' ?>>FATURAMENTO</option>
                                <option value="TRANSFERÊNCIA BANCÁRIA" <?= (isset($_POST['forma_pagamento']) && $_POST['forma_pagamento'] == 'TRANSFERÊNCIA BANCÁRIA') ? 'selected' : '' ?>>TRANSFERÊNCIA BANCÁRIA</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Email (Marcação de Exames) *</label>
                            <input type="text" name="email_marcacao_exames" class="form-control" required pattern="^([\w\.-]+@[\w\.-]+\.[a-zA-Z]{2,}(;[\w\.-]+@[\w\.-]+\.[a-zA-Z]{2,})*)$" title="Separe múltiplos e-mails com ponto e vírgula (;)" value="<?= isset($_POST['email_marcacao_exames']) ? htmlspecialchars($_POST['email_marcacao_exames']) : '' ?>">
                            <small class="form-text text-muted">Separe múltiplos e-mails com ponto e vírgula (;)</small>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Exames Clínicos (Opcional)</label>
                            <select class="form-control selectpicker" name="exames_clinicos[]" id="exames_clinicos" data-hide-disabled="true" data-actions-box="true" data-virtual-scroll="false" data-container="body" data-size="10" title="Selecione o Exame Clínico" multiple data-live-search="true">
                                <?php foreach ($exames_clinicos as $exame): ?>
                                    <option value="<?= $exame['codexameclinico'] ?>" <?= (isset($_POST['exames_clinicos']) && in_array($exame['codexameclinico'], $_POST['exames_clinicos'])) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($exame['exameclinico']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Exames Complementares (Opcional)</label>
                            <select class="form-control selectpicker" name="exames_complementares[]" id="exames_complementares" data-hide-disabled="true" data-actions-box="true" data-virtual-scroll="false" data-container="body" data-size="10" title="Selecione o Exame Complementar" multiple data-live-search="true">
                                <?php foreach ($exames_complementares as $exame): ?>
                                    <option value="<?= $exame['codexamecomplementar'] ?>" <?= (isset($_POST['exames_complementares']) && in_array($exame['codexamecomplementar'], $_POST['exames_complementares'])) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($exame['examecomplementar']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="actions">
                    <button type="submit" class="btn btn-primary">Cadastrar Clínica</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
    <script>
        // Função para atualizar campos de valor dos exames selecionados
        function atualizarCamposValoresExames() {
            // Exames Clínicos
            var examesClinicos = $('#exames_clinicos').val() || [];
            var listaClinicos = <?php echo json_encode($exames_clinicos); ?>;
            var htmlClinicos = '';
            examesClinicos.forEach(function(id) {
                var exame = listaClinicos.find(function(e) { return e.codexameclinico == id; });
                if (exame) {
                    var valor = '';
                    if (window.valoresExamesClinicos && window.valoresExamesClinicos[id]) {
                        valor = window.valoresExamesClinicos[id];
                    } else if (typeof window.oldValoresClinicos === 'object' && window.oldValoresClinicos[id]) {
                        valor = window.oldValoresClinicos[id];
                    }
                    htmlClinicos += `<div class="form-group row align-items-center">
                        <label class="col-sm-7 col-form-label">${exame.exameclinico}</label>
                        <div class="col-sm-5">
                            <input type="number" step="0.01" min="0" class="form-control" name="valores_exames_clinicos[${id}]" value="${valor}" placeholder="Valor (R$)" required>
                        </div>
                    </div>`;
                }
            });
            $('#valores-exames-clinicos').html(htmlClinicos);

            // Exames Complementares
            var examesComplementares = $('#exames_complementares').val() || [];
            var listaComplementares = <?php echo json_encode($exames_complementares); ?>;
            var htmlComplementares = '';
            examesComplementares.forEach(function(id) {
                var exame = listaComplementares.find(function(e) { return e.codexamecomplementar == id; });
                if (exame) {
                    var valor = '';
                    if (window.valoresExamesComplementares && window.valoresExamesComplementares[id]) {
                        valor = window.valoresExamesComplementares[id];
                    } else if (typeof window.oldValoresComplementares === 'object' && window.oldValoresComplementares[id]) {
                        valor = window.oldValoresComplementares[id];
                    }
                    htmlComplementares += `<div class="form-group row align-items-center">
                        <label class="col-sm-7 col-form-label">${exame.examecomplementar}</label>
                        <div class="col-sm-5">
                            <input type="number" step="0.01" min="0" class="form-control" name="valores_exames_complementares[${id}]" value="${valor}" placeholder="Valor (R$)" required>
                        </div>
                    </div>`;
                }
            });
            $('#valores-exames-complementares').html(htmlComplementares);
        }

        // Guardar valores preenchidos ao trocar seleção
        window.valoresExamesClinicos = {};
        window.valoresExamesComplementares = {};

        $(document).on('change', '#exames_clinicos, #exames_complementares', function() {
            // Salvar valores já preenchidos
            $('#valores-exames-clinicos input').each(function() {
                var id = $(this).attr('name').match(/\[(\d+)\]/)[1];
                window.valoresExamesClinicos[id] = $(this).val();
            });
            $('#valores-exames-complementares input').each(function() {
                var id = $(this).attr('name').match(/\[(\d+)\]/)[1];
                window.valoresExamesComplementares[id] = $(this).val();
            });
            atualizarCamposValoresExames();
        });

        // Inicializar campos ao carregar
        $(function() {
            // Recuperar valores antigos se houver erro de validação
            window.oldValoresClinicos = <?php echo isset($_POST['valores_exames_clinicos']) ? json_encode($_POST['valores_exames_clinicos']) : 'null'; ?>;
            window.oldValoresComplementares = <?php echo isset($_POST['valores_exames_complementares']) ? json_encode($_POST['valores_exames_complementares']) : 'null'; ?>;
            atualizarCamposValoresExames();
            $('.selectpicker').selectpicker();
        });

        document.getElementById('estado').addEventListener('change', function() {
            const estadoSelect = this;
            const municipioSelect = document.getElementById('municipio');
            const estadoSigla = estadoSelect.value;
            municipioSelect.innerHTML = '<option value="">Carregando...</option>';
            if (estadoSigla) {
                fetch('src/funcoes.php?action=buscar_municipios&estado_sigla=' + estadoSigla)
                    .then(response => response.json())
                    .then(municipios => {
                        municipioSelect.innerHTML = '<option value="">Selecione o município</option>';
                        municipios.forEach(municipio => {
                            const option = document.createElement('option');
                            option.value = municipio.nome;
                            option.textContent = municipio.nome;
                            municipioSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        municipioSelect.innerHTML = '<option value="">Erro ao carregar municípios</option>';
                    });
            } else {
                municipioSelect.innerHTML = '<option value="">Selecione primeiro o estado</option>';
            }
        });

        // Se existir código JS para editar valor de exame:
        // Trocar:
        // $('.btn-editar-valor').on('click', function() { ... });
        // por:
        $(document).on('click', '.btn-editar-valor', function() {
            $('#modal-exame-id').val($(this).data('id'));
            $('#modal-exame-tipo').val($(this).data('tipo'));
            $('#modal-exame-nome').val($(this).data('nome'));
            $('#modal-exame-valor').val($(this).data('valor'));
            $('#modalEditarValor').modal('show');
        });

        // No final do arquivo, após o carregamento dos municípios via AJAX:
        window.addEventListener('load', function() {
            const estadoSelect = document.getElementById('estado');
            const municipioSelect = document.getElementById('municipio');
            const estadoAtual = estadoSelect.value;
            const municipioAtual = '<?= isset($_POST['municipio']) ? addslashes($_POST['municipio']) : '' ?>';
            if (estadoAtual) {
                carregarMunicipios(estadoAtual, municipioAtual);
            }
        });

        // Validação extra para múltiplos e-mails
        function validarEmailsMultiplo(valor) {
            return valor.split(';').every(function(email) {
                return /^[\w\.-]+@[\w\.-]+\.[a-zA-Z]{2,}$/.test(email.trim());
            });
        }
        document.querySelector('form').addEventListener('submit', function(e) {
            var emailContratual = document.querySelector('[name="email_contratual"]').value;
            var emailMarcacao = document.querySelector('[name="email_marcacao_exames"]').value;
            if (!validarEmailsMultiplo(emailContratual) || !validarEmailsMultiplo(emailMarcacao)) {
                alert('Por favor, insira e-mails válidos separados por ponto e vírgula (;)');
                e.preventDefault();
            }
        });
    </script>
</body>

</html> 
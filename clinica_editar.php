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

$exames_clinicos_associados = buscarExamesClinicosClinica($pdo, $id);
$exames_complementares_associados = buscarExamesComplementaresClinica($pdo, $id);

$erros = [];

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
        if (atualizarClinica($pdo, $id, $dados)) {
      // Associar exames clínicos
      if (isset($_POST['exames_clinicos']) && is_array($_POST['exames_clinicos'])) {
        associarExamesClinicos($pdo, $id, $_POST['exames_clinicos']);
      } else {
        associarExamesClinicos($pdo, $id, []);
      }

      // Associar exames complementares
      if (isset($_POST['exames_complementares']) && is_array($_POST['exames_complementares'])) {
        associarExamesComplementares($pdo, $id, $_POST['exames_complementares']);
      } else {
        associarExamesComplementares($pdo, $id, []);
      }

      header('Location: clinica_editar.php?id=' . $id);
            exit;
        } else {
            $erros[] = 'Erro ao atualizar clínica.';
        }
    }
}

function valor($campo, $clinica)
{
    return isset($_POST[$campo]) ? htmlspecialchars($_POST[$campo]) : htmlspecialchars($clinica[$campo]);
}

// Verificar se exame está selecionado
function exameSelecionado($exame_id, $tipo, $exames_associados, $post_data)
{
  if (isset($post_data[$tipo]) && in_array($exame_id, $post_data[$tipo])) {
    return 'selected';
  }

  foreach ($exames_associados as $associado) {
    if ($tipo == 'exames_clinicos' && $associado['codexameclinico'] == $exame_id) {
      return 'selected';
    }
    if ($tipo == 'exames_complementares' && $associado['codexamecomplementar'] == $exame_id) {
      return 'selected';
    }
  }
  return '';
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar Clínica - Sistema de Gestão</title>
  <link rel="stylesheet" href="css/style.php">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
</head>

<body>
<?php include 'header.php'; ?>
  <div class="container">
    <div class="form-container">
      <div class="card-header">
        <h2>Editar Clínica</h2>
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

      <form id="form-editar-clinica" method="post">
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Nome da Clínica *</label>
              <input type="text" name="nome" class="form-control" required value="<?= valor('nome', $clinica) ?>">
            </div>
            <div class="form-group">
              <label>CNPJ *</label>
              <input type="text" name="cnpj" class="form-control" required maxlength="18" placeholder="00.000.000/0000-00" value="<?= isset($_POST['cnpj']) ? formatarCNPJ(htmlspecialchars($_POST['cnpj'])) : formatarCNPJ($clinica['cnpj']) ?>" oninput="mascaraCNPJ(this)">
            </div>
            <div class="form-group">
              <label>Telefone *</label>
              <input type="text" name="telefone" class="form-control" required maxlength="15" placeholder="(99) 99999-9999" value="<?= isset($_POST['telefone']) ? formatarTelefone(htmlspecialchars($_POST['telefone'])) : formatarTelefone($clinica['telefone']) ?>" oninput="mascaraTelefone(this)">
            </div>
            <div class="form-group">
              <label>Horário de Atendimento *</label>
              <input type="text" name="horario_atendimento" class="form-control" required placeholder="08:00 às 18:00" value="<?= valor('horario_atendimento', $clinica) ?>">
            </div>
            <div class="form-group">
              <label>Email (Contratual) *</label>
              <input type="text" name="email_contratual" class="form-control" required pattern="^([\w\.-]+@[\w\.-]+\.[a-zA-Z]{2,}(;[\w\.-]+@[\w\.-]+\.[a-zA-Z]{2,})*)$" title="Separe múltiplos e-mails com ponto e vírgula (;)" value="<?= valor('email_contratual', $clinica) ?>">
              <small class="form-text text-muted">Separe múltiplos e-mails com ponto e vírgula (;)</small>
            </div>
            <div class="form-group">
              <label>Tipo de Atendimento *</label>
              <div class="d-flex align-items-center gap-3 mt-2">
                <div class="form-check form-check-inline mb-0">
                  <input class="form-check-input" type="radio" id="agendado" name="tipo_atendimento" value="Horário Agendado" <?= (valor('tipo_atendimento', $clinica) == 'Horário Agendado') ? 'checked' : '' ?> required>
                  <label class="form-check-label" for="agendado">Horário Agendado</label>
                </div>
                <div class="form-check form-check-inline mb-0">
                  <input class="form-check-input" type="radio" id="ordem" name="tipo_atendimento" value="Ordem de Chegada" <?= (valor('tipo_atendimento', $clinica) == 'Ordem de Chegada') ? 'checked' : '' ?> required>
                  <label class="form-check-label" for="ordem">Ordem de Chegada</label>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label>Endereço *</label>
              <input type="text" name="endereco" class="form-control" required value="<?= valor('endereco', $clinica) ?>">
            </div>
            <div class="form-group">
              <label>Estado (UF) *</label>
              <select name="estado" id="estado" class="form-control" required>
                <option value="">Selecione o estado</option>
                <?php foreach ($estado as $estado): ?>
                  <option value="<?= $estado['sigla'] ?>" <?= (valor('estado', $clinica) == $estado['sigla']) ? 'selected' : '' ?>>
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
                <option value="BOLETO" <?= (valor('forma_pagamento', $clinica) == 'BOLETO') ? 'selected' : '' ?>>BOLETO</option>
                <option value="CARTÃO CRÉDITO" <?= (valor('forma_pagamento', $clinica) == 'CARTÃO CRÉDITO') ? 'selected' : '' ?>>CARTÃO CRÉDITO</option>
                <option value="CARTÃO DÉBITO" <?= (valor('forma_pagamento', $clinica) == 'CARTÃO DÉBITO') ? 'selected' : '' ?>>CARTÃO DÉBITO</option>
                <option value="CHEQUE" <?= (valor('forma_pagamento', $clinica) == 'CHEQUE') ? 'selected' : '' ?>>CHEQUE</option>
                <option value="DEPÓSITO BANCÁRIO" <?= (valor('forma_pagamento', $clinica) == 'DEPÓSITO BANCÁRIO') ? 'selected' : '' ?>>DEPÓSITO BANCÁRIO</option>
                <option value="DINHEIRO" <?= (valor('forma_pagamento', $clinica) == 'DINHEIRO') ? 'selected' : '' ?>>DINHEIRO</option>
                <option value="FATURAMENTO" <?= (valor('forma_pagamento', $clinica) == 'FATURAMENTO') ? 'selected' : '' ?>>FATURAMENTO</option>
                <option value="TRANSFERÊNCIA BANCÁRIA" <?= (valor('forma_pagamento', $clinica) == 'TRANSFERÊNCIA BANCÁRIA') ? 'selected' : '' ?>>TRANSFERÊNCIA BANCÁRIA</option>
              </select>
            </div>
            <div class="form-group">
              <label>Email (Marcação de Exames) *</label>
              <input type="text" name="email_marcacao_exames" class="form-control" required pattern="^([\w\.-]+@[\w\.-]+\.[a-zA-Z]{2,}(;[\w\.-]+@[\w\.-]+\.[a-zA-Z]{2,})*)$" title="Separe múltiplos e-mails com ponto e vírgula (;)" value="<?= valor('email_marcacao_exames', $clinica) ?>">
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
                  <option value="<?= $exame['codexameclinico'] ?>" <?= exameSelecionado($exame['codexameclinico'], 'exames_clinicos', $exames_clinicos_associados, $_POST) ?>>
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
                  <option value="<?= $exame['codexamecomplementar'] ?>" <?= exameSelecionado($exame['codexamecomplementar'], 'exames_complementares', $exames_complementares_associados, $_POST) ?>>
                    <?= htmlspecialchars($exame['examecomplementar']) ?>
                  </option>
                <?php endforeach; ?>
            </select>
            </div>
          </div>
        </div>
      </form>

      <div class="exames-duas-colunas">
        <div class="exames-section">
          <h3>Exames Clínicos Associados</h3>
          <?php if (empty($exames_clinicos_associados)): ?>
            <p style="color: var(--cor-cinza-escuro); font-style: italic;">Nenhum exame clínico associado.</p>
          <?php else: ?>
            <table class="table">
              <thead>
                <tr>
                  <th style="width: 60px;">Ações</th>
                  <th>Exame Clínico</th>
                  <th style="width: 120px;">Valor (R$)</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($exames_clinicos_associados as $exame): ?>
                  <tr>
                    <td>
                      <div class="exame-actions d-flex gap-1 align-items-center">
                        <button type="button" class="btn btn-warning btn-sm btn-editar-valor" data-id="<?= $exame['assoc_id'] ?>" data-tipo="clinico" data-nome="<?= htmlspecialchars($exame['exameclinico']) ?>" data-valor="<?= $exame['valor'] ?>" title="Editar"><i class="fa-solid fa-pencil"></i></button>
                        <button type="button" class="btn btn-danger btn-sm btn-excluir-exame" data-id="<?= $exame['assoc_id'] ?>" data-tipo="clinico" title="Excluir"><i class="fa-solid fa-trash"></i></button>
                      </div>
                    </td>
                    <td><?= htmlspecialchars($exame['exameclinico']) ?></td>
                    <td>R$ <span class="valor-exame-span" data-id="<?= $exame['assoc_id'] ?>"><?= number_format($exame['valor'] ?? 0, 2, ',', '.') ?></span></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
        <div class="exames-section">
          <h3>Exames Complementares Associados</h3>
          <?php if (empty($exames_complementares_associados)): ?>
            <p style="color: var(--cor-cinza-escuro); font-style: italic;">Nenhum exame complementar associado.</p>
          <?php else: ?>
            <table class="table">
              <thead>
                <tr>
                  <th style="width: 60px;">Ações</th>
                  <th>Exame Complementar</th>
                  <th style="width: 120px;">Valor (R$)</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($exames_complementares_associados as $exame): ?>
                  <tr>
                    <td>
                      <div class="exame-actions d-flex gap-1 align-items-center">
                        <button type="button" class="btn btn-warning btn-sm btn-editar-valor" data-id="<?= $exame['assoc_id'] ?>" data-tipo="complementar" data-nome="<?= htmlspecialchars($exame['examecomplementar']) ?>" data-valor="<?= $exame['valor'] ?>" title="Editar"><i class="fa-solid fa-pencil"></i></button>
                        <button type="button" class="btn btn-danger btn-sm btn-excluir-exame" data-id="<?= $exame['assoc_id'] ?>" data-tipo="complementar" title="Excluir"><i class="fa-solid fa-trash"></i></button>
                      </div>
                    </td>
                    <td><?= htmlspecialchars($exame['examecomplementar']) ?></td>
                    <td>R$ <span class="valor-exame-span" data-id="<?= $exame['assoc_id'] ?>"><?= number_format($exame['valor'] ?? 0, 2, ',', '.') ?></span></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>

      <!-- Modal de edição de valor (mover para fora do form principal) -->
      <div class="modal fade" id="modalEditarValor" tabindex="-1" role="dialog" aria-labelledby="modalEditarValorLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <form id="formEditarValor" method="post">
              <div class="modal-header">
                <h5 class="modal-title" id="modalEditarValorLabel">Editar Valor do Exame</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <input type="hidden" name="id" id="modal-exame-id">
                <input type="hidden" name="tipo" id="modal-exame-tipo">
                <div class="form-group">
                  <label for="modal-exame-nome">Exame</label>
                  <input type="text" class="form-control" id="modal-exame-nome" readonly>
                </div>
                <div class="form-group">
                  <label for="modal-exame-valor">Valor (R$)</label>
                  <input type="number" step="0.01" min="0" class="form-control" name="valor" id="modal-exame-valor" required>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar</button>
              </div>
    </form>
          </div>
        </div>
      </div>
      <div class="actions">
        <button type="button" id="btn-salvar-alteracoes" class="btn btn-primary">Salvar Alterações</button>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js"></script>
  <script>
    $(function() {
      $('.selectpicker').selectpicker();
    });
    // Carregar municípios do estado atual ao carregar a página
    window.addEventListener('load', function() {
      const estadoSelect = document.getElementById('estado');
      const municipioSelect = document.getElementById('municipio');
      const estadoAtual = estadoSelect.value;
      const municipioAtual = '<?= valor('municipio', $clinica) ?>';
      if (estadoAtual) {
        carregarMunicipios(estadoAtual, municipioAtual);
      }
    });

    document.getElementById('estado').addEventListener('change', function() {
      const estadoSelect = this;
      const estadoSigla = estadoSelect.value;

      if (estadoSigla) {
        carregarMunicipios(estadoSigla);
      } else {
        document.getElementById('municipio').innerHTML = '<option value="">Selecione primeiro o estado</option>';
      }
    });

    function carregarMunicipios(estadoSigla, municipioSelecionado = '') {
      const municipioSelect = document.getElementById('municipio');
      municipioSelect.innerHTML = '<option value="">Carregando...</option>';

      fetch('src/funcoes.php?action=buscar_municipios&estado_sigla=' + estadoSigla)
        .then(response => response.json())
        .then(municipios => {
          municipioSelect.innerHTML = '<option value="">Selecione o município</option>';
          municipios.forEach(municipio => {
            const option = document.createElement('option');
            option.value = municipio.nome;
            option.textContent = municipio.nome;
            if (municipio.nome === municipioSelecionado) {
              option.selected = true;
            }
            municipioSelect.appendChild(option);
          });
        })
        .catch(error => {
          municipioSelect.innerHTML = '<option value="">Erro ao carregar municípios</option>';
        });
    }

    $(function() {
      // Editar valor
      $(document).on('click', '.btn-editar-valor', function() {
        $('#modal-exame-id').val($(this).data('id'));
        $('#modal-exame-tipo').val($(this).data('tipo'));
        $('#modal-exame-nome').val($(this).data('nome'));
        $('#modal-exame-valor').val($(this).data('valor'));
        $('#modalEditarValor').modal('show');
      });
      // Salvar valor via AJAX
      $(document).on('submit', '#formEditarValor', function(e) {
        e.preventDefault();
        console.log('Submit do modal capturado!');
        var dados = $(this).serialize() + '&action=atualizar_valor_exame';
        var id = $('#modal-exame-id').val();
        var tipo = $('#modal-exame-tipo').val();
        var valor = $('#modal-exame-valor').val();
        $.post('src/funcoes.php', dados, function(resp) {
          if (resp === 'OK') {
            $('#modalEditarValor').modal('hide');
            // Atualiza valor na tabela
            $(".valor-exame-span[data-id='" + id + "']").text(parseFloat(valor).toLocaleString('pt-BR', {
              minimumFractionDigits: 2
            }));
          } else {
            alert('Erro ao atualizar valor!');
          }
        }).fail(function() {
          alert('Erro ao atualizar valor!');
        });
      });
      // Excluir exame (ocultar)
      $('.btn-excluir-exame').on('click', function() {
        if (!confirm('Tem certeza que deseja excluir este exame?')) return;
        var id = $(this).data('id');
        var tipo = $(this).data('tipo');
        $.post('src/funcoes.php?action=ocultar_exame', {
          id: id,
          tipo: tipo
        }, function(resp) {
          if (resp === 'OK') {
            location.reload();
          } else {
            alert('Erro ao excluir exame!');
          }
        });
      });
    });

    document.getElementById('btn-salvar-alteracoes').addEventListener('click', function() {
      document.getElementById('form-editar-clinica').submit();
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
</body>

</html> 
<?php
// Roteador de ações para endpoints AJAX e links
if (php_sapi_name() !== 'cli' && basename($_SERVER['SCRIPT_NAME']) !== 'login.php') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    require_once __DIR__ . '/../config.php';
    if (!isset($_SESSION['usuario_id'])) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            http_response_code(401);
            exit('Não autenticado');
        } else {
            // Corrigir redirecionamento para login.php relativo à pasta do projeto
            $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
            header('Location: ' . $base . '/login.php');
            exit;
        }
    }
    // Ocultar clínica (exclusão lógica)
    if (isset($_GET['action']) && $_GET['action'] === 'ocultar' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        ocultarClinica($pdo, $id);
        header('Location: ../index.php');
        exit;
    }
    // Reativar clínica
    if (isset($_POST['action']) && $_POST['action'] === 'reativar' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        reativarClinica($pdo, $id);
        header('Location: ../index.php');
        exit;
    }
    // Buscar municípios (AJAX)
    if (isset($_GET['action']) && $_GET['action'] === 'buscar_municipios' && isset($_GET['estado_sigla'])) {
        header('Content-Type: application/json');
        $sigla = $_GET['estado_sigla'];
        $estado = buscarEstadoPorSigla($pdo, $sigla);
        if (!$estado) {
            echo json_encode([]);
            exit;
        }
        $municipios = buscarMunicipiosPorEstado($pdo, $estado['codestado']);
        echo json_encode($municipios);
        exit;
    }
    // Atualizar valor de exame (AJAX)
    if (isset($_POST['action']) && $_POST['action'] === 'atualizar_valor_exame') {
        if (!isset($_POST['id'], $_POST['tipo'], $_POST['valor'])) {
            http_response_code(400);
            exit('Faltam dados');
        }
        $id = (int)$_POST['id'];
        $tipo = $_POST['tipo'];
        $valor = str_replace(',', '.', $_POST['valor']);
        if (!is_numeric($valor)) {
            http_response_code(400);
            exit('Valor inválido');
        }
        if (atualizarValorExame($pdo, $id, $tipo, $valor)) {
            echo 'OK';
        } else {
            http_response_code(500);
            echo 'Erro';
        }
        exit;
    }
    // Logout
    if ((isset($_GET['action']) && $_GET['action'] === 'logout') || (isset($_POST['action']) && $_POST['action'] === 'logout')) {
        $_SESSION = array();
        session_destroy();
        // Redirecionar para a raiz do projeto
        $root = rtrim(str_replace('/src', '', dirname($_SERVER['SCRIPT_NAME'])), '/\\');
        header('Location: ' . $root . '/login.php');
        exit;
    }
    // Ocultar exame (AJAX)
    if ((isset($_POST['action']) && $_POST['action'] === 'ocultar_exame') || (isset($_GET['action']) && $_GET['action'] === 'ocultar_exame')) {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : null);
        $tipo = isset($_POST['tipo']) ? $_POST['tipo'] : (isset($_GET['tipo']) ? $_GET['tipo'] : null);
        if (!$id || !$tipo) {
            http_response_code(400);
            exit('Faltam dados');
        }
        if ($tipo === 'clinico') {
            ocultarExameClinico($pdo, $id);
            echo 'OK';
        } elseif ($tipo === 'complementar') {
            ocultarExameComplementar($pdo, $id);
            echo 'OK';
        } else {
            http_response_code(400);
            exit('Tipo inválido');
        }
        exit;
    }
}

// Listar clínicas
function listarClinicas($pdo) {
    $stmt = $pdo->query('SELECT * FROM clinicas WHERE oculto = "F"');
    return $stmt->fetchAll();
}

// Buscar clínica por ID
function buscarClinica($pdo, $id) {
    $stmt = $pdo->prepare('SELECT * FROM clinicas WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Criar clínica
function criarClinica($pdo, $dados) {
    $sql = 'INSERT INTO clinicas (cnpj, nome, telefone, horario_atendimento, endereco, estado, municipio, email_contratual, email_marcacao_exames, forma_pagamento) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $dados['cnpj'],
        $dados['nome'],
        $dados['telefone'],
        $dados['horario_atendimento'],
        $dados['endereco'],
        $dados['estado'],
        $dados['municipio'],
        $dados['email_contratual'],
        $dados['email_marcacao_exames'],
        $dados['forma_pagamento']
    ]);
}

// Atualizar clínica
function atualizarClinica($pdo, $id, $dados) {
    $sql = 'UPDATE clinicas SET cnpj=?, nome=?, telefone=?, horario_atendimento=?, endereco=?, estado=?, municipio=?, email_contratual=?, email_marcacao_exames=?, forma_pagamento=? WHERE id=?';
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $dados['cnpj'],
        $dados['nome'],
        $dados['telefone'],
        $dados['horario_atendimento'],
        $dados['endereco'],
        $dados['estado'],
        $dados['municipio'],
        $dados['email_contratual'],
        $dados['email_marcacao_exames'],
        $dados['forma_pagamento'],
        $id
    ]);
}

// Excluir clínica
function excluirClinica($pdo, $id) {
    $stmt = $pdo->prepare('DELETE FROM clinicas WHERE id = ?');
    return $stmt->execute([$id]);
}

// Validação de CNPJ
function validarCNPJ($cnpj) {
    $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
    if (strlen($cnpj) != 14) return false;
    if (preg_match('/(\d)\1{13}/', $cnpj)) return false;
    for ($t = 12; $t < 14; $t++) {
        $d = 0;
        $c = 0;
        for ($m = $t - 7, $i = 0; $i < $t; $i++) {
            $d += $cnpj[$i] * $m--;
            if ($m < 2) $m = 9;
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cnpj[$t] != $d) return false;
    }
    return true;
}

// Listar estados
function listarEstados($pdo) {
    $stmt = $pdo->query('SELECT * FROM estado WHERE excluido = "F" ORDER BY estado');
    return $stmt->fetchAll();
}

// Buscar municípios por codestado
function buscarMunicipiosPorEstado($pdo, $codestado) {
    $stmt = $pdo->prepare('SELECT * FROM municipio WHERE codestado = ? AND excluido = "F" ORDER BY nome');
    $stmt->execute([$codestado]);
    return $stmt->fetchAll();
}

// Buscar estado por sigla
function buscarEstadoPorSigla($pdo, $sigla) {
    $stmt = $pdo->prepare('SELECT * FROM estado WHERE sigla = ? AND excluido = "F"');
    $stmt->execute([$sigla]);
    return $stmt->fetch();
}

// Buscar município por nome e codestado
function buscarMunicipioPorNome($pdo, $nome, $codestado) {
    $stmt = $pdo->prepare('SELECT * FROM municipio WHERE nome = ? AND codestado = ? AND excluido = "F"');
    $stmt->execute([$nome, $codestado]);
    return $stmt->fetch();
}

// Listar exames clínicos
function listarExamesClinicos($pdo) {
    $stmt = $pdo->query('SELECT * FROM exameclinico WHERE excluido = "F" ORDER BY exameclinico');
    return $stmt->fetchAll();
}

// Listar exames complementares
function listarExamesComplementares($pdo) {
    $stmt = $pdo->query('SELECT * FROM examecomplementar WHERE excluido = "F" ORDER BY examecomplementar');
    return $stmt->fetchAll();
}

// Buscar exames clínicos de uma clínica
function buscarExamesClinicosClinica($pdo, $clinica_id) {
    $stmt = $pdo->prepare('
        SELECT ec.*, cec.id as assoc_id, cec.valor
        FROM exameclinico ec
        INNER JOIN clinica_exameclinico cec ON ec.codexameclinico = cec.codexameclinico
        WHERE cec.clinica_id = ? AND ec.excluido = "F" AND cec.oculto = "F"
        ORDER BY ec.exameclinico
    ');
    $stmt->execute([$clinica_id]);
    return $stmt->fetchAll();
}

// Buscar exames complementares de uma clínica
function buscarExamesComplementaresClinica($pdo, $clinica_id) {
    $stmt = $pdo->prepare('
        SELECT ec.*, cec.id as assoc_id, cec.valor
        FROM examecomplementar ec
        INNER JOIN clinica_examecomplementar cec ON ec.codexamecomplementar = cec.codexamecomplementar
        WHERE cec.clinica_id = ? AND ec.excluido = "F" AND cec.oculto = "F"
        ORDER BY ec.examecomplementar
    ');
    $stmt->execute([$clinica_id]);
    return $stmt->fetchAll();
}

// Associar exames clínicos a uma clínica (NÃO remove todos, só adiciona/oculta)
function associarExamesClinicos($pdo, $clinica_id, $exames_clinicos) {
    // Buscar exames já associados
    $stmt = $pdo->prepare('SELECT codexameclinico, id FROM clinica_exameclinico WHERE clinica_id = ? AND oculto = "F"');
    $stmt->execute([$clinica_id]);
    $associados = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // codexameclinico => id
    $novos = $exames_clinicos;
    $atuais = array_keys($associados);
    // Exames a adicionar
    $adicionar = array_diff($novos, $atuais);
    // Exames a ocultar
    $ocultar = array_diff($atuais, $novos);
    // Adicionar novos
    foreach ($adicionar as $codexameclinico) {
        // Verifica se já existe (oculto)
        $stmt2 = $pdo->prepare('SELECT id FROM clinica_exameclinico WHERE clinica_id = ? AND codexameclinico = ?');
        $stmt2->execute([$clinica_id, $codexameclinico]);
        $row = $stmt2->fetch();
        if ($row) {
            $stmt3 = $pdo->prepare('UPDATE clinica_exameclinico SET oculto = "F" WHERE id = ?');
            $stmt3->execute([$row['id']]);
        } else {
            $stmt3 = $pdo->prepare('INSERT INTO clinica_exameclinico (clinica_id, codexameclinico, valor, oculto) VALUES (?, ?, 0.00, "F")');
            $stmt3->execute([$clinica_id, $codexameclinico]);
        }
    }
    // Ocultar os removidos
    foreach ($ocultar as $codexameclinico) {
        $id = $associados[$codexameclinico];
        $stmt2 = $pdo->prepare('UPDATE clinica_exameclinico SET oculto = "T" WHERE id = ?');
        $stmt2->execute([$id]);
    }
    return true;
}

// Associar exames complementares a uma clínica (NÃO remove todos, só adiciona/oculta)
function associarExamesComplementares($pdo, $clinica_id, $exames_complementares) {
    $stmt = $pdo->prepare('SELECT codexamecomplementar, id FROM clinica_examecomplementar WHERE clinica_id = ? AND oculto = "F"');
    $stmt->execute([$clinica_id]);
    $associados = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // codexamecomplementar => id
    $novos = $exames_complementares;
    $atuais = array_keys($associados);
    $adicionar = array_diff($novos, $atuais);
    $ocultar = array_diff($atuais, $novos);
    foreach ($adicionar as $codexamecomplementar) {
        $stmt2 = $pdo->prepare('SELECT id FROM clinica_examecomplementar WHERE clinica_id = ? AND codexamecomplementar = ?');
        $stmt2->execute([$clinica_id, $codexamecomplementar]);
        $row = $stmt2->fetch();
        if ($row) {
            $stmt3 = $pdo->prepare('UPDATE clinica_examecomplementar SET oculto = "F" WHERE id = ?');
            $stmt3->execute([$row['id']]);
        } else {
            $stmt3 = $pdo->prepare('INSERT INTO clinica_examecomplementar (clinica_id, codexamecomplementar, valor, oculto) VALUES (?, ?, 0.00, "F")');
            $stmt3->execute([$clinica_id, $codexamecomplementar]);
        }
    }
    foreach ($ocultar as $codexamecomplementar) {
        $id = $associados[$codexamecomplementar];
        $stmt2 = $pdo->prepare('UPDATE clinica_examecomplementar SET oculto = "T" WHERE id = ?');
        $stmt2->execute([$id]);
    }
    return true;
}

// Buscar exames clínicos NÃO associados à clínica (ou associados e ocultos)
function buscarExamesClinicosDisponiveis($pdo, $clinica_id) {
    $sql = 'SELECT ec.* FROM exameclinico ec
            WHERE ec.excluido = "F" AND ec.codexameclinico NOT IN (
                SELECT codexameclinico FROM clinica_exameclinico WHERE clinica_id = ? AND oculto = "F"
            )
            ORDER BY ec.exameclinico';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$clinica_id]);
    return $stmt->fetchAll();
}

// Buscar exames complementares NÃO associados à clínica (ou associados e ocultos)
function buscarExamesComplementaresDisponiveis($pdo, $clinica_id) {
    $sql = 'SELECT ec.* FROM examecomplementar ec
            WHERE ec.excluido = "F" AND ec.codexamecomplementar NOT IN (
                SELECT codexamecomplementar FROM clinica_examecomplementar WHERE clinica_id = ? AND oculto = "F"
            )
            ORDER BY ec.examecomplementar';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$clinica_id]);
    return $stmt->fetchAll();
}

// Adicionar exames clínicos à clínica (reativa se já existe com oculto = 'T', senão insere)
function adicionarExamesClinicosClinica($pdo, $clinica_id, $exames) {
    foreach ($exames as $codexameclinico) {
        // Verifica se já existe (oculto)
        $stmt = $pdo->prepare('SELECT id FROM clinica_exameclinico WHERE clinica_id = ? AND codexameclinico = ?');
        $stmt->execute([$clinica_id, $codexameclinico]);
        $row = $stmt->fetch();
        if ($row) {
            $stmt2 = $pdo->prepare('UPDATE clinica_exameclinico SET oculto = "F" WHERE id = ?');
            $stmt2->execute([$row['id']]);
        } else {
            $stmt2 = $pdo->prepare('INSERT INTO clinica_exameclinico (clinica_id, codexameclinico, valor, oculto) VALUES (?, ?, 0.00, "F")');
            $stmt2->execute([$clinica_id, $codexameclinico]);
        }
    }
}

// Adicionar exames complementares à clínica (reativa se já existe com oculto = 'T', senão insere)
function adicionarExamesComplementaresClinica($pdo, $clinica_id, $exames) {
    foreach ($exames as $codexamecomplementar) {
        $stmt = $pdo->prepare('SELECT id FROM clinica_examecomplementar WHERE clinica_id = ? AND codexamecomplementar = ?');
        $stmt->execute([$clinica_id, $codexamecomplementar]);
        $row = $stmt->fetch();
        if ($row) {
            $stmt2 = $pdo->prepare('UPDATE clinica_examecomplementar SET oculto = "F" WHERE id = ?');
            $stmt2->execute([$row['id']]);
        } else {
            $stmt2 = $pdo->prepare('INSERT INTO clinica_examecomplementar (clinica_id, codexamecomplementar, valor, oculto) VALUES (?, ?, 0.00, "F")');
            $stmt2->execute([$clinica_id, $codexamecomplementar]);
        }
    }
}

// Marcar exame clínico como oculto (exclusão lógica)
function ocultarExameClinico($pdo, $id) {
    $stmt = $pdo->prepare('UPDATE clinica_exameclinico SET oculto = "T" WHERE id = ?');
    return $stmt->execute([$id]);
}

// Marcar exame complementar como oculto (exclusão lógica)
function ocultarExameComplementar($pdo, $id) {
    $stmt = $pdo->prepare('UPDATE clinica_examecomplementar SET oculto = "T" WHERE id = ?');
    return $stmt->execute([$id]);
}

// Ocultar clínica (exclusão lógica)
function ocultarClinica($pdo, $id) {
    $stmt = $pdo->prepare('UPDATE clinicas SET oculto = "T" WHERE id = ?');
    return $stmt->execute([$id]);
}

// Listar clínicas ocultas
function listarClinicasOcultas($pdo) {
    $stmt = $pdo->query('SELECT * FROM clinicas WHERE oculto = "T"');
    return $stmt->fetchAll();
}

// Reativar clínica
function reativarClinica($pdo, $id) {
    $stmt = $pdo->prepare('UPDATE clinicas SET oculto = "F" WHERE id = ?');
    return $stmt->execute([$id]);
}

// Atualizar valor de exame clínico ou complementar
function atualizarValorExame($pdo, $id, $tipo, $valor) {
    if ($tipo === 'clinico') {
        $sql = 'UPDATE clinica_exameclinico SET valor = ? WHERE id = ?';
    } elseif ($tipo === 'complementar') {
        $sql = 'UPDATE clinica_examecomplementar SET valor = ? WHERE id = ?';
    } else {
        return false;
    }
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$valor, $id]);
}

// Formata CNPJ para 00.000.000/0000-00
function formatarCNPJ($cnpj) {
    $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
    if (strlen($cnpj) != 14) return $cnpj;
    return substr($cnpj, 0, 2) . '.' . substr($cnpj, 2, 3) . '.' . substr($cnpj, 5, 3) . '/' . substr($cnpj, 8, 4) . '-' . substr($cnpj, 12, 2);
}

// Formata telefone para (99) 99999-9999 ou (99) 9999-9999
function formatarTelefone($telefone) {
    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    if (strlen($telefone) == 11) {
        return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 5) . '-' . substr($telefone, 7, 4);
    } elseif (strlen($telefone) == 10) {
        return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 4) . '-' . substr($telefone, 6, 4);
    }
    return $telefone;
} 
<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');

$jwt_secret = 'FlexEAD';

// Verificação do JWT
$headers = getallheaders();
$auth = isset($headers['Authorization']) ? $headers['Authorization'] : (isset($headers['authorization']) ? $headers['authorization'] : null);
$jwt = null;
if ($auth && preg_match('/Bearer\s+(.*)$/i', $auth, $matches)) {
    $jwt = $matches[1];
} elseif (isset($_GET['jwt'])) {
    $jwt = $_GET['jwt'];
}
if (!$jwt) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Token JWT ausente']);
    exit;
}
try {
    $payload = JWT::decode($jwt, new Key($jwt_secret, 'HS256'));
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Token JWT inválido: ' . $e->getMessage()]);
    exit;
}


try {
    $stmt = $pdo->query('SELECT * FROM clinicas');
    $clinicas = $stmt->fetchAll();
    foreach ($clinicas as &$clinica) {
        // Buscar exames clínicos associados (apenas nome e valor)
        $stmtEc = $pdo->prepare('SELECT ec.exameclinico, cec.valor FROM exameclinico ec INNER JOIN clinica_exameclinico cec ON ec.codexameclinico = cec.codexameclinico WHERE cec.clinica_id = ? AND cec.oculto = "F"');
        $stmtEc->execute([$clinica['id']]);
        $clinica['exames_clinicos'] = $stmtEc->fetchAll();
        // Buscar exames complementares associados (apenas nome e valor)
        $stmtEcomp = $pdo->prepare('SELECT ec.examecomplementar, cec.valor FROM examecomplementar ec INNER JOIN clinica_examecomplementar cec ON ec.codexamecomplementar = cec.codexamecomplementar WHERE cec.clinica_id = ? AND cec.oculto = "F"');
        $stmtEcomp->execute([$clinica['id']]);
        $clinica['exames_complementares'] = $stmtEcomp->fetchAll();
    }
    echo json_encode([
        'success' => true,
        'clinicas' => $clinicas
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 
<?php
// Script para gerar tokens JWT válidos para o resource/view.php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
require_once(__DIR__ . '/../vendor/autoload.php');
use Firebase\JWT\JWT;

$jwt_secret = 'FlexEAD';

// Parâmetros opcionais via GET
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 2;
$exp_min = isset($_GET['exp']) ? intval($_GET['exp']) : 60; // minutos

$payload = [
    'user_id' => $user_id,
    'exp' => time() + ($exp_min * 60)
];

$jwt = JWT::encode($payload, $jwt_secret, 'HS256');

header('Content-Type: text/html; charset=utf-8');
echo "<strong>Token JWT gerado:</strong><br>";
echo "<code>$jwt</code><br><br>";
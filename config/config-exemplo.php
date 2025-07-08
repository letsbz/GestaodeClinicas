<?php
// config.php na raiz

$host = ' ';
$db   = ' ';
$user = ' ';
$pass = ''; // Altere conforme sua senha
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('Erro na conexão com o banco de dados: ' . $e->getMessage());
}

// Configurações de cores do sistema
$cores = [
    'primaria' => '#011c3b', // Azul moderno
    'secundaria' => '#3a516b', // Cinza escuro
    'accento_claro' => '#7ab8ff', // Azul claro
    'accento_escuro' => '#414141', // Azul escuro
    'branco' => '#ffffff',
    'cinza_claro' => '#f5f6fa',
    'cinza_medio' => '#e0e0e0',
    'cinza_escuro' => '#424242',
    'azul-claro' => '#0a6da9', // Azul claro para detalhes
    'erro' => '#a3120f', // Vermelho suave
    'sucesso' => '#43a047', // Verde
    'aviso' => '#ffc82d', // Laranja
]
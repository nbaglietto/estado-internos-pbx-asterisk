<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/lib/AsteriskSimple.php';

header('Content-Type: application/json; charset=utf-8');

// Obtener todos los datos de la API de Isabel
$extensionesPorArea = AsteriskSimple::obtenerExtensionesPorArea();

if (isset($extensionesPorArea['error'])) {
    echo json_encode(['error' => $extensionesPorArea['error']]);
    exit;
}

// Devolver el resultado final (ya procesado por AsteriskSimple)
echo json_encode($extensionesPorArea);

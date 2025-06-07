<?php
require_once __DIR__ . '/vendor/autoload.php';

// Verifica si la clase TCPDF está cargada correctamente
if (class_exists('TCPDF\TCPDF')) {
    echo "TCPDF cargado correctamente.";
} else {
    echo "TCPDF NO está cargado.";
}

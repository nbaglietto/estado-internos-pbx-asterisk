<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../app/config/config.php';     // Configuración y .env
require_once __DIR__ . '/../app/lib/AsteriskSimple.php'; // Clase de conexión

header('Content-Type: application/json; charset=utf-8');

class Internos {
    // Consulta la API Elastix para obtener estados actuales de las extensiones
    public static function obtenerEstadosExtensiones() {
        $apiUrl = 'https://192.168.1.251/api/asterisk.php';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Ajustar según cert. SSL
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            error_log('Curl error: ' . curl_error($ch));
            curl_close($ch);
            return [];
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("API Elastix respondió con código HTTP $httpCode");
            return [];
        }

        $json = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Error decodificando JSON de API Elastix: " . json_last_error_msg());
            return [];
        }

        return is_array($json) ? $json : [];
    }

    // Obtiene internos con área y estado
    public static function obtenerInternosConEstado() {
        $areas = AsteriskSimple::obtenerExtensionesPorArea();
        $estados = self::obtenerEstadosExtensiones();

        // Recorrer áreas y agregar estado a cada interno
        foreach ($areas as &$area) {
            foreach ($area['internos'] as &$interno) {
                $ext = $interno['extension'];
                // Si estado existe en API, asignar, si no, 'Desconocido'
                $interno['estado'] = isset($estados[$ext]) ? $estados[$ext] : '❔ Desconocido';
            }
        }

        return $areas;
    }
}

// Ejecutar y mostrar resultado en JSON
$resultado = Internos::obtenerInternosConEstado();
echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

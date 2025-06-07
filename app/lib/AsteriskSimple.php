<?php
class AsteriskSimple {
    /**
     * Obtiene extensiones agrupadas por área desde la API de Isabel
     */
    public static function obtenerExtensionesPorArea() {
        $apiUrl = 'https://192.168.1.251/api/estado_extensiones.php';
        
        // Configurar contexto para la solicitud
        $context = stream_context_create([
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ],
            "http" => [
                "timeout" => 3 // Timeout de 3 segundos
            ]
        ]);

        try {
            // Obtener datos de la API
            $jsonData = @file_get_contents($apiUrl, false, $context);

            if ($jsonData === false) {
                throw new Exception("No se pudo conectar al servidor Isabel PBX");
            }

            $extensionesData = json_decode($jsonData, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Respuesta JSON inválida del servidor Isabel PBX");
            }

            // Procesar los datos para agrupar por área
            $resultado = [];
            foreach ($extensionesData as $ext => $datos) {
                // Omitir extensiones no registradas
                if (strpos($datos['estado'], 'No registrada') !== false || strpos($datos['estado'], '🔴') !== false) {
                    continue;
                }

                $area = self::normalizarArea($datos['accountcode'] ?? null);
                if ($area === null) continue;

                if (!isset($resultado[$area])) {
                    $resultado[$area] = [
                        'area' => $area,
                        'internos' => []
                    ];
                }

                // Determinar estado y clase CSS
                $estado = $datos['estado'];
                $claseEstado = 'estado-desconocido';
                $iconoEstado = 'fa-question-circle';

                if (strpos($estado, '☎️ Disponible') !== false || strpos($estado, '🟢') !== false) {
                    $claseEstado = 'estado-verde';
                    $iconoEstado = 'fa-phone';
                } elseif (strpos($estado, '📞') !== false || strpos($estado, 'Ocupada') !== false || strpos($estado, 'hablando') !== false) {
                    $claseEstado = 'estado-ocupado';
                    $iconoEstado = 'fa-phone';
                } elseif (strpos($estado, '🔔') !== false || strpos($estado, 'Llamando') !== false) {
                    $claseEstado = 'estado-llamando';
                    $iconoEstado = 'fa-bell';
                }

                $resultado[$area]['internos'][] = [
                    'extension' => $datos['extension'] ?? $ext,
                    'nombre' => $datos['nombre'] ?? 'Ext. ' . $ext,
                    'estado' => $estado,
                    'estado_clase' => $claseEstado,
                    'estado_icono' => $iconoEstado
                ];
            }

            // Ordenar áreas alfabéticamente
            uksort($resultado, function($a, $b) {
                return strcasecmp($a, $b);
            });

            $resultado = array_values($resultado);

            // Agregar manualmente las extensiones especiales
            self::agregarExtensionesManuales($resultado);

            return $resultado;

        } catch (Exception $e) {
            error_log("Error obteniendo extensiones: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Agrega extensiones manuales (611 y 645)
     */
    private static function agregarExtensionesManuales(&$resultado) {
        // Agregar a Sistemas
        $areaSistemas = null;
        foreach ($resultado as &$area) {
            if (strcasecmp($area['area'], 'Sistemas') === 0) {
                $areaSistemas = &$area;
                break;
            }
        }

        if ($areaSistemas === null) {
            $resultado[] = [
                'area' => 'Sistemas',
                'internos' => []
            ];
            $areaSistemas = &$resultado[count($resultado)-1];
        }

        // Verificar si ya existe la extensión 611
        $existe611 = false;
        foreach ($areaSistemas['internos'] as $interno) {
            if ($interno['extension'] === '611') {
                $existe611 = true;
                break;
            }
        }

        if (!$existe611) {
            array_unshift($areaSistemas['internos'], [
                'extension' => '611',
                'nombre' => 'Soporte TI',
                'estado' => '☎️ Disponible',
                'estado_clase' => 'estado-verde',
                'estado_icono' => 'fa-phone'
            ]);
        }

        // Agregar a Logistica
        $areaLogistica = null;
        foreach ($resultado as &$area) {
            if (strcasecmp($area['area'], 'Logistica') === 0) {
                $areaLogistica = &$area;
                break;
            }
        }

        if ($areaLogistica === null) {
            $resultado[] = [
                'area' => 'Logistica',
                'internos' => []
            ];
            $areaLogistica = &$resultado[count($resultado)-1];
        }

        // Verificar si ya existe la extensión 645
        $existe645 = false;
        foreach ($areaLogistica['internos'] as $interno) {
            if ($interno['extension'] === '645') {
                $existe645 = true;
                break;
            }
        }

        if (!$existe645) {
            array_unshift($areaLogistica['internos'], [
                'extension' => '645',
                'nombre' => 'Logistica',
                'estado' => '☎️ Disponible',
                'estado_clase' => 'estado-verde',
                'estado_icono' => 'fa-phone'
            ]);
        }
    }

    /**
     * Normaliza el nombre del área
     */
    private static function normalizarArea($area) {
        $area = trim($area);
        if (empty($area) || strtolower($area) === 'none' || strtolower($area) === 'general') {
            return null;
        }
        return mb_convert_case($area, MB_CASE_TITLE, 'UTF-8');
    }
}

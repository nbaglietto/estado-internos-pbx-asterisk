<?php
// generar_pdf_test.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';
use TCPDF;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$input = file_get_contents('php://input');
$areasData = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo "Error: Datos JSON inválidos.";
    exit;
}

if (!is_array($areasData) || empty($areasData)) {
    http_response_code(400);
    echo "Error: Datos de áreas ausentes o incorrectos.";
    exit;
}

// Ordenar áreas alfabéticamente
usort($areasData, function($a, $b) {
    return strcasecmp($a['area'], $b['area']);
});

// Filtrar para eliminar la columna General
$areasData = array_filter($areasData, function($area) {
    return strcasecmp($area['area'], 'General') !== 0;
});

// Agregar internos manuales a sus áreas correspondientes
foreach ($areasData as &$area) {
    if (strcasecmp($area['area'], 'Sistemas') === 0) {
        $area['internos'][] = ['extension' => '611', 'nombre' => 'Sistemas'];
    }
    if (strcasecmp($area['area'], 'Logistica') === 0) {
        $area['internos'][] = ['extension' => '645', 'nombre' => 'Logistica'];
    }
    
    // Ordenar internos por extensión
    usort($area['internos'], function($a, $b) {
        return strcmp($a['extension'], $b['extension']);
    });
}
unset($area); // Romper la referencia

class MYPDF extends TCPDF {
    public function Header() {
        // Línea superior
        $this->Line(10, 15, $this->GetPageWidth() - 10, 15);
        
        // Título principal
        $this->SetY(18);
        $this->SetFont('helvetica', 'B', 14);
        $this->Cell(120, 8, 'Directorio Telefónico de Internos EMPDDH', 0, 0, 'L');
        $this->Cell(0, 8, 'URGENCIAS 923', 0, 1, 'R');
        // Fecha de generación
        $this->SetFont('helvetica', '', 8);
        $this->Cell(0, 0, 'Generado el: ' . date('d/m/Y H:i:s'), 0, 1, 'R');
        
        // Línea separadora
        $this->Line(10, 30, $this->GetPageWidth() - 10, 30);
    }

    public function Footer() {
        // Ajustar posición del texto de urgencias
        $this->SetY(-12); // Subido de -15 a -12
        $this->SetFont('helvetica', 'B', 14);
       // $this->Cell(0, 5, 'URGENCIAS 923', 0, 1, 'L'); // Alineado a la izquierda
        
        // Número de página
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 5, 'Página '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, 0, 'R'); // Alineado a la derecha
    }
}

$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('EMPDDH');
$pdf->SetTitle('Directorio Telefónico de Internos');
$pdf->SetMargins(8, 35, 8, 15); // Margen inferior reducido
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(8); // Reducido para ganar espacio
$pdf->SetAutoPageBreak(FALSE);
$pdf->AddPage();

// Configuración de columnas
$num_columns = 5;
$margin_h = 8;
$column_spacing = 3;
$page_width = $pdf->getPageWidth() - ($margin_h * 2);
$column_width = ($page_width - (($num_columns - 1) * $column_spacing)) / $num_columns;
$ext_fixed_width = 15;
$name_remaining_width = $column_width - $ext_fixed_width;

// Alturas de celdas (optimizadas)
$area_header_height = 5; // Reducido de 6 a 5
$interno_row_height = 4.5; // Reducido de 5 a 4.5
$area_bottom_space = 1; // Reducido significativamente

// Posiciones iniciales
$start_y = 32;
$column_y = array_fill(0, $num_columns, $start_y);
$column_x_positions = [];

for ($c = 0; $c < $num_columns; $c++) {
    $column_x_positions[$c] = $margin_h + ($c * ($column_width + $column_spacing));
}

// Primero calcular la altura total necesaria
$total_height_needed = 0;
foreach ($areasData as $area) {
    if (isset($area['internos']) && is_array($area['internos'])) {
        $total_height_needed += calculateAreaHeight($area['internos'], $area_header_height, $interno_row_height, $area_bottom_space);
    }
}

// Función para calcular altura de área
function calculateAreaHeight($internos, $header_height, $row_height, $bottom_space) {
    return $header_height + (count($internos) * $row_height) + $bottom_space;
}

// Distribuir áreas de manera más equilibrada
$areas_by_height = [];
foreach ($areasData as $area) {
    if (isset($area['internos']) && is_array($area['internos'])) {
        $height = calculateAreaHeight($area['internos'], $area_header_height, $interno_row_height, $area_bottom_space);
        $areas_by_height[] = [
            'data' => $area,
            'height' => $height
        ];
    }
}

// Ordenar áreas por altura (de mayor a menor)
usort($areas_by_height, function($a, $b) {
    return $b['height'] - $a['height'];
});

// Distribución inteligente en columnas
foreach ($areas_by_height as $area_item) {
    $area = $area_item['data'];
    $area_height = $area_item['height'];
    
    // Encontrar la columna con menor altura
    $target_column = array_search(min($column_y), $column_y);
    
    // Posicionar en la columna seleccionada
    $pdf->SetY($column_y[$target_column]);
    $pdf->SetX($column_x_positions[$target_column]);

    // Cabecera del área
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetFillColor(0, 0, 0);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell($column_width, $area_header_height, $area['area'], 1, 1, 'L', 1);
    
    $column_y[$target_column] = $pdf->GetY();
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);

    // Dibujar internos
    foreach ($area['internos'] as $interno) {
        $pdf->SetX($column_x_positions[$target_column]);
        $pdf->Cell($ext_fixed_width, $interno_row_height, $interno['extension'], 1, 0, 'C');
        $pdf->Cell($name_remaining_width, $interno_row_height, $interno['nombre'], 1, 1, 'L');
        $column_y[$target_column] = $pdf->GetY();
    }

    $column_y[$target_column] += $area_bottom_space;
}

// Generar nombre de archivo
$filename = "directorio_telefonico_" . date('d-m-Y') . ".pdf";

if (!headers_sent()) {
    header('Content-Type: application/pdf');
    header("Content-Disposition: attachment; filename=\"{$filename}\"");
}

$pdf->Output($filename, 'D');

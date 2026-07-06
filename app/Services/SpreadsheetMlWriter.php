<?php

namespace App\Services;

/**
 * Generador de archivos Excel en formato SpreadsheetML (XML).
 *
 * Produce un archivo .xls compatible con Microsoft Excel, LibreOffice
 * y Google Sheets sin necesidad de ninguna librería externa.
 *
 * Uso:
 *   $writer = new SpreadsheetMlWriter();
 *   $writer->setTitle('Mi Hoja');
 *   $writer->addRow(['Col A', 'Col B'], isHeader: true);
 *   $writer->addRow(['Dato 1', 'Dato 2']);
 *   return $writer->download('reporte.xls');
 */
class SpreadsheetMlWriter
{
    protected string $title = 'Hoja1';

    /** @var list<array{cells: list<array{value: string, type: string, bold: bool, bg: string, wrap: bool}>, height: int|null}> */
    protected array $rows = [];

    /** @var list<int|null> */
    protected array $colWidths = [];

    // Colores personalizables (default: simple sin color)
    protected string $headerBg     = '#FFFFFF';
    protected string $headerColor  = '#000000';
    protected string $titleBg      = '#FFFFFF';
    protected string $titleColor   = '#000000';
    protected string $altRowBg     = '#FFFFFF';

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function setHeaderStyle(string $bg, string $color): static
    {
        $this->headerBg = $bg;
        $this->headerColor = $color;
        return $this;
    }

    public function setTitleStyle(string $bg, string $color): static
    {
        $this->titleBg = $bg;
        $this->titleColor = $color;
        return $this;
    }

    public function setAltRowStyle(string $bg): static
    {
        $this->altRowBg = $bg;
        return $this;
    }

    /**
     * @param  list<string|int|float|null>  $cells
     * @param  list<int|null>  $widths   ancho de columna en puntos (null = auto)
     */
    public function addRow(
        array $cells,
        bool $isHeader = false,
        ?string $bgColor = null,
        bool $wrap = true,
        ?int $height = null,
        array $widths = [],
    ): static {
        $mapped = [];
        foreach ($cells as $i => $cell) {
            $value = $cell === null ? '' : (string) $cell;
            $type  = is_numeric($cell) && !str_starts_with((string) $cell, '0') ? 'Number' : 'String';

            $mapped[] = [
                'value' => $value,
                'type'  => $type,
                'bold'  => $isHeader,
                'bg'    => $bgColor ?? ($isHeader ? $this->headerBg : ''),
                'color' => $isHeader ? $this->headerColor : '#000000',
                'wrap'  => $wrap,
            ];

            // Registrar anchos de columna al pasar la primera fila con anchos definidos
            if (!empty($widths) && !isset($this->colWidths[$i])) {
                $this->colWidths[$i] = $widths[$i] ?? null;
            }
        }

        $this->rows[] = ['cells' => $mapped, 'height' => $height];

        return $this;
    }

    /**
     * Añade una fila de título (fusionada visualmente; celdas restantes vacías).
     */
    public function addMergedTitleRow(string $text, int $totalCols, string $bgColor = '#1F3864'): static
    {
        $cells = array_fill(0, $totalCols, '');
        $cells[0] = $text;

        $mapped = [];
        foreach ($cells as $i => $cell) {
            $mapped[] = [
                'value' => (string) $cell,
                'type'  => 'String',
                'bold'  => true,
                'bg'    => $bgColor,
                'color' => '#FFFFFF',
                'wrap'  => false,
                'merge' => $i === 0 ? $totalCols : 0,
            ];
        }

        $this->rows[] = ['cells' => $mapped, 'height' => 30];

        return $this;
    }

    /**
     * Genera el XML SpreadsheetML y lo devuelve como string.
     */
    public function render(): string
    {
        $title = $this->xmlEscape($this->title);
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
  <Title>{$title}</Title>
  <Author>Sistema de Repositorio UPTP</Author>
  <LastAuthor>Sistema de Repositorio UPTP</LastAuthor>
  <Created>2024-01-01T00:00:00Z</Created>
  <Version>16.00</Version>
 </DocumentProperties>
 <Styles>
  <Style ss:ID="Default">
   <Font ss:FontName="Calibri" ss:Size="10"/>
   <Alignment ss:WrapText="1" ss:Vertical="Top"/>
  </Style>
    <Style ss:ID="sHeader">
     <Font ss:FontName="Calibri" ss:Size="10" ss:Bold="1"/>
     <Alignment ss:WrapText="1" ss:Vertical="Center" ss:Horizontal="Center"/>
     <Borders>
      <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#000000"/>
      <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CCCCCC"/>
     </Borders>
    </Style>
    <Style ss:ID="sTitle">
     <Font ss:FontName="Calibri" ss:Size="12" ss:Bold="1"/>
     <Alignment ss:WrapText="0" ss:Vertical="Center" ss:Horizontal="Center"/>
    </Style>
   <Style ss:ID="sData">
    <Font ss:FontName="Calibri" ss:Size="10"/>
    <Alignment ss:WrapText="1" ss:Vertical="Top"/>
    <Borders>
     <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CCCCCC"/>
     <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CCCCCC"/>
    </Borders>
   </Style>
   <Style ss:ID="sDataAlt">
    <Font ss:FontName="Calibri" ss:Size="10"/>
    <Interior ss:Color="{$this->altRowBg}" ss:Pattern="Solid"/>
    <Alignment ss:WrapText="1" ss:Vertical="Top"/>
    <Borders>
     <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CCCCCC"/>
     <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CCCCCC"/>
    </Borders>
   </Style>
 </Styles>
 <Worksheet ss:Name="{$title}">
  <Table>

XML;

        // Column widths
        foreach ($this->colWidths as $w) {
            $w = $w ?? 80;
            $xml .= "   <Column ss:Width=\"{$w}\"/>\n";
        }

        $dataRowIndex = 0;
        foreach ($this->rows as $row) {
            $cells   = $row['cells'];
            $height  = $row['height'] ?? null;
            $rowAttr = $height ? " ss:Height=\"{$height}\"" : '';

            // Detectar si la fila es header o title
            $firstCell = $cells[0] ?? [];
            $isHeader  = ($firstCell['bold'] ?? false) && ($firstCell['bg'] ?? '') !== '';
            $isTitleRow = ($firstCell['merge'] ?? 0) > 1;

            if (!$isHeader && !$isTitleRow) {
                $dataRowIndex++;
            }

            $xml .= "   <Row{$rowAttr}>\n";

            foreach ($cells as $cell) {
                $styleId = 'sData';
                if ($isTitleRow) {
                    $styleId = 'sTitle';
                } elseif ($isHeader) {
                    $styleId = 'sHeader';
                } elseif ($dataRowIndex % 2 === 0) {
                    $styleId = 'sDataAlt';
                }

                $type  = $cell['type'] ?? 'String';
                $value = $this->xmlEscape($cell['value']);

                $mergeAttr = '';
                if (($cell['merge'] ?? 0) > 1) {
                    $span = (int) $cell['merge'];
                    $mergeAttr = " ss:MergeAcross=\"" . ($span - 1) . "\"";
                }

                if ($type === 'Number' && is_numeric($cell['value'])) {
                    $xml .= "    <Cell ss:StyleID=\"{$styleId}\"{$mergeAttr}><Data ss:Type=\"Number\">{$value}</Data></Cell>\n";
                } else {
                    $xml .= "    <Cell ss:StyleID=\"{$styleId}\"{$mergeAttr}><Data ss:Type=\"String\">{$value}</Data></Cell>\n";
                }
            }

            $xml .= "   </Row>\n";
        }

        $xml .= <<<XML
  </Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <Print>
    <FitWidth>1</FitWidth>
    <ValidPrinterInfo/>
    <HorizontalResolution>300</HorizontalResolution>
    <VerticalResolution>300</VerticalResolution>
   </Print>
   <FreezePanes/>
   <FrozenNoSplit/>
   <SplitHorizontal>2</SplitHorizontal>
   <TopRowBottomPane>2</TopRowBottomPane>
   <ActivePane>2</ActivePane>
  </WorksheetOptions>
 </Worksheet>
</Workbook>
XML;

        return $xml;
    }

    /**
     * Retorna una respuesta HTTP para descargar el archivo.
     */
    public function download(string $filename = 'reporte.xls'): \Illuminate\Http\Response
    {
        $content = $this->render();

        return response($content, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'max-age=0',
            'Pragma'              => 'public',
        ]);
    }

    protected function xmlEscape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}

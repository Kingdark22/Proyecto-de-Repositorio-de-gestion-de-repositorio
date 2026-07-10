<?php

namespace App\Services;

use setasign\Fpdi\Fpdi;

class WatermarkPdf extends Fpdi
{
    protected function _escape(string $s): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $s);
    }

    public function rotateText(float $x, float $y, string $text, float $angle): void
    {
        $angleRad = $angle * M_PI / 180;
        $cos = cos($angleRad);
        $sin = sin($angleRad);

        $this->_out(sprintf(
            'q BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET Q',
            $cos, $sin, -$sin, $cos,
            $x * $this->k, ($this->h - $y) * $this->k,
            $this->_escape($text)
        ));
    }
}

class PdfWatermarkService
{
    public function applyWatermark(string $path, string $watermarkText = 'COPIA NO AUTORIZADA - REPOSITORIO UPTP'): ?string
    {
        try {
            $pdf = new WatermarkPdf();
            $pageCount = $pdf->setSourceFile($path);

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $pdf->AddPage();
                $pdf->useTemplate($templateId, 0, 0, null, null, true);

                $pdf->SetFont('Arial', 'B', 45);
                $pdf->SetTextColor(210, 210, 210);

                $w = $pdf->GetPageWidth();
                $h = $pdf->GetPageHeight();

                $pdf->rotateText(25, $h - 30, $watermarkText, 45);

                $pdf->SetFont('Arial', '', 12);
                $pdf->SetTextColor(180, 180, 180);
                $pdf->rotateText($w - 170, 20, now()->format('d/m/Y H:i'), 0);
            }

            $tempPath = storage_path('app/temp_watermarked_' . uniqid() . '.pdf');
            $pdf->Output($tempPath, 'F');

            return $tempPath;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Error applying PDF watermark: ' . $e->getMessage());
            return null;
        }
    }
}

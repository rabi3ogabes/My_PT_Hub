<?php

namespace App\Domain\Payments\Support;

use Mpdf\Mpdf;

/** Renders Blade-produced HTML to PDF with mPDF (Arabic shaping + RTL via autoScriptToLang/autoLangToFont). */
final class Pdf
{
    public static function render(string $html, bool $rtl = false, string $title = ''): string
    {
        $tmp = storage_path('framework/cache/mpdf');
        if (! is_dir($tmp)) {
            @mkdir($tmp, 0775, true);
        }
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'tempDir' => $tmp,
            'margin_top' => 14,
            'margin_bottom' => 14,
            'margin_left' => 14,
            'margin_right' => 14,
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'default_font' => 'dejavusans',
        ]);
        $mpdf->SetDirectionality($rtl ? 'rtl' : 'ltr');
        if ($title !== '') {
            $mpdf->SetTitle($title);
        }
        $mpdf->WriteHTML($html);

        return $mpdf->Output('', 'S');
    }
}

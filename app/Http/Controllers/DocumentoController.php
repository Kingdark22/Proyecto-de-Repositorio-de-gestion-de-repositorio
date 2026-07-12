<?php

namespace App\Http\Controllers;

use App\Models\ProyectoDocumento;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class DocumentoController extends Controller
{
    public function view(int $id)
    {
        $doc = ProyectoDocumento::findOrFail($id);
        $path = $doc->pd_archivo_path;

        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $fullPath = Storage::disk('public')->path($path);
        $mime = Storage::disk('public')->mimeType($path);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $imgs = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];

        if (in_array($extension, $imgs, true)) {
            return $this->servirImagenConMarca($fullPath, $mime);
        }

        if ($extension === 'pdf') {
            return view('documentos.viewer', [
                'docUrl' => route('documentos.serve', ['path' => $path]),
                'doc' => $doc,
            ]);
        }

        return Storage::disk('public')->response($path);
    }

    protected function servirImagenConMarca(string $fullPath, string $mime)
    {
        if (!extension_loaded('gd')) {
            return Response::file($fullPath, ['Content-Type' => $mime]);
        }

        $img = $this->crearImagenDesdeArchivo($fullPath);
        if (!$img) {
            return Response::file($fullPath, ['Content-Type' => $mime]);
        }

        $marcaPath = public_path('imagenes/uptp-logo.png');
        if (file_exists($marcaPath)) {
            $marca = @imagecreatefrompng($marcaPath);
            if ($marca) {
                $mW = imagesx($marca);
                $mH = imagesy($marca);
                $iW = imagesx($img);
                $iH = imagesy($img);

                if ($mW > 0 && $mH > 0) {
                    $scale = min($iW * 0.5 / $mW, $iH * 0.5 / $mH, 1);
                    $nW = (int) ($mW * $scale);
                    $nH = (int) ($mH * $scale);
                    $marcaRedim = imagescale($marca, $nW, $nH);
                    if ($marcaRedim) {
                        $x = (int) (($iW - $nW) / 2);
                        $y = (int) (($iH - $nH) / 2);
                        imagecopy($img, $marcaRedim, 0, 0, 0, 0, $nW, $nH);
                        imagedestroy($marcaRedim);
                    }
                }
                imagedestroy($marca);
            }
        }

        ob_start();
        imagepng($img);
        $contenido = ob_get_clean();
        imagedestroy($img);

        return Response::make($contenido, 200, ['Content-Type' => 'image/png']);
    }

    protected function crearImagenDesdeArchivo(string $path)
    {
        $info = @getimagesize($path);
        if (!$info) return null;

        return match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_GIF => @imagecreatefromgif($path),
            IMAGETYPE_WEBP => @imagecreatefromwebp($path),
            IMAGETYPE_BMP => @imagecreatefrombmp($path),
            default => null,
        };
    }
}

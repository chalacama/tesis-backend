<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ImageProxyController extends Controller
{
    public function show(Request $request)
    {
        $request->validate([
            'url' => ['required', 'url'],
        ]);

        $url = $request->query('url');

        // (Opcional pero MUY recomendable) limitar hosts permitidos
        $allowedHosts = [
            'lh3.googleusercontent.com',
            'i.pinimg.com',
            'www.espam.edu.ec',
            'espam.edu.ec',
        ];

        $host = parse_url($url, PHP_URL_HOST);
        if (!in_array($host, $allowedHosts, true)) {
            abort(403, 'Host no permitido para el proxy de imágenes.');
        }

        $response = Http::withHeaders([
            'User-Agent' => 'DIGIMENTOR-CertificateProxy/1.0',
            'Accept' => 'image/*',
        ])->get($url);

        if (!$response->successful()) {
            abort(404, 'No se pudo obtener la imagen remota.');
        }

        $contentType = $response->header('Content-Type', 'image/jpeg');

        return response($response->body(), 200)
            ->header('Content-Type', $contentType)
            // por si en algún momento usas el proxy desde otro dominio
            ->header('Access-Control-Allow-Origin', '*');
    }
}

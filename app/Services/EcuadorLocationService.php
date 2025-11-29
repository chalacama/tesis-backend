<?php

namespace App\Services;

class EcuadorLocationService
{
    /**
     * Ruta relativa dentro de storage/app
     */
    private string $relativePath = 'json/provincias.json';

    /**
     * Obtiene la ruta absoluta al archivo.
     */
    private function getFullPath(): string
    {
        // Esto arma: storage_path('app/json/provincias.json')
        return storage_path('app/' . $this->relativePath);
    }

    /**
     * Carga el JSON y lanza error claro si algo falla.
     */
    private function loadData(): array
    {
        $path = $this->getFullPath();

        // 1) Verificar que el archivo exista físicamente
        if (!file_exists($path)) {
            throw new \RuntimeException(
                "No se encontró el archivo provincias.json en: {$path}"
            );
        }

        // 2) Leer el contenido
        $json = file_get_contents($path);

        // 3) Decodificar
        $data = json_decode($json, true);

        // 4) Verificar errores de JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException(
                'Error al decodificar provincias.json: ' . json_last_error_msg()
            );
        }

        return $data;
    }

    /**
     * Obtener todas las provincias.
     * Devuelve: [ { id, name }, ... ]
     */
    public function getProvinces(): array
    {
        $data = $this->loadData();
        $result = [];

        foreach ($data as $provinceId => $province) {
            $result[] = [
                'id'   => (string) $provinceId,
                'name' => $province['provincia'] ?? null,
            ];
        }

        return $result;
    }

    /**
     * Obtener los cantones de una provincia.
     * Devuelve: [ { id, name }, ... ]
     */
    public function getCantons(string $provinceId): array
    {
        $data = $this->loadData();

        if (!isset($data[$provinceId]['cantones'])) {
            return [];
        }

        $result = [];

        foreach ($data[$provinceId]['cantones'] as $cantonId => $canton) {
            $result[] = [
                'id'   => (string) $cantonId,
                'name' => $canton['canton'] ?? null,
            ];
        }

        return $result;
    }

    /**
     * Obtener las parroquias de un cantón de una provincia.
     * Devuelve: [ { id, name }, ... ]
     */
    public function getParishes(string $provinceId, string $cantonId): array
    {
        $data = $this->loadData();

        if (!isset($data[$provinceId]['cantones'][$cantonId]['parroquias'])) {
            return [];
        }

        $parroquias = $data[$provinceId]['cantones'][$cantonId]['parroquias'];

        $result = [];
        foreach ($parroquias as $parishId => $parishName) {
            $result[] = [
                'id'   => (string) $parishId,
                'name' => $parishName,
            ];
        }

        return $result;
    }

    /**
     * (Opcional) Obtener una provincia específica.
     */
    public function getProvince(string $provinceId): ?array
    {
        $data = $this->loadData();

        if (!isset($data[$provinceId])) {
            return null;
        }

        return [
            'id'       => (string) $provinceId,
            'name'     => $data[$provinceId]['provincia'] ?? null,
            'cantones' => $this->getCantons($provinceId),
        ];
    }
}

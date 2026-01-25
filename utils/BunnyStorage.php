<?php
/**
 * Clase BunnyStorage
 * Maneja la subida y eliminación de archivos en Bunny.net CDN
 */

class BunnyStorage {
    private $storageZone;
    private $apiKey;
    private $storageUrl;
    private $pullZoneUrl;

    public function __construct() {
        // Cargar configuración desde .env o usar valores por defecto
        $this->loadEnv();
        
        $this->storageZone = $_ENV['BUNNY_STORAGE_ZONE'] ?? 'hersil-shop';
        $this->apiKey = $_ENV['BUNNY_API_KEY'] ?? '';
        $this->storageUrl = $_ENV['BUNNY_STORAGE_URL'] ?? 'https://storage.bunnycdn.com';
        $this->pullZoneUrl = $_ENV['BUNNY_PULL_ZONE_URL'] ?? 'https://hersil-shop.b-cdn.net';
    }

    private function loadEnv() {
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                if (strpos($line, '=') === false) continue;
                list($key, $value) = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($value);
            }
        }
    }

    /**
     * Subir archivo a Bunny.net
     * @param string $localFilePath - Ruta del archivo local
     * @param string $remotePath - Ruta en Bunny (ej: "products/imagen.jpg")
     * @return array - ['success' => bool, 'url' => string, 'message' => string]
     */
    public function upload($localFilePath, $remotePath) {
        if (!file_exists($localFilePath)) {
            return [
                'success' => false,
                'url' => '',
                'message' => 'El archivo local no existe'
            ];
        }

        $url = $this->storageUrl . '/' . $this->storageZone . '/' . $remotePath;
        
        $fileContent = file_get_contents($localFilePath);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_PUT, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fileContent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'AccessKey: ' . $this->apiKey,
            'Content-Type: application/octet-stream'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($httpCode === 201 || $httpCode === 200) {
            return [
                'success' => true,
                'url' => $this->pullZoneUrl . '/' . $remotePath,
                'message' => 'Archivo subido correctamente'
            ];
        }

        return [
            'success' => false,
            'url' => '',
            'message' => 'Error al subir: ' . ($error ?: $response)
        ];
    }

    /**
     * Subir archivo desde $_FILES
     * @param array $file - $_FILES['campo']
     * @param string $folder - Carpeta destino (ej: "products", "categories")
     * @param string|null $customName - Nombre personalizado (sin extensión)
     * @return array
     */
    public function uploadFromForm($file, $folder = 'uploads', $customName = null) {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return [
                'success' => false,
                'url' => '',
                'message' => 'No se recibió un archivo válido'
            ];
        }

        // Obtener extensión
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Validar extensión
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($extension, $allowedExtensions)) {
            return [
                'success' => false,
                'url' => '',
                'message' => 'Extensión no permitida. Use: ' . implode(', ', $allowedExtensions)
            ];
        }

        // Generar nombre único si no se proporciona
        $fileName = $customName 
            ? $customName . '.' . $extension 
            : uniqid() . '_' . time() . '.' . $extension;

        // Ruta remota
        $remotePath = $folder . '/' . $fileName;

        return $this->upload($file['tmp_name'], $remotePath);
    }

    /**
     * Eliminar archivo de Bunny.net
     * @param string $remotePath - Ruta del archivo (ej: "products/imagen.jpg")
     * @return array
     */
    public function delete($remotePath) {
        // Limpiar la URL si viene completa
        if (strpos($remotePath, $this->pullZoneUrl) !== false) {
            $remotePath = str_replace($this->pullZoneUrl . '/', '', $remotePath);
        }

        $url = $this->storageUrl . '/' . $this->storageZone . '/' . $remotePath;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'AccessKey: ' . $this->apiKey
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 || $httpCode === 404) {
            return [
                'success' => true,
                'message' => 'Archivo eliminado correctamente'
            ];
        }

        return [
            'success' => false,
            'message' => 'Error al eliminar archivo'
        ];
    }

    /**
     * Obtener URL pública de un archivo
     * @param string $remotePath
     * @return string
     */
    public function getPublicUrl($remotePath) {
        return $this->pullZoneUrl . '/' . $remotePath;
    }

    /**
     * Listar archivos en una carpeta
     * @param string $folder
     * @return array
     */
    public function listFiles($folder = '') {
        $url = $this->storageUrl . '/' . $this->storageZone . '/' . $folder . '/';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'AccessKey: ' . $this->apiKey
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            return [
                'success' => true,
                'files' => json_decode($response, true)
            ];
        }

        return [
            'success' => false,
            'files' => []
        ];
    }
}
?>
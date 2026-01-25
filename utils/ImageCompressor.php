<?php
/**
 * Clase ImageCompressor
 * Comprime y optimiza imágenes antes de subirlas
 */

class ImageCompressor {
    private $maxWidth;
    private $maxHeight;
    private $quality;
    private $tempDir;

    public function __construct($maxWidth = 1200, $maxHeight = 1200, $quality = 80) {
        $this->maxWidth = $maxWidth;
        $this->maxHeight = $maxHeight;
        $this->quality = $quality;
        $this->tempDir = sys_get_temp_dir();
    }

    /**
     * Comprimir imagen desde $_FILES
     * @param array $file - $_FILES['campo']
     * @return array - ['success' => bool, 'path' => string, 'message' => string]
     */
    public function compressFromUpload($file) {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return [
                'success' => false,
                'path' => '',
                'message' => 'No se recibió un archivo válido'
            ];
        }

        return $this->compress($file['tmp_name'], $file['name']);
    }

    /**
     * Comprimir imagen desde ruta
     * @param string $sourcePath - Ruta del archivo original
     * @param string $originalName - Nombre original del archivo
     * @return array
     */
    public function compress($sourcePath, $originalName = '') {
        // Verificar que el archivo existe
        if (!file_exists($sourcePath)) {
            return [
                'success' => false,
                'path' => '',
                'message' => 'El archivo no existe'
            ];
        }

        // Obtener información de la imagen
        $imageInfo = @getimagesize($sourcePath);
        if (!$imageInfo) {
            return [
                'success' => false,
                'path' => '',
                'message' => 'El archivo no es una imagen válida'
            ];
        }

        $mimeType = $imageInfo['mime'];
        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];

        // Crear imagen desde el archivo original
        $sourceImage = $this->createImageFromFile($sourcePath, $mimeType);
        if (!$sourceImage) {
            return [
                'success' => false,
                'path' => '',
                'message' => 'No se pudo procesar la imagen. Formato no soportado.'
            ];
        }

        // Calcular nuevas dimensiones manteniendo proporción
        list($newWidth, $newHeight) = $this->calculateDimensions(
            $originalWidth, 
            $originalHeight, 
            $this->maxWidth, 
            $this->maxHeight
        );

        // Crear imagen redimensionada
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

        // Preservar transparencia para PNG y GIF
        if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
            imagealphablending($resizedImage, false);
            imagesavealpha($resizedImage, true);
            $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
            imagefilledrectangle($resizedImage, 0, 0, $newWidth, $newHeight, $transparent);
        } else {
            // Fondo blanco para JPEG
            $white = imagecolorallocate($resizedImage, 255, 255, 255);
            imagefilledrectangle($resizedImage, 0, 0, $newWidth, $newHeight, $white);
        }

        // Redimensionar con mejor calidad
        imagecopyresampled(
            $resizedImage, 
            $sourceImage, 
            0, 0, 0, 0, 
            $newWidth, 
            $newHeight, 
            $originalWidth, 
            $originalHeight
        );

        // Generar nombre temporal único
        $extension = $this->getExtensionFromMime($mimeType);
        $tempFileName = uniqid('compressed_') . '.' . $extension;
        $tempPath = $this->tempDir . DIRECTORY_SEPARATOR . $tempFileName;

        // Guardar imagen comprimida
        $saved = $this->saveImage($resizedImage, $tempPath, $mimeType);

        // Liberar memoria
        imagedestroy($sourceImage);
        imagedestroy($resizedImage);

        if ($saved) {
            $originalSize = filesize($sourcePath);
            $compressedSize = filesize($tempPath);
            $savings = round((1 - ($compressedSize / $originalSize)) * 100, 1);

            return [
                'success' => true,
                'path' => $tempPath,
                'original_size' => $originalSize,
                'compressed_size' => $compressedSize,
                'savings_percent' => $savings,
                'width' => $newWidth,
                'height' => $newHeight,
                'message' => "Imagen comprimida. Ahorro: {$savings}%"
            ];
        }

        return [
            'success' => false,
            'path' => '',
            'message' => 'Error al guardar la imagen comprimida'
        ];
    }

    /**
     * Comprimir y convertir a WebP
     * @param string $sourcePath
     * @param string $originalName
     * @return array
     */
    public function compressToWebP($sourcePath, $originalName = '') {
        if (!file_exists($sourcePath)) {
            return [
                'success' => false,
                'path' => '',
                'message' => 'El archivo no existe'
            ];
        }

        $imageInfo = @getimagesize($sourcePath);
        if (!$imageInfo) {
            return [
                'success' => false,
                'path' => '',
                'message' => 'El archivo no es una imagen válida'
            ];
        }

        $mimeType = $imageInfo['mime'];
        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];

        $sourceImage = $this->createImageFromFile($sourcePath, $mimeType);
        if (!$sourceImage) {
            return [
                'success' => false,
                'path' => '',
                'message' => 'No se pudo procesar la imagen'
            ];
        }

        list($newWidth, $newHeight) = $this->calculateDimensions(
            $originalWidth, 
            $originalHeight, 
            $this->maxWidth, 
            $this->maxHeight
        );

        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preservar transparencia
        imagealphablending($resizedImage, false);
        imagesavealpha($resizedImage, true);

        imagecopyresampled(
            $resizedImage, 
            $sourceImage, 
            0, 0, 0, 0, 
            $newWidth, 
            $newHeight, 
            $originalWidth, 
            $originalHeight
        );

        $tempFileName = uniqid('webp_') . '.webp';
        $tempPath = $this->tempDir . DIRECTORY_SEPARATOR . $tempFileName;

        $saved = imagewebp($resizedImage, $tempPath, $this->quality);

        imagedestroy($sourceImage);
        imagedestroy($resizedImage);

        if ($saved) {
            $originalSize = filesize($sourcePath);
            $compressedSize = filesize($tempPath);
            $savings = round((1 - ($compressedSize / $originalSize)) * 100, 1);

            return [
                'success' => true,
                'path' => $tempPath,
                'original_size' => $originalSize,
                'compressed_size' => $compressedSize,
                'savings_percent' => $savings,
                'width' => $newWidth,
                'height' => $newHeight,
                'format' => 'webp',
                'message' => "Convertido a WebP. Ahorro: {$savings}%"
            ];
        }

        return [
            'success' => false,
            'path' => '',
            'message' => 'Error al convertir a WebP'
        ];
    }

    /**
     * Crear imagen desde archivo según tipo MIME
     */
    private function createImageFromFile($path, $mimeType) {
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                return @imagecreatefromjpeg($path);
            case 'image/png':
                return @imagecreatefrompng($path);
            case 'image/gif':
                return @imagecreatefromgif($path);
            case 'image/webp':
                return @imagecreatefromwebp($path);
            default:
                return false;
        }
    }

    /**
     * Guardar imagen según tipo MIME
     */
    private function saveImage($image, $path, $mimeType) {
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                return imagejpeg($image, $path, $this->quality);
            case 'image/png':
                // PNG usa compresión 0-9
                $pngQuality = round((100 - $this->quality) / 10);
                return imagepng($image, $path, $pngQuality);
            case 'image/gif':
                return imagegif($image, $path);
            case 'image/webp':
                return imagewebp($image, $path, $this->quality);
            default:
                return imagejpeg($image, $path, $this->quality);
        }
    }

    /**
     * Calcular dimensiones manteniendo proporción
     */
    private function calculateDimensions($origWidth, $origHeight, $maxWidth, $maxHeight) {
        // Si la imagen es más pequeña que los máximos, no redimensionar
        if ($origWidth <= $maxWidth && $origHeight <= $maxHeight) {
            return [$origWidth, $origHeight];
        }

        $ratio = $origWidth / $origHeight;

        if ($maxWidth / $maxHeight > $ratio) {
            $newWidth = (int)($maxHeight * $ratio);
            $newHeight = $maxHeight;
        } else {
            $newWidth = $maxWidth;
            $newHeight = (int)($maxWidth / $ratio);
        }

        return [$newWidth, $newHeight];
    }

    /**
     * Obtener extensión desde tipo MIME
     */
    private function getExtensionFromMime($mimeType) {
        $map = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp'
        ];
        return $map[$mimeType] ?? 'jpg';
    }

    /**
     * Establecer calidad de compresión
     * @param int $quality - 1 a 100
     */
    public function setQuality($quality) {
        $this->quality = max(1, min(100, $quality));
        return $this;
    }

    /**
     * Establecer dimensiones máximas
     */
    public function setMaxDimensions($width, $height) {
        $this->maxWidth = $width;
        $this->maxHeight = $height;
        return $this;
    }

    /**
     * Limpiar archivo temporal
     */
    public function cleanup($path) {
        if (file_exists($path) && strpos($path, $this->tempDir) === 0) {
            @unlink($path);
        }
    }
}
?>
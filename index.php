<?php
/**
 * Archivo de entrada principal
 * Redirige todas las peticiones a la carpeta public
 */

// Redirigir al directorio public
$uri = $_SERVER['REQUEST_URI'];

// Si ya está en /public, dejar pasar
if (strpos($uri, '/public') === 0) {
    return false;
}

// Redirigir todo lo demás a public/index.php
require_once __DIR__ . '/public/index.php';
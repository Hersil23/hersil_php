<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();

echo "1. Inicio<br>";

require_once __DIR__ . '/config/config.php';
echo "2. Config OK<br>";

require_once __DIR__ . '/models/category.php';
echo "3. Model OK<br>";

require_once __DIR__ . '/utils/Security.php';
echo "4. Security OK<br>";

require_once __DIR__ . '/utils/BunnyStorage.php';
echo "5. BunnyStorage OK<br>";

require_once __DIR__ . '/utils/ImageCompressor.php';
echo "6. ImageCompressor OK<br>";

echo "7. Creando instancias...<br>";

$categoryModel = new Category();
echo "8. Category instance OK<br>";

$bunnyStorage = new BunnyStorage();
echo "9. BunnyStorage instance OK<br>";

$imageCompressor = new ImageCompressor(800, 800, 85);
echo "10. ImageCompressor instance OK<br>";

echo "<br>TODO FUNCIONA CORRECTAMENTE";
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "1. Inicio<br>";

echo "2. Cargando config...<br>";
require_once __DIR__ . '/config/config.php';
echo "3. Config OK<br>";

echo "4. Cargando model...<br>";
require_once __DIR__ . '/models/category.php';
echo "5. Model OK<br>";

echo "6. Cargando Security...<br>";
require_once __DIR__ . '/utils/Security.php';
echo "7. Security OK<br>";

echo "8. Cargando BunnyStorage...<br>";
require_once __DIR__ . '/utils/BunnyStorage.php';
echo "9. BunnyStorage OK<br>";

echo "10. Cargando ImageCompressor...<br>";
require_once __DIR__ . '/utils/ImageCompressor.php';
echo "11. ImageCompressor OK<br>";

echo "<br>TODOS LOS ARCHIVOS CARGARON CORRECTAMENTE";
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();

echo "1. Inicio<br>";

echo "2. Cargando CategoryController...<br>";
require_once __DIR__ . '/controllers/CategoryController.php';
echo "3. CategoryController cargado OK<br>";

echo "<br>TODO FUNCIONA";
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/product.php';

echo "Config OK<br>";

$productModel = new Product();
echo "Model OK<br>";

$products = $productModel->getAll();
echo "Products: " . count($products);
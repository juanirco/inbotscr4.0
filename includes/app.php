<?php

// CORREGIDO: Cargar dotenv PRIMERO antes que database
require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// AHORA cargar database (después de que las variables ENV estén disponibles)
require 'database.php';
<?php

/**
 * Hostinger: document root = public_html, Laravel vive en carpeta HERMANA (misma carpeta padre).
 *
 * Estructura esperada:
 *   domains/tu-dominio.com/
 *     sivso_copia/          ← proyecto completo (git clone, composer, .env)
 *     public_html/          ← solo lo público (este index, .htaccess, build, imágenes…)
 *
 * Si clonaste con otro nombre de carpeta, cambia $laravelFolder abajo.
 */

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$laravelFolder = 'sivso_copia';
$laravelRoot = dirname(__DIR__).DIRECTORY_SEPARATOR.$laravelFolder;

if (! is_dir($laravelRoot)) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    exit(
        "No encuentro Laravel en:\n{$laravelRoot}\n\n".
        "Edita \$laravelFolder en public_html/index.php para que coincida con el nombre de la carpeta del proyecto."
    );
}

if (file_exists($maintenance = $laravelRoot.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $laravelRoot.'/vendor/autoload.php';

/** @var Application $app */
$app = require_once $laravelRoot.'/bootstrap/app.php';

$app->handleRequest(Request::capture());

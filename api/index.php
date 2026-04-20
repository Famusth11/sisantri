<?php

declare(strict_types=1);

$publicIndex = __DIR__ . '/../public/index.php';
$publicPath = realpath(__DIR__ . '/../public') ?: __DIR__ . '/../public';

$_SERVER['SCRIPT_FILENAME'] = $publicIndex;
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';
$_SERVER['DOCUMENT_ROOT'] = $publicPath;

require $publicIndex;

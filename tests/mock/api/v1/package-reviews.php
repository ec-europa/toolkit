<?php

header('Content-Type: application/json; charset=utf-8');

if (!empty($_GET['version']) && file_exists('package-reviews-' . $_GET['version'] . '.php')) {
    require_once 'package-reviews-' . $_GET['version'] . '.php';
} else {
    require_once 'package-reviews-all.php';
}

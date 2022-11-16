<?php

header('Content-Type: application/json; charset=utf-8');

if (!empty($_GET['version'])) {
    require_once $_GET['version'] . '.php';
} else {
    require_once 'all.php';
}

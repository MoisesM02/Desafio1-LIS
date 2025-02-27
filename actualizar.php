<?php
session_start();
include('config.php');

header('Content-Type: application/json');
echo json_encode($_SESSION['precios']);
?>


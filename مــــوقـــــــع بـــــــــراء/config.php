<?php

$host = "sql313.infinityfree.com";
$dbname = "if0_42805162_XXX";
$username = "if0_42805162";
$password = "CLMBQiZ7dENP224";

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

} catch (PDOException $e) {

    die("فشل الاتصال بقاعدة البيانات.");

}
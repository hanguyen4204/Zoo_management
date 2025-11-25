<?php
$dsn = "mysql:host=localhost;dbname=loginreg;charset=utf8mb4";
$db_user = "root";
$db_pass = "";
$options = [ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC ];
try { $pdo = new PDO($dsn, $db_user, $db_pass, $options); }
catch (PDOException $e) { die("DB connection failed: ".$e->getMessage()); }

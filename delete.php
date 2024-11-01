<?php
require_once('config.php');
require_once('functions.php');

try {
    $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo $e->getMessage();
}

deleteData($pdo, "free-food");
deleteData($pdo, "free-food1");
deleteData($pdo, "delikanti");
deleteData($pdo, "venza");

echo "Údaje boli úspešne vymazané.";
?>

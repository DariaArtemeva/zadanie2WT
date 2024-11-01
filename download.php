<?php
require_once('config.php');
require_once('functions.php');

try {
    $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo $e->getMessage();
}

$freefoodURL = "http://www.freefood.sk/menu/#fayn-food";
$Delikanti = "https://www.delikanti.sk/prevadzky/3-jedalen-prif-uk/";
$Venza = "https://www.novavenza.sk/tyzdenne-menu";

getPageContent($pdo, $freefoodURL, "free-food");
getPageContent($pdo, $Delikanti, "delikanti");
getPageContent($pdo, $Venza, "venza");

echo "Údaje boli úspešne uložené.";
?>
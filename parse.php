<?php
require_once('config.php');
require_once('functions.php');

try {
    $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo $e->getMessage();
}

$output = getMenuFromDB($pdo, "free-food");
$output3 = getMenuFromDB($pdo, "free-food1");
$output1 = getMenuFromDB($pdo, "delikanti");
$output2 = getMenuFromDB($pdo, "venza");
libxml_use_internal_errors(true);

$dom = new DOMDocument();
$dom->loadHTML($output);
$xpath = new DOMXPath($dom);

$dom1 = new DOMDocument();
$dom1->loadHTML($output1);
$xpath1 = new DOMXPath($dom1);

 
    $dom2 = new DOMDocument();
    $dom2->loadHTML($output2);
    $xpath2 = new DOMXPath($dom2);
    // Запускаем анализ DOM-дерева и выводим информацию о меню

// Pomocou xpath viem ziskat aj elementy podla atributov a teda aj podla triedy
$menu_lists = $xpath->query('//ul[contains(@class, "daily-offer")]');
// Stranka poskytuje menu pre 3 restauracie teda su tam aj 3x daily-offer zoznamy.
$fayn_food = $menu_lists[1];

foreach ($fayn_food->childNodes as $day) {
    // Nezaujima ma DOMText, iba prvok typu DOMElement.
    if ($day->nodeType === XML_ELEMENT_NODE) {
        // Ziskam si datum a rozdelim ho na den a datum, kedze tieto dva su oddelene ciarkou.
        $datum = explode(',', $day->firstElementChild->textContent);
        echo "Den: " . $datum[0] . " Datum: " . trim($datum[1]);
        echo '<br>';

        // Iterujem cez ponuku dna.
        foreach ($day->lastElementChild->childNodes as $ponuka) {
            // Ziskam si poradove cislo jedla, resp. pismeno P. urcuje polievku
            $typ = $ponuka->firstElementChild;
            $cena = $ponuka->lastElementChild;

            // Odstranim typ a cenu aby mi ostal iba text ponuky.
            $ponuka->removeChild($typ); // Vymazanie por. cisla
            $ponuka->removeChild($cena); // Vymazanie ceny

            echo "Typ: " . $typ->textContent . ' Jedlo: ' . $ponuka->textContent . ' Cena: ' . $cena->textContent;
            echo '<br>';
        }
        echo '<hr>';
    }
}








$delikanti_table = $xpath1->query('//table[contains(@class, "prif-denne-table")]')->item(0);


$days = $xpath1->query('.//tr[@class]', $delikanti_table);

foreach ($days as $day) {

    $date_element = $xpath1->query('.//th', $day)->item(0);
    if ($date_element !== null) {
        $date_parts = explode('<br>', $date_element->nodeValue);
        $day_name = isset($date_parts[0]) ? trim($date_parts[0]) : "";
        echo "Den: " . $day_name . "<br>";
    }


    $menu_rows = $xpath1->query('following-sibling::tr[position() < count(following-sibling::tr[not(@class)])]', $day);
    
    foreach ($menu_rows as $menu_row) {

        $food_number = $xpath1->query('.//td[1]', $menu_row)->item(0);
        if ($food_number !== null) {
            $food_number = $food_number->nodeValue;
        }
      
        $food_name = $xpath1->query('.//td[2]', $menu_row)->item(0);
        if ($food_name !== null) {
            $food_name = $food_name->nodeValue;
        }
    
        }
        echo "Typ: " . $food_number . " Jedlo: " . $food_name ."<br>" ;
       
    }
    echo "<hr>";

    $menus = $xpath2->query('//div[starts-with(@id, "day_")]');

    // Обойти все div элементы и извлечь информацию о меню
    foreach ($menus as $menu) {
        echo "Menu dna: " . $menu->getAttribute('id') . "<br>";
    
        // Найти все элементы списка (li) внутри div элемента
        $menu_items = $xpath2->query('.//li', $menu);
    
        // Обойти все элементы списка и извлечь информацию о каждом блюде
        foreach ($menu_items as $item) {
            // Название блюда
            $name = $xpath2->query('.//h5', $item)->item(0)->textContent;
    
            // Аллергены
            $allergens = $xpath2->query('.//p', $item)->item(0)->textContent;
    
            // Цена
            $price_element = $xpath2->query('.//h5', $item->lastElementChild)->item(0);

            if ($price_element !== null) {
                $price = $price_element->textContent;
            } else {
                $price = "Cena";
            }
            
            echo "Nazov: $name - Alergeny: $allergens - Cena: $price<br>";
    
        }
    
        echo "<hr>";
    }


?>
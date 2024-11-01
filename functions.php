<?php

function getPageContent($pdo, $url, $name)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $output = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Chyba cURL: ' . curl_error($ch);
    } else {
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        echo "HTTP status: {$http_status}<br>";

        curl_close($ch);
        $sql = "INSERT INTO sites (name, html, date) VALUES (:name, :html, NOW()) ON DUPLICATE KEY UPDATE html = :html, date = NOW()";
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(":name", $name, PDO::PARAM_STR);
        $stmt->bindParam(":html", $output, PDO::PARAM_STR);

        if ($stmt->execute()) {
            echo "Stránka bola uložená.<br>";
        } else {
            echo "Ups. Niečo sa pokazilo.<br>";
            print_r($stmt->errorInfo());
        }

        unset($stmt);
    }
}

function getMenuFromDB($pdo, $name)
{
    $page_content = "";
    $sql = "SELECT html FROM sites WHERE name = :name LIMIT 1";

    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(":name", $name, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() == 1) {
        $row = $stmt->fetch();
        $page_content = $row["html"];
    } else {
        echo "Nenachadza sa v tabulke alebo je duplicitne.";
    }

    return $page_content;
}

function deleteData($pdo, $name)
{
    $sql = "DELETE FROM sites WHERE name = :name";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":name", $name, PDO::PARAM_STR);

    if ($stmt->execute()) {
        echo "Údaje boli úspešne odstránené pre {$name}.<br>";
    } else {
        echo "Chyba pri odstraňovaní údajov pre {$name}.<br>";
        print_r($stmt->errorInfo());
    }

    unset($stmt);
}
function parseFreeFood($html, $requestedDate = null)
{
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    $menu_lists = $xpath->query('//ul[contains(@class, "daily-offer")]');
    $fayn_food = $menu_lists[1];

    $menu_data = [];

    foreach ($fayn_food->childNodes as $day) {
        if ($day->nodeType === XML_ELEMENT_NODE) {
            $datum = explode(',', $day->firstElementChild->textContent);
            $day_name = trim($datum[0]);
            $date = trim($datum[1]);

            if ($requestedDate !== null && $requestedDate != $date) {
                continue;
            }

            $daily_menu = [];

            foreach ($day->lastElementChild->childNodes as $ponuka) {
                $typ = $ponuka->firstElementChild;
                $cena = $ponuka->lastElementChild;

                $ponuka->removeChild($typ);
                $ponuka->removeChild($cena);

                $daily_menu[] = [
                    'type' => $typ->textContent,
                    'food' => $ponuka->textContent,
                    'price' => $cena->textContent,
                ];
            }

            $menu_data[] = [
                'day' => $day_name,
                'date' => $date,
                'menu' => $daily_menu,
            ];
        }
    }

    return $menu_data;
}


function parseDelikanti($html) {
    $dom = new DOMDocument();
    @$dom->loadHTML($html);

    $xpath = new DOMXPath($dom);

    $menu_table = $xpath->query('//table[contains(@class, "menu-table")]')->item(0);
    $menu_rows = $xpath->query('.//tr', $menu_table);

    $days = [];
    $current_day = [
        "day" => "",
        "date" => "",
        "menu" => []
    ];

    foreach ($menu_rows as $row) {
        $day_cell = $xpath->query('.//th', $row)->item(0);
        if ($day_cell !== null) {
            if ($current_day["day"] !== "") {
                $days[] = $current_day;
                $current_day = [
                    "day" => "",
                    "date" => "",
                    "menu" => []
                ];
            }

            preg_match('/(.+?)\s+(\d{2}\.\d{2}\.\d{4})/', $day_cell->nodeValue, $matches);
            $current_day["day"] = trim($matches[1]);
            $current_day["date"] = trim($matches[2]);
        }

        $number_cell = $xpath->query('.//td[1]', $row)->item(0);
        $dish_cell = $xpath->query('.//td[2]', $row)->item(0);

        if ($number_cell !== null && $dish_cell !== null) {
            $number = trim($number_cell->nodeValue);
            $dish = trim(preg_replace('/\s+/', ' ', $dish_cell->nodeValue));

            $current_day["menu"][] = [
                'number' => $number,
                'food' => $dish,
                'price' => 'Cena ne uvedena'
            ];
        }
    }

    if ($current_day["day"] !== "") {
        $days[] = $current_day;
    }

    return $days;
}


function parseVenza($html) {
    $dom = new DOMDocument();
    @$dom->loadHTML($html);

    $xpath = new DOMXPath($dom);

    $menu = [];
    $days = ["Pondelok", "Utorok", "Streda", "Štvrtok", "Piatok"];
    $dayIndex = 0;

    $tab_content = $xpath->query('//div[@id="pills-tabContent"]')->item(0);
    $tab_panes = $xpath->query('.//div[contains(@class, "tab-pane")]', $tab_content);

    foreach ($tab_panes as $tab_pane) {
        $menu_items = $xpath->query('.//li[contains(@class, "d-flex")]', $tab_pane);
        $menu_per_day = [];

        foreach ($menu_items as $menu_item) {
            $food = $xpath->query('.//h5', $menu_item)->item(0)->nodeValue;
            $allergens = $xpath->query('.//p', $menu_item)->item(0)->nodeValue;
            $price = $xpath->query('.//div[contains(@class, "rightbar")]//h5', $menu_item)->item(0)->nodeValue;

            $menu_per_day[] = [
                "type" => "",
                "food" => $food . " " . $allergens,
                "price" => $price
            ];
        }

        if (!empty($menu_per_day)) {
            $menu[] = [
                "day" => $days[$dayIndex],
                "menu" => $menu_per_day
            ];
        }

        $dayIndex++;
    }

    return $menu;
}


?>

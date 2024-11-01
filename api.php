<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

require_once('config.php');
require_once('functions.php');

try {
    $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo $e->getMessage();
}

function getMenuData($pdo, $name) {
    $sql = "SELECT html FROM sites WHERE name = :name";
    $statement = $pdo->prepare($sql);
    $statement->execute(['name' => $name]);
    $row = $statement->fetch(PDO::FETCH_ASSOC);
    return $row['html'] ?? '';
}

function getMenu() {
    global $pdo;
    $free_food_html = getMenuData($pdo, 'free-food');
    $delikanti_html = getMenuData($pdo, 'delikanti');
    $venza_html = getMenuData($pdo, 'venza');
    $menu_data = [
        'free-food' => $free_food_html ? parseFreeFood($free_food_html) : [],
        'delikanti' => $delikanti_html ? parseDelikanti($delikanti_html) : [],
        'venza' => $venza_html ? parseVenza($venza_html) : []
    ];
    return $menu_data;
}


function updatePrice($menu_data, $restaurant, $day, $menu_item_number, $new_price) {
    global $pdo;

    foreach ($menu_data[$restaurant] as $index => $menu_day) {
        if (strtolower($menu_day['day']) == strtolower($day)) {
            $menu_data[$restaurant][$index]['menu'][$menu_item_number]['price'] = $new_price;

            $html = getMenuData($pdo, $restaurant);
            $updated_html = generateUpdatedHtml($html, $menu_data[$restaurant], $restaurant);
            saveMenuData($pdo, $restaurant, $updated_html);

            return $menu_data;
        }
    }

    return $menu_data;
}


function saveMenuData($pdo, $restaurant_name, $html) {
    $sql = "UPDATE sites SET html = :html WHERE name = :name";
    $statement = $pdo->prepare($sql);
    $statement->execute(['html' => $html, 'name' => $restaurant_name]);
}
function deleteRestaurant($pdo, $restaurant_name) {
    $sql = "DELETE FROM sites WHERE name = :name";
    $statement = $pdo->prepare($sql);
    $statement->execute(['name' => $restaurant_name]);
}


function generateUpdatedHtml($html, $menu_data, $restaurant_name) {
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    switch ($restaurant_name) {
        case 'free-food':
            $menu_lists = $xpath->query('//ul[contains(@class, "daily-offer")]');
            $fayn_food = $menu_lists[1];

            $day_index = 0;
            foreach ($fayn_food->childNodes as $day) {
                if ($day->nodeType === XML_ELEMENT_NODE) {
                    $daily_menu = $menu_data[$day_index]['menu'];
                    $menu_item_index = 0;
                    foreach ($day->lastElementChild->childNodes as $ponuka) {
                        $cena = $ponuka->lastElementChild;
                        $cena->nodeValue = $daily_menu[$menu_item_index]['price'];
                        $menu_item_index++;
                    }
                    $day_index++;
                }
            }
            break;

            case 'delikanti':
                $menu_table = $xpath->query('//table[contains(@class, "menu-table")]')->item(0);
                $menu_rows = $xpath->query('.//tr', $menu_table);
        
                $day_index = 0;
                foreach ($menu_rows as $row) {
                    $day_cell = $xpath->query('.//th', $row)->item(0);
                    if ($day_cell !== null) {
                        $current_day = $menu_data[$day_index];
                        $day_index++;
                    }
        
                    $number_cell = $xpath->query('.//td[1]', $row)->item(0);
                    $dish_cell = $xpath->query('.//td[2]', $row)->item(0);
                    $price_cell = $xpath->query('.//td[3]', $row)->item(0); 
        
                    if ($number_cell !== null && $dish_cell !== null && $price_cell !== null) {
                        $dish = $current_day["menu"][0]["food"];
                        $price = $current_day["menu"][0]["price"]; 
                        $dish_cell->nodeValue = $dish;
                        $price_cell->nodeValue = $price; 
                        array_shift($current_day["menu"]);
                    }
                }
                break;
        

        case 'venza':
            $tab_content = $xpath->query('//div[@id="pills-tabContent"]')->item(0);
            $tab_panes = $xpath->query('.//div[contains(@class, "tab-pane")]', $tab_content);
            $day_index = 0;

            foreach ($tab_panes as $tab_pane) {
                $menu_items = $xpath->query('.//li[contains(@class, "d-flex")]', $tab_pane);
                $menu_item_index = 0;

                foreach ($menu_items as $menu_item) {
                    $price = $xpath->query('.//div[contains(@class, "rightbar")]//h5', $menu_item)->item(0);
                    $price->nodeValue = $menu_data[$day_index]["menu"][$menu_item_index]["price"];
                    $menu_item_index++;
                }

                $day_index++;
            }
            break;
 
    }

    return $dom->saveHTML();
}

function addMenuItem($pdo, $restaurant, $new_item, $new_price) {
    $menu_data = getMenu();
    foreach ($menu_data[$restaurant] as $index => $menu_day) {
        $menu_data[$restaurant][$index]['menu'][] = ['food' => $new_item, 'price' => $new_price];
    }

    $html = getMenuData($pdo, $restaurant);
    $updated_html = generateUpdatedHtml($html, $menu_data[$restaurant], $restaurant);
    saveMenuData($pdo, $restaurant, $updated_html);
    
    return $menu_data;
}




$input_data = json_decode(file_get_contents('php://input'), true);

header('Content-Type: application/json');
$request_method = $_SERVER['REQUEST_METHOD'];

if ($request_method == 'GET') {
    $menu_data = getMenu();
    if (!empty($menu_data)) {
        echo json_encode($menu_data, JSON_PRETTY_PRINT);
    } else {
        header("HTTP/1.0 404 Not Found");
        echo json_encode(['message' => "No data found."], JSON_PRETTY_PRINT);
    }
}
elseif ($request_method == 'PUT' || $request_method == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    file_put_contents('debug.log', print_r($data, true), FILE_APPEND);
    if (isset($data['action']) && $data['action'] == 'update_price') {
        if (isset($data['restaurant']) && isset($data['day']) && isset($data['menu_item_number']) && isset($data['new_price'])) {
            $restaurant = $data['restaurant'];
            $day = $data['day'];
            $menu_item_number = $data['menu_item_number'];
            $new_price = $data['new_price'];

            $menu_data = getMenu();
            $updated_menu = updatePrice($menu_data, $restaurant, $day, $menu_item_number, $new_price);
            
            $html = getMenuData($pdo, $restaurant);
            $updated_html = generateUpdatedHtml($html, $updated_menu[$restaurant], $restaurant);
            saveMenuData($pdo, $restaurant, $updated_html);
            
            echo json_encode($updated_menu, JSON_PRETTY_PRINT);
        }
    } elseif (isset($data['action']) && $data['action'] == 'add_item' && isset($data['restaurant']) && isset($data['new_item']) && isset($data['new_price'])) {

        $restaurant = $data['restaurant'];
        $new_item = $data['new_item'];
        $new_price = $data['new_price'];
    
        $updated_menu = addMenuItem($pdo, $restaurant, $new_item, $new_price);
        echo json_encode($updated_menu, JSON_PRETTY_PRINT);
    
    
    }
     else {
        header("HTTP/1.0 400 Bad Request");
    }

}elseif ($request_method == 'DELETE') {
        if (isset($input_data['action']) && $input_data['action'] == 'delete_restaurant' && isset($input_data['restaurant'])) {
            $restaurant = $input_data['restaurant'];
            deleteRestaurant($pdo, $restaurant);
            echo json_encode(['message' => "Restaurant '{$restaurant}' deleted successfully."], JSON_PRETTY_PRINT);
        } else {
            header("HTTP/1.0 400 Bad Request");
        }
    }
    




?>

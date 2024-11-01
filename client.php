<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Klientska aplikácia pre API</title>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
  <h1>Klientska aplikácia pre API</h1>
  <form id="api-form">
    <label for="input-data">Zadajte údaje:</label>
    <input type="text" id="input-data" name="input-data" required>
    <button type="submit">Odoslať</button>
  </form>

  <div id="response"></div>

  <div class="update-price-form">
    <h3>Aktualizácia ceny</h3>
    <form onsubmit="event.preventDefault(); updatePrice(restaurant.value, day.value, menuItemNumber.value, newPrice.value);">
        <label for="restaurant">Reštaurácia:</label>
        <input type="text" id="restaurant" name="restaurant" required>
        <label for="day">Deň:</label>
        <input type="text" id="day" name="day" required>
        <label for="menuItemNumber">Číslo položky menu:</label>
        <input type="number" id="menuItemNumber" name="menuItemNumber" required>
        <label for="newPrice">Nová cena:</label>
        <input type="number" id="newPrice" name="newPrice" required>
        <button type="submit">Aktualizovať cenu</button>
    </form>
</div>

<div class="add-menu-item-form">
    <h3>Pridať položku </h3>
    <form onsubmit="event.preventDefault(); addMenuItem(addRestaurant.value, {food: addFood.value, price: addPrice.value});">
        <label for="addRestaurant">Reštaurácia:</label>
        <input type="text" id="addRestaurant" name="addRestaurant" required>
        <label for="addFood">Jedlo:</label>
        <input type="text" id="addFood" name="addFood" required>
        <label for="addPrice">Cena:</label>
        <input type="number" id="addPrice" name="addPrice" required>
        <button type="submit">Pridať položku</button>
    </form>
</div>

<div class="menu-container"></div>

<script>
  document.getElementById('api-form').addEventListener('submit', async (event) => {
    event.preventDefault();

    const field1 = document.getElementById('input-data').value;

    const payload = {
      field1: field1,
    };

    try {
      const response = await fetch('https://site40.webte.fei.stuba.sk/food/api.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload),
      });

      const data = await response.json();
      console.log(data);

      // Обработайте ответ API здесь (например, отобразите сообщение об успехе или ошибке)
    } catch (error) {
      console.error('Error:', error);
      // Обработайте ошибки здесь
    }
});

// Функция для обновления цены
function updatePrice(restaurant, day, menuItemNumber, newPrice) {
  const apiUrl = "https://site40.webte.fei.stuba.sk/food/api.php";
  const data = {
    action: 'update_price',
    restaurant: restaurant,
    day: day,
    menu_item_number: menuItemNumber,
    new_price: newPrice,
  };
  fetch(apiUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data),
    })
        .then(response => response.json())
        .then(data => {
            console.log('Price updated:', data);
            fetchMenuData();
        })
        .catch(error => {
            console.error('Error updating price:', error);
        });
}


// Функция для добавления пункта меню
function addMenuItem(restaurant, newItem) {
  const apiUrl = "https://site40.webte.fei.stuba.sk/food/api.php";
  const data = {
    action: 'add_item',
    restaurant: restaurant,
    new_item: newItem,
  };
  fetch(apiUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data),
    })
    .then(response => response.json())
    .then(data => {
        console.log('Menu item added:', data);
        fetchMenuData(); // Обновление данных меню после добавления блюда
    })
    .catch(error => {
        console.error('Error adding menu item:', error);
    });
}


document.addEventListener("DOMContentLoaded", function () {
// Инициализация выбора дней
const daySelector = document.getElementById("day-selector");
if (daySelector) {
daySelector.addEventListener("change", function (event) {
fetchMenuData(event.target.value);
});
}
fetchMenuData();
});
function fetchMenuData(day = "") {
const apiUrl = "https://site40.webte.fei.stuba.sk/food/api.php";
// Добавлен параметр cache: 'no-store' для предотвращения кеширования
fetch(apiUrl + (day ? "?day=" + day : ""), { cache: 'no-store' })
      .then(response => response.json())
      .then(data => {
          const groupedData = groupNewMenuData(data);
          displayNewMenuData(groupedData);
      })
      .catch(error => {
          console.error("Error fetching menu data:", error);
      });
    }

function groupNewMenuData(menuData) {
const groupedData = [];
for (const restaurant in menuData) {
      for (const menuItem of menuData[restaurant]) {
          groupedData.push({
              restaurant: restaurant,
              day: menuItem.day,
              date: menuItem.date,
              menu: menuItem.menu
          });
      }
  }

  return groupedData;
}

function displayNewMenuData(menuData) {
const menuContainer = document.querySelector(".menu-container");
menuContainer.innerHTML = "";
for (const restaurantMenu of menuData) {
      const dayMenuElement = createNewDayMenuElement(restaurantMenu);
      menuContainer.appendChild(dayMenuElement);
  }
}

function createNewDayMenuElement(restaurantMenu) {
const dayMenuElement = document.createElement("div");
dayMenuElement.className = "day-menu";
const restaurantTitle = document.createElement("h3");
restaurantTitle.textContent = restaurantMenu.restaurant;
  dayMenuElement.appendChild(restaurantTitle);

  const dayTitle = document.createElement("h4");
  dayTitle.textContent = `${restaurantMenu.day} - ${restaurantMenu.date}`;
  dayMenuElement.appendChild(dayTitle);

  const menuList = document.createElement("ul");

  for (const menuItem of restaurantMenu.menu) {
      const menuItemElement = createNewMenuItemElement(menuItem);
      menuList.appendChild(menuItemElement);
  }

  dayMenuElement.appendChild(menuList);
  return dayMenuElement;

}

function createNewMenuItemElement(menuItem) {
const menuItemElement = document.createElement("li");
const typeOrNumber = menuItem.type || menuItem.number || '';
menuItemElement.textContent = `${typeOrNumber ? typeOrNumber + ' - ' : ''}${menuItem.food} - ${menuItem.price || ""}`;

return menuItemElement;
}
</script>

</body>
</html>
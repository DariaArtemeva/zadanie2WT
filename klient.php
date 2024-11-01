
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Menu API</title>
    <style>
    
  .main1{
    background-color:white;
    padding:20px;
    border-radius:20px;
    margin:12%;
  }
         body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient( rgba(89,71,143,1) 0%, rgba(151,91,198,1) 14%, rgba(253,250,255,1) 48%);
         }
        h1 {
            text-align: center;
        }

        button {
            background-color: #7c5dc9;
            border: none;
            color: white;
            padding: 8px 16px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 4px;
        }

        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 12px 20px;
            margin: 8px 0;
            box-sizing: border-box;
            border: 2px solid #ccc;
            border-radius: 4px;
            background-color: #f8f8f8;
        }

        label {
            display: block;
        }

        form {
            margin-bottom: 20px;
        }

        #menu-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .restaurant {
            flex-basis: 30%;
        }

        @media screen and (max-width: 768px) {
            .restaurant {
                flex-basis: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="main1">
    <h1>Restaurant Menu API</h1>
    <form action="buttons.php" target="_blank">
   <button>buttons</button>
   </form>
   <form action="documentation.html" target="_blank">
   <button>documentation</button>
   </form>
   <form action="index.php" target="_blank">
   <button>json</button>
   </form>
   <br><br>
    <form id="search-form" autocomplete="off">
  <label for="search-day">Enter day:</label>
  <input type="text" id="search-day" name="search-day" placeholder="pondelok">
  <button type="submit">Search</button>
</form>

<div id="search-results">

</div>
<form id="update-price-form" autocomplete="off">
  <h3>Update price</h3>
  <label for="update-restaurant">Restaurant:</label>
  <input type="text" id="update-restaurant" name="update-restaurant" placeholder="free-food">
  <label for="update-day">Day:</label>
  <input type="text" id="update-day" name="update-day" placeholder="pondelok">
  <label for="update-item-number">Number of the dish:</label>
  <input type="number" id="update-item-number" name="update-item-number" min="0">
  <label for="update-new-price">New price:</label>
  <input type="number" id="update-new-price" name="update-new-price" step="0.01" min="0">
  <button type="submit">Update</button>

</form>
<form id="delete-menu-form" autocomplete="off">
  <h3>Delete the menu</h3>
  <label for="delete-restaurant">Restaurant:</label>
  <input type="text" id="delete-restaurant" name="delete-restaurant" placeholder="free-food">
  <button type="submit">Delete</button>
</form>

<form id="add-dish-form" autocomplete="off">
  <h3>Add the dish</h3>
  <label for="add-restaurant">Restaurant:</label>
  <input type="text" id="add-restaurant" name="add-restaurant" placeholder="free-food">
  <label for="add-dish">Dish:</label>
  <input type="text" id="add-dish" name="add-dish" placeholder="Vyprazeny syr">
  <label for="add-type">Typ:</label>
<input type="text" id="add-type" name="add-type">
  <label for="add-price">Price:</label>
  <input type="number" id="add-price" name="add-price" step="0.01" min="0">
  <button type="submit">Add</button>
</form>
    </div>
    <div id="menu-container">
    </div>
    <script>
  const API_URL = "https://site40.webte.fei.stuba.sk/food/api.php"; 

  async function getMenu() {
    try {
        const response = await fetch(API_URL);
        if (response.ok) {
            const menuData = await response.json();
            displayMenu(menuData);
            return menuData; 
        } else {
            console.error("Ошибка при получении меню:", response.status);
        }
    } catch (error) {
        console.error("Ошибка при получении меню:", error);
    }
}


function displayMenu(menuData) {
    const menuContainer = document.getElementById("menu-container");
    menuContainer.innerHTML = ""; 

    for (const restaurant in menuData) {
        const restaurantMenu = menuData[restaurant];
        const restaurantDiv = document.createElement("div");
        restaurantDiv.className = "restaurant";
        restaurantDiv.innerHTML = `<h2>${restaurant}</h2>`;

        for (const day of restaurantMenu) {
            const dayDiv = document.createElement("div");
            dayDiv.className = "day";
            dayDiv.innerHTML = `<h3>${day.day}</h3><ul></ul>`;
            restaurantDiv.appendChild(dayDiv);

            const menuList = dayDiv.querySelector("ul");

            for (const menuItem of day.menu) {
                const menuItemLi = document.createElement("li");
                menuItemLi.innerText = `${menuItem.type} ${menuItem.food} - ${menuItem.price}`; 
                menuList.appendChild(menuItemLi);
            }
        }

        menuContainer.appendChild(restaurantDiv);
    }
}



getMenu();
    </script>
    <script>
        const searchForm = document.getElementById("search-form");
        searchForm.addEventListener("submit", (event) => {
  event.preventDefault();
  const searchDay = document.getElementById("search-day").value;

  if (searchDay) {
    getMenu().then((menuData) => {
      displaySearchResults(menuData, searchDay);
    });
  }
});


function displaySearchResults(menuData, searchDay) {
            const searchResults = document.getElementById("search-results");
            searchResults.innerHTML = `<h3>Results for "${searchDay}":</h3>`;

            for (const restaurant in menuData) {
                const restaurantMenu = menuData[restaurant];
                const dayMenu = restaurantMenu.find(
                    (day) => day.day.toLowerCase() === searchDay.toLowerCase()
                );

                if (dayMenu) {
                    const restaurantDiv = document.createElement("div");
                    restaurantDiv.innerHTML = `<h4>${restaurant}</h4>`;
                    const menuList = document.createElement("ul");

                    for (const menuItem of dayMenu.menu) {
                        const menuItemLi = document.createElement("li");
                        menuItemLi.innerText = `${menuItem.food} - ${menuItem.price}`;
                        menuList.appendChild(menuItemLi);
                    }

                    restaurantDiv.appendChild(menuList);
                    searchResults.appendChild(restaurantDiv);
                }
            }
        }

    </script>
    <script>
        const deleteMenuForm = document.getElementById("delete-menu-form");
deleteMenuForm.addEventListener("submit", async (event) => {
  event.preventDefault();

  const restaurant = document.getElementById("delete-restaurant").value;

  if (restaurant) {
    const response = await fetch(`${API_URL}`, {
    method: "DELETE",
    headers: {
        "Content-Type": "application/json",
    },
    body: JSON.stringify({
        action: "delete_restaurant",
        restaurant: restaurant
    }),
});



    if (response.ok) {
      alert("Menu was successfully deleted!");
    } else {
        console.error("Error while deleting. Status:", response.status);
    }
  }
});

    </script>
    <script>
        
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
        getMenu(); 
    })
    .catch(error => {
        console.error('Error updating price:', error);
    });
}


    const updatePriceForm = document.getElementById("update-price-form");
    updatePriceForm.addEventListener("submit", (event) => {
      event.preventDefault();

      const restaurant = document.getElementById("update-restaurant").value;
      const day = document.getElementById("update-day").value;
      const itemNumber = parseInt(document.getElementById("update-item-number").value);
      const newPrice = parseFloat(document.getElementById("update-new-price").value);

      if (restaurant && day && !isNaN(itemNumber) && !isNaN(newPrice)) {
        updatePrice(restaurant, day, itemNumber, newPrice);
      }
    });
    </script>
    <script>
        function addDish(restaurant, food, price, type) {
    const apiUrl = "https://site40.webte.fei.stuba.sk/food/api.php";
    const data = {
        action: 'add_item',
        restaurant: restaurant,
        new_item: food,
        new_price: price,
        type: type, 
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
        console.log('Dish added:', data);
        getMenu();
    })
    .catch(error => {
        console.error('Error adding dish:', error);
    });
}

const addDishForm = document.getElementById("add-dish-form");
addDishForm.addEventListener("submit", (event) => {
    event.preventDefault();

    const restaurant = document.getElementById("add-restaurant").value;
    const food = document.getElementById("add-dish").value;
    const price = parseFloat(document.getElementById("add-price").value);
    const type = document.getElementById("add-type").value; 

    if (restaurant && food && !isNaN(price) && type) {
        addDish(restaurant, food, price, type);
    }
});



    </script>
</body>
</html>

function displayMenu(menuData) {
    const menuContainer = document.getElementById("menu");

    for (const [restaurantName, days] of Object.entries(menuData)) {
        const restaurantDiv = document.createElement("div");
        const restaurantHeader = document.createElement("h2");
        restaurantHeader.innerText = restaurantName;
        restaurantDiv.appendChild(restaurantHeader);

        for (const day of days) {
            const dayHeader = document.createElement("h3");
            dayHeader.innerText = day.day;
            restaurantDiv.appendChild(dayHeader);

            const menuList = document.createElement("ul");

            day.menu.forEach((menuItem, index) => {
                const listItem = document.createElement("li");
                listItem.innerText = `${menuItem.food} - ${menuItem.price}`;

                const editButton = document.createElement("button");
                editButton.innerText = "Edit price";
                editButton.addEventListener("click", () => {
                    const newPrice = prompt("Enter new price:");
                    if (newPrice) {
                        updatePrice(restaurantName, day.day, index, newPrice);
                    }
                });

                listItem.appendChild(editButton);
                menuList.appendChild(listItem);
            });

            restaurantDiv.appendChild(menuList);
        }

        menuContainer.appendChild(restaurantDiv);
    }
}

function updatePrice(restaurant, day, menuItemNumber, newPrice) {
    const data = {
        action: "update_price",
        restaurant,
        day,
        menu_item_number: menuItemNumber,
        new_price: newPrice
    };

    fetch("https://site40.webte.fei.stuba.sk/food/api.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(data)
    })
        .then(response => response.json())
        .then(menuData => {
            document.getElementById("menu").innerHTML = "";
            displayMenu(menuData);
        });
}

function addItem(restaurant, newItem, newPrice) {
    const data = {
        action: "add_item",
        restaurant,
        new_item: {
            food: newItem,
            price: newPrice
        }
    };

    fetch("your_api_url_here", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(data)
    })
        .then(response => response.json())
        .then(menuData => {
            document.getElementById("menu").innerHTML = "";
            displayMenu(menuData);
        });
}

document.getElementById("add-item-form").addEventListener("submit", (event) => {
    event.preventDefault();
    const restaurant = document.getElementById("restaurant").value;
    const newItem = document.getElementById("new-item").value;
    const newPrice = document.getElementById("new-price").value;

    if (newItem && newPrice) {
        addItem(restaurant, newItem, newPrice);
        document.getElementById("new-item").value = "";
        document.getElementById("new-price").value = "";
    }
});

fetch("https://site40.webte.fei.stuba.sk/food/api.php")
    .then(response => response.json())
    .then(menuData => displayMenu(menuData));

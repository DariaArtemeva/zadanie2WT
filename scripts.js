document.addEventListener("DOMContentLoaded", function () {
    // Инициализация выбора дней
    const daySelector = document.getElementById("day-selector");
    daySelector.addEventListener("change", function (event) {
        fetchMenuData(event.target.value);
    });

    // Получение обеденного меню при загрузке страницы
    fetchMenuData();
});

function fetchMenuData(day = "") {
    // Замените этот URL на ваш API-конечную точку
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






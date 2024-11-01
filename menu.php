<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu</title>
</head>
<body>
    <h1>Menu</h1>
    <div id="menu"></div>

    <h2>Add item</h2>
    <form id="add-item-form">
        <label for="restaurant">Restaurant:</label>
        <select id="restaurant" name="restaurant">
            <option value="free-food">Free Food</option>
            <option value="delikanti">Delikanti</option>
            <option value="venza">Venza</option>
        </select>
        <br>
        <label for="new-item">New item:</label>
        <input type="text" id="new-item" name="new-item">
        <br>
        <label for="new-price">New price:</label>
        <input type="text" id="new-price" name="new-price">
        <br>
        <button type="submit">Add item</button>
    </form>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="app.js"></script>
</body>
</html>

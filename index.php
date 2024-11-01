<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Test</title>
</head>
<body>
    <h1>API Test</h1>
    <pre id="output"></pre>

    <script>
        const output = document.getElementById("output");

        fetch('https://site40.webte.fei.stuba.sk/food/api.php')
            .then(response => response.json())
            .then(data => {
                output.textContent = JSON.stringify(data, null, 2);
            })
            .catch(error => {
                console.error('Error fetching API data:', error);
            });
    </script>
</body>
</html>











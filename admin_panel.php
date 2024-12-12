<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
</head>

<body>
    <h1>Uppdatera admininställningar</h1>

    <!-- Form to update admin settings -->
    <form method="POST" action="update_admin.php">
        <label for="price_budget">Pris för budget:</label>
        <input type="number" name="price_budget" id="price_budget" step "0.01" required><br><br>

        <label for="price_standard">Pris för standard:</label>
        <input type="number" name="price_standard" id="price_standard" step "0.01" required><br><br>

        <label for="price_luxury">Pris för luxury:</label>
        <input type="number" name="price_luxury" id="price_luxury" step "0.01" required><br><br>

        <label for="discount">Pris för rabatt:</label>
        <input type="number" name="discount" id="discount" step "0.01" required><br><br>

        <button type="submit">Uppdatera</button>
</body>

</html>
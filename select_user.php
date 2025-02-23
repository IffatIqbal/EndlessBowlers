<?php
require 'database.php';

// Fetch all users from the database
$userQuery = $pdo->query("SELECT id, username FROM users");
$users = $userQuery->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Player | EndlessBowlers</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <h1>Select Your Player</h1>
    <form action="set_user.php" method="POST">
        <label for="user">Choose a player:</label>
        <select name="user_id" id="user">
            <?php foreach ($users as $user): ?>
                <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Start Game</button>
    </form>
</div>
</body>
</html>

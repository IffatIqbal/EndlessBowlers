<?php
require 'database.php';
session_start();

// Ensure user is selected
if (!isset($_SESSION["user_id"])) {
    header("Location: select_user.php");
    exit;
}

$userId = $_SESSION["user_id"];

// Get user details
$userQuery = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$userQuery->execute([$userId]);
$user = $userQuery->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: select_user.php?error=invalid_user");
    exit;
}

$username = $user["username"];

// Get ELO history (ordered by question count)
$eloHistoryQuery = $pdo->prepare("SELECT elo FROM elo_history WHERE user_id = ? ORDER BY id ASC");
$eloHistoryQuery->execute([$userId]);
$eloHistory = $eloHistoryQuery->fetchAll(PDO::FETCH_ASSOC);

$eloValues = array_column($eloHistory, "elo");
$questionNumbers = count($eloValues) ? range(1, count($eloValues)) : []; // Avoid error if empty

// Get leaderboard (top 4 players, ignoring blank usernames)
$leaderboardQuery = $pdo->query("SELECT username, elo FROM users WHERE username <> '' ORDER BY elo DESC LIMIT 4");
$leaderboard = $leaderboardQuery->fetchAll(PDO::FETCH_ASSOC);

// Get average response time (ignoring responses over 10 sec)
$responseTimeQuery = $pdo->prepare("SELECT AVG(response_time) AS avg_time FROM response_times WHERE user_id = ? AND response_time <= 10");
$responseTimeQuery->execute([$userId]);
$avgResponseTime = $responseTimeQuery->fetchColumn();
$avgResponseTime = $avgResponseTime ? round($avgResponseTime, 2) . " sec" : "N/A";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ELO Graph & Leaderboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            display: flex;
            flex-direction: row;
            height: 100vh;
            margin: 0;
            background-color: #3F8740;
            font-family: Arial, sans-serif;
            color: white;
        }
        .graph-container {
            width: 75%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .sidebar {
            width: 25%;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            background-color: black;
            border-left: 2px solid #FFD700;
            position: relative;
        }
        .btn {
            display: inline-block;
            background-color: #808080; /* Grey color */
            color: white;
            font-weight: bold;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .leaderboard {
            width: 100%;
            text-align: center;
            margin-top: 20px;
        }
        .leaderboard table {
            width: 100%;
            border-collapse: collapse;
        }
        .leaderboard th, .leaderboard td {
            padding: 10px;
            border-bottom: 1px solid #FFD700;
        }
        .leaderboard th {
            background-color: #FFD700;
            color: black;
        }
        .gold { color: #FFD700; font-weight: bold; }
        .silver { color: #C0C0C0; font-weight: bold; }
        .bronze { color: #CD7F32; font-weight: bold; }
        .red { color: #FF0000; font-weight: bold; }
        .response-time-box {
            position: absolute;
            bottom: 20px;
            right: 20px;
            background-color: rgba(255, 255, 255, 0.2);
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="graph-container">
    <canvas id="eloChart"></canvas>
</div>

<div class="sidebar">
    <a href="index.php" class="btn">Back to Trainer</a>
    <h2>Leaderboard</h2>
    <div class="leaderboard">
        <table>
            <tbody>
            <?php foreach ($leaderboard as $index => $player):
                $class = ($index == 0) ? "gold" : (($index == 1) ? "silver" : (($index == 2) ? "bronze" : "red")); ?>
                <tr>
                    <td><strong><?php echo $index + 1; ?></strong></td>
                    <td class="<?php echo $class; ?>"><?php echo htmlspecialchars($player["username"]); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Response Time Display -->
    <div class="response-time-box">
        Avg Response Time: <br>
        <span style="font-size: 1.5em;"><?php echo $avgResponseTime; ?></span>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById("eloChart").getContext("2d");

        const eloData = {
            labels: <?php echo json_encode($questionNumbers); ?>, // X-axis: Question count
            datasets: [{
                label: "ELO Over Time",
                data: <?php echo json_encode($eloValues); ?>,
                borderColor: "white",
                backgroundColor: "rgba(255, 255, 255, 0.2)",
                fill: true,
                tension: 0.2,
                pointRadius: 0 // Hide points for smooth line
            }]
        };

        new Chart(ctx, {
            type: "line",
            data: eloData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: "white" // Make legend text white
                        }
                    }
                },
                scales: {
                    x: {
                        title: { display: true, text: "Questions Answered", color: "white" },
                        grid: { display: false },
                        ticks: { color: "white" }
                    },
                    y: {
                        title: { display: true, text: "ELO", color: "white" },
                        beginAtZero: false,
                        suggestedMin: Math.min(...eloData.datasets[0].data) - 50,
                        suggestedMax: Math.max(...eloData.datasets[0].data) + 50,
                        ticks: { color: "white" }
                    }
                }
            }
        });

        // ESC key to return to index.php
        document.addEventListener("keydown", function(event) {
            if (event.key === "Escape") {
                window.location.href = "index.php";
            }
        });
    });
</script>

</body>
</html>

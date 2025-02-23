<?php


require 'database.php';
session_start();

// Ensure user is selected
if (!isset($_SESSION["user_id"])) {
    header("Location: select_user.php");
    exit;
}

$userId = $_SESSION["user_id"];

// Get user's ELO and username
$eloQuery = $pdo->prepare("SELECT username, elo FROM users WHERE id = ?");
$eloQuery->execute([$userId]);
$user = $eloQuery->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: select_user.php?error=invalid_user");
    exit;
}

$_SESSION["elo"] = $user["elo"];

// Get a random question
$query = $pdo->query("SELECT id, question FROM questions ORDER BY RAND() LIMIT 1");
$question = $query->fetch(PDO::FETCH_ASSOC);

$_SESSION["start_time"] = microtime(true);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>EndlessBowlers</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="logo-container">
    <img src="hosabowl.png" alt="HOSABowl Logo">
</div>

<div class="container">
    <div class="question-area">
        <?php if ($question): ?>
            <h1><?php echo htmlspecialchars(str_replace(["[", "]"], "", $question['question'])); ?></h1>

            <form id="answerForm">
                <input type="hidden" name="question_id" value="<?php echo htmlspecialchars($question['id']); ?>">
                <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                <input type="text" id="answerInput" name="answer" placeholder="buzz!" required>
                <button type="submit">Submit</button>
            </form>

            <p id="feedback" class="feedback-message" style="display: none;"></p>
        <?php else: ?>
            <h1>Error: No questions available.</h1>
        <?php endif; ?>
    </div>

    <div class="sidebar">
        <p><strong>Player:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
        <p><strong>ELO:</strong> <span id="elo"><?php echo htmlspecialchars($_SESSION["elo"]); ?></span></p>
        <a href="graph.php" class="btn">View ELO Graph</a>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let form = document.getElementById("answerForm");
        let feedbackBox = document.getElementById("feedback");
        let eloElement = document.getElementById("elo");

        form.addEventListener("submit", function(event) {
            event.preventDefault();

            let formData = new FormData(form);

            fetch("submit-answer.php", {
                method: "POST",
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }

                    feedbackBox.style.display = "block";
                    feedbackBox.style.opacity = "1";

                    if (data.correct) {
                        feedbackBox.textContent = "CORRECT";
                        feedbackBox.style.color = "yellowgreen";
                        feedbackBox.style.fontSize = "2rem";
                        feedbackBox.style.fontWeight = "bold";
                    } else {
                        feedbackBox.textContent = "Correct Answer: " + data.correct_answer;
                        feedbackBox.style.color = "red";
                        feedbackBox.style.fontSize = "2rem";
                        feedbackBox.style.fontWeight = "bold";
                    }

                    if (eloElement) {
                        eloElement.textContent = data.newElo;
                    }

                    sessionStorage.setItem("elo", data.newElo);

                    setTimeout(() => {
                        feedbackBox.style.opacity = "0";
                    }, 1000);

                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                })
                .catch(error => console.error("Error:", error));
        });
    });
</script>

</body>
</html>

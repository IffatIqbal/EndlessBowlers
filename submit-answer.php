<?php
require 'database.php';
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

if (!isset($_POST["user_id"], $_POST["question_id"], $_POST["answer"])) {
    echo json_encode(["error" => "Missing input"]);
    exit;
}

$userId = $_POST["user_id"];
$questionId = $_POST["question_id"];
$userAnswer = trim($_POST["answer"]);

// Fetch the correct answer
$query = $pdo->prepare("SELECT correct_answer FROM questions WHERE id = ?");
$query->execute([$questionId]);
$question = $query->fetch(PDO::FETCH_ASSOC);

if (!$question) {
    echo json_encode(["error" => "Invalid question"]);
    exit;
}

$correctAnswer = trim(str_replace(["[", "]"], "", $question["correct_answer"]));
$userAnswer = strtolower($userAnswer);
$correctAnswer = strtolower($correctAnswer);

// Fetch user's ELO
$eloQuery = $pdo->prepare("SELECT elo FROM users WHERE id = ?");
$eloQuery->execute([$userId]);
$user = $eloQuery->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(["error" => "User not found"]);
    exit;
}

$currentElo = (int) $user["elo"];

// Levenshtein distance allows for small typos
$levenshteinDistance = levenshtein($userAnswer, $correctAnswer);
$correct = ($levenshteinDistance <= 3);

// Update ELO score
$newElo = $correct ? $currentElo + 20 : max(0, $currentElo - 15);
$updateElo = $pdo->prepare("UPDATE users SET elo = ? WHERE id = ?");
$updateElo->execute([$newElo, $userId]);

// Store ELO history
$insertHistory = $pdo->prepare("INSERT INTO elo_history (user_id, elo) VALUES (?, ?)");
$insertHistory->execute([$userId, $newElo]);

$_SESSION["elo"] = $newElo;

echo json_encode([
    "correct" => $correct,
    "correct_answer" => $correctAnswer,
    "newElo" => $newElo
]);
exit;

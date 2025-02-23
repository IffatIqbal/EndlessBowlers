<?php
require __DIR__ . '/includes/database.php';


$csvFile = __DIR__ . '/sql/questions.csv';

if (!file_exists($csvFile)) {
    die("Error: File not found.\n");
}

$file = fopen($csvFile, 'r');
if (!$file) {
    die("Error: Cannot open file.\n");
}

while (($line = fgetcsv($file)) !== false) {
    if (count($line) < 2) {
        continue; // Skip invalid rows
    }

    $question = trim($line[0], "[]"); // Remove brackets
    $answer = trim($line[1], "[]");   // Remove brackets

    $stmt = $pdo->prepare("INSERT INTO questions (question, correct_answer) VALUES (?, ?)");
    $stmt->execute([$question, $answer]);
}

fclose($file);
echo "Import successful!\n";
?>

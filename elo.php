<?php
function calculate_elo($currentElo, $opponentElo, $isWin, $kFactor = 32) {
    $expectedScore = 1 / (1 + pow(10, ($opponentElo - $currentElo) / 400));
    $score = $isWin ? 1 : 0;
    return round($kFactor * ($score - $expectedScore));
}
?>
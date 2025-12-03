<?php

session_start();

if (!isset($_SESSION['quiz_id']) || !isset($_SESSION['player_name'])) {
    header('Location: join.php');
    exit();
}


$_SESSION['current_question']++;


header('Location: play.php');
exit();
?>

<?php
session_start();
require 'config.php';
require 'classes/Flashcard.php';

header('Content-Type: application/json');

// Verifica sessão e parâmetros
if (!isset($_SESSION['user_id'], $_POST['flashcard_id'], $_POST['is_correct'])) {
    echo json_encode(['success' => false, 'error' => 'Dados incompletos']);
    exit;
}

$userId = $_SESSION['user_id'];
$flashcardId = intval($_POST['flashcard_id']);
$isCorrect = intval($_POST['is_correct']);

$flashcardModel = new Flashcard($conn);
$logged = $flashcardModel->logQuizAttempt($userId, $flashcardId, $isCorrect);

echo json_encode(['success' => $logged]);
exit;
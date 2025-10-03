<?php
// filepath: c:\xampp\htdocs\FlashCard\js\buscas\buscar_assuntos_por_id.php


session_start();
require '../../config.php';  // Caminho relativo para a raiz (simples e direto)
require '../../classes/Flashcard.php';  // Caminho relativo para a raiz
header('Content-Type: application/json');

// Verifica sessão e parâmetros (responsabilidade única: validação)
if (!isset($_SESSION['user_id']) || !isset($_GET['subject_id'])) {
    echo json_encode(['success' => false, 'error' => 'Dados incompletos']);
    exit;
}

$subjectId = intval($_GET['subject_id']);
$flashcardModel = new Flashcard($conn);  // Usa POO para encapsular lógica
$topics = $flashcardModel->getTopicsBySubjectId($subjectId);

echo json_encode(['success' => true, 'data' => $topics]);
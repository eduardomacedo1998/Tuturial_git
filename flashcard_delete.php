<?php
session_start();
require 'config.php';
require 'classes/Flashcard.php';

// Redirect if not logged in or no id
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: flashcards.php');
    exit;
}

$id = intval($_GET['id']);
$flashcardModel = new Flashcard($conn);
$flashcardModel->delete($id);
header('Location: flashcards.php');
exit;

<?php
session_start();
require 'config.php';
require 'classes/Topic.php';

// Autenticação
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); exit;
}

$topicModel = new Topic($conn);
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id) {
    $topicModel->delete($id);
}
header('Location: topic_list.php'); exit;
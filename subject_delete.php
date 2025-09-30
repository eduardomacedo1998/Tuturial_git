<?php
session_start();
require 'config.php';
require 'classes/Subject.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$subjectModel = new Subject($conn);
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id) {
    $subjectModel->delete($id);
}

header('Location: subject_list.php');
exit;
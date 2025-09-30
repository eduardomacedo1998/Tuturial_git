<?php
session_start();
require 'config.php';
require 'classes/Subject.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    if (empty($name)) {
        $errors[] = 'Nome da matéria é obrigatório.';
    } else {
        $subjectModel = new Subject($conn);
        if ($subjectModel->create($name)) {
            header('Location: subject_list.php'); exit;
        } else {
            $errors[] = 'Erro ao salvar.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head><meta charset="UTF-8"><title>Adicionar Matéria</title><link rel="stylesheet" href="css/style.css"></head>
<body>
    <?php include 'header.php'; ?>
<div class="container">
    <h2>Adicionar Matéria</h2>
    <?php if ($errors): ?><div class="error"><?php echo implode('<br>', $errors); ?></div><?php endif; ?>
    <form action="" method="post">
        <div class="form-group"><label for="name">Nome</label><input type="text" id="name" name="name"></div>
        <button type="submit">Salvar</button>
    </form>
    <p><a href="subject_list.php">Voltar</a></p>
</div>
</body>
</html>
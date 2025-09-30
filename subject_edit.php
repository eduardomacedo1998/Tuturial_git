<?php
session_start();
require 'config.php';
require 'classes/Subject.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); exit;
}

$subjectModel = new Subject($conn);
$errors = [];

// Get subject by id
if (!isset($_GET['id'])) {
    header('Location: subject_list.php'); exit;
}
$id = intval($_GET['id']);
$subject = $subjectModel->getById($id);

// Ensure subject exists
if (!$subject) {
    header('Location: subject_list.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    if (empty($name)) {
        $errors[] = 'Nome da matéria é obrigatório.';
    }
    if (empty($errors)) {
        if ($subjectModel->update($id, $name)) {
            header('Location: subject_list.php'); exit;
        } else {
            $errors[] = 'Erro ao salvar.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Matéria</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <h2>Editar Matéria</h2>
        <?php if ($errors): ?>
            <div class="error"><?php echo implode('<br>', $errors); ?></div>
        <?php endif; ?>
        <form action="" method="post">
            <div class="form-group">
                <label for="name">Nome</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($subject['name']); ?>">
            </div>
            <button type="submit">Salvar</button>
            <a href="subject_list.php"><button type="button">Cancelar</button></a>
        </form>
    </div>
</body>
</html>
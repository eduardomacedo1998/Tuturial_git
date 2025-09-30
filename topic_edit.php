<?php
session_start();
require 'config.php';
require 'classes/Topic.php';
require 'classes/Subject.php';

// Autenticação
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); exit;
}

$topicModel = new Topic($conn);
$subjectModel = new Subject($conn);
$subjects = $subjectModel->getAll();
$errors = [];

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$topic = $topicModel->getById($id);
if (!$topic) {
    header('Location: topic_list.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $subject_id = intval($_POST['subject_id']);
    if (empty($name)) {
        $errors[] = 'Nome do assunto é obrigatório.';
    }
    if (!$subjectModel->getById($subject_id)) {
        $errors[] = 'Matéria inválida.';
    }
    if (!$errors) {
        if ($topicModel->update($id, $subject_id, $name)) {
            header('Location: topic_list.php'); exit;
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
    <title>Editar Assunto</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">
    <h2>Editar Assunto</h2>
    <?php if ($errors): ?><div class="error"><?php echo implode('<br>', $errors); ?></div><?php endif; ?>
    <form action="" method="post">
        <div class="form-group"><label for="subject_id">Matéria</label>
            <select id="subject_id" name="subject_id">
                <?php foreach ($subjects as $sub): ?>
                    <option value="<?php echo $sub['id']; ?>" <?php echo $sub['id'] == $topic['subject_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($sub['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label for="name">Nome</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($topic['name']); ?>">
        </div>
        <button type="submit">Salvar</button>
    </form>
    <p><a href="topic_list.php">Voltar</a></p>
</div>
</body>
</html>
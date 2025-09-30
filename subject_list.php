<?php
session_start();
require 'config.php';
require 'classes/Subject.php';

// Authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$subjectModel = new Subject($conn);
$subjects = $subjectModel->getAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Matérias</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>
<div class="container">
    <h2>Matérias</h2>
    <p><a href="subject_add.php"><button>Adicionar Matéria</button></a></p>
    <table>
        <thead><tr><th>ID</th><th>Nome</th><th>Ações</th></tr></thead>
        <tbody>
        <?php foreach ($subjects as $sub): ?>
            <tr>
                <td><?php echo $sub['id']; ?></td>
                <td><?php echo htmlspecialchars($sub['name']); ?></td>
                <td>
                    <a href="subject_edit.php?id=<?php echo $sub['id']; ?>">Editar</a> |
                    <a href="subject_delete.php?id=<?php echo $sub['id']; ?>" onclick="return confirm('Excluir?');">Excluir</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p><a href="index.php">Voltar</a></p>
</div>
</body>
</html>
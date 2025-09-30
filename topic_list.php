<?php
session_start();
require 'config.php';
require 'classes/Topic.php';

// Autenticação
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); exit;
}

$topicModel = new Topic($conn);
$topics = $topicModel->getAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Assuntos</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>
<div class="container">
    <h2>Assuntos</h2>
    <p><a href="topic_add.php"><button>Adicionar Assunto</button></a></p>
    <table>
        <thead>
        <tr><th>ID</th><th>Matéria</th><th>Nome</th><th>Ações</th></tr>
        </thead>
        <tbody>
        <?php foreach ($topics as $t): ?>
            <tr>
                <td><?php echo $t['id']; ?></td>
                <td><?php echo htmlspecialchars($t['subject_name']); ?></td>
                <td><?php echo htmlspecialchars($t['name']); ?></td>
                <td>
                    <a href="topic_edit.php?id=<?php echo $t['id']; ?>">Editar</a> |
                    <a href="topic_delete.php?id=<?php echo $t['id']; ?>" onclick="return confirm('Tem certeza?');">Excluir</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p><a href="index.php">Voltar</a></p>
</div>
</body>
</html>
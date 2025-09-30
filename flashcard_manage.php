<?php
session_start();
require 'config.php';
require 'classes/Flashcard.php';
require 'classes/Subject.php';
require 'classes/Topic.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
// Load all flashcards for editing
$stmt = $conn->prepare(
    'SELECT f.id, f.question, f.answer, s.name AS subject, t.name AS topic
     FROM flashcards f
     JOIN subjects s ON f.subject_id = s.id
     JOIN topics t ON f.topic_id = t.id
     WHERE f.user_id = ?
     ORDER BY f.id DESC'
);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$flashcards = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Flashcards</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <h2>Gerenciar Flashcards</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Matéria</th>
                    <th>Assunto</th>
                    <th>Pergunta</th>
                    <th>Resposta</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($flashcards as $card): ?>
                <tr>
                    <td><?php echo $card['id']; ?></td>
                    <td><?php echo htmlspecialchars($card['subject']); ?></td>
                    <td><?php echo htmlspecialchars($card['topic']); ?></td>
                    <td><?php echo htmlspecialchars($card['question']); ?></td>
                    <td><?php echo htmlspecialchars($card['answer']); ?></td>
                    <td>
                        <a href="flashcard_edit.php?id=<?php echo $card['id']; ?>"><button>Editar</button></a>
                        <a href="flashcard_delete.php?id=<?php echo $card['id']; ?>" onclick="return confirm('Tem certeza que deseja excluir este flashcard?');"><button>Excluir</button></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p><a href="index.php">Voltar</a></p>
    </div>
</body>
</html>
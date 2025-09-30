<?php
session_start();
require 'config.php';
// Verify flashcards table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'flashcards'");
if (!$tableCheck || $tableCheck->num_rows === 0) {
    die("Erro: tabela 'flashcards' não encontrada. Importe 'database/flashcards_schema.sql'.");
}
require 'classes/Subject.php';
require 'classes/Topic.php';
require 'classes/Flashcard.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Determine navigation state: subject -> topic -> flashcards
$subjectId = isset($_GET['subject_id']) ? intval($_GET['subject_id']) : null;
$topicId   = isset($_GET['topic_id'])   ? intval($_GET['topic_id'])   : null;
$subjectModel   = new Subject($conn);
$topicModel     = new Topic($conn);
$flashcardModel = new Flashcard($conn);
if ($topicId) {
    // Load flashcards for this subject/topic combination
    $stmt = $conn->prepare(
        'SELECT f.id, f.question, f.answer, s.name AS subject, t.name AS topic
         FROM flashcards f
         JOIN subjects s ON f.subject_id = s.id
         JOIN topics t ON f.topic_id = t.id
         WHERE f.user_id = ? AND f.subject_id = ? AND f.topic_id = ?
         ORDER BY f.id DESC'
    );
    $stmt->bind_param('iii', $_SESSION['user_id'], $subjectId, $topicId);
    $stmt->execute();
    $result     = $stmt->get_result();
    $flashcards = $result->fetch_all(MYSQLI_ASSOC);
} elseif ($subjectId) {
    // Load topics for this subject
    $topics = $topicModel->getAll($subjectId);
} else {
    // Load subjects for user
    $subjects = $subjectModel->getAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flashcards</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/flashcards.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <?php if (empty($subjectId)): ?>
            <h2>Selecione uma Matéria</h2>
            <div class="list-subjects">
                <?php foreach ($subjects as $sub): ?>
                    <a href="flashcards.php?subject_id=<?php echo $sub['id']; ?>"><button><?php echo htmlspecialchars($sub['name']); ?></button></a>
                <?php endforeach; ?>
            </div>
        <?php elseif (empty($topicId)): ?>
            <?php $subj = $subjectModel->getById($subjectId); ?>
            <h2>Matéria: <?php echo htmlspecialchars($subj['name']); ?></h2>
            <div class="list-topics">
                <?php foreach ($topics as $top): ?>
                    <div class="topic-item">
                        <a href="flashcards.php?subject_id=<?php echo $subjectId; ?>&topic_id=<?php echo $top['id']; ?>"><button><?php echo htmlspecialchars($top['name']); ?></button></a>
                        <a href="flashcard_add.php?subject_id=<?php echo $subjectId; ?>&topic_id=<?php echo $top['id']; ?>"><button class="add-flash-btn">Adicionar Flashcard</button></a>
                    </div>
                <?php endforeach; ?>
            </div>
            <p><a href="flashcards.php"><button>Voltar ao início</button></a></p>
        <?php else: ?>
            <?php
                $t = $topicModel->getById($topicId);
                if (!$t) {
                    // Topic not found, redirect to topic list
                    header('Location: flashcards.php?subject_id=' . $subjectId);
                    exit;
                }
            ?>
            <h2>Assunto: <?php echo htmlspecialchars($t['name']); ?></h2>
            <!-- Mode selector for review modes -->
            <div class="mode-selector">
                <button id="modeFlash" class="mode-btn active" type="button">Flashcards</button>
                <button id="modeQuiz" class="mode-btn" type="button">Quiz</button>
            </div>
            <!-- Quiz container (multiple choice) -->
            <div id="quizContainer" style="display: none;"></div>
            <div class="flashcards-controls">
                <!-- Apenas edição em massa deste assunto -->
                <a href="flashcard_batch_edit.php?subject_id=<?php echo $subjectId; ?>&topic_id=<?php echo $topicId; ?>">
                    <button>Editar Este Assunto</button>
                </a>
            </div>
            <?php if (count($flashcards) > 1): ?>
            <div class="flashcards-controls">
                <button id="prevCard" type="button">Anterior</button>
                <span id="cardCounter"></span>
                <button id="nextCard" type="button">Próximo</button>
            </div>
            <?php endif; ?>
            <div class="flashcards-container">
                <?php foreach ($flashcards as $card): ?>
                    <div class="flashcard" data-id="<?php echo $card['id']; ?>">
                        <div class="flashcard-inner">
                            <div class="flashcard-side flashcard-front">
                                <h3><?php echo $card['question']; ?></h3>
                            </div>
                            <div class="flashcard-side flashcard-back">
                                <h3><?php echo $card['answer']; ?></h3>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <p><a href="flashcards.php?subject_id=<?php echo $subjectId; ?>"><button>Voltar aos Assuntos</button></a></p>
        <?php endif; ?>
        <!-- Pass flashcards data to JS -->
        <script>
            const flashcardsData = <?php echo json_encode($flashcards, JSON_HEX_TAG); ?>;
        </script>
    <script src="js/flashcards.js"></script>
</body>
</html>
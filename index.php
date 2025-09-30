<?php
session_start();
require 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FlashCard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <?php if (isset($_SESSION['user_id'])): ?>
            <h2>Olá, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
            <?php
            // Quiz statistics
            $stmt = $conn->prepare('SELECT COALESCE(SUM(is_correct),0) AS correct, COALESCE(COUNT(*)-SUM(is_correct),0) AS incorrect FROM quiz_attempts WHERE user_id = ?');
            $stmt->bind_param('i', $_SESSION['user_id']);
            $stmt->execute(); $res = $stmt->get_result()->fetch_assoc();
            // Flashcards and errors per subject
            $subStats = $conn->prepare(
                'SELECT s.name,
                        COUNT(f.id) AS total,
                        COALESCE(SUM(CASE WHEN qa.is_correct = 0 THEN 1 ELSE 0 END),0) AS errors
                 FROM subjects s
                 LEFT JOIN topics t ON t.subject_id = s.id
                 LEFT JOIN flashcards f ON f.topic_id = t.id AND f.user_id = ?
                 LEFT JOIN quiz_attempts qa ON qa.flashcard_id = f.id AND qa.user_id = ?
                 GROUP BY s.id'
            );
            $subStats->bind_param('ii', $_SESSION['user_id'], $_SESSION['user_id']);
            $subStats->execute();
            $subsResult = $subStats->get_result();
            $subsArr = $subsResult->fetch_all(MYSQLI_ASSOC);
            // Flashcards per topic
            $topStats = $conn->prepare('SELECT t.name, COUNT(f.id) AS total FROM topics t LEFT JOIN flashcards f ON f.topic_id = t.id AND f.user_id = ? GROUP BY t.id');
            $topStats->bind_param('i', $_SESSION['user_id']); $topStats->execute();
            $topsResult = $topStats->get_result();
            $topsArr = $topsResult->fetch_all(MYSQLI_ASSOC);
            ?>
            <h2>Dashboard</h2>
            <div class="dashboard">
                <div class="card">
                    <h3>Quiz Statistics</h3>
                    <p>Acertos: <?php echo $res['correct']; ?></p>
                    <p>Erros: <?php echo $res['incorrect']; ?></p>
                </div>
                <div class="card">
                        <h3>Erros por Assunto</h3>
                        <table>
                        <?php foreach($subsArr as $row): ?>
                            <tr><td><?php echo htmlspecialchars($row['name']); ?></td><td><?php echo $row['errors']; ?></td></tr>
                        <?php endforeach; ?>
                        </table>
                    </div>
                <div class="card">
                    <h3>Flashcards por Assunto</h3>
                    <table>
                    <?php foreach($topsArr as $row): ?>
                        <tr><td><?php echo htmlspecialchars($row['name']); ?></td><td><?php echo $row['total']; ?></td></tr>
                    <?php endforeach; ?>
                    </table>
                </div>
            </div>
            <div class="charts">
                <div class="chart-card"><canvas id="quizChart"></canvas></div>
                <div class="chart-card"><canvas id="subChart"></canvas></div>
                <div class="chart-card"><canvas id="topChart"></canvas></div>
            </div>
        <?php else: ?>
            <h2>Bem-vindo ao FlashCard</h2>
        <?php endif; ?>
    </div>
    <!-- Chart.js library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Quiz pie chart
        new Chart(document.getElementById('quizChart'), {
            type: 'pie',
            data: {
                labels: ['Acertos','Erros'],
                datasets: [{ data: [<?php echo $res['correct']; ?>, <?php echo $res['incorrect']; ?>], backgroundColor: ['#2ecc71','#e74c3c'] }]
            }
        });
        // Subjects bar chart
        new Chart(document.getElementById('subChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($subsArr, 'name')); ?>,
                datasets: [{ label: 'Flashcards por Matéria', data: <?php echo json_encode(array_column($subsArr, 'total')); ?>, backgroundColor: '#3498db' }]
            }
        });
        // Topics bar chart
        new Chart(document.getElementById('topChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($topsArr, 'name')); ?>,
                datasets: [{ label: 'Flashcards por Assunto', data: <?php echo json_encode(array_column($topsArr, 'total')); ?>, backgroundColor: '#9b59b6' }]
            }
        });
    });
    </script>
</body>
</html>
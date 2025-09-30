<?php
// flashcard_import.php: importação de flashcards via arquivo .txt
session_start();
require 'config.php';
require 'classes/Flashcard.php';

// Redireciona se não estiver logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$errors = [];
$successCount = 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['flashcard_file']) && $_FILES['flashcard_file']['error'] === UPLOAD_ERR_OK) {
        $tmpPath = $_FILES['flashcard_file']['tmp_name'];
        $ext = pathinfo($_FILES['flashcard_file']['name'], PATHINFO_EXTENSION);
        if (strtolower($ext) === 'txt') {
            $content = file_get_contents($tmpPath);
            // Função para parsear texto em flashcards: bloco separado por linhas em branco
            $blocks = preg_split('/\R{2,}/', trim($content));
            $model = new Flashcard($conn);
            foreach ($blocks as $block) {
                // Cada bloco: primeira linha Pergunta:..., segunda Resposta:...
                $lines = preg_split('/\R/', trim($block));
                if (count($lines) >= 2) {
                    $q = preg_replace('/^Pergunta:\s*/i', '', $lines[0]);
                    $a = preg_replace('/^Resposta:\s*/i', '', $lines[1]);
                    if ($q !== '' && $a !== '') {
                        if ($model->create($_SESSION['user_id'], intval($_POST['subject_id'] ?? 0), intval($_POST['topic_id'] ?? 0), $q, $a)) {
                            $successCount++;
                        } else {
                            $errors[] = "Falha ao salvar flashcard: $q";
                        }
                    }
                }
            }
        } else {
            $errors[] = 'Somente arquivos .txt são permitidos.';
        }
    } else {
        $errors[] = 'Erro no upload do arquivo.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Importar Flashcards</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <h2>Importar Flashcards via TXT</h2>
        <div class="info">
            <p>O arquivo .txt deve seguir o formato abaixo, com blocos de Pergunta e Resposta separados por linha em branco:</p>
            <pre>
Pergunta: Qual é a capital do Brasil?
Resposta: Brasília

Pergunta: Qual é a cor do céu?
Resposta: Azul
            </pre>
        </div>
        <?php if ($errors): ?>
            <div class="error"><?php echo implode('<br>', $errors); ?></div>
        <?php elseif ($_SERVER['REQUEST_METHOD']==='POST'): ?>
            <div class="success"><?php echo "Importados: $successCount flashcards."; ?></div>
        <?php endif; ?>
        <form action="" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label>Arquivo TXT</label>
                <input type="file" name="flashcard_file" accept=".txt" required>
            </div>
            <!-- Seleção de matéria e assunto existentes -->
            <div class="form-group">
                <label>Matéria</label>
                <select name="subject_id">
                    <?php
                    $subs = $conn->query('SELECT id,name FROM subjects')->fetch_all(MYSQLI_ASSOC);
                    foreach ($subs as $s) {
                        echo "<option value=\"{$s['id']}\">".htmlspecialchars($s['name'])."</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label>Assunto</label>
                <select name="topic_id">
                    <?php
                    $tops = $conn->query('SELECT id,name FROM topics')->fetch_all(MYSQLI_ASSOC);
                    foreach ($tops as $t) {
                        echo "<option value=\"{$t['id']}\">".htmlspecialchars($t['name'])."</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit">Importar</button>
        </form>
    </div>
</body>
</html>
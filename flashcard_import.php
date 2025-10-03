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

        // Apenas arquivos .txt são permitidos
        if (strtolower($ext) === 'txt') {

            // Lê o conteúdo do arquivo
            $content = file_get_contents($tmpPath);
            // Função para parsear texto em flashcards: bloco separado por linhas em branco
            $blocks = preg_split('/\R{2,}/', trim($content));

            // ==== Resolver matéria e assunto antes de criar flashcards ====
            $model = new Flashcard($conn);
            $rawSubject   = $_POST['subject_id'] ?? '';
            $rawTopic     = $_POST['topic_id'] ?? '';
            $newSubjectIn = trim($_POST['new_subject'] ?? '');
            $newTopicIn   = trim($_POST['new_topic'] ?? '');

            $subjectId = null;
            $topicId   = null;

            // Criar ou validar matéria
            if ($rawSubject === 'nova materia') {
                if ($newSubjectIn === '') {
                    $errors[] = 'Informe o nome da nova matéria.';
                } else {
                    $stmt = $conn->prepare('INSERT INTO subjects (name) VALUES (?)');
                    $stmt->bind_param('s', $newSubjectIn);
                    if ($stmt->execute()) {
                        $subjectId = $conn->insert_id;
                    } else {
                        $errors[] = 'Erro ao criar nova matéria (talvez já exista).';
                    }
                }
            } elseif ($rawSubject !== '' && ctype_digit($rawSubject)) {
                $subjectId = (int)$rawSubject;
            } else {
                $errors[] = 'Selecione uma matéria válida.';
            }

            // Criar ou validar assunto (depende da matéria válida)
            if (empty($errors)) {
                if ($rawTopic === 'novo assunto') {
                    if ($newTopicIn === '') {
                        $errors[] = 'Informe o nome do novo assunto.';
                    } elseif (!$subjectId) {
                        $errors[] = 'Matéria inválida para criar assunto.';
                    } else {
                        $stmt = $conn->prepare('INSERT INTO topics (name, subject_id) VALUES (?, ?)');
                        $stmt->bind_param('si', $newTopicIn, $subjectId);
                        if ($stmt->execute()) {
                            $topicId = $conn->insert_id;
                        } else {
                            $errors[] = 'Erro ao criar novo assunto (talvez já exista).';
                        }
                    }
                } elseif ($rawTopic !== '' && ctype_digit($rawTopic)) {
                    $topicId = (int)$rawTopic;
                } else {
                    $errors[] = 'Selecione um assunto válido.';
                }
            }

            // Criar flashcards somente se matéria e assunto válidos
            if (empty($errors) && $subjectId && $topicId) {
                foreach ($blocks as $block) {
                    $lines = preg_split('/\R/', trim($block));
                    if (count($lines) >= 2) {
                        $q = preg_replace('/^Pergunta:\s*/i', '', $lines[0]);
                        $a = preg_replace('/^Resposta:\s*/i', '', $lines[1]);
                        if ($q !== '' && $a !== '') {
                            if ($model->create($_SESSION['user_id'], $subjectId, $topicId, $q, $a)) {
                                $successCount++;
                            } else {
                                $errors[] = 'Falha ao salvar flashcard: ' . htmlspecialchars($q);
                            }
                        } else {
                            $errors[] = 'Pergunta ou resposta vazia em um bloco.';
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
    <link rel="stylesheet" href="css/flashcard_import.css">
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
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <div class="success"><?php echo "Importados: $successCount flashcards."; ?></div>
        <?php endif; ?>





        <!-- Formulário -->
        <form action="" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label>Arquivo TXT</label>
                <input type="file" name="flashcard_file" accept=".txt" required>
            </div>
            <div class="form-group">
                <label>Matéria</label>
                <select name="subject_id" id="subject_select">
                    <option value="" selected>Selecione Matéria</option>
                    <?php
                    $subs = $conn->query('SELECT DISTINCT id,name FROM subjects')->fetch_all(MYSQLI_ASSOC);
                    foreach ($subs as $s) {
                        echo "<option value=\"{$s['id']}\">" . htmlspecialchars($s['name']) . "</option>";
                    }
                    ?>
                    <option value="nova materia">Nova Matéria</option>
                </select>
                <input type="text" name="new_subject" id="new_subject" placeholder="Digite o nome da nova matéria" style="display:none;">
            </div>
            <div class="form-group">
                <label>Assunto</label>
                <select name="topic_id" id="topicSelect">
                    <option value="" selected>Selecione Assunto</option>
                    <!-- preenchido via JS -->
                </select>
                <input type="text" name="new_topic" id="new_topic" placeholder="Digite o nome do novo assunto" style="display:none;">
            </div>
            <button type="submit">Importar</button>
        </form>

            <script src="./js/flashcards_imports.js"></script>
    </div>
</body>

</html>
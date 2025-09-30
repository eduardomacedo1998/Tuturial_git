<?php
// flashcard_add.php: página para adicionar múltiplos flashcards com seleção de matéria e assunto
session_start();
require 'config.php';
require 'classes/Flashcard.php';
require 'classes/Subject.php';
require 'classes/Topic.php';

// Redireciona se o usuário não estiver logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Carrega matérias e assuntos para seleção
$subjects = $conn->query('SELECT id, name FROM subjects')->fetch_all(MYSQLI_ASSOC);
$topics   = $conn->query('SELECT id, name, subject_id FROM topics')->fetch_all(MYSQLI_ASSOC);

$errors = [];
// Trata submissão do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lógica: lê arrays de perguntas e respostas submetidos pelo usuário
    $questions = $_POST['question'] ?? [];
    $answers   = $_POST['answer']   ?? [];

    // ----------- Processamento de Matéria -----------
    // Lógica: Verifica se o usuário selecionou criar nova Matéria ('new') ou escolheu uma existente
    $subjectRaw = $_POST['subject_id'] ?? '';
    if ($subjectRaw === 'new') {
        // Usuário solicitou criar nova Matéria: valida nome e insere no banco
        $newSubjectName = trim($_POST['new_subject_name'] ?? '');
        if (empty($newSubjectName)) {
            $errors[] = 'Nome da nova Matéria é obrigatório.';
        } else {
            $subjectModel = new Subject($conn);
            if ($subjectModel->create($newSubjectName)) {
                // Obtém o ID da nova matéria inserida
                $subjectId = $conn->insert_id;
            } else {
                $errors[] = 'Falha ao criar nova Matéria.';
            }
        }
    } else {
        // Usuário escolheu Matéria existente: converte para inteiro e valida
        $subjectId = intval($subjectRaw);
        if ($subjectId <= 0) {
            $errors[] = 'Matéria é obrigatória.';
        }
    }

    // ----------- Processamento de Assunto -----------
    // Lógica: Verifica se o usuário solicitou criar novo Assunto ('new') ou escolheu um existente
    $topicRaw = $_POST['topic_id'] ?? '';
    if ($topicRaw === 'new') {
        // Usuário solicitou criar novo Assunto: valida nome e insere no banco
        $newTopicName = trim($_POST['new_topic_name'] ?? '');
        if (empty($newTopicName)) {
            $errors[] = 'Nome do novo Assunto é obrigatório.';
        } else {
            $topicModel = new Topic($conn);
            if ($topicModel->create($subjectId, $newTopicName)) {
                // Obtém o ID do novo assunto inserido
                $topicId = $conn->insert_id;
            } else {
                $errors[] = 'Falha ao criar novo Assunto.';
            }
        }
    } else {
        // Usuário escolheu Assunto existente: converte para inteiro e valida
        $topicId = intval($topicRaw);
        if ($topicId <= 0) {
            $errors[] = 'Assunto é obrigatório.';
        }
    }

    // ----------- Salvando Flashcards -----------
    // Se não houver erros de validação, percorre cada par pergunta/resposta para salvar
    if (empty($errors)) {
        $model = new Flashcard($conn);
        foreach ($questions as $i => $q) {
            // Limpa espaços em branco nas extremidades
            $que = trim($q);
            $ans = trim($answers[$i] ?? '');
            // Se pergunta ou resposta estiverem vazias, ignora esta entrada
            if ($que === '' || $ans === '') {
                continue;
            }
            // Tenta salvar o flashcard no banco; registra erro com número da linha se falhar
            if (! $model->create($_SESSION['user_id'], $subjectId, $topicId, $que, $ans)) {
                $errors[] = "Falha ao salvar flashcard na linha " . ($i + 1);
            }
        }
        // Se nenhum erro adicional, redireciona para lista de flashcards
        if (empty($errors)) {
            header('Location: flashcards.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Flashcard</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/flashcards.css">
    <link rel="stylesheet" href="css/flashcard_add.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <h2>Adicionar Flashcard</h2>
        <?php if ($errors): ?>
            <div class="error"><?php echo implode('<br>', $errors); ?></div>
        <?php endif; ?>
        <form action="" method="post">
            <div class="form-group">
                <label for="subject_id">Matéria</label>
                <!-- Dropdown de seleção de Matéria -->
                <select name="subject_id" id="subjectSelect">
                    <option value="">Selecione a Matéria</option>
                    <?php foreach ($subjects as $sub): ?>
                        <option value="<?php echo $sub['id']; ?>"><?php echo htmlspecialchars($sub['name']); ?></option>
                    <?php endforeach; ?>
                    <!-- Opção para criar nova Matéria -->
                    <option value="new">Nova Matéria</option>
                </select>
            </div>
            <div class="form-group" id="newSubjectGroup" style="display:none;">
                <label>Nome da Nova Matéria</label>
                <input type="text" name="new_subject_name">
            </div>
            <div class="form-group">
                <label for="topic_id">Assunto</label>
                <!-- Dropdown de seleção de Assunto -->
                <select name="topic_id" id="topicSelect">
                    <option value="">Selecione o Assunto</option>
                    <?php foreach ($topics as $top): ?>
                        <option value="<?php echo $top['id']; ?>"><?php echo htmlspecialchars($top['name']); ?></option>
                    <?php endforeach; ?>
                    <!-- Opção para criar novo Assunto -->
                    <option value="new">Novo Assunto</option>
                </select>
            </div>
            <div class="form-group" id="newTopicGroup" style="display:none;">
                <label>Nome do Novo Assunto</label>
                <input type="text" name="new_topic_name">
            </div>
            <!-- Entradas de flashcard dinâmicas -->
            <div id="entries">
                <div class="entry">
                    <button type="button" class="remove-entry" title="Excluir">×</button>
                    <div class="form-group">
                        <label>Pergunta</label>
                        <!-- Visual editor: contenteditable div -->
                        <div class="editable" contenteditable="true"></div>
                        <!-- Hidden textarea to hold HTML content for submission -->
                        <textarea name="question[]" class="hidden-input"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Resposta</label>
                        <div class="editable" contenteditable="true"></div>
                        <textarea name="answer[]" class="hidden-input"></textarea>
                    </div>
                </div>
            </div>
            <button type="button" id="addEntry">Adicionar mais</button>
            <button type="submit">Salvar</button>
        </form>
    </div>
    <script src="js/flashcards.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const entriesContainer = document.getElementById('entries');
        // Cria barra de ferramentas para div editável com Negrito, Lista e Cor
        function createToolbar(editable) {
            const toolbar = document.createElement('div');
            toolbar.className = 'text-toolbar';
            ['Bold', 'List', 'Color', 'Font', 'Center'].forEach(action => {
                if (action === 'Color') {
                    const colorInput = document.createElement('input');
                    colorInput.type = 'color';
                    colorInput.title = 'Color';
                    colorInput.addEventListener('input', () => {
                        const sel = window.getSelection();
                        if (!sel.rangeCount) return;
                        const range = sel.getRangeAt(0);
                        if (!editable.contains(range.commonAncestorContainer)) return;
                        const fragment = range.cloneContents();
                        const temp = document.createElement('div');
                        temp.appendChild(fragment);
                        const contentHtml = temp.innerHTML;
                        const wrapped = '<span style="color:' + colorInput.value + ';">' + contentHtml + '</span>';
                        range.deleteContents();
                        const frag = document.createRange().createContextualFragment(wrapped);
                        range.insertNode(frag);
                    });
                    toolbar.appendChild(colorInput);
                } else if (action === 'Font') {
                    const fontSelect = document.createElement('select');
                    fontSelect.title = 'Font';
                    const fonts = ['Arial', 'Georgia', 'Courier New', 'Times New Roman', 'Verdana'];
                    fonts.forEach(f => {
                        const opt = document.createElement('option');
                        opt.value = f;
                        opt.textContent = f;
                        fontSelect.appendChild(opt);
                    });
                    fontSelect.addEventListener('change', () => {
                        const sel = window.getSelection();
                        if (!sel.rangeCount) return;
                        const range = sel.getRangeAt(0);
                        if (!editable.contains(range.commonAncestorContainer)) return;
                        const fragment = range.cloneContents();
                        const temp = document.createElement('div');
                        temp.appendChild(fragment);
                        const contentHtml = temp.innerHTML;
                        const wrapped = '<span style="font-family:' + fontSelect.value + ';">' + contentHtml + '</span>';
                        range.deleteContents();
                        const frag = document.createRange().createContextualFragment(wrapped);
                        range.insertNode(frag);
                    });
                    toolbar.appendChild(fontSelect);
                } else if (action === 'Center') {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.textContent = 'Center';
                    btn.title = 'Center Text';
                    btn.addEventListener('click', () => {
                        const sel = window.getSelection();
                        if (!sel.rangeCount) return;
                        const range = sel.getRangeAt(0);
                        if (!editable.contains(range.commonAncestorContainer)) return;
                        const fragment = range.cloneContents();
                        const temp = document.createElement('div');
                        temp.appendChild(fragment);
                        const contentHtml = temp.innerHTML;
                        const wrapped = '<div style="text-align:center;">' + contentHtml + '</div>';
                        range.deleteContents();
                        const frag = document.createRange().createContextualFragment(wrapped);
                        range.insertNode(frag);
                    });
                    toolbar.appendChild(btn);
                } else {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.textContent = action;
                    btn.addEventListener('click', () => {
                        const sel = window.getSelection();
                        if (!sel.rangeCount) return;
                        const range = sel.getRangeAt(0);
                        if (!editable.contains(range.commonAncestorContainer)) return;
                        const fragment = range.cloneContents();
                        const temp = document.createElement('div');
                        temp.appendChild(fragment);
                        const contentHtml = temp.innerHTML;
                        let wrapped = contentHtml;
                        if (action === 'Bold') {
                            wrapped = '<strong>' + contentHtml + '</strong>';
                        } else if (action === 'List') {
                            const lines = contentHtml.split(/<br>|\n/).filter(l=>l.trim());
                            const items = lines.map(l=>'<li>'+l+'</li>').join('');
                            wrapped = '<ul>' + items + '</ul>';
                        }
                        range.deleteContents();
                        const frag = document.createRange().createContextualFragment(wrapped);
                        range.insertNode(frag);
                    });
                    toolbar.appendChild(btn);
                }
            });
            editable.parentNode.insertBefore(toolbar, editable);
        }
        // Attach toolbar to all editable divs in an entry
        function attachToolbar(entry) {
            entry.querySelectorAll('.editable').forEach(createToolbar);
        }
        // Initial entries
        document.querySelectorAll('.entry').forEach(attachToolbar);
        // Add new entry
        document.getElementById('addEntry').addEventListener('click', function() {
            const first = entriesContainer.querySelector('.entry');
            const clone = first.cloneNode(true);
            // Clear hidden textarea inputs
            clone.querySelectorAll('textarea').forEach(ta => ta.value = '');
            // Clear contenteditable divs
            clone.querySelectorAll('.editable').forEach(ed => ed.innerHTML = '');
            // Remove any cloned toolbars
            clone.querySelectorAll('.text-toolbar').forEach(tb => tb.remove());
            entriesContainer.appendChild(clone);
            attachToolbar(clone);
        });
        // Remove entry
        entriesContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-entry')) {
                const entry = e.target.closest('.entry');
                if (entry) entry.remove();
            }
        });
        // Toggle new subject field
        const subjectSelect = document.getElementById('subjectSelect');
        const newSubjectGroup = document.getElementById('newSubjectGroup');
        subjectSelect.addEventListener('change', function() {
            newSubjectGroup.style.display = this.value === 'new' ? 'block' : 'none';
        });
        // Toggle new topic field
        const topicSelect = document.getElementById('topicSelect');
        const newTopicGroup = document.getElementById('newTopicGroup');
        topicSelect.addEventListener('change', function() {
            newTopicGroup.style.display = this.value === 'new' ? 'block' : 'none';
        });
        // Before submitting, copy editable content into hidden textareas
        const multiForm = document.getElementById('multiFlashForm');
        multiForm.addEventListener('submit', function() {
            document.querySelectorAll('#entries .entry').forEach(entry => {
                const editables = entry.querySelectorAll('.editable');
                const textareas = entry.querySelectorAll('textarea');
                if (editables[0] && textareas[0]) textareas[0].value = editables[0].innerHTML;
                if (editables[1] && textareas[1]) textareas[1].value = editables[1].innerHTML;
            });
        });
    });
    </script>
</body>
</html>
<?php
session_start();
require 'config.php';
require 'classes/Flashcard.php';
require 'classes/Subject.php';
require 'classes/Topic.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); exit;
}

$subjectId = isset($_GET['subject_id']) ? intval($_GET['subject_id']) : 0;
$topicId = isset($_GET['topic_id']) ? intval($_GET['topic_id']) : 0;

$subjectModel = new Subject($conn);
$topicModel = new Topic($conn);
$flashcardModel = new Flashcard($conn);

$subject = $subjectModel->getById($subjectId);
$topic = $topicModel->getById($topicId);
if (!$subject || !$topic) {
    header('Location: flashcards.php'); exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ids = $_POST['id'] ?? [];
    $questions = $_POST['question'] ?? [];
    $answers = $_POST['answer'] ?? [];

    foreach ($ids as $i => $fid) {
        $qid = intval($fid);
        $q = trim($questions[$i]);
        $a = trim($answers[$i]);
        if ($q === '' || $a === '') continue;
        $flashcardModel->update($qid, $subjectId, $topicId, $q, $a);
    }
    header("Location: flashcards.php?subject_id={$subjectId}&topic_id={$topicId}");
    exit;
}

// Load flashcards
$flashcards = $flashcardModel->getAllByUser($_SESSION['user_id']);
// Filter by subject and topic
$filtered = array_filter($flashcards, function($c) use($subjectId, $topicId) {
    return $c['subject_id'] == $subjectId && $c['topic_id'] == $topicId;
});
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Batch Edit Flashcards</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/flashcards.css">
    <!-- Entry card styling from flashcard_add -->
    <style>
        /* Entry cards styling */
        #entries { margin-bottom: 1rem; }
        .entry { background: #fff; border: 1px solid #ccc; padding: 1rem; margin-bottom: 1rem; position: relative; border-radius: 4px; }
        .entry .remove-entry { position: absolute; top: 8px; right: 8px; background: #e74c3c; border: none; color: #fff; font-size: 1.2rem; line-height: 1; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; }
        .entry .remove-entry:hover { background: #c0392b; }
        .entry .form-group { margin-bottom: 0.5rem; }
        /* Rich text editor controls */
        .hidden-input { display: none; }
        .editable {
            min-height: 80px;
            border: 1px solid #ccc;
            padding: 0.5rem;
            border-radius: 4px;
            background: #fff;
            overflow: auto;
        }
        .text-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }
        .text-toolbar button,
        .text-toolbar select,
        .text-toolbar input[type="color"] {
            padding: 0.25rem;
            font-size: 0.9rem;
            background: #4a90e2;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: auto;
        }
        .text-toolbar button:hover,
        .text-toolbar select:hover,
        .text-toolbar input[type="color"]:hover {
            background: #357ab8;
        }
        .text-toolbar input[type="color"] {
            width: 2rem;
            height: 2rem;
            padding: 0;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <h2>Editar Flashcards: <?php echo htmlspecialchars("{$subject['name']} - {$topic['name']}");?></h2>
        <?php if ($errors): ?><div class="error"><?php echo implode('<br>', $errors);?></div><?php endif;?>
    <form action="" method="post" id="batchEditForm">
            <div id="entries">
                <?php foreach ($filtered as $card): ?>
                <div class="entry">
                    <button type="button" class="remove-entry" title="Excluir">×</button>
                    <input type="hidden" name="id[]" value="<?php echo $card['id'];?>">
                    <div class="form-group">
                        <label>Pergunta</label>
                        <div class="editable" contenteditable="true"><?php echo $card['question'];?></div>
                        <textarea name="question[]" class="hidden-input"><?php echo $card['question'];?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Resposta</label>
                        <div class="editable" contenteditable="true"><?php echo $card['answer'];?></div>
                        <textarea name="answer[]" class="hidden-input"><?php echo $card['answer'];?></textarea>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="submit">Salvar Alterações</button>
            <a href="flashcards.php?subject_id=<?php echo $subjectId;?>&topic_id=<?php echo $topicId;?>"><button type="button">Cancelar</button></a>
        </form>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const entriesContainer = document.getElementById('entries');
        function createToolbar(editable) {
            const toolbar = document.createElement('div');
            toolbar.className = 'text-toolbar';
            ['Bold', 'List', 'Color', 'Font', 'Center'].forEach(action => {
                if (action === 'Color') {
                    const colorInput = document.createElement('input');
                    colorInput.type = 'color'; colorInput.title = 'Color';
                    colorInput.addEventListener('input', () => {
                        const sel = window.getSelection(); if (!sel.rangeCount) return;
                        const range = sel.getRangeAt(0);
                        if (!editable.contains(range.commonAncestorContainer)) return;
                        const fragment = range.cloneContents(); const temp = document.createElement('div'); temp.appendChild(fragment);
                        const wrapped = '<span style="color:' + colorInput.value + ';">' + temp.innerHTML + '</span>';
                        range.deleteContents(); const frag = document.createRange().createContextualFragment(wrapped);
                        range.insertNode(frag);
                    }); toolbar.appendChild(colorInput);
                } else if (action === 'Font') {
                    const fontSelect = document.createElement('select'); fontSelect.title = 'Font';
                    ['Arial','Georgia','Courier New','Times New Roman','Verdana'].forEach(f => { const opt = document.createElement('option'); opt.value = f; opt.textContent = f; fontSelect.appendChild(opt); });
                    fontSelect.addEventListener('change', () => {
                        const sel = window.getSelection(); if (!sel.rangeCount) return; const range = sel.getRangeAt(0);
                        if (!editable.contains(range.commonAncestorContainer)) return;
                        const temp = document.createElement('div'); temp.appendChild(range.cloneContents());
                        const wrapped = '<span style="font-family:' + fontSelect.value + ';">' + temp.innerHTML + '</span>';
                        range.deleteContents(); range.insertNode(document.createRange().createContextualFragment(wrapped));
                    }); toolbar.appendChild(fontSelect);
                } else if (action === 'Center') {
                    const btn = document.createElement('button'); btn.type='button'; btn.textContent='Center'; btn.title='Center Text';
                    btn.addEventListener('click', () => {
                        const sel = window.getSelection(); if (!sel.rangeCount) return; const range = sel.getRangeAt(0);
                        if (!editable.contains(range.commonAncestorContainer)) return;
                        const temp = document.createElement('div'); temp.appendChild(range.cloneContents());
                        const wrapped = '<div style="text-align:center;">' + temp.innerHTML + '</div>';
                        range.deleteContents(); range.insertNode(document.createRange().createContextualFragment(wrapped));
                    }); toolbar.appendChild(btn);
                } else {
                    const btn = document.createElement('button'); btn.type='button'; btn.textContent=action;
                    btn.addEventListener('click', () => {
                        const sel = window.getSelection(); if (!sel.rangeCount) return; const range = sel.getRangeAt(0);
                        if (!editable.contains(range.commonAncestorContainer)) return;
                        const temp = document.createElement('div'); temp.appendChild(range.cloneContents());
                        let wrapped = temp.innerHTML;
                        if (action === 'Bold') wrapped = '<strong>' + wrapped + '</strong>';
                        else if (action === 'List') {
                            const items = wrapped.split(/<br>|\n/).filter(l=>l.trim()).map(l=>'<li>'+l+'</li>').join(''); wrapped = '<ul>' + items + '</ul>';
                        }
                        range.deleteContents(); range.insertNode(document.createRange().createContextualFragment(wrapped));
                    }); toolbar.appendChild(btn);
                }
            }); editable.parentNode.insertBefore(toolbar, editable);
        }
        function attachToolbar(entry) { entry.querySelectorAll('.editable').forEach(createToolbar); }
        document.querySelectorAll('.entry').forEach(attachToolbar);
        // Remove entry
        entriesContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-entry')) e.target.closest('.entry').remove();
        });
        // Before submit, copy content
        document.getElementById('batchEditForm').addEventListener('submit', function() {
            entriesContainer.querySelectorAll('.entry').forEach(entry => {
                const ed = entry.querySelectorAll('.editable'); const ta = entry.querySelectorAll('textarea');
                if (ed[0] && ta[0]) ta[0].value = ed[0].innerHTML;
                if (ed[1] && ta[1]) ta[1].value = ed[1].innerHTML;
            });
        });
    });
    </script>
</body>
</html>
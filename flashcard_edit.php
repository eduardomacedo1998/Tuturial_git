<?php
session_start();
require 'config.php';
require 'classes/Flashcard.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$flashcardModel = new Flashcard($conn);
$errors = [];

// Get flashcard by id
if (!isset($_GET['id'])) {
    header('Location: flashcards.php');
    exit;
}
$id = intval($_GET['id']);
$card = $flashcardModel->getById($id);

// Ensure card exists and belongs to user
if (!$card) {
    header('Location: flashcards.php');
    exit;
}

// Load subjects and topics
$subjects = $conn->query('SELECT id, name FROM subjects')->fetch_all(MYSQLI_ASSOC);
$topics = $conn->query('SELECT id, name, subject_id FROM topics')->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = trim($_POST['question']);
    $answer = trim($_POST['answer']);
    $subject_id = intval($_POST['subject_id']);
    $topic_id = intval($_POST['topic_id']);

    if (empty($question)) {
        $errors[] = 'Question is required.';
    }
    if (empty($answer)) {
        $errors[] = 'Answer is required.';
    }

    if (empty($errors)) {
        if ($flashcardModel->update($id, $question, $answer, $subject_id, $topic_id)) {
            header('Location: flashcards.php');
            exit;
        } else {
            $errors[] = 'Failed to update flashcard.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Flashcard</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/flashcards.css">
    <!-- Rich text editor styles -->
    <style>
        .hidden-input { display: none; }
        .editable { min-height: 80px; border: 1px solid #ccc; padding: 0.5rem; border-radius: 4px; background: #fff; overflow: auto; }
        .text-toolbar { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; }
        .text-toolbar button, .text-toolbar select, .text-toolbar input[type="color"] { padding: 0.25rem; font-size: 0.9rem; background: #4a90e2; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        .text-toolbar button:hover, .text-toolbar select:hover, .text-toolbar input[type="color"]:hover { background: #357ab8; }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <h2>Edit Flashcard</h2>
        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form action="" method="post" id="singleEditForm">
            <div class="form-group">
                <label for="question">Pergunta</label>
                <div class="text-toolbar" id="toolbarQuestion"></div>
                <div class="editable" id="editableQuestion" contenteditable="true"><?php echo $card['question']; ?></div>
                <textarea id="question" name="question" class="hidden-input"><?php echo $card['question']; ?></textarea>
            </div>
            <div class="form-group">
                <label for="answer">Resposta</label>
                <div class="text-toolbar" id="toolbarAnswer"></div>
                <div class="editable" id="editableAnswer" contenteditable="true"><?php echo $card['answer']; ?></div>
                <textarea id="answer" name="answer" class="hidden-input"><?php echo $card['answer']; ?></textarea>
            </div>

            <label for="subject_id">Matéria</label>
            <select id="subject_id" name="subject_id">
                <option value="">Selecione a Matéria</option>
                <?php foreach ($subjects as $sub): ?>
                    <option value="<?php echo $sub['id']; ?>" <?php echo ($card['subject_id'] == $sub['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($sub['name']); ?></option>
                <?php endforeach; ?>
            </select>

            <label for="topic_id">Assunto</label>
            <select id="topic_id" name="topic_id">
                <option value="">Selecione o Assunto</option>
                <?php foreach ($topics as $top): ?>
                    <?php if ($top['subject_id'] == $card['subject_id']): ?>
                        <option value="<?php echo $top['id']; ?>" <?php echo ($card['topic_id'] == $top['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($top['name']); ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>

            <button type="submit">Atualizar Flashcard</button>
        </form>
        <p><a href="flashcards.php">Back to Flashcards</a></p>
    </div>
    <script src="js/flashcards.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const controls = ['Bold','List','Color','Font','Center'];
        function createToolbar(toolbarId, editableId) {
            const toolbar = document.getElementById(toolbarId);
            const editable = document.getElementById(editableId);
            controls.forEach(action => {
                if (action === 'Color') {
                    const colorInput = document.createElement('input'); colorInput.type='color'; colorInput.title='Color';
                    colorInput.addEventListener('input', () => { const sel = window.getSelection(); if (!sel.rangeCount) return; const range=sel.getRangeAt(0); if (!editable.contains(range.commonAncestorContainer)) return; const temp=document.createElement('div'); temp.appendChild(range.cloneContents()); const wrapped='<span style="color:'+colorInput.value+';">'+temp.innerHTML+'</span>'; range.deleteContents(); range.insertNode(document.createRange().createContextualFragment(wrapped)); });
                    toolbar.appendChild(colorInput);
                } else if (action === 'Font') {
                    const select=document.createElement('select'); select.title='Font'; ['Arial','Georgia','Courier New','Times New Roman','Verdana'].forEach(f=>{const opt=document.createElement('option');opt.value=f;opt.textContent=f;select.appendChild(opt);});
                    select.addEventListener('change',()=>{const sel=window.getSelection(); if(!sel.rangeCount) return; const range=sel.getRangeAt(0); if(!editable.contains(range.commonAncestorContainer))return; const temp=document.createElement('div'); temp.appendChild(range.cloneContents()); const wrapped='<span style="font-family:'+select.value+';">'+temp.innerHTML+'</span>'; range.deleteContents(); range.insertNode(document.createRange().createContextualFragment(wrapped));});
                    toolbar.appendChild(select);
                } else {
                    const btn=document.createElement('button'); btn.type='button'; btn.textContent=action; btn.title=action;
                    btn.addEventListener('click',()=>{const sel=window.getSelection(); if(!sel.rangeCount)return; const range=sel.getRangeAt(0); if(!editable.contains(range.commonAncestorContainer))return; const temp=document.createElement('div'); temp.appendChild(range.cloneContents()); let wrapped=temp.innerHTML;
                        if(action==='Bold') wrapped='<strong>'+wrapped+'</strong>';
                        else if(action==='List'){ const items=wrapped.split(/<br>|\n/).filter(l=>l.trim()).map(l=>'<li>'+l+'</li>').join(''); wrapped='<ul>'+items+'</ul>';} 
                        else if(action==='Center') wrapped='<div style="text-align:center;">'+wrapped+'</div>';
                        range.deleteContents(); range.insertNode(document.createRange().createContextualFragment(wrapped));});
                    toolbar.appendChild(btn);
                }
            });
        }
        createToolbar('toolbarQuestion','editableQuestion');
        createToolbar('toolbarAnswer','editableAnswer');
        document.getElementById('singleEditForm').addEventListener('submit',function(){
            document.getElementById('question').value=document.getElementById('editableQuestion').innerHTML;
            document.getElementById('answer').value=document.getElementById('editableAnswer').innerHTML;
        });
    });
    </script>
</body>
</html>
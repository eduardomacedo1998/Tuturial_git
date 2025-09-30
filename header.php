<?php
// header.php: Cabeçalho compartilhado com menu de navegação
?>
<header class="site-header">
    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="flashcards.php">Meus Flashcards</a></li>
                <li><a href="flashcard_add.php">Adicionar Flashcards</a></li>
                <li><a href="flashcard_import.php">Importar Flashcards</a></li>
                <li><a href="flashcard_manage.php">Gerenciar Flashcards</a></li>
                <li><a href="subject_list.php">Matérias</a></li>
                <li><a href="topic_list.php">Assuntos</a></li>
                <li><a href="logout.php">Sair</a></li>
            <?php else: ?>
                <li><a href="login.php">Entrar</a></li>
                <li><a href="register.php">Registrar-se</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
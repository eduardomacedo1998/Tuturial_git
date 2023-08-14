<?php include_once "./recebrEnviar.php"; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Exemplo de exibição de dados</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>


<div class="search-container">
        <form method="POST" class="search-form">
            <label for="nome" class="search-label">Digite o nome do produto:</label>
            <div class="search-input-container">
                <input type="text" name="nome" id="nome" class="search-input">
                <button type="submit" class="search-button"  >Buscar</button>
            </div>
        </form>
    </div>

    <?php include_once "./tabela.php"; ?>
    

    <div id="div"></div>

    <script src="script.js"></script>

</body>
</html>


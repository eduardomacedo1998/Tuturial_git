<?php

include_once "./chave.php";


$usuarios = array(); // Inicializa a variável que irá armazenar os dados dos usuários

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["nome"]) && !empty($_POST["nome"])) {
        $nome = $_POST["nome"];

        // Faz a consulta SQL
        $sql = "SELECT id, nome FROM produtos WHERE nome = '$nome' ";
        $result = $conn->query($sql);

        // Verifica se há resultados
        if ($result->num_rows > 0) {
            // Loop para percorrer os resultados da consulta e armazená-los na variável
            while ($row = $result->fetch_assoc()) {
                $usuarios[] = $row;
            }
        }
    }
}
?>
<?php 
// Arquivo de conexão com o banco de dados
$servername = "localhost"; // Altere para o nome do servidor do banco de dados
$username = "root"; // Altere para o nome de usuário do banco de dados
$password = ""; // Altere para a senha do banco de dados
$dbname = "bancoprincipal"; // Altere para o nome do banco de dados que contém as tabelas "perguntas" e "respostas"

// Criação da conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica se ocorreu algum erro na conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

?>
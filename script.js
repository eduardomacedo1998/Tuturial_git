function verificarID() {
    const idElement = document.getElementById("id");
    const id = idElement.innerText;

    if (id === "2") {
        document.body.style.backgroundColor = "pink";
      } else if (id === "") {
        // Se o ID estiver vazio, faz alguma ação específica (se necessário)
        document.body.style.backgroundColor = "black";
        // Neste exemplo, não fazemos nada no caso de um ID vazio
      } else {
        // Se o ID não for igual a 2 nem estiver vazio, restaura a cor de fundo padrão (branco)
        document.body.style.backgroundColor = "green";
      }
    }

  document.addEventListener("DOMContentLoaded", verificarID);

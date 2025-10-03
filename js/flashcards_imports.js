// função para lidar com a criação de novas matérias e assuntos
document.getElementById('subject_select').addEventListener('change', function() {

    if (this.value === 'nova materia') {
        const input = document.getElementById('new_subject');
        input.style.display = 'inline';
        input.required = true;
    }
    else if (this.value !== 'nova materia') {
        const input = document.getElementById('new_subject');
        input.style.display = 'none';
    }           
});      

document.getElementById('topicSelect').addEventListener('change', function() {
    if (this.value === 'novo assunto') {
        const input = document.getElementById('new_topic');
        input.style.display = 'inline';
        input.required = true;
    }
});



// função para pegar o id da materia e buscar os assuntos relacionados
document.getElementById('subject_select').addEventListener('change', function() {

    

    const subjectId = this.value;
    const topicSelect = document.getElementById('topicSelect');
    topicSelect.innerHTML = '<option value="">Carregando...</option>';

    if (subjectId === 'nova materia') {
        topicSelect.innerHTML = '<option value="novo assunto">Criar novo assunto</option>';

            const input = document.getElementById('new_topic');
            input.style.display = 'inline';
            input.required = true;

        return;
    }else if (subjectId !== 'nova materia') {

        const input = document.getElementById('new_topic');
        input.style.display = 'none';
        input.required = false;
    

    // Fazer uma requisição AJAX para buscar os tópicos relacionados à matéria
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `./js/buscas/buscar_assuntos_por_id.php?subject_id=${subjectId}`, true);
    xhr.onload = function() {
        console.log(this.responseText);
        if (this.status === 200) {

            //{"success":true,"data":[{"id":5,"name":"Classes de palavras"},{"id":2,"name":"Morfologia-adjetivos"}]}
            const response = JSON.parse(this.responseText);
            if (response.success) {
                const topics = response.data;
                let options = '<option value="">Selecione um assunto</option>';
                topics.forEach(topic => {
                    options += `<option value="${topic.id}">${topic.name}</option>`;
                });
                options += '<option value="novo assunto">Novo Assunto</option>';
                topicSelect.innerHTML = options;
            }
        } else {
            topicSelect.innerHTML = '<option value="">Erro ao carregar tópicos</option>';
        }
    };
    xhr.send();
}});
document.getElementById('topicSelect').addEventListener('change', function() {
    if (this.value !== 'novo assunto') {
        const input = document.getElementById('new_topic');
        const button = document.getElementById('btn_novo_assunto_import');
        button.style.display = 'none';
        input.style.display = 'none';
    }
});

// código do quiz
const modeFlash = document.getElementById('modeFlash');
const modeQuiz = document.getElementById('modeQuiz');
const quizContainer = document.getElementById('quizContainer');
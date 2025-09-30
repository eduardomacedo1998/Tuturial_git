document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.flashcard');
    let current = 0;
    function updateView() {
        cards.forEach((card, index) => {
            if (index === current) {
                card.style.display = 'block';
                card.style.transform = 'scale(1.1)';
                card.style.zIndex = '1';
            } else {
                card.style.display = 'none';
                card.style.transform = 'scale(1)';
                card.style.zIndex = '0';
            }
        });
        const counter = document.getElementById('cardCounter');
        if (counter) {
            counter.textContent = `${current + 1} / ${cards.length}`;
        }
    }
    // Initialize view
    if (cards.length > 0) {
        updateView();
    }
    // Prev/Next buttons
    const prevBtn = document.getElementById('prevCard');
    const nextBtn = document.getElementById('nextCard');
    if (prevBtn) prevBtn.addEventListener('click', () => {
        current = (current - 1 + cards.length) % cards.length;
        updateView();
    });
    if (nextBtn) nextBtn.addEventListener('click', () => {
        current = (current + 1) % cards.length;
        updateView();
    });
    // Flip behavior: click on visible card to flip
    cards.forEach(card => {
        card.addEventListener('click', () => {
            if (card.style.display === 'block') {
                // Toggle flipped class on card to match CSS rule
                card.classList.toggle('flipped');
            }
        });
    });
    // Mode switching and quiz logic
    const modeFlash = document.getElementById('modeFlash');
    const modeQuiz = document.getElementById('modeQuiz');
    const flashContainer = document.querySelector('.flashcards-container');
    const controlsContainers = document.querySelectorAll('.flashcards-controls');
    const quizContainer = document.getElementById('quizContainer');
    let quizQuestions = [];
    let quizIndex = 0;
    let score = 0;

    function shuffle(array) {
        for (let i = array.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [array[i], array[j]] = [array[j], array[i]];
        }
        return array;
    }

    function initQuiz() {
        quizQuestions = shuffle(flashcardsData.slice());
        quizIndex = 0;
        score = 0;
        flashContainer.style.display = 'none';
        controlsContainers.forEach(el => el.style.display = 'none');
        quizContainer.style.display = 'block';
        showQuestion();
    }

    function showQuestion() {
        // Preparar pergunta e escolhas pelo objeto completo
        const card = quizQuestions[quizIndex];
        // pegar outros cards para distratores
        const otherCards = flashcardsData.filter(c => c.id !== card.id);
        // construir escolhas: resposta correta + 3 distratores aleatórios
        const sample = shuffle(otherCards).slice(0,3);
        const choices = shuffle([card, ...sample]);
        // renderizar pergunta e botões com data-id
        quizContainer.innerHTML = `
            <div class="quiz-question">
                <h3>${card.question}</h3>
                <div class="quiz-choices">
                    ${choices.map((c, idx) =>
                        `<button class="choice-btn" data-id="${c.id}">
                            ${String.fromCharCode(65+idx)}. ${c.answer}
                        </button>`
                    ).join('')}
                </div>
            </div>
        `;
        quizContainer.querySelectorAll('.choice-btn').forEach(btn => {
            btn.addEventListener('click', () => handleAnswer(btn));
        });
    }

    function handleAnswer(btn) {
        // disable all buttons immediately to avoid multiple clicks
        quizContainer.querySelectorAll('.choice-btn').forEach(b => b.disabled = true);
        // verificar resposta por ID
        const selectedId = parseInt(btn.getAttribute('data-id'), 10);
        const correctId = quizQuestions[quizIndex].id;
        const isCorrect = (selectedId === correctId) ? 1 : 0;
        // registrar no servidor
        fetch('quiz_log.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `flashcard_id=${selectedId}&is_correct=${isCorrect}`
        }).catch(err => console.error('Log error:', err));
    // show visual feedback
    const feedback = document.createElement('p');
    feedback.className = 'quiz-feedback';
    feedback.textContent = isCorrect ? 'Correto!' : 'Errado!';
    quizContainer.appendChild(feedback);
    // restante do fluxo
        if (selectedId === correctId) {
            score++;
            btn.classList.add('correct');
        } else {
            btn.classList.add('wrong');
            quizContainer.querySelectorAll('.choice-btn').forEach(b => {
                if (parseInt(b.getAttribute('data-id'), 10) === correctId) {
                    b.classList.add('correct');
                }
            });
        }
        setTimeout(() => {
            quizIndex++;
            if (quizIndex < quizQuestions.length) showQuestion();
            else showResult();
        }, 1000);
    }

    function showResult() {
        quizContainer.innerHTML = `
            <div class="quiz-result">
                <h3>Você acertou ${score} de ${quizQuestions.length}</h3>
                <button id="retryQuiz">Tentar Novamente</button>
            </div>
        `;
        document.getElementById('retryQuiz').addEventListener('click', initQuiz);
    }

    modeFlash.addEventListener('click', () => {
        modeFlash.classList.add('active');
        modeQuiz.classList.remove('active');
        quizContainer.style.display = 'none';
        flashContainer.style.display = 'block';
        controlsContainers.forEach(el => el.style.display = 'flex');
    });

    modeQuiz.addEventListener('click', initQuiz);
});
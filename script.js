// Toggle between Login and Register forms
function toggleAuth(type) {
    if (type === 'register') {
        $('#login-form').hide();
        $('#register-form').fadeIn();
    } else {
        $('#register-form').hide();
        $('#login-form').fadeIn();
    }
}

function validateAuthForm(form) {
    const username = form.username.value.trim();
    const password = form.password.value.trim();
    if (!username || !password) {
        alert("Please fill in all fields.");
        return false;
    }
    return true;
}

let currentQuizData = [];
let quizTopic = "";
let quizDifficulty = "";

async function generateQuiz() {
    quizTopic = document.getElementById('topic').value.trim();
    quizDifficulty = document.getElementById('difficulty').value;

    if (!quizTopic) {
        alert("Please enter a topic.");
        return;
    }

    $('#quiz-setup').hide();
    $('#loading').fadeIn();

    const prompt = `Generate exactly 5 MCQ questions on the topic "${quizTopic}" with ${quizDifficulty} difficulty.
Return ONLY a valid JSON array, no extra text, no markdown, no explanation outside JSON.
Format:
[
  {
    "question": "",
    "options": ["A", "B", "C", "D"],
    "correct_answer": "A",
    "explanation": "Why this answer is correct"
  }
]`;

    try {
        const response = await fetch('https://api.groq.com/openai/v1/chat/completions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${GROQ_API_KEY}`
            },
            body: JSON.stringify({
                model: "llama-3.3-70b-versatile",
                messages: [{ role: "user", content: prompt }],
                temperature: 0.5
            })
        });

        if (!response.ok) throw new Error("API request failed");

        const data = await response.json();
        const content = data.choices[0].message.content;

        // Clean up markdown block if model accidentally adds it
        const cleanContent = content.replace(/```json/gi, '').replace(/```/g, '').trim();
        currentQuizData = JSON.parse(cleanContent);

        renderQuiz();
    } catch (error) {
        console.error("Error generating quiz:", error);
        alert("Failed to generate quiz. Please check your API key and try again.");
        $('#loading').hide();
        $('#quiz-setup').fadeIn();
    }
}

function renderQuiz() {
    $('#loading').hide();
    const wrapper = $('#questions-wrapper');
    wrapper.empty();

    $('#quiz-title').text(`Topic: ${quizTopic} (${quizDifficulty})`);

    currentQuizData.forEach((q, index) => {
        let optionsHtml = '';
        q.options.forEach((opt, optIndex) => {
            optionsHtml += `
                <label class="option-label">
                    <input type="radio" name="q${index}" value="${opt.replace(/"/g, '&quot;')}">
                    ${opt}
                </label>
            `;
        });

        const qHtml = `
            <div class="question-card" style="display: none;">
                <h4>${index + 1}. ${q.question}</h4>
                <div class="options">${optionsHtml}</div>
            </div>
        `;
        wrapper.append(qHtml);
    });

    $('#quiz-container').fadeIn();
    $('.question-card').each(function (i) {
        $(this).delay(i * 100).fadeIn(400);
    });
}

function evaluateQuiz() {
    let score = 0;
    const resultsWrapper = $('#results-wrapper');
    resultsWrapper.empty();

    // Check if all questions are answered
    for (let i = 0; i < currentQuizData.length; i++) {
        if (!$(`input[name="q${i}"]:checked`).val()) {
            alert(`Please answer question ${i + 1}.`);
            return;
        }
    }

    currentQuizData.forEach((q, index) => {
        const selected = $(`input[name="q${index}"]:checked`).val();
        const isCorrect = selected === q.correct_answer;
        if (isCorrect) score++;

        // Reconstruct the original options for the front of the card
        let optionsHtml = '';
        q.options.forEach((opt) => {
            const isChecked = opt === selected ? 'checked' : '';
            optionsHtml += `
                <label class="option-label">
                    <input type="radio" disabled ${isChecked}>
                    ${opt}
                </label>
            `;
        });

        const flipHtml = `
            <div class="flip-card">
                <div class="flip-card-inner" id="flip-${index}">
                    <div class="flip-card-front">
                        <div class="question-card">
                            <h4>${index + 1}. ${q.question}</h4>
                            <div class="options">${optionsHtml}</div>
                        </div>
                    </div>
                    <div class="flip-card-back">
                        <div class="question-card ${isCorrect ? 'result-correct' : 'result-wrong'}">
                            <h4>${index + 1}. ${q.question}</h4>
                            <p><strong>Your Answer:</strong> ${selected}</p>
                            ${!isCorrect ? `<p><strong>Correct Answer:</strong> ${q.correct_answer}</p>` : ''}
                            <div class="explanation">${q.explanation}</div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        resultsWrapper.append(flipHtml);
    });

    $('#final-score').text(`You scored ${score} / ${currentQuizData.length}`);

    $('#quiz-container').hide();
    $('#results-container').show(); // Show without slideDown to prep for flip

    // Trigger flip animations one by one
    $('.flip-card').each(function (i) {
        setTimeout(() => {
            $(this).addClass('flipped');
            if (i === currentQuizData.length - 1) {
                setTimeout(() => {
                    $('#final-score').fadeIn(800);
                }, 600); // show score after last card finishes flipping
            }
        }, i * 300); // 300ms delay between each flip
    });

    // Save attempt via AJAX
    $.post('save_attempt.php', {
        topic: quizTopic,
        difficulty: quizDifficulty,
        score: score,
        total: currentQuizData.length
    }, function (response) {
        if (!response.success) {
            console.error("Failed to save attempt", response.message);
        }
    }, 'json');
}

// Theme Toggle Logic
function toggleTheme() {
    const root = document.documentElement;
    root.classList.toggle('light-mode');
    const isLight = root.classList.contains('light-mode');
    localStorage.setItem('theme', isLight ? 'light' : 'dark');
    
    const btn = document.getElementById('theme-toggle');
    if (btn) {
        btn.innerHTML = isLight ? '☀️' : '🌙';
        btn.classList.add('rotate');
        setTimeout(() => btn.classList.remove('rotate'), 300);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const theme = localStorage.getItem('theme');
    if (theme === 'light') {
        document.documentElement.classList.add('light-mode');
        const btn = document.getElementById('theme-toggle');
        if(btn) btn.innerHTML = '☀️';
    }
});

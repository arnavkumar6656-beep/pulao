<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$username = htmlspecialchars($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pulao - Take a Quiz</title>
    <script>
        if (localStorage.getItem('theme') === 'light') {
            document.documentElement.classList.add('light-mode');
        }
    </script>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        const GROQ_API_KEY = "<?php echo GROQ_API_KEY; ?>";
    </script>
</head>

<body>
    <nav class="navbar">
        <div style="display: flex; align-items: center;">
            <a href="home.php" class="logo">Pulao</a>
            <button id="theme-toggle" onclick="toggleTheme()" title="Toggle Theme">🌙</button>
        </div>
        <div class="nav-links">
            <span>Welcome, <?php echo $username; ?></span>
            <a href="quiz.php">Quiz</a>
            <a href="attempts.php">Previous Attempts</a>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="card" id="quiz-setup">
            <h3>Generate AI Quiz</h3>
            <div class="form-group">
                <label>Topic</label>
                <input type="text" id="topic" placeholder="e.g., Python Basics, World War 2" required>
            </div>
            <div class="form-group">
                <label>Difficulty</label>
                <select id="difficulty">
                    <option value="Easy">Easy</option>
                    <option value="Medium">Medium</option>
                    <option value="Hard">Hard</option>
                </select>
            </div>
            <button class="btn" onclick="generateQuiz()">Generate Quiz</button>
        </div>

        <div class="loading-spinner" id="loading">
            <div class="spinner"></div>
            <p>Generating your quiz via Groq AI...</p>
        </div>

        <div id="quiz-container">
            <h2 id="quiz-title"></h2>
            <div id="questions-wrapper"></div>
            <button class="btn" id="submit-quiz" onclick="evaluateQuiz()">Submit Quiz</button>
        </div>

        <div id="results-container">
            <div class="final-score" id="final-score"></div>
            <div id="results-wrapper"></div>
            <button class="btn" onclick="location.reload()" style="margin-top: 2rem;">Take Another Quiz</button>
        </div>
    </div>

    <script>
        function toggleTheme() {
            const root = document.documentElement;
            root.classList.toggle('light-mode');
            const isLight = root.classList.contains('light-mode');
            localStorage.setItem('theme', isLight ? 'light' : 'dark');
            const btn = document.getElementById('theme-toggle');
            if (btn) {
                btn.innerHTML = isLight ? '☀️' : '🌙';
            }
        }
    </script>
    <script src="script.js"></script>
</body>

</html>
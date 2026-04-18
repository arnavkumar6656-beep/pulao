# Pulao — AI-Powered MCQ Quiz Platform

Pulao is a full-stack web application that generates AI-powered multiple choice quizzes on any topic. Users can register, log in, take quizzes at different difficulty levels, and track their performance over time on a personal dashboard with a global leaderboard.

---

## Features

- User authentication (register & login with hashed passwords)
- AI-generated MCQ questions using the Groq API
- Three difficulty levels — Easy, Medium, Hard
- Animated card flip reveal for quiz results
- Personal performance chart (score over time)
- Global leaderboard ranked by average score
- Previous attempts history page
- Dark / Light mode toggle with preference saved across sessions
- All attempts saved to MySQL database

---

## Tech Stack

| Layer | Technology |
|---|---|
| Frontend | HTML, CSS, JavaScript, jQuery |
| Backend | PHP |
| Database | MySQL |
| AI API | Groq API (llama-3.3-70b-versatile) |
| Charts | Chart.js |

---

## Requirements

Before running this project, make sure you have the following installed:

### Local Server Environment
You need a local server that supports PHP and MySQL. Choose one:
- **XAMPP** (recommended) — [Download here](https://www.apachefriends.org/)
- **Laragon** — [Download here](https://laragon.org/)
- **WAMP** — [Download here](https://www.wampserver.com/)

### Groq API Key
- Sign up for a free account at [console.groq.com](https://console.groq.com)
- Generate a free API key from the dashboard
- The free tier is generous and sufficient for this project

---

## Setup & Installation

### Step 1 — Clone or Download the Project

```bash
git clone https://github.com/yourusername/pulao
```

Or download the ZIP and extract it.

---

### Step 2 — Move to Web Server Root

Move the `pulao` folder into your local server's root directory:

- **XAMPP:** `C:/xampp/htdocs/pulao/`
- **Laragon:** `C:/laragon/www/pulao/`
- **WAMP:** `C:/wamp64/www/pulao/`

---

### Step 3 — Start Your Local Server

Open XAMPP Control Panel (or equivalent) and start:
- ✅ Apache
- ✅ MySQL

---

### Step 4 — Create the Database

1. Open your browser and go to `http://localhost/phpmyadmin`
2. Click **New** in the left sidebar
3. Name the database `pulao` and click **Create**
4. Select the `pulao` database
5. Click the **Import** tab
6. Click **Choose File** and select `database.sql` from the project folder
7. Click **Go** to import

This will create the required `users` and `attempts` tables automatically.

---

### Step 5 — Configure db.php

Open `db.php` in the project folder and update the following:

```php
$db_host = 'localhost';       // Leave as is
$db_user = 'root';            // Your MySQL username (default: root for XAMPP)
$db_pass = '';                // Your MySQL password (default: empty for XAMPP)
$db_name = 'pulao';           // Leave as is

define('GROQ_API_KEY', 'your_groq_api_key_here'); // Paste your Groq API key here
```

> ⚠️ Make sure your Groq API key is pasted between the single quotes with no extra spaces.

---

### Step 6 — Run the Application

Open your browser and navigate to:

```
http://localhost/pulao/
```

---

## Project Structure

```
/pulao
│
├── index.php           # Login & Register page
├── home.php            # Dashboard (leaderboard, chart, average score)
├── quiz.php            # Quiz generation and evaluation page
├── attempts.php        # Previous attempts history page
├── save_attempt.php    # AJAX endpoint to save quiz attempts
├── logout.php          # Session destroy and redirect
├── db.php              # Database connection + Groq API key config
├── style.css           # All styling (dark/light theme, animations)
├── script.js           # All frontend JS (quiz logic, flip animation, theme toggle)
├── database.sql        # SQL script to create tables
└── README.md           # This file
```

---

## Database Schema

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    topic VARCHAR(100) NOT NULL,
    difficulty VARCHAR(10) NOT NULL,
    score INT NOT NULL,
    total INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

---

## How to Use

1. **Register** a new account on the login page
2. **Log in** with your credentials
3. On the **Dashboard**, view your average score, performance chart, and the global leaderboard
4. Click **Quiz** in the navbar to go to the quiz page
5. Enter a **topic** (e.g., Python Basics, World War 2, One Piece)
6. Select a **difficulty** — Easy, Medium, or Hard
7. Click **Generate Quiz** and wait for the AI to generate 5 questions
8. Answer all 5 questions and click **Submit Quiz**
9. Watch the cards **flip** to reveal your results and explanations
10. View your updated stats on the **Dashboard** and your attempt history on **Previous Attempts**

---

## Key Implementation Details

### AI Integration
Questions are generated by calling the Groq API with a structured prompt that returns a strict JSON array of 5 MCQ objects, each containing the question, 4 options, the correct answer, and an explanation. The API call is made client-side using the Fetch API.

### Answer Evaluation
Answers are evaluated locally in JavaScript by comparing the user's selected option against the correct answer stored from the initial API response. No second API call is made for evaluation.

### Security
- Passwords are hashed using PHP's `password_hash()` with the BCRYPT algorithm
- All database queries use prepared statements to prevent SQL injection
- All pages check for an active PHP session and redirect to login if not authenticated
- User input is sanitized using `htmlspecialchars()` before display

### Theme Toggle
The dark/light mode preference is stored in `localStorage` and applied before the CSS loads on every page to prevent a flash of the wrong theme.

---

## ⚠️ Common Issues

| Problem | Solution |
|---|---|
| `http://localhost/pulao/` not loading | Make sure Apache is running in XAMPP and the folder is in `htdocs` |
| Database connection failed | Check db.php credentials match your MySQL setup |
| Quiz not generating | Verify your Groq API key is correctly pasted in db.php with no extra spaces |
| Blank chart on dashboard | Take at least one quiz first — the chart needs data to display |
| Page redirects to login | Make sure PHP sessions are working — restart Apache in XAMPP |

---

## License

This project is open source and available under the [MIT License](LICENSE).

---

## Acknowledgements

- [Groq](https://groq.com/) for the fast and free LLM API
- [Chart.js](https://www.chartjs.org/) for the performance charts
- [jQuery](https://jquery.com/) for DOM manipulation and animations

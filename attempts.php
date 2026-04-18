<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = htmlspecialchars($_SESSION['username']);

$stmt = $conn->prepare("SELECT topic, difficulty, score, total, created_at FROM attempts WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pulao - Previous Attempts</title>
    <script>
        if (localStorage.getItem('theme') === 'light') {
            document.documentElement.classList.add('light-mode');
        }
    </script>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
        <div class="card" id="attempts-card" style="display: none;">
            <h3>Your Past Attempts</h3>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Topic</th>
                        <th>Difficulty</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . date('M d, Y h:i A', strtotime($row['created_at'])) . "</td>";
                            echo "<td>" . htmlspecialchars($row['topic']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['difficulty']) . "</td>";
                            echo "<td>" . $row['score'] . " / " . $row['total'] . " (" . round(($row['score'] / $row['total']) * 100, 1) . "%)</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>No attempts found. Take a quiz first!</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#attempts-card').fadeIn(800);
        });
    </script>
</body>

</html>
<?php $stmt->close(); ?>
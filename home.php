<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = htmlspecialchars($_SESSION['username']);

// Get user's average score
$stmt = $conn->prepare("SELECT AVG((score/total)*100) as avg_score FROM attempts WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$avg_score = $row['avg_score'] ? round($row['avg_score'], 1) : 0;
$stmt->close();

// Get user's improvement data for Chart.js
$stmt = $conn->prepare("SELECT created_at, (score/total)*100 as percentage FROM attempts WHERE user_id = ? ORDER BY created_at ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$chart_labels = [];
$chart_data = [];
while ($row = $result->fetch_assoc()) {
    $chart_labels[] = date('M d, y', strtotime($row['created_at']));
    $chart_data[] = round($row['percentage'], 1);
}
$stmt->close();

// Get global leaderboard
$leaderboard_query = "
    SELECT u.username, AVG((a.score/a.total)*100) as avg_score 
    FROM attempts a 
    JOIN users u ON a.user_id = u.id 
    GROUP BY a.user_id 
    ORDER BY avg_score DESC 
    LIMIT 10
";
$leaderboard_result = $conn->query($leaderboard_query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pulao - Dashboard</title>
    <script>
        if (localStorage.getItem('theme') === 'light') {
            document.documentElement.classList.add('light-mode');
        }
    </script>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <div class="dashboard-grid">
            <!-- Left Column: User Stats & Chart -->
            <div>
                <div class="card" style="margin-bottom: 2rem;">
                    <h3>Your Average Score</h3>
                    <div class="stat-value"><?php echo $avg_score; ?>%</div>
                </div>

                <div class="card">
                    <h3>Performance History</h3>
                    <canvas id="improvementChart"></canvas>
                </div>
            </div>

            <!-- Right Column: Leaderboard -->
            <div>
                <div class="card">
                    <h3>Global Leaderboard</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Username</th>
                                <th>Average Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $rank = 1;
                            if ($leaderboard_result->num_rows > 0) {
                                while ($l_row = $leaderboard_result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . $rank++ . "</td>";
                                    echo "<td>" . htmlspecialchars($l_row['username']) . "</td>";
                                    echo "<td>" . round($l_row['avg_score'], 1) . "%</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3'>No attempts yet.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('improvementChart').getContext('2d');
        const improvementChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chart_labels); ?>,
                datasets: [{
                    label: 'Score Percentage',
                    data: <?php echo json_encode($chart_data); ?>,
                    borderColor: '#bb86fc',
                    backgroundColor: 'rgba(187, 134, 252, 0.2)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            color: '#333'
                        },
                        ticks: {
                            color: '#e0e0e0'
                        }
                    },
                    x: {
                        grid: {
                            color: '#333'
                        },
                        ticks: {
                            color: '#e0e0e0'
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: '#e0e0e0'
                        }
                    }
                }
            }
        });
    </script>
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

        // Set correct icon on load
        if (localStorage.getItem('theme') === 'light') {
            const btn = document.getElementById('theme-toggle');
            if (btn) btn.innerHTML = '☀️';
        }
    </script>
</body>

</html>
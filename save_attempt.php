<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $topic = $_POST['topic'] ?? '';
    $difficulty = $_POST['difficulty'] ?? '';
    $score = intval($_POST['score'] ?? 0);
    $total = intval($_POST['total'] ?? 0);

    if (empty($topic) || empty($difficulty) || $total == 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO attempts (user_id, topic, difficulty, score, total) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issii", $user_id, $topic, $difficulty, $score, $total);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

<?php
// db.php
$db_host = 'localhost';
$db_user = 'root';      // Update this according to your local setup
$db_pass = '';          // Update this according to your local setup
$db_name = 'pulao';

// Groq API Key
define('GROQ_API_KEY', 'your_groq_api_key_here'); // Replace with your actual Groq API Key

// Using MySQLi
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

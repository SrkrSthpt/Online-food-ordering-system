<?php
/**
 * Database Import Script
 * Run this once to set up the database.
 * Access: http://localhost/bitezy/database/import.php
 * Delete this file after successful import.
 */

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'bitezy';

try {
  $conn = new mysqli($host, $user, $pass);
  if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
  }

  $sql = file_get_contents(__DIR__ . '/schema.sql');
  if ($conn->multi_query($sql)) {
    do {
      if ($result = $conn->store_result()) $result->free();
    } while ($conn->next_result());
    echo '<div style="font-family:sans-serif;max-width:600px;margin:50px auto;padding:30px;background:#d4edda;color:#155724;border-radius:12px;text-align:center;">';
    echo '<h1 style="margin-bottom:12px;">&#10003; Database Setup Complete</h1>';
    echo '<p>Database <strong>' . $dbname . '</strong> has been created with all tables.</p>';
    echo '<hr style="border: none; border-top: 1px solid rgba(0,0,0,0.1); margin: 20px 0;">';
    echo '<p style="font-size:0.9rem;">Default login credentials:<br>';
    echo '<strong>Admin:</strong> admin@bitezy.com / password<br>';
    echo '<strong>Manager:</strong> manager@bitezy.com / password</p>';
    echo '<a href="/bitezy/index.php" style="display:inline-block;margin-top:20px;padding:12px 24px;background:#155724;color:white;text-decoration:none;border-radius:8px;">Go to Bitezy</a>';
    echo '</div>';
  } else {
    echo 'Error: ' . $conn->error;
  }
  $conn->close();
} catch (Exception $e) {
  echo 'Error: ' . $e->getMessage();
}

<?php
session_start();
require 'db.php';

function registerUser($name, $email, $password) {
  global $conn;
  $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
  $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
  $stmt->bind_param("sss", $name, $email, $hashedPassword);
  return $stmt->execute();
}

function loginUser($email, $password) {
  global $conn;
  $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();

  if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_role'] = $user['role'];
    return true;
  }
  return false;
}

function createTicket($userId, $title, $description) {
  global $conn;
  $stmt = $conn->prepare("INSERT INTO tickets (user_id, title, description) VALUES (?, ?, ?)");
  $stmt->bind_param("iss", $userId, $title, $description);
  return $stmt->execute();
}

function getTickets($userId) {
  global $conn;
  $stmt = $conn->prepare("SELECT * FROM tickets WHERE user_id = ?");
  $stmt->bind_param("i", $userId);
  $stmt->execute();
  return $stmt->get_result();
}
?>

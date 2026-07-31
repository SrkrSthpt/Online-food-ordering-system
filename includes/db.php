<?php
require_once __DIR__ . '/../config.php';

function getDB() {
  static $conn = null;
  if ($conn === null) {
    try {
      $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
      if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
      }
      $conn->set_charset('utf8mb4');
    } catch (Exception $e) {
      die('Database connection error. Please check your configuration.');
    }
  }
  return $conn;
}

function dbQuery($sql, $params = []) {
  $conn = getDB();
  if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
      die('SQL Error: ' . $conn->error);
    }
    $types = '';
    foreach ($params as $param) {
      if (is_int($param)) $types .= 'i';
      elseif (is_double($param)) $types .= 'd';
      elseif (is_string($param)) $types .= 's';
      else $types .= 's';
    }
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result;
  } else {
    return $conn->query($sql);
  }
}

function dbInsert($sql, $params = []) {
  $conn = getDB();
  if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
      die('SQL Error: ' . $conn->error);
    }
    $types = '';
    foreach ($params as $param) {
      if (is_int($param)) $types .= 'i';
      elseif (is_double($param)) $types .= 'd';
      elseif (is_string($param)) $types .= 's';
      else $types .= 's';
    }
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $id = $stmt->insert_id;
    $stmt->close();
    return $id;
  } else {
    $conn->query($sql);
    return $conn->insert_id;
  }
}

function dbEscape($value) {
  $conn = getDB();
  return $conn->real_escape_string($value);
}

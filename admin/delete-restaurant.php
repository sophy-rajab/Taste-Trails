<?php
session_start();
include "../db.php";

if (!isset($_SESSION["user_id"]) || ($_SESSION["user_role"] ?? "") !== "admin" || $_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../login.php");
    exit;
}

$id = (int) ($_POST["id"] ?? 0);

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM restaurants WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: index.php?deleted=1#restaurants");
exit;
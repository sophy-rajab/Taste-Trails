<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$restaurant_id = (int) ($_POST["restaurant_id"] ?? 0);
$back = $_POST["back"] ?? "restaurant";

if ($restaurant_id > 0) {
    $check = $conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND restaurant_id = ?");
    $check->bind_param("ii", $user_id, $restaurant_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $del = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND restaurant_id = ?");
        $del->bind_param("ii", $user_id, $restaurant_id);
        $del->execute();
    } else {
        $ins = $conn->prepare("INSERT INTO favorites (user_id, restaurant_id) VALUES (?, ?)");
        $ins->bind_param("ii", $user_id, $restaurant_id);
        $ins->execute();
    }
}

if ($back === "favorites") {
    header("Location: favorites.php");
} else {
    header("Location: restaurant.php?id=" . $restaurant_id);
}
exit;
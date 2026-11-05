<?php
ini_set("display_errors", 1);
error_reporting(E_ALL);

session_start();
include "../db.php";

if (!isset($_SESSION["user_id"]) || ($_SESSION["user_role"] ?? "") !== "admin" || $_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../login.php");
    exit;
}

$id          = (int) ($_POST["id"] ?? 0);
$name        = trim($_POST["name"] ?? "");
$cuisine     = trim($_POST["cuisine"] ?? "");
$location    = trim($_POST["location"] ?? "");
$city        = trim($_POST["city"] ?? "") ?: "Nairobi";
$phone       = trim($_POST["phone"] ?? "");
$hours       = trim($_POST["hours"] ?? "");
$tier        = $_POST["priceTier"] ?? "mid";
$priceMin    = (int) ($_POST["priceMin"] ?? 0);
$priceMax    = (int) ($_POST["priceMax"] ?? 0);
$tags        = trim($_POST["tags"] ?? "");
$description = trim($_POST["description"] ?? "");
$featured    = isset($_POST["featured"]) ? 1 : 0;
$image       = trim($_POST["image"] ?? "");

function fail($msg) {
    header("Location: index.php?error=" . urlencode($msg) . "#restaurants");
    exit;
}

if ($name === "" || $cuisine === "" || $location === "" || $description === "") {
    fail("Please fill in all the required fields (*).");
}

if (!in_array($tier, ["budget", "mid", "upscale"])) {
    $tier = "mid";
}

// Cover photo upload (optional)
if (!empty($_FILES["coverFile"]["name"]) && $_FILES["coverFile"]["error"] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES["coverFile"]["name"], PATHINFO_EXTENSION));
    $allowed = ["jpg", "jpeg", "png", "webp"];

    if (!in_array($ext, $allowed)) {
        fail("Cover photo must be a JPG, PNG or WEBP image.");
    }
    if ($_FILES["coverFile"]["size"] > 4 * 1024 * 1024) {
        fail("Cover photo must be under 4MB.");
    }
    if (@getimagesize($_FILES["coverFile"]["tmp_name"]) === false) {
        fail("That file is not a valid image.");
    }

    $newName = "resto-" . time() . "-" . bin2hex(random_bytes(3)) . "." . $ext;
    if (!move_uploaded_file($_FILES["coverFile"]["tmp_name"], __DIR__ . "/../images/" . $newName)) {
        fail("Could not save the photo. Please try again.");
    }
    $image = "images/" . $newName;
}

if ($image === "") {
    $image = "images/javahouse.jpg"; // placeholder if no photo was uploaded
}

if ($id > 0) {
    $stmt = $conn->prepare("UPDATE restaurants SET name=?, image=?, location=?, city=?, cuisine=?, price_range=?, price_min=?, price_max=?, tags=?, description=?, hours=?, phone=?, featured=? WHERE id=?");
    $stmt->bind_param("ssssssiissssii", $name, $image, $location, $city, $cuisine, $tier, $priceMin, $priceMax, $tags, $description, $hours, $phone, $featured, $id);
} else {
    $stmt = $conn->prepare("INSERT INTO restaurants (name, image, location, city, cuisine, price_range, price_min, price_max, tags, description, hours, phone, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssiissssi", $name, $image, $location, $city, $cuisine, $tier, $priceMin, $priceMax, $tags, $description, $hours, $phone, $featured);
}
$stmt->execute();

header("Location: index.php?saved=1#restaurants");
exit;
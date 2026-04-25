<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "level_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/* -----------------------------
   1. SAFE UPLOAD DIRECTORY
------------------------------*/
$uploadDir = __DIR__ . '/uploads/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

/* -----------------------------
   2. HANDLE FILE UPLOAD SAFELY
------------------------------*/
$uploadedFiles = [];

if (isset($_FILES['photos']) && !empty($_FILES['photos']['name'][0])) {

    foreach ($_FILES['photos']['tmp_name'] as $key => $tmpName) {

        if (!empty($tmpName)) {
            $fileName = time() . "_" . basename($_FILES['photos']['name'][$key]);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($tmpName, $targetPath)) {
                $uploadedFiles[] = $fileName;
            }
        }
    }
}

/* -----------------------------
   3. COLLECT FORM DATA SAFELY
------------------------------*/
$phone = $_POST['phone'] ?? '';
$email = $_POST['email'] ?? '';
$first_name = $_POST['first_name'] ?? '';
$dob = $_POST['dob'] ?? '';
$gender = $_POST['gender'] ?? '';
$location = $_POST['location'] ?? '';

$photosList = implode(",", $uploadedFiles);

/* -----------------------------
   4. INSERT INTO DATABASE
------------------------------*/
$stmt = $conn->prepare("
    INSERT INTO users 
    (phone, email, first_name, dob, gender, location, photos)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "sssssss",
    $phone,
    $email,
    $first_name,
    $dob,
    $gender,
    $location,
    $photosList
);

/* -----------------------------
   5. EXECUTE + REDIRECT
------------------------------*/
if ($stmt->execute()) {
    header("Location: index.html");
    exit();
} else {
    die("Database Error: " . $stmt->error);
}

$stmt->close();
$conn->close();
?>
<?php
session_start();
require_once '../db.php'; // connects to salonsync DB

$errors = [];
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $name = isset($_POST["name"]) ? trim($_POST["name"]) : '';
  $email = isset($_POST["email"]) ? trim($_POST["email"]) : '';
  $password = isset($_POST["password"]) ? $_POST["password"] : '';
  $confirm = isset($_POST["confirm_password"]) ? $_POST["confirm_password"] : '';

  if (empty($name) || empty($email) || empty($password) || empty($confirm)) {
    $errors[] = "All fields are required.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format.";
  } elseif ($password !== $confirm) {
    $errors[] = "Passwords do not match.";
  } else {
    $check = $conn->prepare("SELECT id FROM admins WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
      $errors[] = "An admin with this email already exists.";
    } else {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $conn->prepare("INSERT INTO admins (name, email, password_hash) VALUES (?, ?, ?)");
      $stmt->bind_param("sss", $name, $email, $hash);
      if ($stmt->execute()) {
        $success = "Admin account created. You can now <a href='login.php' class='text-pink-500 underline'>login</a>.";
      } else {
        $errors[] = "Something went wrong. Please try again.";
      }
    }
  }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Sign Up - SalonSync</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-pink-100 min-h-screen flex items-center justify-center">
  <div class="bg-white p-8 rounded-2xl shadow-md w-full max-w-md">
    <h2 class="text-3xl font-bold text-pink-500 text-center mb-6">Admin Sign Up</h2>

    <?php if (!empty($errors)): ?>
      <div class="bg-red-100 text-red-700 p-3 mb-4 rounded">
        <?php foreach ($errors as $error) echo "<p>$error</p>"; ?>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="bg-green-100 text-green-700 p-3 mb-4 rounded">
        <?= $success ?>
      </div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
      <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
        <input type="text" name="name" id="name" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-400">
      </div>

      <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <input type="email" name="email" id="email" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-400">
      </div>

      <div>
        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
        <input type="password" name="password" id="password" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-400">
      </div>

      <div>
        <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm Password</label>
        <input type="password" name="confirm_password" id="confirm_password"  required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-400">
      </div>

      <button type="submit" class="w-full bg-pink-500 text-white py-2 px-4 rounded-xl hover:bg-pink-600 transition duration-300">
        Create Admin Account
      </button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-600">
      Already have an account?
      <a href="login.php" class="text-pink-500 hover:underline">Login here</a>
    </p>
  </div>
</body>
</html>

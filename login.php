<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "mutasilp_mutasilpakistan"; 
$password = "pgQnVvXD52S_87X"; 
$dbname = "mutasilp_mutasil_pakistan";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Handle login before sending any HTML
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    // Special admin login
if ($email === "musa@mutasil.com" && $password === ")@^@musa") {
    $_SESSION['admin_id'] = 1; // set something consistent
    $_SESSION['email'] = $email;
    $_SESSION['role'] = "admin";
    header("Location: panel.php");
    exit;
}


    // 🔑 Normal users
    $sql = "SELECT id, email, password FROM members WHERE email = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();

            // For now, plain text check (replace with password_verify() later)
            if ($password === $row['password']) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['email'] = $row['email'];
                $_SESSION['role'] = "user";
                $_SESSION['last_activity'] = time();

                header("Location: dashboard.php?id=" . $row['id']);
                exit;
            } else {
                $error = "Wrong password!";
            }
        } else {
            $error = "No user found with that email!";
        }
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>MP - Login</title>
  <link rel="icon" href="images/favicon-32x32.png" sizes="32x32" type="image/x-icon">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center text-white bg-[url('images/back.png')]">

  <div class="max-w-3xl sm:max-w-4xl mx-auto bg-white/10 backdrop-blur-lg rounded-3xl shadow-xl p-8 sm:p-10 text-center">
    <!-- Logo -->
    <div class="flex items-center justify-center h-24">
      <a href="#"><img class="h-16 w-auto drop-shadow-lg" src="images/mpmun.png" alt="Logo"></a>
    </div>
    <h1 class="text-3xl font-bold mb-6">Member Login</h1>
    <p class="text-gray-300 mb-8">Welcome back! Please login to your account.</p>

    <!-- Error Message -->
    <?php if (!empty($error)): ?>
      <p class="mb-4 text-red-400 font-semibold"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <!-- Login Form -->
    <form class="space-y-6" method="POST" action="">
      <div class="text-left">
        <label for="email" class="block text-sm font-medium mb-2">Email</label>
        <input id="email" name="email" type="email" required
          class="w-full px-4 py-3 rounded-xl bg-white/20 text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400" 
          placeholder="Enter your email">
      </div>
      <div class="text-left">
        <label for="password" class="block text-sm font-medium mb-2">Password</label>
        <input id="password" name="password" type="password" required
          class="w-full px-4 py-3 rounded-xl bg-white/20 text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400" 
          placeholder="Enter your password">
      </div>
      <button type="submit" 
        class="w-full py-3 rounded-xl bg-blue-500 hover:bg-blue-600 text-white font-semibold shadow-md transition">
        Login
      </button>
    </form>
  </div>

</body>
</html>

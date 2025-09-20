<?php
session_start();

$servername = "localhost";
$username = "mutasilp_mutasilpakistan";
$password = "pgQnVvXD52S_87X";
$dbname = "mutasilp_mutasil_pakistan";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_GET['id'])) {
    die("❌ No user ID provided in URL!");
}

$id = intval($_GET['id']);

$sql = "SELECT * FROM members WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL Error: " . $conn->error);
}
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("❌ No user found with id = $id");
}

$user = $result->fetch_assoc();
// logout handling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    session_start();
    session_unset();
    session_destroy();
    header("Location: index.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>MP - Dashboard</title>
  <meta name="google-site-verification" content="arI0DGA1h6V_EEM66-JRm_kE6XhyBF0YUBPXxnO_YQA" />
  <meta name="description" content="Mutasil Pakistan . A Non-Govermental organization . Established to connnect youth around the world">
  <link rel="icon" href="images/favicon-32x32.png" sizes="32x32" type="image/x-icon">
  <script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "Mutasil Pakistan",
  "url": "https://mutasil.pk",
  "logo": "https://mutasil.pk/images/favicon-32x32.png"
}
</script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js"></script>



  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css"
/>
<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
  <style>
    /* Optional: Prevent scrollbar shift when using 100vh */
    html, body {
      margin: 0;
      padding: 0;
      height: 100%;
    }
  </style>
</head>
<body class="bg-[url('images/back.png')] text-white">
<!-- Sidebar -->
<nav id="sidebar" 
  class="fixed top-0 left-0 h-full w-64 z-50 
         bg-white/10 backdrop-blur-lg shadow-xl 
         border-r border-white/20 flex flex-col rounded-r-3xl 
         transform -translate-x-full lg:translate-x-0 
         transition-transform duration-300">

  <!-- Logo -->
  <div class="flex items-center justify-center h-24 border-b border-white/20">
    <a href="#h">
      <img class="h-16 w-auto drop-shadow-lg" src="images/mpmun.png" alt="Logo">
    </a>
  </div>

  <!-- Menu Links -->
  <div class="flex-1 overflow-y-auto px-6 py-8 space-y-4 font-semibold text-base text-white">
    <a href="#h" class="block hover:text-green-400 transition duration-300">Home</a>
    <a href="#a" class="block hover:text-green-400 transition duration-300">About Us</a>
    <a href="#d" class="block hover:text-green-400 transition duration-300">Board Of Directors</a>
    <a href="#c" class="block hover:text-green-400 transition duration-300">Our Initiatives</a>
    <a href="#ag" class="block hover:text-green-400 transition duration-300">Our Future Goals</a>
    <a href="#p" class="block hover:text-green-400 transition duration-300">Past Events</a>
    <a href="#ev" class="block hover:text-green-400 transition duration-300">Our Sponsors</a>
    <a href="#gal" class="block hover:text-green-400 transition duration-300">Gallery</a>
    <a href="#cu" class="block hover:text-green-400 transition duration-300">Contact Us</a>
  </div>
</nav>
<div class="flex justify-end p-4">
  <form method="post" action="">
    <button type="submit" name="logout" 
      class="flex items-center gap-2 text-white font-medium hover:text-red-400 transition duration-300">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h4a2 2 0 012 2v1" />
      </svg>
      Logout
    </button>
  </form>
</div>
<!-- Motto Section -->
<!-- Motto Section -->
 <hr>
<section 
  class="relative min-h-[30vh] flex items-center justify-center lg:ml-64 px-4 bg-cover bg-center text-white"
  id="motto">
  <!-- Motto Content -->
  <div class="relative z-10 text-center max-w-3xl mx-auto">
    <p class="text-xl sm:text-2xl md:text-3xl font-bold">
      WHERE DREAMS TAKE ROOT, 
      <span class="text-green-500">PAKISTAN GROWS </span>
    </p>

    <p class="mt-2 text-lg sm:text-xl md:text-2xl font-semibold">
      جہان خواب جُڑيں  
      <span class="text-green-500">وہاں پاكستان بڑھے</span>
    </p>
  </div>
</section>

<hr>

<!-- Welcome Section -->
<section 
  class="relative min-h-[30vh] flex items-center justify-center lg:ml-64 px-4 mt-6">

  <div class="text-center max-w-3xl">
    <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-white">
      Welcome to <span class="text-green-500">Dashboard</span>
    </h1>
    <p class="mt-3 text-lg sm:text-xl font-semibold text-gray-200">
      <span class="text-green-400"><?php echo htmlspecialchars($user['name']); ?></span> !
    </p>
  </div>
</section>


<!-- Dashboard Content Section -->
<section class="lg:ml-64 px-4 mt-6 space-y-6">
  <!-- First Row: Profile + Tasks -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
<!-- Profile Info Card -->
<div class="bg-white/10 backdrop-blur-md rounded-2xl shadow-lg p-6 text-white">
  <h2 class="text-xl font-bold mb-6">Profile Information</h2>

  <!-- Profile Picture -->
  <div class="flex justify-center mb-6">
    <img 
      src="<?php echo !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : 'images/default.png'; ?>" 
      alt="Profile Picture"
      class="w-32 h-32 rounded-full object-cover border-4 border-green-400 shadow-md"
    >
  </div>

  <div class="space-y-3">
    <div class="flex justify-between border-b border-white/10 pb-2">
      <span class="font-semibold text-green-400">Name</span>
      <span class="text-gray-200"><?php echo htmlspecialchars($user['name']); ?></span>
    </div>
    <div class="flex justify-between border-b border-white/10 pb-2">
      <span class="font-semibold text-green-400">Institute</span>
      <span class="text-gray-200"><?php echo htmlspecialchars($user['institute']); ?></span>
    </div>
    <div class="flex justify-between border-b border-white/10 pb-2">
      <span class="font-semibold text-green-400">Date of Joining</span>
      <span class="text-gray-200"><?php echo htmlspecialchars($user['date_of_joining']); ?></span>
    </div>
    <div class="flex justify-between">
      <span class="font-semibold text-green-400">Age</span>
      <span class="text-gray-200"><?php echo htmlspecialchars($user['age']); ?></span>
    </div>
  </div>
</div>

    <!-- Assigned Tasks Card -->
    <div class="bg-white/10 backdrop-blur-md rounded-2xl shadow-lg p-6 text-white">
      <h2 class="text-xl font-bold mb-4">Assigned Tasks</h2>
      <ul class="space-y-3">
        <?php 
          $tasks = explode(",", $user['assigned_tasks']); 
          foreach ($tasks as $task): ?>
            <li class="flex justify-between border-b border-white/10 pb-2">
              <span class="font-medium"><?php echo htmlspecialchars(trim($task)); ?></span>
            </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <!-- Past Achievements -->
  <div class="bg-white/10 backdrop-blur-md rounded-2xl shadow-lg p-6 text-white">
    <h2 class="text-xl font-bold mb-4">Past Achievements</h2>
    <div class="space-y-4">
      <?php 
        $achievements = explode(",", $user['past_achievements']); 
        foreach ($achievements as $ach): ?>
          <div class="p-4 rounded-xl bg-white/5 hover:bg-white/10 transition">
            <p class="text-gray-200 font-medium">🏆 <?php echo htmlspecialchars(trim($ach)); ?></p>
          </div>
      <?php endforeach; ?>
    </div>
  </div>

<!-- Certificates Card -->
<div class="bg-white/10 backdrop-blur-md rounded-2xl shadow-lg p-6 text-white">
  <h2 class="text-xl font-bold mb-4">Certificates</h2>
  <div class="space-y-4">
    <?php 
      // Certificates stored as comma-separated paths
      $certificates = explode(",", $user['certificates']); 
      foreach($certificates as $cert) {
        $cert = trim($cert);
        if($cert != "") {
          $filename = basename($cert); // get file name only
          echo '
          <div class="p-4 rounded-xl bg-white/5 hover:bg-white/10 transition flex justify-between items-center">
            <div>
              <p class="text-gray-200 font-medium">'. $filename .'</p>
            </div>
            <a href="'. $cert .'" target="_blank" 
               class="text-sm text-green-400 bg-green-900/40 px-3 py-1 rounded-lg hover:bg-green-800 transition">
               View
            </a>
          </div>';
        }
      }
    ?>
  </div>
</div>


  <!-- Upcoming Events -->
  <div class="bg-white/10 backdrop-blur-md rounded-2xl shadow-lg p-6 text-white mt-6">
    <h2 class="text-xl font-bold mb-4">Upcoming Events</h2>
    <div class="space-y-4">
      <?php 
        $events = explode(",", $user['upcoming_events']); 
        foreach ($events as $event): ?>
          <div class="p-4 rounded-xl bg-white/5 hover:bg-white/10 transition">
            <p class="text-lg font-semibold text-green-400">📅 <?php echo htmlspecialchars(trim($event)); ?></p>
          </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>



<!-- Mobile Toggle Button -->
<button id="menu-toggle" 
  class="fixed top-4 left-4 z-50 p-2 rounded-lg bg-white/10 backdrop-blur-md shadow-lg border border-white/20 text-white lg:hidden">
  <!-- Hamburger icon -->
  <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
  </svg>
</button>
<script>
  const sidebar = document.getElementById('sidebar');
  const toggleBtn = document.getElementById('menu-toggle');

  toggleBtn.addEventListener('click', () => {
    sidebar.classList.toggle('-translate-x-full');
  });
</script>
</body>
</html>
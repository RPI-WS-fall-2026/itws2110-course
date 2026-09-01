<?php
// No database anywhere in this example. State goes to a plain file,
// which is enough to show where a container keeps things -- and loses them.
$logFile = "/var/www/html/data/visits.log";

@mkdir(dirname($logFile), 0777, true);

$note = trim($_POST["note"] ?? "");
$error = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  if ($note === "") {
    $error = "Say something first.";
  } else {
    $line = date("Y-m-d H:i:s") . "\t" . str_replace(["\n", "\t"], " ", $note) . "\n";
    if (@file_put_contents($logFile, $line, FILE_APPEND) === false) {
      $error = "Could not write to " . $logFile . " -- is it mounted read-only?";
    }
  }
}

$lines = is_readable($logFile) ? array_filter(file($logFile, FILE_IGNORE_NEW_LINES)) : [];
$lines = array_reverse($lines);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Guestbook</title>
  <style>
    body { font-family: system-ui, sans-serif; max-width: 42rem; margin: 3rem auto; padding: 0 1rem; line-height: 1.6; }
    .meta { color: #666; font-size: 0.85rem; }
    .err  { color: #c00; }
    li { margin-bottom: 0.4rem; }
    time { color: #666; font-variant-numeric: tabular-nums; }
  </style>
</head>
<body>

  <h1>Guestbook</h1>
  <p class="meta">
    Running as <code><?= htmlspecialchars(trim(shell_exec("id -un") ?? "?")) ?></code>
    on <code><?= htmlspecialchars(gethostname()) ?></code>.
    Storing to <code><?= htmlspecialchars($logFile) ?></code>.
  </p>

  <?php if ($error): ?><p class="err"><?= htmlspecialchars($error) ?></p><?php endif; ?>

  <form method="post">
    <input name="note" size="40" placeholder="Leave a note" autofocus>
    <button type="submit">Sign</button>
  </form>

  <p class="meta"><?= count($lines) ?> entries.</p>
  <ul>
    <?php foreach ($lines as $line): ?>
      <?php [$when, $what] = array_pad(explode("\t", $line, 2), 2, ""); ?>
      <li><time><?= htmlspecialchars($when) ?></time> — <?= htmlspecialchars($what) ?></li>
    <?php endforeach; ?>
  </ul>

</body>
</html>

<?php
// Given, complete. Validates on the server, inserts with a prepared statement,
// and escapes everything it echoes. You do not need to change this file --
// but read it, because it is what your containers exist to run.

include __DIR__ . "/db/connect.php";

// Given: the map from form field name -> database column -> display label.
// The familiarity items are all 0-3.
$items = [
    "htmlCss"       => ["html_css",       "HTML & CSS"],
    "javascript"    => ["javascript",     "JavaScript"],
    "php"           => ["php",            "PHP"],
    "sqlMysql"      => ["sql_mysql",      "SQL / MySQL"],
    "gitGithub"     => ["git_github",     "Git & GitHub"],
    "docker"        => ["docker",         "Docker"],
    "nodeExpress"   => ["node_express",   "Node.js / Express"],
    "reactFrontend" => ["react_frontend", "React / frontend framework"],
    "restApis"      => ["rest_apis",      "REST APIs"],
    "cloud"         => ["cloud",          "Cloud platforms"],
    "agenticAi"     => ["agentic_ai",     "Agentic AI patterns"],
    "aiAssistants"  => ["ai_assistants",  "AI coding assistants"],
];

// -- 4a. Server-side validation -------------------------------------------
$required = ["firstName", "lastName", "email", "goals"];
foreach ($required as $field) {
    if (trim($_POST[$field] ?? "") === "") {
        http_response_code(422);
        exit("Missing field: " . htmlspecialchars($field));
    }
}

$lampComfort = (int) ($_POST["lampComfort"] ?? 0);
if ($lampComfort < 1 || $lampComfort > 5) {
    http_response_code(422);
    exit("LAMP comfort rating is required (1-5).");
}

$familiarity = [];
foreach ($items as $field => $info) {
    $familiarity[$field] = max(0, min(3, (int) ($_POST[$field] ?? 0)));
}


// -- 4b. Insert ------------------------------------------------------------
$pdo = connectDb();

$columns = array_map(fn($info) => $info[0], array_values($items));
$placeholders = implode(", ", array_fill(0, count($columns) + 5, "?"));
$sql = "INSERT INTO survey (first_name, last_name, email, lamp_comfort, "
     . implode(", ", $columns) . ", goals) VALUES ($placeholders)";

$stmt = $pdo->prepare($sql);
$stmt->execute(array_merge(
    [trim($_POST["firstName"]), trim($_POST["lastName"]), trim($_POST["email"]), $lampComfort],
    array_values($familiarity),
    [trim($_POST["goals"])]
));


// -- 4c. Two summary numbers for the confirmation page ---------------------
$total   = $pdo->query("SELECT COUNT(*) FROM survey")->fetchColumn();
$avgLamp = $pdo->query("SELECT ROUND(AVG(lamp_comfort), 1) FROM survey")->fetchColumn();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Survey Received</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>

  <h1>Thanks, <?= htmlspecialchars(trim($_POST["firstName"])) ?>!</h1>

  <p>Your survey was saved.
     Submissions so far: <?= (int) $total ?>.
     Class average LAMP comfort: <?= htmlspecialchars($avgLamp) ?> / 5.</p>

  <h2>Your answers</h2>
  <p>LAMP comfort: <?= $lampComfort ?> / 5</p>
  <ul>
    <?php foreach ($items as $field => $info): ?>
      <li><?= htmlspecialchars($info[1]) ?>: <?= $familiarity[$field] ?> / 3</li>
    <?php endforeach; ?>
  </ul>
  <p><strong>Goals:</strong> <?= htmlspecialchars(trim($_POST["goals"])) ?></p>

  <p><a href="index.html">Back to the survey</a></p>

</body>
</html>

<?php
// TASK 4 — the PHP layer.
//
// The browser already validated this form. That does not matter: anyone can
// POST here with curl and skip your JavaScript entirely. Validate again.

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
// firstName, lastName, email and goals must be non-empty after trimming.
// lampComfort must be an integer from 1 to 5.
// Clamp each familiarity score into 0-3.
// On bad input: http_response_code(422) and stop. Do not insert.
//
// YOUR VALIDATION HERE


// -- 4b. Insert ------------------------------------------------------------
// Use a PREPARED STATEMENT. Never build SQL by concatenating $_POST into a
// string -- we will look for this, and week 12 is about what happens when
// you do. connectDb() is in db/connect.php and returns a PDO handle.
//
// YOUR INSERT HERE


// -- 4c. Two summary numbers for the confirmation page ---------------------
// $total   = how many rows are in survey
// $avgLamp = the average lamp_comfort, rounded to one decimal
//
// YOUR QUERIES HERE

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

  <!-- 4d. Echo the submitter's first name, $total, and $avgLamp.
       Every value that came from the user goes through htmlspecialchars().
       Skipping that is the bug we spend week 12 on. -->

  <h1>Thanks!</h1>

  <p><a href="index.html">Back to the survey</a></p>

</body>
</html>

<?php
// Given. Reads from the environment, so credentials live in .env and never in git.
// Your docker-compose.yml is what puts these variables into the container.

$dbHost = getenv("DB_HOST") ?: "db";
$dbName = getenv("DB_NAME") ?: "app";
$dbUser = getenv("DB_USER") ?: "appuser";
$dbPass = getenv("DB_PASSWORD") ?: "";

$dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
$user = $dbUser;
$pass = $dbPass;

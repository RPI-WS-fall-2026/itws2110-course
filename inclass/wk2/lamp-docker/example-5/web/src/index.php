<?php
// "db" is not a hostname you configured anywhere. It is the container's name.
// Docker's embedded DNS resolves it, but only on a user-defined network.
$dsn = "mysql:host=db;port=3306;dbname=app;charset=utf8mb4";

try {
  $pdo = new PDO($dsn, "root", "root", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  ]);
} catch (PDOException $e) {
  http_response_code(503);
  echo "<h1>Cannot reach the database</h1>";
  echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
  echo "<p>Is the db container running, and is this container on the same network?</p>";
  exit;
}

$sql = "SELECT s.name, s.class_year, c.code, c.title
        FROM students s
        JOIN enrollments e ON e.student_id = s.id
        JOIN courses c     ON c.id = e.course_id
        ORDER BY s.name, c.code";
$rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Enrollments</title>
  <style>
    body { font-family: system-ui, sans-serif; margin: 2rem; }
    table { border-collapse: collapse; }
    th, td { border: 1px solid #ccc; padding: 0.4rem 0.8rem; text-align: left; }
  </style>
</head>
<body>

  <h1>Enrollments</h1>
  <p>Served by PHP in one container, read from MySQL in another.</p>

  <table>
    <tr><th>Student</th><th>Class year</th><th>Course</th><th>Title</th></tr>
    <?php foreach ($rows as $row): ?>
      <tr>
        <td><?= htmlspecialchars($row["name"]) ?></td>
        <td><?= (int) $row["class_year"] ?></td>
        <td><?= htmlspecialchars($row["code"]) ?></td>
        <td><?= htmlspecialchars($row["title"]) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

</body>
</html>

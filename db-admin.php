<?php
$host = getenv('DB_HOST');
$port = getenv('DB_PORT');
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$pass");

if (!$conn) {
    die("Connection failed: " . pg_last_error());
} else {
    echo "Database connected successfully.<br>";
}

// Insert a test record
$sql = "INSERT INTO users (name, email) VALUES ('John Doe', 'john@example.com')";
$result = pg_query($conn, $sql);

if ($result) {
    echo "New record created successfully.";
} else {
    echo "Error: " . pg_last_error($conn);
}

pg_close($conn);
?>


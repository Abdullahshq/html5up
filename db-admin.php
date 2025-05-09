<?php
// Database connection parameters from environment variables
$host = getenv('DB_HOST');
$dbname = getenv('DB_NAME');
$username = getenv('DB_USER');
$password = getenv('DB_PASSWORD');

// Simple authentication (you should implement a more secure method)
$admin_password = "your_secure_password"; // Change this to a secure password

// Check if user is authenticated
session_start();
$authenticated = false;

if (isset($_POST['login']) && $_POST['password'] === $admin_password) {
    $_SESSION['authenticated'] = true;
}

if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
    $authenticated = true;
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Function to execute queries
function executeQuery($pdo, $sql) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        
        // Check if it's a SELECT query
        if (stripos(trim($sql), 'SELECT') === 0) {
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['success' => true, 'results' => $results];
        }
        
        return ['success' => true, 'message' => 'Query executed successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

// Connect to database if authenticated
$pdo = null;
$connection_error = null;
$query_result = null;

if ($authenticated) {
    try {
        $dsn = "mysql:host=$host;dbname=$dbname";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $pdo = new PDO($dsn, $username, $password, $options);
        
        // Handle query submission
        if (isset($_POST['execute_query']) && !empty($_POST['sql_query'])) {
            $query_result = executeQuery($pdo, $_POST['sql_query']);
        }
    } catch (PDOException $e) {
        $connection_error = "Connection failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE HTML>
<html>
<head>
    <title>Database Admin - Twenty by Abdullah</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <link rel="stylesheet" href="assets/css/main.css" />
    <noscript><link rel="stylesheet" href="assets/css/noscript.css" /></noscript>
    <style>
        .query-box {
            width: 100%;
            height: 150px;
            font-family: monospace;
        }
        .result-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .result-table th, .result-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .result-table th {
            background-color: #83d3c9;
            color: white;
        }
        .error {
            color: red;
            font-weight: bold;
        }
        .success {
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body class="index is-preload">
    <div id="page-wrapper">
        <!-- Header -->
        <header id="header" class="alt">
            <h1 id="logo"><a href="index.html">Twenty <span>by Abdullah</span></a></h1>
            <nav id="nav">
                <ul>
                    <li><a href="index.html">Home</a></li>
                    <li class="current"><a href="db-admin.php">Database Admin</a></li>
                    <?php if ($authenticated): ?>
                    <li><a href="?logout=1" class="button primary">Logout</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </header>

        <!-- Main -->
        <article id="main">
            <header class="special container">
                <span class="icon solid fa-database"></span>
                <h2>Database Administration</h2>
                <p>Manage your MySQL database</p>
            </header>

            <section class="wrapper style4 container">
                <?php if (!$authenticated): ?>
                <!-- Login Form -->
                <div class="content">
                    <form method="post" action="">
                        <div class="row gtr-50">
                            <div class="col-12">
                                <h3>Login to Database Admin</h3>
                            </div>
                            <div class="col-12">
                                <input type="password" name="password" placeholder="Admin Password" required />
                            </div>
                            <div class="col-12">
                                <ul class="buttons">
                                    <li><input type="submit" name="login" class="special" value="Login" /></li>
                                </ul>
                            </div>
                        </div>
                    </form>
                </div>
                <?php else: ?>
                <!-- Database Management Interface -->
                <div class="content">
                    <?php if ($connection_error): ?>
                        <p class="error"><?php echo $connection_error; ?></p>
                    <?php else: ?>
                        <h3>Execute SQL Query</h3>
                        <form method="post" action="">
                            <div class="row gtr-50">
                                <div class="col-12">
                                    <textarea class="query-box" name="sql_query" placeholder="Enter your SQL query here..."><?php echo isset($_POST['sql_query']) ? htmlspecialchars($_POST['sql_query']) : ''; ?></textarea>
                                </div>
                                <div class="col-12">
                                    <ul class="buttons">
                                        <li><input type="submit" name="execute_query" class="special" value="Execute Query" /></li>
                                    </ul>
                                </div>
                            </div>
                        </form>

                        <?php if ($query_result): ?>
                            <div class="query-results">
                                <?php if ($query_result['success']): ?>
                                    <?php if (isset($query_result['message'])): ?>
                                        <p class="success"><?php echo $query_result['message']; ?></p>
                                    <?php elseif (isset($query_result['results'])): ?>
                                        <?php if (empty($query_result['results'])): ?>
                                            <p>Query returned no results.</p>
                                        <?php else: ?>
                                            <table class="result-table">
                                                <thead>
                                                    <tr>
                                                        <?php foreach (array_keys($query_result['results'][0]) as $column): ?>
                                                            <th><?php echo htmlspecialchars($column); ?></th>
                                                        <?php endforeach; ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($query_result['results'] as $row): ?>
                                                        <tr>
                                                            <?php foreach ($row as $value): ?>
                                                                <td><?php echo htmlspecialchars($value); ?></td>
                                                            <?php endforeach; ?>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p class="error">Error: <?php echo $query_result['error']; ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </section>
        </article>

        <!-- Footer -->
        <footer id="footer">
            <ul class="copyright">
                <li>&copy; Untitled</li><li>Design: <a href="http://html5up.net">HTML5 UP</a></li>
            </ul>
        </footer>
    </div>

    <!-- Scripts -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/jquery.dropotron.min.js"></script>
    <script src="assets/js/jquery.scrolly.min.js"></script>
    <script src="assets/js/jquery.scrollex.min.js"></script>
    <script src="assets/js/browser.min.js"></script>
    <script src="assets/js/breakpoints.min.js"></script>
    <script src="assets/js/util.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
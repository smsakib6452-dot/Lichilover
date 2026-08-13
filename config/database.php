<?php
declare(strict_types=1);

/**
 * PDO database connection (singleton).
 * Always use prepared statements via this connection.
 */

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_NAME);

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_PERSISTENT         => false,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        error_log('DB connection failed: ' . $e->getMessage() . PHP_EOL, 3, LOG_PATH . '/php-errors.log');
        http_response_code(500);
        if (APP_DEBUG) {
            throw new RuntimeException('Database connection failed: ' . $e->getMessage());
        }
        exit('Database connection failed. Please check your configuration.');
    }

    return $pdo;
}

/**
 * Run a query with parameters and return the PDO statement.
 */
function query(string $sql, array $params = []): PDOStatement
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Fetch a single row.
 */
function fetch_one(string $sql, array $params = []): ?array
{
    $row = query($sql, $params)->fetch();
    return $row === false ? null : $row;
}

/**
 * Fetch all rows.
 */
function fetch_all(string $sql, array $params = []): array
{
    return query($sql, $params)->fetchAll();
}

/**
 * Fetch a single column value.
 */
function fetch_val(string $sql, array $params = []): mixed
{
    return query($sql, $params)->fetchColumn();
}

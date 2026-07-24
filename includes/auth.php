<?php

require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function findUserByEmail(string $email): ?array
{
    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare(
        'SELECT user_id, first_name, last_name, email, pass, is_admin
        FROM users
        WHERE email = :email
        LIMIT 1'
    );
    $statement->execute(['email' => $email]);
    $user = $statement->fetch();

    return $user ?: null;
}

function findUserById(int $id): ?array
{
    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare(
        'SELECT user_id, first_name, last_name, email, is_admin
        FROM users
        WHERE user_id = :id
        LIMIT 1'
    );
    $statement->execute(['id' => $id]);
    $user = $statement->fetch();

    return $user ?: null;
}

function createUser(string $fullName, string $email, string $password): int
{
    $pdo = getDatabaseConnection();
    [$firstName, $lastName] = splitFullName($fullName);
    $statement = $pdo->prepare(
        'INSERT INTO users (first_name, last_name, email, pass) VALUES (:first_name, :last_name, :email, :pass)'
    );
    $statement->execute([
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
        'pass' => hashPasswordForDatabase($password),
    ]);

    return (int) $pdo->lastInsertId();
}

function splitFullName(string $fullName): array
{
    $parts = preg_split('/\s+/', trim($fullName), 2);
    $firstName = $parts[0] ?? '';
    $lastName = $parts[1] ?? '';

    return [$firstName, $lastName];
}

function hashPasswordForDatabase(string $password): string
{
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyUserPassword(string $password, string $storedPasswordHash): bool
{
    return password_verify($password, $storedPasswordHash);
}

function userDisplayName(array $user): string
{
    $name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));

    return $name !== '' ? $name : 'UWM Student';
}

function userIsAdmin(array $user): bool
{
    return (int) ($user['is_admin'] ?? 0) === 1;
}

function logInUser(int $userId): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
}

function currentUser(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    try {
        $user = findUserById((int) $_SESSION['user_id']);
    } catch (Throwable $error) {
        error_log('Unable to load current user: ' . $error->getMessage());
        clearLoggedInUser();
        setAuthMessage('We could not load your dashboard because the database connection needs attention. Please check includes/db.php and try again.');

        return null;
    }

    if ($user === null) {
        clearLoggedInUser();
        setAuthMessage('Please sign in again to continue.');
    }

    return $user;
}

function requireLogin(): array
{
    $user = currentUser();

    if ($user === null) {
        header('Location: login.php');
        exit;
    }

    return $user;
}

function redirectIfLoggedIn(): void
{
    if (currentUser() !== null) {
        header('Location: dashboard.php');
        exit;
    }
}

function clearLoggedInUser(): void
{
    unset($_SESSION['user_id']);
}

function setAuthMessage(string $message): void
{
    $_SESSION['auth_message'] = $message;
}

function consumeAuthMessage(): string
{
    $message = $_SESSION['auth_message'] ?? '';
    unset($_SESSION['auth_message']);

    return $message;
}

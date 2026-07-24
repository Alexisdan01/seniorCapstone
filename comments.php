<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

requireLogin();

$postId = (int) ($_GET['post_id'] ?? 0);

if ($postId <= 0) {
    header('Location: dashboard.php');
    exit;
}

$pdo = getDatabaseConnection();
$statement = $pdo->prepare(
    'SELECT forum_id
    FROM blog_posts
    WHERE blogpost_id = :blogpost_id
    LIMIT 1'
);
$statement->execute(['blogpost_id' => $postId]);
$post = $statement->fetch();

if (!$post || (int) ($post['forum_id'] ?? 0) <= 0) {
    header('Location: dashboard.php');
    exit;
}

header('Location: forum.php?forum_id=' . (int) $post['forum_id'] . '#post-' . $postId);
exit;

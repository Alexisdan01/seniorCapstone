<?php

require_once __DIR__ . '/db.php';

function dashboardJoinedCourses(int $userId): array
{
    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare(
        'SELECT
            c.CourseID,
            c.CourseName,
            uc.joined_at,
            MIN(f.forum_id) AS forum_id,
            MIN(f.forum_name) AS forum_name,
            COUNT(DISTINCT bp.blogpost_id) AS post_count
        FROM user_courses uc
        INNER JOIN courses c ON c.CourseID = uc.course_id
        LEFT JOIN forum f ON f.course_id = c.CourseID
        LEFT JOIN blog_posts bp ON bp.forum_id = f.forum_id
        WHERE uc.user_id = :user_id
        GROUP BY c.CourseID, c.CourseName, uc.joined_at
        ORDER BY c.CourseName ASC'
    );
    $statement->execute(['user_id' => $userId]);

    return $statement->fetchAll();
}

function dashboardFeedPosts(int $userId, bool $limitToJoinedCourses, int $limit = 10): array
{
    $pdo = getDatabaseConnection();
    $courseFilter = $limitToJoinedCourses
        ? 'WHERE f.course_id IN (SELECT course_id FROM user_courses WHERE user_id = :joined_user_id)'
        : 'WHERE f.course_id IS NOT NULL';

    $statement = $pdo->prepare(
        'SELECT
            bp.blogpost_id,
            bp.user_id,
            bp.forum_id,
            bp.blogpost_title,
            bp.blogpost_body,
            bp.image_url,
            bp.blogpost_timestamp,
            u.first_name,
            u.last_name,
            f.forum_name,
            f.course_id,
            c.CourseName,
            CASE WHEN joined_course.user_id IS NULL THEN 0 ELSE 1 END AS current_user_joined_course,
            COALESCE(reaction_counts.like_count, 0) AS like_count,
            COALESCE(comment_counts.comment_count, 0) AS comment_count,
            user_reaction.reaction_type AS current_user_reaction,
            saved_posts.saved_post_id AS current_user_saved_id
        FROM blog_posts bp
        LEFT JOIN users u ON u.user_id = bp.user_id
        LEFT JOIN forum f ON f.forum_id = bp.forum_id
        LEFT JOIN courses c ON c.CourseID = f.course_id
        LEFT JOIN user_courses joined_course
            ON joined_course.course_id = f.course_id
            AND joined_course.user_id = :membership_user_id
        LEFT JOIN (
            SELECT blogpost_id, COUNT(*) AS like_count
            FROM post_reactions
            WHERE reaction_type = "like"
            GROUP BY blogpost_id
        ) reaction_counts ON reaction_counts.blogpost_id = bp.blogpost_id
        LEFT JOIN (
            SELECT blogpost_id, COUNT(*) AS comment_count
            FROM post_comments
            GROUP BY blogpost_id
        ) comment_counts ON comment_counts.blogpost_id = bp.blogpost_id
        LEFT JOIN post_reactions user_reaction
            ON user_reaction.blogpost_id = bp.blogpost_id
            AND user_reaction.user_id = :reaction_user_id
        LEFT JOIN saved_posts
            ON saved_posts.blogpost_id = bp.blogpost_id
            AND saved_posts.user_id = :saved_user_id
        ' . $courseFilter . '
        ORDER BY bp.blogpost_timestamp DESC, like_count DESC, comment_count DESC
        LIMIT ' . max(1, min(50, $limit))
    );

    $parameters = [
        'membership_user_id' => $userId,
        'reaction_user_id' => $userId,
        'saved_user_id' => $userId,
    ];

    if ($limitToJoinedCourses) {
        $parameters['joined_user_id'] = $userId;
    }

    $statement->execute($parameters);

    return $statement->fetchAll();
}

function dashboardTrendingPosts(int $limit = 3): array
{
    $pdo = getDatabaseConnection();
    $statement = $pdo->query(
        'SELECT
            bp.blogpost_id,
            bp.blogpost_title,
            bp.blogpost_timestamp,
            f.forum_name,
            c.CourseName,
            COALESCE(comment_counts.comment_count, 0) AS comment_count,
            COALESCE(reaction_counts.like_count, 0) AS like_count
        FROM blog_posts bp
        LEFT JOIN forum f ON f.forum_id = bp.forum_id
        LEFT JOIN courses c ON c.CourseID = f.course_id
        LEFT JOIN (
            SELECT blogpost_id, COUNT(*) AS comment_count
            FROM post_comments
            GROUP BY blogpost_id
        ) comment_counts ON comment_counts.blogpost_id = bp.blogpost_id
        LEFT JOIN (
            SELECT blogpost_id, COUNT(*) AS like_count
            FROM post_reactions
            WHERE reaction_type = "like"
            GROUP BY blogpost_id
        ) reaction_counts ON reaction_counts.blogpost_id = bp.blogpost_id
        ORDER BY (COALESCE(comment_counts.comment_count, 0) + COALESCE(reaction_counts.like_count, 0)) DESC,
            bp.blogpost_timestamp DESC
        LIMIT ' . max(1, min(10, $limit))
    );

    return $statement->fetchAll();
}

function dashboardActiveForums(int $limit = 3): array
{
    $pdo = getDatabaseConnection();
    $statement = $pdo->query(
        'SELECT
            f.forum_id,
            f.forum_name,
            c.CourseID,
            c.CourseName,
            COUNT(DISTINCT bp.blogpost_id) AS post_count,
            MAX(bp.blogpost_timestamp) AS latest_post_at
        FROM forum f
        INNER JOIN courses c ON c.CourseID = f.course_id
        LEFT JOIN blog_posts bp ON bp.forum_id = f.forum_id
        GROUP BY f.forum_id, f.forum_name, c.CourseID, c.CourseName
        ORDER BY latest_post_at DESC, post_count DESC, f.forum_name ASC
        LIMIT ' . max(1, min(10, $limit))
    );

    return $statement->fetchAll();
}

function dashboardTogglePostLike(int $userId, int $blogpostId): void
{
    if ($blogpostId <= 0) {
        return;
    }

    if (!dashboardUserCanInteractWithPost($userId, $blogpostId)) {
        throw new RuntimeException('Join the class forum before liking its posts.');
    }

    $pdo = getDatabaseConnection();
    $pdo->beginTransaction();

    try {
        $findStatement = $pdo->prepare(
            'SELECT reaction_id, reaction_type
            FROM post_reactions
            WHERE user_id = :user_id AND blogpost_id = :blogpost_id
            LIMIT 1'
        );
        $findStatement->execute([
            'user_id' => $userId,
            'blogpost_id' => $blogpostId,
        ]);
        $reaction = $findStatement->fetch();

        if ($reaction && $reaction['reaction_type'] === 'like') {
            $deleteStatement = $pdo->prepare('DELETE FROM post_reactions WHERE reaction_id = :reaction_id');
            $deleteStatement->execute(['reaction_id' => (int) $reaction['reaction_id']]);
        } elseif ($reaction) {
            $updateStatement = $pdo->prepare(
                'UPDATE post_reactions
                SET reaction_type = "like"
                WHERE reaction_id = :reaction_id'
            );
            $updateStatement->execute(['reaction_id' => (int) $reaction['reaction_id']]);
        } else {
            $insertStatement = $pdo->prepare(
                'INSERT INTO post_reactions (user_id, blogpost_id, reaction_type)
                VALUES (:user_id, :blogpost_id, "like")'
            );
            $insertStatement->execute([
                'user_id' => $userId,
                'blogpost_id' => $blogpostId,
            ]);
        }

        $pdo->commit();
    } catch (Throwable $error) {
        $pdo->rollBack();
        throw $error;
    }
}

function dashboardUserCanInteractWithPost(int $userId, int $blogpostId): bool
{
    if ($userId <= 0 || $blogpostId <= 0) {
        return false;
    }

    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare(
        'SELECT uc.user_course_id
        FROM blog_posts bp
        INNER JOIN forum f ON f.forum_id = bp.forum_id
        INNER JOIN user_courses uc
            ON uc.course_id = f.course_id
            AND uc.user_id = :user_id
        WHERE bp.blogpost_id = :blogpost_id
        LIMIT 1'
    );
    $statement->execute([
        'user_id' => $userId,
        'blogpost_id' => $blogpostId,
    ]);

    return (bool) $statement->fetch();
}

function dashboardPublicForumPreviewPost(): ?array
{
    $pdo = getDatabaseConnection();
    $statement = $pdo->query(
        'SELECT
            bp.blogpost_id,
            bp.blogpost_title,
            bp.blogpost_body,
            bp.blogpost_timestamp,
            u.first_name,
            u.last_name,
            f.forum_name,
            c.CourseName,
            COALESCE(comment_counts.comment_count, 0) AS comment_count
        FROM blog_posts bp
        INNER JOIN forum f ON f.forum_id = bp.forum_id
        INNER JOIN courses c ON c.CourseID = f.course_id
        LEFT JOIN users u ON u.user_id = bp.user_id
        LEFT JOIN (
            SELECT blogpost_id, COUNT(*) AS comment_count
            FROM post_comments
            GROUP BY blogpost_id
        ) comment_counts ON comment_counts.blogpost_id = bp.blogpost_id
        ORDER BY bp.blogpost_timestamp DESC
        LIMIT 1'
    );
    $post = $statement->fetch();

    return $post ?: null;
}

function dashboardShortCourseName(?string $courseName): string
{
    $courseName = trim((string) $courseName);

    if ($courseName === '') {
        return 'Campus-wide';
    }

    $parts = preg_split('/\s+/', $courseName);

    if (count($parts) >= 2 && preg_match('/^\d/', $parts[1]) === 1) {
        return $parts[0] . ' ' . $parts[1];
    }

    return substr($courseName, 0, 22);
}

function dashboardCourseDescription(?string $courseName): string
{
    $courseName = trim((string) $courseName);

    if ($courseName === '') {
        return 'General discussion';
    }

    $parts = preg_split('/\s+/', $courseName, 3);

    return $parts[2] ?? $courseName;
}

function dashboardCourseInitials(?string $courseName): string
{
    $courseName = trim((string) $courseName);

    if ($courseName === '') {
        return 'SP';
    }

    $parts = preg_split('/\s+/', $courseName);
    $code = preg_replace('/[^A-Za-z]/', '', $parts[0] ?? 'SP');

    return strtoupper(substr($code ?: 'SP', 0, 2));
}

function dashboardForumLabel(array $post): string
{
    if (!empty($post['forum_name'])) {
        return $post['forum_name'];
    }

    if (!empty($post['CourseName'])) {
        return dashboardShortCourseName($post['CourseName']);
    }

    return 'Campus Feed';
}

function dashboardAuthorName(array $post): string
{
    $name = trim(($post['first_name'] ?? '') . ' ' . ($post['last_name'] ?? ''));

    return $name !== '' ? $name : 'UWM Student';
}

function dashboardTimeAgo(?string $timestamp): string
{
    if (empty($timestamp)) {
        return 'recently';
    }

    $time = strtotime($timestamp);

    if ($time === false) {
        return 'recently';
    }

    $seconds = max(1, time() - $time);
    $units = [
        'year' => 31536000,
        'month' => 2592000,
        'week' => 604800,
        'day' => 86400,
        'hour' => 3600,
        'minute' => 60,
    ];

    foreach ($units as $label => $unitSeconds) {
        if ($seconds >= $unitSeconds) {
            $value = (int) floor($seconds / $unitSeconds);

            return $value . ' ' . $label . ($value === 1 ? '' : 's') . ' ago';
        }
    }

    return 'just now';
}

function dashboardFormatCount(int $count): string
{
    if ($count >= 1000000) {
        return round($count / 1000000, 1) . 'm';
    }

    if ($count >= 1000) {
        return round($count / 1000, 1) . 'k';
    }

    return (string) $count;
}

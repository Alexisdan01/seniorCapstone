<?php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

function forumCatalogData(): array
{
    $pdo = getDatabaseConnection();

    $schools = $pdo->query(
        'SELECT SchoolID, Name, SchoolType
        FROM schools
        ORDER BY Name ASC'
    )->fetchAll();

    $courses = $pdo->query(
        'SELECT CourseID, CourseName, SchoolID
        FROM courses
        ORDER BY CourseName ASC'
    )->fetchAll();

    $majors = $pdo->query(
        'SELECT MajorID, Name, SchoolID
        FROM majors
        ORDER BY Name ASC'
    )->fetchAll();

    return [
        'schools' => $schools,
        'courses' => $courses,
        'majors' => $majors,
    ];
}

function forumList(int $userId): array
{
    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare(
        'SELECT
            f.forum_id,
            f.forum_name,
            f.forum_description,
            f.course_id,
            f.major_id,
            f.created_by_user_id,
            f.created_at,
            f.updated_at,
            c.CourseID,
            c.CourseName,
            s.SchoolID,
            s.Name AS school_name,
            s.SchoolType,
            m.Name AS major_name,
            creator.first_name AS creator_first_name,
            creator.last_name AS creator_last_name,
            COUNT(DISTINCT uc.user_id) AS member_count,
            COUNT(DISTINCT bp.blogpost_id) AS post_count,
            MAX(bp.blogpost_timestamp) AS latest_post_at,
            MAX(CASE WHEN joined.user_id IS NULL THEN 0 ELSE 1 END) AS current_user_joined
        FROM forum f
        INNER JOIN courses c ON c.CourseID = f.course_id
        INNER JOIN schools s ON s.SchoolID = c.SchoolID
        LEFT JOIN majors m ON m.MajorID = f.major_id
        LEFT JOIN users creator ON creator.user_id = f.created_by_user_id
        LEFT JOIN user_courses uc ON uc.course_id = c.CourseID
        LEFT JOIN user_courses joined
            ON joined.course_id = c.CourseID
            AND joined.user_id = :user_id
        LEFT JOIN blog_posts bp ON bp.forum_id = f.forum_id
        GROUP BY
            f.forum_id,
            f.forum_name,
            f.forum_description,
            f.course_id,
            f.major_id,
            f.created_by_user_id,
            f.created_at,
            f.updated_at,
            c.CourseID,
            c.CourseName,
            s.SchoolID,
            s.Name,
            s.SchoolType,
            m.Name,
            creator.first_name,
            creator.last_name
        ORDER BY s.Name ASC, c.CourseName ASC'
    );
    $statement->execute(['user_id' => $userId]);

    return $statement->fetchAll();
}

function forumFindById(int $forumId, ?int $userId = null): ?array
{
    if ($forumId <= 0) {
        return null;
    }

    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare(
        'SELECT
            f.forum_id,
            f.forum_name,
            f.forum_description,
            f.course_id,
            f.major_id,
            f.created_by_user_id,
            f.created_at,
            f.updated_at,
            c.CourseID,
            c.CourseName,
            s.SchoolID,
            s.Name AS school_name,
            s.SchoolType,
            m.Name AS major_name,
            creator.first_name AS creator_first_name,
            creator.last_name AS creator_last_name,
            COUNT(DISTINCT uc.user_id) AS member_count,
            COUNT(DISTINCT bp.blogpost_id) AS post_count,
            MAX(bp.blogpost_timestamp) AS latest_post_at,
            MAX(CASE WHEN joined.user_id IS NULL THEN 0 ELSE 1 END) AS current_user_joined
        FROM forum f
        INNER JOIN courses c ON c.CourseID = f.course_id
        INNER JOIN schools s ON s.SchoolID = c.SchoolID
        LEFT JOIN majors m ON m.MajorID = f.major_id
        LEFT JOIN users creator ON creator.user_id = f.created_by_user_id
        LEFT JOIN user_courses uc ON uc.course_id = c.CourseID
        LEFT JOIN user_courses joined
            ON joined.course_id = c.CourseID
            AND joined.user_id = :user_id
        LEFT JOIN blog_posts bp ON bp.forum_id = f.forum_id
        WHERE f.forum_id = :forum_id
        GROUP BY
            f.forum_id,
            f.forum_name,
            f.forum_description,
            f.course_id,
            f.major_id,
            f.created_by_user_id,
            f.created_at,
            f.updated_at,
            c.CourseID,
            c.CourseName,
            s.SchoolID,
            s.Name,
            s.SchoolType,
            m.Name,
            creator.first_name,
            creator.last_name
        LIMIT 1'
    );
    $statement->execute([
        'forum_id' => $forumId,
        'user_id' => $userId ?? 0,
    ]);
    $forum = $statement->fetch();

    return $forum ?: null;
}

function forumFindByCourse(int $courseId, ?int $userId = null): ?array
{
    if ($courseId <= 0) {
        return null;
    }

    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare('SELECT forum_id FROM forum WHERE course_id = :course_id LIMIT 1');
    $statement->execute(['course_id' => $courseId]);
    $forum = $statement->fetch();

    if (!$forum) {
        return null;
    }

    return forumFindById((int) $forum['forum_id'], $userId);
}

function forumUserCanManage(array $forum, array $user): bool
{
    return userIsAdmin($user) && (int) ($forum['created_by_user_id'] ?? 0) === (int) ($user['user_id'] ?? 0);
}

function forumUserHasJoined(int $userId, int $courseId): bool
{
    if ($userId <= 0 || $courseId <= 0) {
        return false;
    }

    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare(
        'SELECT user_course_id
        FROM user_courses
        WHERE user_id = :user_id AND course_id = :course_id
        LIMIT 1'
    );
    $statement->execute([
        'user_id' => $userId,
        'course_id' => $courseId,
    ]);

    return (bool) $statement->fetch();
}

function forumCreate(array $user, array $input): int
{
    if (!userIsAdmin($user)) {
        throw new RuntimeException('Only admins can create a class forum.');
    }

    $schoolId = (int) ($input['school_id'] ?? 0);
    $courseId = (int) ($input['course_id'] ?? 0);
    $majorId = forumOptionalId($input['major_id'] ?? null);
    $forumName = trim((string) ($input['forum_name'] ?? ''));
    $forumDescription = trim((string) ($input['forum_description'] ?? ''));

    if ($schoolId <= 0 || $courseId <= 0 || $forumName === '' || $forumDescription === '') {
        throw new RuntimeException('Please complete the school, course, forum name, and description fields.');
    }

    $course = forumCourseRecord($courseId, $schoolId);

    if ($course === null) {
        throw new RuntimeException('Please choose a valid course for the selected school.');
    }

    if ($majorId !== null && !forumMajorMatchesSchool($majorId, $schoolId)) {
        throw new RuntimeException('Please choose a valid major for the selected school.');
    }

    if (forumFindByCourse($courseId) !== null) {
        throw new RuntimeException('A forum already exists for that class.');
    }

    $pdo = getDatabaseConnection();
    $pdo->beginTransaction();

    try {
        $statement = $pdo->prepare(
            'INSERT INTO forum (forum_name, forum_description, course_id, major_id, created_by_user_id)
            VALUES (:forum_name, :forum_description, :course_id, :major_id, :created_by_user_id)'
        );
        $statement->execute([
            'forum_name' => $forumName,
            'forum_description' => $forumDescription,
            'course_id' => $courseId,
            'major_id' => $majorId,
            'created_by_user_id' => (int) $user['user_id'],
        ]);

        forumEnsureMembership($pdo, (int) $user['user_id'], $courseId);

        $forumId = (int) $pdo->lastInsertId();
        $pdo->commit();

        return $forumId;
    } catch (Throwable $error) {
        $pdo->rollBack();
        throw $error;
    }
}

function forumUpdate(array $forum, array $user, array $input): void
{
    if (!forumUserCanManage($forum, $user)) {
        throw new RuntimeException('Only the admin who created this class can edit it.');
    }

    $schoolId = (int) ($forum['SchoolID'] ?? 0);
    $majorId = forumOptionalId($input['major_id'] ?? null);
    $forumName = trim((string) ($input['forum_name'] ?? ''));
    $forumDescription = trim((string) ($input['forum_description'] ?? ''));

    if ($forumName === '' || $forumDescription === '') {
        throw new RuntimeException('Please add both a forum name and a description.');
    }

    if ($majorId !== null && !forumMajorMatchesSchool($majorId, $schoolId)) {
        throw new RuntimeException('Please choose a valid major for this school.');
    }

    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare(
        'UPDATE forum
        SET forum_name = :forum_name,
            forum_description = :forum_description,
            major_id = :major_id,
            updated_at = CURRENT_TIMESTAMP
        WHERE forum_id = :forum_id'
    );
    $statement->execute([
        'forum_name' => $forumName,
        'forum_description' => $forumDescription,
        'major_id' => $majorId,
        'forum_id' => (int) $forum['forum_id'],
    ]);
}

function forumJoinCourse(int $userId, int $courseId): void
{
    if ($userId <= 0 || $courseId <= 0) {
        throw new RuntimeException('We could not join that class.');
    }

    $pdo = getDatabaseConnection();
    forumEnsureMembership($pdo, $userId, $courseId);
}

function forumLeaveCourse(array $forum, array $user): void
{
    $userId = (int) ($user['user_id'] ?? 0);
    $courseId = (int) ($forum['course_id'] ?? 0);

    if ($userId <= 0 || $courseId <= 0) {
        throw new RuntimeException('We could not leave that class.');
    }

    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare(
        'DELETE FROM user_courses
        WHERE user_id = :user_id AND course_id = :course_id'
    );
    $statement->execute([
        'user_id' => $userId,
        'course_id' => $courseId,
    ]);
}

function forumRemoveMember(array $forum, array $user, int $memberUserId): void
{
    if (!forumUserCanManage($forum, $user)) {
        throw new RuntimeException('Only the admin who created this class can remove members.');
    }

    if ($memberUserId <= 0) {
        throw new RuntimeException('Please choose a valid member.');
    }

    if ($memberUserId === (int) $forum['created_by_user_id']) {
        throw new RuntimeException('The admin who created the class cannot be removed from it.');
    }

    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare(
        'DELETE FROM user_courses
        WHERE user_id = :user_id AND course_id = :course_id'
    );
    $statement->execute([
        'user_id' => $memberUserId,
        'course_id' => (int) $forum['course_id'],
    ]);
}

function forumListMembers(int $courseId): array
{
    if ($courseId <= 0) {
        return [];
    }

    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare(
        'SELECT
            u.user_id,
            u.first_name,
            u.last_name,
            u.email,
            u.is_admin,
            uc.joined_at
        FROM user_courses uc
        INNER JOIN users u ON u.user_id = uc.user_id
        WHERE uc.course_id = :course_id
        ORDER BY uc.joined_at ASC, u.first_name ASC, u.last_name ASC'
    );
    $statement->execute(['course_id' => $courseId]);

    return $statement->fetchAll();
}

function forumListPosts(int $forumId): array
{
    if ($forumId <= 0) {
        return [];
    }

    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare(
        'SELECT
            bp.blogpost_id,
            bp.user_id,
            bp.blogpost_title,
            bp.blogpost_body,
            bp.blogpost_timestamp,
            u.first_name,
            u.last_name,
            COALESCE(comment_counts.comment_count, 0) AS comment_count
        FROM blog_posts bp
        INNER JOIN users u ON u.user_id = bp.user_id
        LEFT JOIN (
            SELECT blogpost_id, COUNT(*) AS comment_count
            FROM post_comments
            GROUP BY blogpost_id
        ) comment_counts ON comment_counts.blogpost_id = bp.blogpost_id
        WHERE bp.forum_id = :forum_id
        ORDER BY bp.blogpost_timestamp DESC'
    );
    $statement->execute(['forum_id' => $forumId]);

    return $statement->fetchAll();
}

function forumCommentsByPost(int $forumId): array
{
    if ($forumId <= 0) {
        return [];
    }

    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare(
        'SELECT
            pc.comment_id,
            pc.blogpost_id,
            pc.user_id,
            pc.comment_text,
            pc.created_at,
            u.first_name,
            u.last_name
        FROM post_comments pc
        INNER JOIN blog_posts bp ON bp.blogpost_id = pc.blogpost_id
        INNER JOIN users u ON u.user_id = pc.user_id
        WHERE bp.forum_id = :forum_id
        ORDER BY pc.created_at ASC'
    );
    $statement->execute(['forum_id' => $forumId]);

    $grouped = [];

    foreach ($statement->fetchAll() as $comment) {
        $grouped[(int) $comment['blogpost_id']][] = $comment;
    }

    return $grouped;
}

function forumCreatePost(array $forum, array $user, array $input): void
{
    $title = trim((string) ($input['blogpost_title'] ?? ''));
    $body = trim((string) ($input['blogpost_body'] ?? ''));

    if ($title === '' || $body === '') {
        throw new RuntimeException('Please add both a post title and a post body.');
    }

    if (!forumUserHasJoined((int) $user['user_id'], (int) $forum['course_id'])) {
        throw new RuntimeException('Join the class before posting in its forum.');
    }

    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare(
        'INSERT INTO blog_posts (user_id, forum_id, blogpost_title, blogpost_body)
        VALUES (:user_id, :forum_id, :blogpost_title, :blogpost_body)'
    );
    $statement->execute([
        'user_id' => (int) $user['user_id'],
        'forum_id' => (int) $forum['forum_id'],
        'blogpost_title' => $title,
        'blogpost_body' => $body,
    ]);
}

function forumUpdatePost(array $forum, array $user, array $input): void
{
    $postId = (int) ($input['blogpost_id'] ?? 0);
    $title = trim((string) ($input['blogpost_title'] ?? ''));
    $body = trim((string) ($input['blogpost_body'] ?? ''));

    if ($postId <= 0 || $title === '' || $body === '') {
        throw new RuntimeException('Please complete the post title and body.');
    }

    $post = forumPostRecord($postId);

    if ($post === null || (int) $post['forum_id'] !== (int) $forum['forum_id']) {
        throw new RuntimeException('We could not find that post in this class forum.');
    }

    if ((int) $post['user_id'] !== (int) $user['user_id']) {
        throw new RuntimeException('You can only edit posts you created.');
    }

    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare(
        'UPDATE blog_posts
        SET blogpost_title = :blogpost_title,
            blogpost_body = :blogpost_body
        WHERE blogpost_id = :blogpost_id'
    );
    $statement->execute([
        'blogpost_title' => $title,
        'blogpost_body' => $body,
        'blogpost_id' => $postId,
    ]);
}

function forumDeletePost(array $forum, array $user, int $postId): void
{
    if ($postId <= 0) {
        throw new RuntimeException('We could not find that post.');
    }

    $post = forumPostRecord($postId);

    if ($post === null || (int) $post['forum_id'] !== (int) $forum['forum_id']) {
        throw new RuntimeException('We could not find that post in this class forum.');
    }

    $canDelete = (int) $post['user_id'] === (int) $user['user_id'] || forumUserCanManage($forum, $user);

    if (!$canDelete) {
        throw new RuntimeException('You do not have permission to delete that post.');
    }

    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare('DELETE FROM blog_posts WHERE blogpost_id = :blogpost_id');
    $statement->execute(['blogpost_id' => $postId]);
}

function forumCreateComment(array $forum, array $user, array $input): void
{
    $postId = (int) ($input['blogpost_id'] ?? 0);
    $commentText = trim((string) ($input['comment_text'] ?? ''));

    if ($postId <= 0 || $commentText === '') {
        throw new RuntimeException('Please add a comment before posting.');
    }

    if (!forumUserHasJoined((int) $user['user_id'], (int) $forum['course_id'])) {
        throw new RuntimeException('Join the class before commenting in its forum.');
    }

    $post = forumPostRecord($postId);

    if ($post === null || (int) $post['forum_id'] !== (int) $forum['forum_id']) {
        throw new RuntimeException('We could not find that post in this class forum.');
    }

    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare(
        'INSERT INTO post_comments (blogpost_id, user_id, comment_text)
        VALUES (:blogpost_id, :user_id, :comment_text)'
    );
    $statement->execute([
        'blogpost_id' => $postId,
        'user_id' => (int) $user['user_id'],
        'comment_text' => $commentText,
    ]);
}

function forumUpdateComment(array $forum, array $user, array $input): void
{
    $commentId = (int) ($input['comment_id'] ?? 0);
    $commentText = trim((string) ($input['comment_text'] ?? ''));

    if ($commentId <= 0 || $commentText === '') {
        throw new RuntimeException('Please add a comment before saving.');
    }

    $comment = forumCommentRecord($commentId);

    if ($comment === null || (int) $comment['forum_id'] !== (int) $forum['forum_id']) {
        throw new RuntimeException('We could not find that comment in this class forum.');
    }

    if ((int) $comment['user_id'] !== (int) $user['user_id']) {
        throw new RuntimeException('You can only edit comments you created.');
    }

    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare(
        'UPDATE post_comments
        SET comment_text = :comment_text
        WHERE comment_id = :comment_id'
    );
    $statement->execute([
        'comment_text' => $commentText,
        'comment_id' => $commentId,
    ]);
}

function forumDeleteComment(array $forum, array $user, int $commentId): void
{
    if ($commentId <= 0) {
        throw new RuntimeException('We could not find that comment.');
    }

    $comment = forumCommentRecord($commentId);

    if ($comment === null || (int) $comment['forum_id'] !== (int) $forum['forum_id']) {
        throw new RuntimeException('We could not find that comment in this class forum.');
    }

    $canDelete = (int) $comment['user_id'] === (int) $user['user_id'] || forumUserCanManage($forum, $user);

    if (!$canDelete) {
        throw new RuntimeException('You do not have permission to delete that comment.');
    }

    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare('DELETE FROM post_comments WHERE comment_id = :comment_id');
    $statement->execute(['comment_id' => $commentId]);
}

function forumPostRecord(int $postId): ?array
{
    if ($postId <= 0) {
        return null;
    }

    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare(
        'SELECT blogpost_id, user_id, forum_id, blogpost_title, blogpost_body
        FROM blog_posts
        WHERE blogpost_id = :blogpost_id
        LIMIT 1'
    );
    $statement->execute(['blogpost_id' => $postId]);
    $post = $statement->fetch();

    return $post ?: null;
}

function forumCommentRecord(int $commentId): ?array
{
    if ($commentId <= 0) {
        return null;
    }

    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare(
        'SELECT
            pc.comment_id,
            pc.user_id,
            pc.blogpost_id,
            bp.forum_id
        FROM post_comments pc
        INNER JOIN blog_posts bp ON bp.blogpost_id = pc.blogpost_id
        WHERE pc.comment_id = :comment_id
        LIMIT 1'
    );
    $statement->execute(['comment_id' => $commentId]);
    $comment = $statement->fetch();

    return $comment ?: null;
}

function forumCourseRecord(int $courseId, int $schoolId): ?array
{
    if ($courseId <= 0 || $schoolId <= 0) {
        return null;
    }

    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare(
        'SELECT CourseID, CourseName, SchoolID
        FROM courses
        WHERE CourseID = :course_id AND SchoolID = :school_id
        LIMIT 1'
    );
    $statement->execute([
        'course_id' => $courseId,
        'school_id' => $schoolId,
    ]);
    $course = $statement->fetch();

    return $course ?: null;
}

function forumMajorMatchesSchool(int $majorId, int $schoolId): bool
{
    if ($majorId <= 0 || $schoolId <= 0) {
        return false;
    }

    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare(
        'SELECT MajorID
        FROM majors
        WHERE MajorID = :major_id AND SchoolID = :school_id
        LIMIT 1'
    );
    $statement->execute([
        'major_id' => $majorId,
        'school_id' => $schoolId,
    ]);

    return (bool) $statement->fetch();
}

function forumEnsureMembership(PDO $pdo, int $userId, int $courseId): void
{
    $statement = $pdo->prepare(
        'INSERT INTO user_courses (user_id, course_id)
        VALUES (:user_id, :course_id)
        ON DUPLICATE KEY UPDATE joined_at = joined_at'
    );
    $statement->execute([
        'user_id' => $userId,
        'course_id' => $courseId,
    ]);
}

function forumOptionalId(mixed $value): ?int
{
    $normalized = (int) $value;

    return $normalized > 0 ? $normalized : null;
}

function forumDisplayCreator(array $forum): string
{
    return userDisplayName([
        'first_name' => $forum['creator_first_name'] ?? '',
        'last_name' => $forum['creator_last_name'] ?? '',
    ]);
}

function forumDisplayUser(array $record): string
{
    return userDisplayName([
        'first_name' => $record['first_name'] ?? '',
        'last_name' => $record['last_name'] ?? '',
    ]);
}

function forumTimeAgo(?string $timestamp): string
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

<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/account-menu.php';
require_once __DIR__ . '/includes/dashboard-data.php';
require_once __DIR__ . '/includes/forum-data.php';

$currentUser = requireLogin();
$userId = (int) $currentUser['user_id'];
$isAdmin = userIsAdmin($currentUser);

$catalog = forumCatalogData();
$schools = $catalog['schools'];
$courses = $catalog['courses'];
$majors = $catalog['majors'];

$requestedForumId = (int) ($_GET['forum_id'] ?? 0);
$requestedCourseId = (int) ($_GET['course_id'] ?? 0);
$forum = $requestedForumId > 0
    ? forumFindById($requestedForumId, $userId)
    : ($requestedCourseId > 0 ? forumFindByCourse($requestedCourseId, $userId) : null);

function forumReturnAnchor(?string $value): string
{
    $value = trim((string) $value);

    if ($value === '' || preg_match('/^[A-Za-z0-9_-]+$/', $value) !== 1) {
        return '';
    }

    return '#' . $value;
}

$forumError = '';
$forumMessage = consumeAuthMessage();
$courseLookup = [];
$schoolLookup = [];

foreach ($courses as $courseOption) {
    $courseLookup[(int) $courseOption['CourseID']] = $courseOption;
}

foreach ($schools as $schoolOption) {
    $schoolLookup[(int) $schoolOption['SchoolID']] = $schoolOption;
}

$prefillCourse = $courseLookup[$requestedCourseId] ?? null;
$prefillSchoolId = $prefillCourse !== null ? (int) $prefillCourse['SchoolID'] : 0;
$createFormValues = [
    'school_id' => $prefillSchoolId > 0 ? (string) $prefillSchoolId : '',
    'course_id' => $requestedCourseId > 0 ? (string) $requestedCourseId : '',
    'major_id' => '',
    'forum_name' => $prefillCourse['CourseName'] ?? '',
    'forum_description' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_forum') {
        $createFormValues = [
            'school_id' => trim((string) ($_POST['school_id'] ?? '')),
            'course_id' => trim((string) ($_POST['course_id'] ?? '')),
            'major_id' => trim((string) ($_POST['major_id'] ?? '')),
            'forum_name' => trim((string) ($_POST['forum_name'] ?? '')),
            'forum_description' => trim((string) ($_POST['forum_description'] ?? '')),
        ];
    }

    try {
        if ($action === 'create_forum') {
            $newForumId = forumCreate($currentUser, $_POST);
            setAuthMessage('Class forum created. Students can now join, post, and comment.');
            header('Location: forum.php?forum_id=' . $newForumId);
            exit;
        }

        $targetForumId = (int) ($_POST['forum_id'] ?? ($forum['forum_id'] ?? 0));
        $forum = forumFindById($targetForumId, $userId);

        if ($forum === null) {
            throw new RuntimeException('We could not load that class forum.');
        }

        if ($action === 'update_forum') {
            forumUpdate($forum, $currentUser, $_POST);
            setAuthMessage('Class details updated.');
        } elseif ($action === 'join_course') {
            forumJoinCourse($userId, (int) $forum['course_id']);
            setAuthMessage('You joined the class forum.');
        } elseif ($action === 'leave_course') {
            forumLeaveCourse($forum, $currentUser);
            setAuthMessage('You left the class forum.');
        } elseif ($action === 'remove_member') {
            forumRemoveMember($forum, $currentUser, (int) ($_POST['member_user_id'] ?? 0));
            setAuthMessage('Member removed from the class.');
        } elseif ($action === 'create_post') {
            forumCreatePost($forum, $currentUser, $_POST);
            setAuthMessage('Post published to the class forum.');
        } elseif ($action === 'update_post') {
            forumUpdatePost($forum, $currentUser, $_POST);
            setAuthMessage('Post updated.');
        } elseif ($action === 'delete_post') {
            forumDeletePost($forum, $currentUser, (int) ($_POST['blogpost_id'] ?? 0));
            setAuthMessage('Post deleted.');
        } elseif ($action === 'create_comment') {
            forumCreateComment($forum, $currentUser, $_POST);
            setAuthMessage('Comment posted.');
        } elseif ($action === 'update_comment') {
            forumUpdateComment($forum, $currentUser, $_POST);
            setAuthMessage('Comment updated.');
        } elseif ($action === 'delete_comment') {
            forumDeleteComment($forum, $currentUser, (int) ($_POST['comment_id'] ?? 0));
            setAuthMessage('Comment deleted.');
        }

        header('Location: forum.php?forum_id=' . (int) $forum['forum_id'] . forumReturnAnchor($_POST['return_to'] ?? ''));
        exit;
    } catch (Throwable $error) {
        $forumError = $error->getMessage();
        $forum = $requestedForumId > 0
            ? forumFindById($requestedForumId, $userId)
            : ($requestedCourseId > 0 ? forumFindByCourse($requestedCourseId, $userId) : $forum);
    }
}

$listingForums = [];
$members = [];
$posts = [];
$commentsByPost = [];
$isJoined = false;
$canManage = false;
$showMissingForum = $forum === null && ($requestedForumId > 0 || $requestedCourseId > 0);
$missingForumCourseName = $prefillCourse['CourseName'] ?? 'this class';
$missingForumSchoolName = $prefillSchoolId > 0 ? ($schoolLookup[$prefillSchoolId]['Name'] ?? 'the selected school') : 'the selected school';

if ($forum === null && !$showMissingForum) {
    $listingForums = forumList($userId);
} elseif ($forum !== null) {
    $isJoined = forumUserHasJoined($userId, (int) $forum['course_id']);
    $canManage = forumUserCanManage($forum, $currentUser);
    $members = forumListMembers((int) $forum['course_id']);
    $posts = forumListPosts((int) $forum['forum_id']);
    $commentsByPost = forumCommentsByPost((int) $forum['forum_id']);
}

$firstName = trim($currentUser['first_name'] ?? '') ?: 'Student';
$majorOptionsForForum = [];

if ($forum !== null) {
    foreach ($majors as $majorOption) {
        if ((int) $majorOption['SchoolID'] === (int) $forum['SchoolID']) {
            $majorOptionsForForum[] = $majorOption;
        }
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Scholarly Pulse | Class Forums</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin=""/>
    <link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@400;700;900&family=Manrope:wght@400;500;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        "surface-variant": "#ffd2d8",
                        "primary-fixed": "#ff7672",
                        "on-primary-fixed": "#000000",
                        "on-tertiary": "#fff1d6",
                        "on-secondary": "#ffefea",
                        "on-error-container": "#520c00",
                        "surface": "#fff4f4",
                        "on-background": "#4d212a",
                        "secondary-fixed-dim": "#ffb193",
                        "on-tertiary-fixed-variant": "#664f00",
                        "on-primary-fixed-variant": "#60000b",
                        "background": "#fff4f4",
                        "primary-container": "#ff7672",
                        "error": "#b02500",
                        "tertiary-fixed": "#fad056",
                        "surface-tint": "#b12029",
                        "on-secondary-fixed-variant": "#8c3508",
                        "surface-dim": "#ffc6ce",
                        "inverse-on-surface": "#cb8c97",
                        "on-error": "#ffefec",
                        "surface-container-highest": "#ffd2d8",
                        "error-dim": "#b92902",
                        "surface-container-low": "#ffecee",
                        "secondary": "#9b3f14",
                        "on-primary-container": "#4e0007",
                        "on-surface-variant": "#814c56",
                        "on-surface": "#4d212a",
                        "error-container": "#f95630",
                        "outline": "#a06771",
                        "tertiary-container": "#fad056",
                        "surface-container-lowest": "#ffffff",
                        "tertiary": "#715800",
                        "on-secondary-fixed": "#601f00",
                        "on-secondary-container": "#802c00",
                        "on-tertiary-container": "#5b4600",
                        "secondary-dim": "#8b3407",
                        "tertiary-dim": "#634d00",
                        "primary-dim": "#a0101f",
                        "secondary-fixed": "#ffc5ae",
                        "tertiary-fixed-dim": "#ebc24a",
                        "surface-container": "#ffe1e4",
                        "surface-container-high": "#ffd9de",
                        "inverse-surface": "#24020b",
                        "outline-variant": "#dd9ca7",
                        "inverse-primary": "#ff5a5a",
                        "on-tertiary-fixed": "#443400",
                        "on-primary": "#ffefee",
                        "surface-bright": "#fff4f4",
                        "primary-fixed-dim": "#ff5a5a",
                        "primary": "#b12029",
                        "secondary-container": "#ffc5ae"
                    },
                    fontFamily: {
                        "headline": ["Epilogue"],
                        "body": ["Manrope"],
                        "label": ["Manrope"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    }
                }
            }
        };
    </script>
    <style>
        .glass-panel {
            background: rgba(255, 210, 216, 0.6);
            backdrop-filter: blur(12px);
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="bg-background text-on-surface font-body selection:bg-primary-container selection:text-on-primary-container">
<header class="sticky top-0 z-50 w-full border-b border-outline-variant/20 bg-[#fff4f4]/85 px-4 py-3 shadow-sm backdrop-blur-md md:px-6">
    <div class="mx-auto flex max-w-[1600px] items-center justify-between gap-4">
        <div class="flex min-w-0 flex-1 items-center gap-5 lg:gap-8">
            <a class="shrink-0 text-2xl font-black tracking-tight text-[#b12029] font-headline" href="dashboard.php">Scholarly Pulse</a>
            <nav class="hidden items-center gap-2 font-headline text-base font-bold lg:flex">
                <a class="rounded-full px-4 py-2 text-[#4d212a] transition-colors hover:bg-[#ffe1e4] hover:text-primary" href="dashboard.php">Feed</a>
                <a class="rounded-full bg-primary px-4 py-2 text-on-primary shadow-sm transition-all hover:shadow-md" href="forum.php">Class Forums</a>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            <?php if ($isAdmin): ?>
            <span class="hidden rounded-full bg-tertiary-container px-3 py-1 text-[11px] font-black uppercase tracking-widest text-on-tertiary-container md:inline-flex">Admin</span>
            <?php endif; ?>
            <?php renderAccountMenu($currentUser); ?>
        </div>
    </div>
</header>

<div class="mx-auto flex max-w-[1600px] gap-8 px-4 py-8 md:px-6">
    <aside class="hidden w-72 shrink-0 xl:block">
        <div class="sticky top-28 space-y-6">
            <section class="rounded-3xl bg-surface-container-lowest p-6 shadow-sm border border-outline-variant/10">
                <p class="text-xs font-black uppercase tracking-widest text-primary">Forum Space</p>
                <h1 class="mt-3 font-headline text-3xl font-black text-on-surface">Classes with real course permissions.</h1>
                <p class="mt-3 text-sm leading-relaxed text-on-surface-variant">
                    Admins can create and manage class forums. Students can join, leave, post, and comment inside the classes they belong to.
                </p>
            </section>
            <section class="rounded-3xl bg-surface-container p-6">
                <p class="text-xs font-black uppercase tracking-widest text-on-surface-variant">Signed In</p>
                <p class="mt-2 font-headline text-2xl font-black text-on-surface"><?php echo htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="mt-2 text-sm text-on-surface-variant"><?php echo $isAdmin ? 'You can create and manage class forums you own.' : 'Join a class forum to unlock posting and commenting.'; ?></p>
                <div class="mt-5 flex flex-col gap-2">
                    <a class="rounded-2xl bg-primary px-4 py-3 text-center text-sm font-black text-on-primary" href="dashboard.php">Back to Dashboard</a>
                    <a class="rounded-2xl bg-surface-container-lowest px-4 py-3 text-center text-sm font-black text-on-surface" href="logout.php">Log Out</a>
                </div>
            </section>
        </div>
    </aside>

    <main class="min-w-0 flex-1 space-y-8" id="top">
        <?php if ($forumMessage !== ''): ?>
        <section class="rounded-3xl border border-primary/10 bg-primary/5 p-4 text-sm font-bold text-primary">
            <?php echo htmlspecialchars($forumMessage, ENT_QUOTES, 'UTF-8'); ?>
        </section>
        <?php endif; ?>

        <?php if ($forumError !== ''): ?>
        <section class="rounded-3xl border border-error/20 bg-error-container/20 p-4 text-sm font-bold text-on-error-container">
            <?php echo htmlspecialchars($forumError, ENT_QUOTES, 'UTF-8'); ?>
        </section>
        <?php endif; ?>

        <?php if ($forum !== null): ?>
        <section class="rounded-[2rem] border border-outline-variant/10 bg-surface-container-lowest p-8 shadow-sm">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-3xl">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-primary/10 px-3 py-1 text-[11px] font-black uppercase tracking-widest text-primary"><?php echo htmlspecialchars($forum['school_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php if (!empty($forum['major_name'])): ?>
                        <span class="rounded-full bg-secondary-container px-3 py-1 text-[11px] font-black text-secondary"><?php echo htmlspecialchars($forum['major_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </div>
                    <h1 class="mt-4 font-headline text-4xl font-black text-on-surface"><?php echo htmlspecialchars($forum['forum_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h1>
                    <p class="mt-2 text-lg font-bold text-primary"><?php echo htmlspecialchars($forum['CourseName'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                    <p class="mt-4 max-w-2xl text-sm leading-relaxed text-on-surface-variant"><?php echo nl2br(htmlspecialchars($forum['forum_description'] ?? '', ENT_QUOTES, 'UTF-8')); ?></p>
                    <div class="mt-5 flex flex-wrap gap-5 text-xs font-bold text-on-surface-variant">
                        <span><?php echo (int) ($forum['member_count'] ?? 0); ?> members</span>
                        <span><?php echo (int) ($forum['post_count'] ?? 0); ?> posts</span>
                        <span>Created by <?php echo htmlspecialchars(forumDisplayCreator($forum), ENT_QUOTES, 'UTF-8'); ?></span>
                        <span>Updated <?php echo htmlspecialchars(forumTimeAgo($forum['updated_at'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                </div>

                <div class="flex w-full flex-col gap-3 lg:w-auto lg:min-w-[280px]">
                    <?php if ($isJoined): ?>
                    <div class="rounded-3xl bg-surface-container p-4 text-sm text-on-surface">
                        <p class="font-black text-primary">You are in this class forum.</p>
                        <p class="mt-1 text-on-surface-variant">You can create posts and join the conversation here.</p>
                    </div>
                    <form method="post">
                        <input type="hidden" name="action" value="leave_course"/>
                        <input type="hidden" name="forum_id" value="<?php echo (int) $forum['forum_id']; ?>"/>
                        <button class="w-full rounded-2xl bg-surface-container-low px-4 py-3 text-sm font-black text-on-surface transition-colors hover:bg-surface-container" type="submit">Leave Class</button>
                    </form>
                    <?php else: ?>
                    <form method="post">
                        <input type="hidden" name="action" value="join_course"/>
                        <input type="hidden" name="forum_id" value="<?php echo (int) $forum['forum_id']; ?>"/>
                        <button class="w-full rounded-3xl bg-gradient-to-r from-primary to-primary-container px-5 py-4 text-base font-black text-on-primary shadow-lg shadow-primary/20 transition-all hover:-translate-y-0.5 hover:shadow-xl" type="submit">Join This Class Forum</button>
                    </form>
                    <p class="rounded-3xl border border-primary/10 bg-primary/5 p-4 text-sm font-bold text-primary">Join this class forum to unlock comments, likes, and discussion tools.</p>
                    <?php endif; ?>

                    <?php if ($canManage): ?>
                    <button class="w-full rounded-2xl bg-tertiary-container px-4 py-3 text-sm font-black text-on-tertiary-container" type="button" data-toggle-target="forum-manage-panel">Manage This Class</button>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <?php if ($canManage): ?>
        <section class="hidden rounded-[2rem] border border-outline-variant/10 bg-surface-container-lowest p-8 shadow-sm" id="forum-manage-panel">
            <div class="grid gap-8 xl:grid-cols-[minmax(0,1fr)_320px]">
                <div>
                    <div class="mb-6">
                        <p class="text-xs font-black uppercase tracking-widest text-primary">Admin Controls</p>
                        <h2 class="mt-2 font-headline text-3xl font-black text-on-surface">Edit course details</h2>
                    </div>
                    <form class="space-y-5" method="post">
                        <input type="hidden" name="action" value="update_forum"/>
                        <input type="hidden" name="forum_id" value="<?php echo (int) $forum['forum_id']; ?>"/>
                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-bold text-on-surface-variant">School</label>
                                <input class="w-full rounded-2xl border-none bg-surface-container px-4 py-3 text-sm text-on-surface" readonly value="<?php echo htmlspecialchars($forum['school_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"/>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-bold text-on-surface-variant">Course</label>
                                <input class="w-full rounded-2xl border-none bg-surface-container px-4 py-3 text-sm text-on-surface" readonly value="<?php echo htmlspecialchars($forum['CourseName'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"/>
                            </div>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-bold text-on-surface-variant" for="forum_name">Forum Name</label>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-4 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/20" id="forum_name" name="forum_name" type="text" value="<?php echo htmlspecialchars($forum['forum_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"/>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-bold text-on-surface-variant" for="major_id">Major</label>
                            <select class="w-full rounded-2xl border-none bg-surface-container-low px-4 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/20" id="major_id" name="major_id">
                                <option value="">No major filter</option>
                                <?php foreach ($majorOptionsForForum as $majorOption): ?>
                                <option value="<?php echo (int) $majorOption['MajorID']; ?>" <?php echo (int) ($forum['major_id'] ?? 0) === (int) $majorOption['MajorID'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($majorOption['Name'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-bold text-on-surface-variant" for="forum_description">Description</label>
                            <textarea class="h-36 w-full rounded-3xl border-none bg-surface-container-low px-4 py-4 text-sm text-on-surface focus:ring-2 focus:ring-primary/20" id="forum_description" name="forum_description"><?php echo htmlspecialchars($forum['forum_description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>
                        <button class="rounded-2xl bg-primary px-5 py-3 text-sm font-black text-on-primary transition-opacity hover:opacity-90" type="submit">Save Class Changes</button>
                    </form>
                </div>

                <div class="rounded-[2rem] bg-surface-container p-6">
                    <p class="text-xs font-black uppercase tracking-widest text-on-surface-variant">Members</p>
                    <h3 class="mt-2 font-headline text-2xl font-black text-on-surface">Joined users</h3>
                    <div class="mt-5 space-y-3">
                        <?php if (empty($members)): ?>
                        <div class="rounded-2xl bg-surface-container-lowest p-4 text-sm text-on-surface-variant">No members have joined yet.</div>
                        <?php else: ?>
                        <?php foreach ($members as $member): ?>
                        <?php $memberIsOwner = (int) $member['user_id'] === (int) $forum['created_by_user_id']; ?>
                        <div class="rounded-2xl bg-surface-container-lowest p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-black text-on-surface"><?php echo htmlspecialchars(forumDisplayUser($member), ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="truncate text-[11px] text-on-surface-variant"><?php echo htmlspecialchars($member['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="mt-2 text-[11px] font-bold text-on-surface-variant"><?php echo $memberIsOwner ? 'Forum owner' : 'Joined ' . htmlspecialchars(forumTimeAgo($member['joined_at'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                                <?php if (!$memberIsOwner): ?>
                                <form method="post" onsubmit="return confirm('Remove this user from the class?');">
                                    <input type="hidden" name="action" value="remove_member"/>
                                    <input type="hidden" name="forum_id" value="<?php echo (int) $forum['forum_id']; ?>"/>
                                    <input type="hidden" name="member_user_id" value="<?php echo (int) $member['user_id']; ?>"/>
                                    <button class="rounded-xl bg-error-container/30 px-3 py-2 text-[11px] font-black text-error transition-colors hover:bg-error-container/45" type="submit">Remove</button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <section class="grid gap-8 xl:grid-cols-[minmax(0,1fr)_320px]">
            <div class="space-y-8">
                <?php if ($isJoined): ?>
                <section class="rounded-[2rem] border border-outline-variant/10 bg-surface-container-lowest p-8 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-widest text-primary">New Post</p>
                    <h2 class="mt-2 font-headline text-2xl font-black text-on-surface">Start a discussion in this class</h2>
                    <form class="mt-6 space-y-4" method="post">
                        <input type="hidden" name="action" value="create_post"/>
                        <input type="hidden" name="forum_id" value="<?php echo (int) $forum['forum_id']; ?>"/>
                        <input type="hidden" name="return_to" value="top"/>
                        <div>
                            <label class="mb-2 block text-sm font-bold text-on-surface-variant" for="blogpost_title">Post title</label>
                            <input class="w-full rounded-2xl border-none bg-surface-container-low px-4 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/20" id="blogpost_title" name="blogpost_title" type="text"/>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-bold text-on-surface-variant" for="blogpost_body">Post body</label>
                            <textarea class="h-32 w-full rounded-3xl border-none bg-surface-container-low px-4 py-4 text-sm text-on-surface focus:ring-2 focus:ring-primary/20" id="blogpost_body" name="blogpost_body"></textarea>
                        </div>
                        <button class="rounded-2xl bg-primary px-5 py-3 text-sm font-black text-on-primary transition-opacity hover:opacity-90" type="submit">Publish Post</button>
                    </form>
                </section>
                <?php endif; ?>

                <section class="space-y-6">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-black uppercase tracking-widest text-primary">Discussion Feed</p>
                            <h2 class="mt-2 font-headline text-3xl font-black text-on-surface">Posts in <?php echo htmlspecialchars(dashboardShortCourseName($forum['CourseName'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></h2>
                        </div>
                        <span class="rounded-full bg-surface-container px-3 py-1 text-xs font-black text-on-surface-variant"><?php echo count($posts); ?> posts</span>
                    </div>

                    <?php if (empty($posts)): ?>
                    <article class="rounded-[2rem] border border-outline-variant/10 bg-surface-container-lowest p-10 text-center shadow-sm">
                        <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                            <span class="material-symbols-outlined text-4xl">forum</span>
                        </div>
                        <h3 class="font-headline text-2xl font-black text-on-surface">No posts yet</h3>
                        <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-on-surface-variant">
                            <?php echo $isJoined ? 'Be the first person to post in this class forum.' : 'Join this class forum to create the first post.'; ?>
                        </p>
                    </article>
                    <?php endif; ?>

                    <?php foreach ($posts as $post): ?>
                    <?php
                        $postId = (int) $post['blogpost_id'];
                        $postOwner = (int) $post['user_id'] === $userId;
                        $postComments = $commentsByPost[$postId] ?? [];
                    ?>
                    <article class="rounded-[2rem] border border-outline-variant/10 bg-surface-container-lowest p-8 shadow-sm" id="post-<?php echo $postId; ?>">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-black uppercase tracking-widest text-primary"><?php echo htmlspecialchars(forumDisplayUser($post), ENT_QUOTES, 'UTF-8'); ?></p>
                                <h3 class="mt-3 font-headline text-2xl font-black text-on-surface"><?php echo htmlspecialchars($post['blogpost_title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h3>
                                <p class="mt-2 text-xs font-bold text-on-surface-variant"><?php echo htmlspecialchars(forumTimeAgo($post['blogpost_timestamp'] ?? ''), ENT_QUOTES, 'UTF-8'); ?> • <?php echo (int) ($post['comment_count'] ?? 0); ?> comments</p>
                            </div>
                            <?php if ($postOwner): ?>
                            <div class="flex gap-2">
                                <button class="rounded-xl bg-surface-container px-3 py-2 text-xs font-black text-on-surface transition-colors hover:bg-surface-variant" type="button" data-toggle-target="edit-post-<?php echo $postId; ?>">Edit</button>
                                <form method="post" onsubmit="return confirm('Delete this post?');">
                                    <input type="hidden" name="action" value="delete_post"/>
                                    <input type="hidden" name="forum_id" value="<?php echo (int) $forum['forum_id']; ?>"/>
                                    <input type="hidden" name="blogpost_id" value="<?php echo $postId; ?>"/>
                                    <input type="hidden" name="return_to" value="post-<?php echo $postId; ?>"/>
                                    <button class="rounded-xl bg-error-container/30 px-3 py-2 text-xs font-black text-error transition-colors hover:bg-error-container/45" type="submit">Delete</button>
                                </form>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="mt-5 text-sm leading-relaxed text-on-surface-variant"><?php echo nl2br(htmlspecialchars($post['blogpost_body'] ?? '', ENT_QUOTES, 'UTF-8')); ?></div>

                        <?php if ($postOwner): ?>
                        <div class="mt-6 hidden rounded-3xl bg-surface-container p-5" id="edit-post-<?php echo $postId; ?>">
                            <form class="space-y-4" method="post">
                                <input type="hidden" name="action" value="update_post"/>
                                <input type="hidden" name="forum_id" value="<?php echo (int) $forum['forum_id']; ?>"/>
                                <input type="hidden" name="blogpost_id" value="<?php echo $postId; ?>"/>
                                <input type="hidden" name="return_to" value="post-<?php echo $postId; ?>"/>
                                <div>
                                    <label class="mb-2 block text-sm font-bold text-on-surface-variant">Edit title</label>
                                    <input class="w-full rounded-2xl border-none bg-surface-container-lowest px-4 py-3 text-sm text-on-surface" name="blogpost_title" type="text" value="<?php echo htmlspecialchars($post['blogpost_title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"/>
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-bold text-on-surface-variant">Edit body</label>
                                    <textarea class="h-28 w-full rounded-3xl border-none bg-surface-container-lowest px-4 py-4 text-sm text-on-surface" name="blogpost_body"><?php echo htmlspecialchars($post['blogpost_body'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </div>
                                <button class="rounded-2xl bg-primary px-4 py-3 text-sm font-black text-on-primary" type="submit">Save Post</button>
                            </form>
                        </div>
                        <?php endif; ?>

                        <div class="mt-8 border-t border-outline-variant/10 pt-6">
                            <div class="space-y-4">
                                <?php if (empty($postComments)): ?>
                                <p class="text-sm text-on-surface-variant">No comments yet.</p>
                                <?php endif; ?>

                                <?php foreach ($postComments as $comment): ?>
                                <?php
                                    $commentId = (int) $comment['comment_id'];
                                    $commentOwner = (int) $comment['user_id'] === $userId;
                                    $commentCanDelete = $commentOwner || $canManage;
                                ?>
                                <div class="rounded-3xl bg-surface-container p-5">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-black text-on-surface"><?php echo htmlspecialchars(forumDisplayUser($comment), ENT_QUOTES, 'UTF-8'); ?></p>
                                            <p class="mt-1 text-[11px] font-bold text-on-surface-variant"><?php echo htmlspecialchars(forumTimeAgo($comment['created_at'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                                        </div>
                                        <?php if ($commentOwner || $commentCanDelete): ?>
                                        <div class="flex gap-2">
                                            <?php if ($commentOwner): ?>
                                            <button class="rounded-xl bg-surface-container-lowest px-3 py-2 text-[11px] font-black text-on-surface" type="button" data-toggle-target="edit-comment-<?php echo $commentId; ?>">Edit</button>
                                            <?php endif; ?>
                                            <?php if ($commentCanDelete): ?>
                                            <form method="post" onsubmit="return confirm('Delete this comment?');">
                                                <input type="hidden" name="action" value="delete_comment"/>
                                                <input type="hidden" name="forum_id" value="<?php echo (int) $forum['forum_id']; ?>"/>
                                                <input type="hidden" name="comment_id" value="<?php echo $commentId; ?>"/>
                                                <input type="hidden" name="return_to" value="post-<?php echo $postId; ?>"/>
                                                <button class="rounded-xl bg-error-container/30 px-3 py-2 text-[11px] font-black text-error transition-colors hover:bg-error-container/45" type="submit">Delete</button>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <p class="mt-3 text-sm leading-relaxed text-on-surface-variant"><?php echo nl2br(htmlspecialchars($comment['comment_text'] ?? '', ENT_QUOTES, 'UTF-8')); ?></p>

                                    <?php if ($commentOwner): ?>
                                    <div class="mt-4 hidden rounded-3xl bg-surface-container-lowest p-4" id="edit-comment-<?php echo $commentId; ?>">
                                        <form class="space-y-3" method="post">
                                            <input type="hidden" name="action" value="update_comment"/>
                                            <input type="hidden" name="forum_id" value="<?php echo (int) $forum['forum_id']; ?>"/>
                                            <input type="hidden" name="comment_id" value="<?php echo $commentId; ?>"/>
                                            <input type="hidden" name="return_to" value="post-<?php echo $postId; ?>"/>
                                            <textarea class="h-24 w-full rounded-2xl border-none bg-surface-container px-4 py-3 text-sm text-on-surface" name="comment_text"><?php echo htmlspecialchars($comment['comment_text'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                            <button class="rounded-2xl bg-primary px-4 py-3 text-sm font-black text-on-primary" type="submit">Save Comment</button>
                                        </form>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <?php if ($isJoined): ?>
                            <form class="mt-6 space-y-3" method="post">
                                <input type="hidden" name="action" value="create_comment"/>
                                <input type="hidden" name="forum_id" value="<?php echo (int) $forum['forum_id']; ?>"/>
                                <input type="hidden" name="blogpost_id" value="<?php echo $postId; ?>"/>
                                <input type="hidden" name="return_to" value="post-<?php echo $postId; ?>"/>
                                <label class="block text-sm font-bold text-on-surface-variant">Add a comment</label>
                                <textarea class="h-24 w-full rounded-3xl border-none bg-surface-container px-4 py-4 text-sm text-on-surface focus:ring-2 focus:ring-primary/20" name="comment_text"></textarea>
                                <button class="rounded-2xl bg-secondary px-4 py-3 text-sm font-black text-white" type="submit">Post Comment</button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </section>
            </div>

            <div class="space-y-6">
                <section class="rounded-3xl bg-surface-container p-6">
                    <p class="text-xs font-black uppercase tracking-widest text-on-surface-variant">Course Snapshot</p>
                    <div class="mt-5 grid gap-3">
                        <div class="rounded-2xl bg-surface-container-lowest p-4">
                            <p class="text-[11px] font-black uppercase tracking-widest text-primary">School</p>
                            <p class="mt-2 text-sm font-bold text-on-surface"><?php echo htmlspecialchars($forum['school_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                        <div class="rounded-2xl bg-surface-container-lowest p-4">
                            <p class="text-[11px] font-black uppercase tracking-widest text-secondary">Course</p>
                            <p class="mt-2 text-sm font-bold text-on-surface"><?php echo htmlspecialchars(dashboardShortCourseName($forum['CourseName'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                        <div class="rounded-2xl bg-surface-container-lowest p-4">
                            <p class="text-[11px] font-black uppercase tracking-widest text-tertiary">Major</p>
                            <p class="mt-2 text-sm font-bold text-on-surface"><?php echo htmlspecialchars($forum['major_name'] ?? 'Open to students across the school', ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                    </div>
                </section>

                <section class="rounded-3xl border border-outline-variant/20 bg-surface-container-lowest p-6">
                    <p class="text-xs font-black uppercase tracking-widest text-on-surface-variant">Class Rules</p>
                    <div class="mt-4 space-y-3 text-sm leading-relaxed text-on-surface-variant">
                        <p>Only joined users can create posts and comments.</p>
                        <p>Users can edit or delete only the posts and comments they created.</p>
                        <p>Admins can update the classes they created and remove any comment inside those classes.</p>
                    </div>
                </section>
            </div>
        </section>

        <?php elseif ($showMissingForum): ?>
        <section class="rounded-[2rem] border border-outline-variant/10 bg-surface-container-lowest p-8 shadow-sm">
            <p class="text-xs font-black uppercase tracking-widest text-primary">Class Forum Missing</p>
            <h1 class="mt-2 font-headline text-4xl font-black text-on-surface">No forum exists yet for <?php echo htmlspecialchars($missingForumCourseName, ENT_QUOTES, 'UTF-8'); ?>.</h1>
            <p class="mt-4 max-w-2xl text-sm leading-relaxed text-on-surface-variant">
                This course belongs to <?php echo htmlspecialchars($missingForumSchoolName, ENT_QUOTES, 'UTF-8'); ?>. Once an admin creates the forum, students can join it and begin posting.
            </p>
        </section>

        <?php if ($isAdmin): ?>
        <section class="rounded-[2rem] border border-outline-variant/10 bg-surface-container-lowest p-8 shadow-sm">
            <p class="text-xs font-black uppercase tracking-widest text-primary">Create This Class Forum</p>
            <h2 class="mt-2 font-headline text-3xl font-black text-on-surface">Set up the class details</h2>
            <p class="mt-3 text-sm text-on-surface-variant">Choose the school first, then the course and major dropdowns will narrow to the matching options.</p>
            <form class="mt-6 grid gap-5 lg:grid-cols-2" method="post" id="create-forum-form">
                <input type="hidden" name="action" value="create_forum"/>
                <div>
                    <label class="mb-2 block text-sm font-bold text-on-surface-variant" for="school_id">School</label>
                    <select class="w-full rounded-2xl border-none bg-surface-container-low px-4 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/20" id="school_id" name="school_id" data-selected="<?php echo htmlspecialchars($createFormValues['school_id'], ENT_QUOTES, 'UTF-8'); ?>">
                        <option value="">Select a school</option>
                        <?php foreach ($schools as $school): ?>
                        <option value="<?php echo (int) $school['SchoolID']; ?>" <?php echo (string) $school['SchoolID'] === $createFormValues['school_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($school['Name'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold text-on-surface-variant" for="course_id">Course</label>
                    <select class="w-full rounded-2xl border-none bg-surface-container-low px-4 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/20 disabled:opacity-50" id="course_id" name="course_id" data-selected="<?php echo htmlspecialchars($createFormValues['course_id'], ENT_QUOTES, 'UTF-8'); ?>" disabled>
                        <option value="">Select a course</option>
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <label class="mb-2 block text-sm font-bold text-on-surface-variant" for="major_id_create">Major</label>
                    <select class="w-full rounded-2xl border-none bg-surface-container-low px-4 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/20 disabled:opacity-50" id="major_id_create" name="major_id" data-selected="<?php echo htmlspecialchars($createFormValues['major_id'], ENT_QUOTES, 'UTF-8'); ?>" disabled>
                        <option value="">No major filter</option>
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <label class="mb-2 block text-sm font-bold text-on-surface-variant" for="forum_name_create">Forum Name</label>
                    <input class="w-full rounded-2xl border-none bg-surface-container-low px-4 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/20" id="forum_name_create" name="forum_name" type="text" value="<?php echo htmlspecialchars($createFormValues['forum_name'], ENT_QUOTES, 'UTF-8'); ?>"/>
                </div>
                <div class="lg:col-span-2">
                    <label class="mb-2 block text-sm font-bold text-on-surface-variant" for="forum_description_create">Description</label>
                    <textarea class="h-36 w-full rounded-3xl border-none bg-surface-container-low px-4 py-4 text-sm text-on-surface focus:ring-2 focus:ring-primary/20" id="forum_description_create" name="forum_description"><?php echo htmlspecialchars($createFormValues['forum_description'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
                <div class="lg:col-span-2">
                    <button class="rounded-2xl bg-primary px-5 py-3 text-sm font-black text-on-primary transition-opacity hover:opacity-90" type="submit">Create Class Forum</button>
                </div>
            </form>
        </section>
        <?php endif; ?>

        <?php else: ?>
        <section class="rounded-[2rem] border border-outline-variant/10 bg-surface-container-lowest p-8 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <p class="text-xs font-black uppercase tracking-widest text-primary">Class Forums</p>
                    <h1 class="mt-2 font-headline text-4xl font-black text-on-surface">Browse classes by school, major, and course.</h1>
                    <p class="mt-4 text-sm leading-relaxed text-on-surface-variant">
                        Join a forum to participate. If you are an admin, you can also create new class forums and manage the ones you own.
                    </p>
                </div>
                <div class="grid grid-cols-2 gap-3 text-center">
                    <div class="rounded-2xl bg-surface-container p-4">
                        <p class="font-headline text-2xl font-black text-primary"><?php echo count($listingForums); ?></p>
                        <p class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">Live Forums</p>
                    </div>
                    <div class="rounded-2xl bg-surface-container p-4">
                        <p class="font-headline text-2xl font-black text-secondary"><?php echo count(array_filter($listingForums, static fn(array $item): bool => (int) ($item['current_user_joined'] ?? 0) === 1)); ?></p>
                        <p class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">Joined</p>
                    </div>
                </div>
            </div>
        </section>

        <?php if ($isAdmin): ?>
        <section class="rounded-[2rem] border border-outline-variant/10 bg-surface-container-lowest p-8 shadow-sm">
            <p class="text-xs font-black uppercase tracking-widest text-primary">Admin Setup</p>
            <h2 class="mt-2 font-headline text-3xl font-black text-on-surface">Create a new class forum</h2>
            <p class="mt-3 text-sm text-on-surface-variant">Pick a school first. The course and major dropdowns will unlock with only that school’s options.</p>
            <form class="mt-6 grid gap-5 lg:grid-cols-2" method="post" id="create-forum-form">
                <input type="hidden" name="action" value="create_forum"/>
                <div>
                    <label class="mb-2 block text-sm font-bold text-on-surface-variant" for="school_id">School</label>
                    <select class="w-full rounded-2xl border-none bg-surface-container-low px-4 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/20" id="school_id" name="school_id" data-selected="<?php echo htmlspecialchars($createFormValues['school_id'], ENT_QUOTES, 'UTF-8'); ?>">
                        <option value="">Select a school</option>
                        <?php foreach ($schools as $school): ?>
                        <option value="<?php echo (int) $school['SchoolID']; ?>" <?php echo (string) $school['SchoolID'] === $createFormValues['school_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($school['Name'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold text-on-surface-variant" for="course_id">Course</label>
                    <select class="w-full rounded-2xl border-none bg-surface-container-low px-4 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/20 disabled:opacity-50" id="course_id" name="course_id" data-selected="<?php echo htmlspecialchars($createFormValues['course_id'], ENT_QUOTES, 'UTF-8'); ?>" disabled>
                        <option value="">Select a course</option>
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <label class="mb-2 block text-sm font-bold text-on-surface-variant" for="major_id_create">Major</label>
                    <select class="w-full rounded-2xl border-none bg-surface-container-low px-4 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/20 disabled:opacity-50" id="major_id_create" name="major_id" data-selected="<?php echo htmlspecialchars($createFormValues['major_id'], ENT_QUOTES, 'UTF-8'); ?>" disabled>
                        <option value="">No major filter</option>
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <label class="mb-2 block text-sm font-bold text-on-surface-variant" for="forum_name_create">Forum Name</label>
                    <input class="w-full rounded-2xl border-none bg-surface-container-low px-4 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary/20" id="forum_name_create" name="forum_name" type="text" value="<?php echo htmlspecialchars($createFormValues['forum_name'], ENT_QUOTES, 'UTF-8'); ?>"/>
                </div>
                <div class="lg:col-span-2">
                    <label class="mb-2 block text-sm font-bold text-on-surface-variant" for="forum_description_create">Description</label>
                    <textarea class="h-36 w-full rounded-3xl border-none bg-surface-container-low px-4 py-4 text-sm text-on-surface focus:ring-2 focus:ring-primary/20" id="forum_description_create" name="forum_description"><?php echo htmlspecialchars($createFormValues['forum_description'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
                <div class="lg:col-span-2">
                    <button class="rounded-2xl bg-primary px-5 py-3 text-sm font-black text-on-primary transition-opacity hover:opacity-90" type="submit">Create Class Forum</button>
                </div>
            </form>
        </section>
        <?php endif; ?>

        <section class="grid gap-6 md:grid-cols-2 2xl:grid-cols-3">
            <?php if (empty($listingForums)): ?>
            <article class="rounded-[2rem] border border-outline-variant/10 bg-surface-container-lowest p-10 text-center shadow-sm md:col-span-2 2xl:col-span-3">
                <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                    <span class="material-symbols-outlined text-4xl">school</span>
                </div>
                <h3 class="font-headline text-2xl font-black text-on-surface">No class forums yet</h3>
                <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-on-surface-variant">Once an admin creates a course forum, it will show up here for students to join.</p>
            </article>
            <?php endif; ?>

            <?php foreach ($listingForums as $listedForum): ?>
            <?php
                $listedForumId = (int) $listedForum['forum_id'];
                $listedJoined = (int) ($listedForum['current_user_joined'] ?? 0) === 1;
                $listedOwner = (int) ($listedForum['created_by_user_id'] ?? 0) === $userId && $isAdmin;
            ?>
            <article class="rounded-[2rem] border border-outline-variant/10 bg-surface-container-lowest p-6 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <span class="rounded-full bg-primary/10 px-3 py-1 text-[11px] font-black uppercase tracking-widest text-primary"><?php echo htmlspecialchars($listedForum['school_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php if ($listedJoined): ?>
                    <span class="rounded-full bg-secondary-container px-3 py-1 text-[11px] font-black text-secondary">Joined</span>
                    <?php endif; ?>
                </div>
                <h3 class="mt-4 font-headline text-2xl font-black text-on-surface"><?php echo htmlspecialchars($listedForum['forum_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h3>
                <p class="mt-2 text-sm font-bold text-primary"><?php echo htmlspecialchars($listedForum['CourseName'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                <?php if (!empty($listedForum['major_name'])): ?>
                <p class="mt-2 text-[11px] font-bold uppercase tracking-widest text-secondary"><?php echo htmlspecialchars($listedForum['major_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
                <p class="mt-4 text-sm leading-relaxed text-on-surface-variant"><?php echo nl2br(htmlspecialchars($listedForum['forum_description'] ?? '', ENT_QUOTES, 'UTF-8')); ?></p>
                <div class="mt-5 flex flex-wrap gap-4 text-xs font-bold text-on-surface-variant">
                    <span><?php echo (int) ($listedForum['member_count'] ?? 0); ?> members</span>
                    <span><?php echo (int) ($listedForum['post_count'] ?? 0); ?> posts</span>
                    <span><?php echo !empty($listedForum['latest_post_at']) ? htmlspecialchars(forumTimeAgo($listedForum['latest_post_at']), ENT_QUOTES, 'UTF-8') : 'No posts yet'; ?></span>
                </div>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a class="rounded-2xl bg-primary px-4 py-3 text-sm font-black text-on-primary transition-opacity hover:opacity-90" href="forum.php?forum_id=<?php echo $listedForumId; ?>">Open Forum</a>
                    <?php if ($listedOwner): ?>
                    <a class="rounded-2xl bg-tertiary-container px-4 py-3 text-sm font-black text-on-tertiary-container" href="forum.php?forum_id=<?php echo $listedForumId; ?>">Manage</a>
                    <?php elseif (!$listedJoined): ?>
                    <form method="post">
                        <input type="hidden" name="action" value="join_course"/>
                        <input type="hidden" name="forum_id" value="<?php echo $listedForumId; ?>"/>
                        <button class="rounded-2xl bg-gradient-to-r from-primary to-primary-container px-4 py-3 text-sm font-black text-on-primary shadow-lg shadow-primary/20" type="submit">Join Forum</button>
                    </form>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </section>
        <?php endif; ?>
    </main>
</div>

<nav class="fixed bottom-0 left-0 right-0 z-50 flex h-16 items-center justify-around border-t border-outline-variant/10 bg-surface/90 px-4 backdrop-blur-md md:hidden">
    <a class="flex flex-col items-center gap-1 text-on-surface-variant" href="dashboard.php">
        <span class="material-symbols-outlined">hub</span>
        <span class="text-[10px] font-bold">Feed</span>
    </a>
    <a class="flex flex-col items-center gap-1 text-primary" href="forum.php">
        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">forum</span>
        <span class="text-[10px] font-bold">Classes</span>
    </a>
    <a class="flex flex-col items-center gap-1 text-on-surface-variant" href="logout.php">
        <span class="material-symbols-outlined">logout</span>
        <span class="text-[10px] font-bold">Logout</span>
    </a>
</nav>

<script>
    const catalogCourses = <?php echo json_encode(array_values($courses), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    const catalogMajors = <?php echo json_encode(array_values($majors), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;

    function togglePanels() {
        document.querySelectorAll('[data-toggle-target]').forEach((button) => {
            button.addEventListener('click', () => {
                const targetId = button.getAttribute('data-toggle-target');
                const target = document.getElementById(targetId);

                if (!target) {
                    return;
                }

                target.classList.toggle('hidden');
            });
        });
    }

    function renderOptions(select, items, valueKey, labelKey, placeholder, selectedValue) {
        select.innerHTML = '';

        const placeholderOption = document.createElement('option');
        placeholderOption.value = '';
        placeholderOption.textContent = placeholder;
        select.appendChild(placeholderOption);

        items.forEach((item) => {
            const option = document.createElement('option');
            option.value = String(item[valueKey]);
            option.textContent = item[labelKey];

            if (selectedValue !== '' && option.value === selectedValue) {
                option.selected = true;
            }

            select.appendChild(option);
        });
    }

    function setupCreateForumForm() {
        const form = document.getElementById('create-forum-form');

        if (!form) {
            return;
        }

        const schoolSelect = form.querySelector('#school_id');
        const courseSelect = form.querySelector('#course_id');
        const majorSelect = form.querySelector('#major_id_create');
        const forumNameInput = form.querySelector('#forum_name_create');

        function refreshDependentDropdowns() {
            const schoolId = schoolSelect.value;
            const filteredCourses = catalogCourses.filter((course) => String(course.SchoolID) === schoolId);
            const filteredMajors = catalogMajors.filter((major) => String(major.SchoolID) === schoolId);
            const selectedCourse = courseSelect.dataset.selected || courseSelect.value || '';
            const selectedMajor = majorSelect.dataset.selected || majorSelect.value || '';

            renderOptions(courseSelect, filteredCourses, 'CourseID', 'CourseName', 'Select a course', selectedCourse);
            renderOptions(majorSelect, filteredMajors, 'MajorID', 'Name', 'No major filter', selectedMajor);

            const hasSchool = schoolId !== '';
            courseSelect.disabled = !hasSchool;
            majorSelect.disabled = !hasSchool;

            if (hasSchool && courseSelect.value !== '') {
                const activeCourse = filteredCourses.find((course) => String(course.CourseID) === courseSelect.value);

                if (activeCourse && forumNameInput.value.trim() === '') {
                    forumNameInput.value = activeCourse.CourseName;
                }
            }
        }

        schoolSelect.addEventListener('change', () => {
            courseSelect.dataset.selected = '';
            majorSelect.dataset.selected = '';
            refreshDependentDropdowns();
        });

        courseSelect.addEventListener('change', () => {
            const activeCourse = catalogCourses.find((course) => String(course.CourseID) === courseSelect.value);

            if (activeCourse && forumNameInput.value.trim() === '') {
                forumNameInput.value = activeCourse.CourseName;
            }
        });

        refreshDependentDropdowns();
    }

    togglePanels();
    setupCreateForumForm();
</script>
<?php renderAccountMenuScript(); ?>
</body>
</html>

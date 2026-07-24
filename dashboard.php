<?php require_once __DIR__ . '/includes/image-config.php'; ?>
<?php require_once __DIR__ . '/includes/auth.php'; ?>
<?php require_once __DIR__ . '/includes/account-menu.php'; ?>
<?php require_once __DIR__ . '/includes/dashboard-data.php'; ?>
<?php
$currentUser = requireLogin();
$userId = (int) $currentUser['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'toggle_like') {
        $blogpostId = (int) ($_POST['blogpost_id'] ?? 0);

        try {
            dashboardTogglePostLike($userId, $blogpostId);
        } catch (Throwable $error) {
            error_log('Unable to toggle dashboard like: ' . $error->getMessage());
            setAuthMessage($error->getMessage() !== '' ? $error->getMessage() : 'We could not update that like yet. Please try again.');
        }

        header('Location: dashboard.php#post-' . $blogpostId);
        exit;
    }
}

$displayName = userDisplayName($currentUser);
$firstName = trim($currentUser['first_name'] ?? '') ?: 'Student';
$dashboardMessage = consumeAuthMessage();
$dashboardError = '';
$joinedCourses = [];
$dashboardPosts = [];
$trendingPosts = [];
$activeForums = [];
$limitFeedToJoinedCourses = false;
$feedFallbackToCampus = false;

try {
    $joinedCourses = dashboardJoinedCourses($userId);
    $limitFeedToJoinedCourses = count($joinedCourses) > 0;
    $dashboardPosts = dashboardFeedPosts($userId, $limitFeedToJoinedCourses, 10);

    if ($limitFeedToJoinedCourses && empty($dashboardPosts)) {
        $dashboardPosts = dashboardFeedPosts($userId, false, 10);
        $feedFallbackToCampus = true;
    }

    $trendingPosts = dashboardTrendingPosts(3);
    $activeForums = dashboardActiveForums(3);
} catch (Throwable $error) {
    error_log('Unable to load dashboard data: ' . $error->getMessage());
    $dashboardError = 'We could not load the dashboard feed from the database yet. Please confirm the updated tables were imported in cPanel.';
}
?>
<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Scholarly Pulse | UWM Student Dashboard</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@400;700;900&amp;family=Manrope:wght@400;500;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
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
            borderRadius: {"DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"},
          },
        },
      }
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
<header class="sticky top-0 z-50 w-full border-b border-outline-variant/20 bg-[#fff4f4]/85 px-4 py-3 shadow-sm backdrop-blur-md dark:shadow-none md:px-6">
<div class="mx-auto flex max-w-[1600px] items-center justify-between gap-4">
<div class="flex min-w-0 flex-1 items-center gap-5 lg:gap-8">
<a class="shrink-0 text-2xl font-black tracking-tight text-[#b12029] dark:text-[#ff7672] font-headline" href="dashboard.php">Scholarly Pulse</a>
</div>
<nav aria-label="Dashboard navigation" class="hidden items-center gap-2 font-headline text-base font-bold lg:flex">
<a class="rounded-full bg-primary px-4 py-2 text-on-primary shadow-sm transition-all hover:shadow-md" href="dashboard.php">Feed</a>
<a class="rounded-full px-4 py-2 text-[#4d212a] transition-colors hover:bg-[#ffe1e4] hover:text-primary dark:text-[#dd9ca7]" href="forum.php">Class Forums</a>
<a class="rounded-full px-4 py-2 text-[#4d212a] transition-colors hover:bg-[#ffe1e4] hover:text-primary dark:text-[#dd9ca7]" href="#">Library</a>
</nav>
<div class="flex items-center gap-2 sm:gap-3">
<button aria-label="Open notifications" class="relative rounded-full p-2 text-on-surface-variant transition-transform hover:bg-surface-container active:scale-95" type="button">
<span class="material-symbols-outlined">notifications</span>
<span class="absolute right-2 top-2 h-2.5 w-2.5 rounded-full border-2 border-background bg-primary"></span>
</button>
<?php renderAccountMenu($currentUser); ?>
</div>
</div>
</header>
<div class="flex max-w-[1600px] mx-auto min-h-screen">
<aside class="hidden lg:flex flex-col w-64 fixed left-0 top-0 pt-20 h-screen bg-[#ffecee] dark:bg-[#251617] px-4">
<div class="mb-6 px-4">
<h2 class="text-[#b12029] font-headline font-bold text-xl mb-1">Dashboard</h2>
<p class="text-on-surface-variant text-xs font-medium">Signed in as <?php echo htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8'); ?></p>
</div>
<nav aria-label="Main dashboard navigation" class="flex flex-col gap-1">
<a class="flex items-center gap-3 rounded-r-full bg-[#fff4f4] px-4 py-3 font-bold text-[#b12029] transition-transform hover:translate-x-1 dark:bg-[#3d2427] dark:text-[#ff7672]" href="dashboard.php">
<span class="material-symbols-outlined">home</span>
<span class="font-['Manrope'] text-sm font-bold">Home Feed</span>
</a>
<a class="flex items-center gap-3 rounded-r-full px-4 py-3 text-[#4d212a] transition-all hover:translate-x-1 hover:bg-[#ffe1e4] dark:text-[#dd9ca7]" href="forum.php">
<span class="material-symbols-outlined">menu_book</span>
<span class="font-['Manrope'] text-sm font-semibold">My Classes</span>
</a>
<a class="flex items-center gap-3 rounded-r-full px-4 py-3 text-[#4d212a] transition-all hover:translate-x-1 hover:bg-[#ffe1e4] dark:text-[#dd9ca7]" href="#">
<span class="material-symbols-outlined">star_half</span>
<span class="font-['Manrope'] text-sm font-semibold">Professor Ratings</span>
</a>
<a class="flex items-center gap-3 rounded-r-full px-4 py-3 text-[#4d212a] transition-all hover:translate-x-1 hover:bg-[#ffe1e4] dark:text-[#dd9ca7]" href="#">
<span class="material-symbols-outlined">favorite</span>
<span class="font-['Manrope'] text-sm font-semibold">Liked Posts</span>
</a>
<a class="flex items-center gap-3 rounded-r-full px-4 py-3 text-[#4d212a] transition-all hover:translate-x-1 hover:bg-[#ffe1e4] dark:text-[#dd9ca7]" href="#">
<span class="material-symbols-outlined">bookmark</span>
<span class="font-['Manrope'] text-sm font-semibold">Bookmarks</span>
</a>
</nav>
<section class="mt-8 rounded-3xl border border-outline-variant/20 bg-surface-container-lowest/60 p-4">
<div class="mb-4 flex items-center justify-between">
<p class="text-on-surface-variant text-[10px] uppercase tracking-widest font-black">My Courses</p>
<span class="rounded-full bg-primary/10 px-2 py-1 text-[10px] font-black text-primary"><?php echo count($joinedCourses); ?></span>
</div>
<div class="flex flex-col gap-2">
<?php if (empty($joinedCourses)): ?>
<div class="rounded-2xl bg-surface-container p-4 text-sm text-on-surface-variant">
<p class="font-bold text-on-surface">No joined courses yet</p>
<p class="mt-1 text-xs leading-relaxed">Join a class forum to have it appear here.</p>
</div>
<?php else: ?>
<?php foreach ($joinedCourses as $course): ?>
<?php
    $courseId = (int) $course['CourseID'];
    $forumId = (int) ($course['forum_id'] ?? 0);
    $courseHref = $forumId > 0 ? 'forum.php?forum_id=' . $forumId : 'forum.php?course_id=' . $courseId;
?>
<a class="group flex items-center gap-3 rounded-2xl px-2 py-2 transition-colors hover:bg-surface-container" href="<?php echo htmlspecialchars($courseHref, ENT_QUOTES, 'UTF-8'); ?>">
<div class="h-8 w-8 shrink-0 rounded-lg bg-primary-container flex items-center justify-center text-on-primary-container font-black text-xs"><?php echo htmlspecialchars(dashboardCourseInitials($course['CourseName'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
<div class="min-w-0">
<p class="truncate text-sm font-bold text-on-surface group-hover:text-primary"><?php echo htmlspecialchars(dashboardShortCourseName($course['CourseName'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
<p class="truncate text-[11px] text-on-surface-variant"><?php echo htmlspecialchars(dashboardCourseDescription($course['CourseName'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
</div>
</a>
<?php endforeach; ?>
<?php endif; ?>
</div>
</section>
<div class="mt-auto pb-8 border-t border-outline-variant/20 pt-4">
<a class="flex items-center gap-3 px-4 py-2 text-on-surface-variant hover:text-primary transition-colors" href="support.php">
<span class="material-symbols-outlined text-xl">help</span>
<span class="text-sm">Support</span>
</a>
<a class="flex items-center gap-3 px-4 py-2 text-on-surface-variant hover:text-primary transition-colors" href="logout.php">
<span class="material-symbols-outlined text-xl">logout</span>
<span class="text-sm">Log Out</span>
</a>
</div>
</aside>
<main class="flex-1 lg:ml-64 px-4 md:px-8 pt-8 pb-20">
<div class="max-w-4xl mx-auto">
<?php if ($dashboardMessage !== ''): ?>
<div class="mb-6 rounded-3xl border border-error/20 bg-error-container/20 p-4 text-sm font-bold text-on-error-container">
<?php echo htmlspecialchars($dashboardMessage, ENT_QUOTES, 'UTF-8'); ?>
</div>
<?php endif; ?>
<section class="mb-8 rounded-[2rem] bg-surface-container-lowest p-8 shadow-sm border border-outline-variant/10">
<div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
<div>
<p class="text-xs font-black uppercase tracking-widest text-primary mb-2">Home Feed</p>
<h1 class="font-headline font-black text-4xl text-on-surface mb-3">Welcome back, <?php echo htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8'); ?>.</h1>
<p class="max-w-2xl text-on-surface-variant">
<?php if ($limitFeedToJoinedCourses): ?>
<?php if ($feedFallbackToCampus): ?>
Your joined courses do not have forum posts yet, so recent campus posts are shown for testing.
<?php else: ?>
Showing recent posts from the courses you have joined.
<?php endif; ?>
<?php else: ?>
Showing recent campus posts while you build your joined course list.
<?php endif; ?>
</p>
</div>
<div class="grid grid-cols-2 gap-3 text-center">
<div class="rounded-2xl bg-surface-container-low p-4">
<p class="font-headline text-2xl font-black text-primary"><?php echo count($joinedCourses); ?></p>
<p class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">Joined Courses</p>
</div>
<div class="rounded-2xl bg-surface-container-low p-4">
<p class="font-headline text-2xl font-black text-secondary"><?php echo count($dashboardPosts); ?></p>
<p class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">Feed Posts</p>
</div>
</div>
</div>
</section>
<?php if ($dashboardError !== ''): ?>
<section class="mb-8 rounded-3xl border border-error/20 bg-error-container/20 p-6 text-on-error-container">
<p class="font-headline text-xl font-black">Dashboard data needs attention</p>
<p class="mt-2 text-sm leading-relaxed"><?php echo htmlspecialchars($dashboardError, ENT_QUOTES, 'UTF-8'); ?></p>
</section>
<?php endif; ?>
<div class="mb-8 flex items-center justify-between gap-4">
<div>
<h2 class="font-headline text-2xl font-black text-on-surface">Latest Discussions</h2>
<p class="text-sm text-on-surface-variant">Posts are loaded from your database and ranked by recency first.</p>
</div>
<?php if (!empty($joinedCourses)): ?>
<?php
    $firstCourse = $joinedCourses[0];
    $firstForumId = (int) ($firstCourse['forum_id'] ?? 0);
    $firstCourseId = (int) ($firstCourse['CourseID'] ?? 0);
    $newPostHref = $firstForumId > 0 ? 'forum.php?forum_id=' . $firstForumId : 'forum.php?course_id=' . $firstCourseId;
?>
<a class="hidden rounded-full bg-gradient-to-r from-primary to-primary-container px-5 py-3 text-sm font-black text-on-primary shadow-lg shadow-primary/10 transition-all hover:shadow-xl md:inline-flex" href="<?php echo htmlspecialchars($newPostHref, ENT_QUOTES, 'UTF-8'); ?>">
Post in a Course
</a>
<?php endif; ?>
</div>
<div class="flex flex-col gap-8">
<?php if (!$limitFeedToJoinedCourses && $dashboardError === ''): ?>
<section class="rounded-[2rem] border border-primary/10 bg-gradient-to-r from-primary/5 via-surface-container-lowest to-tertiary-container/30 p-8 shadow-sm">
<div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
<div class="max-w-2xl">
<p class="text-xs font-black uppercase tracking-widest text-primary">Start Here</p>
<h2 class="mt-2 font-headline text-3xl font-black text-on-surface">Join your first class forum.</h2>
<p class="mt-3 text-sm leading-relaxed text-on-surface-variant">New users can preview posts from across campus below, but joining a class is what unlocks likes, comments, and posting.</p>
</div>
<a class="inline-flex items-center justify-center rounded-3xl bg-gradient-to-r from-primary to-primary-container px-6 py-4 text-sm font-black text-on-primary shadow-lg shadow-primary/20 transition-all hover:-translate-y-0.5 hover:shadow-xl" href="forum.php">
Browse and Join Classes
</a>
</div>
</section>
<?php endif; ?>
<?php if (empty($dashboardPosts) && $dashboardError === ''): ?>
<section class="rounded-[2rem] bg-surface-container-lowest p-10 text-center shadow-sm border border-outline-variant/10">
<div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-primary/10 text-primary">
<span class="material-symbols-outlined text-4xl">forum</span>
</div>
<h2 class="font-headline text-2xl font-black text-on-surface">No dashboard posts yet</h2>
<p class="mx-auto mt-3 max-w-lg text-on-surface-variant">Once forum posts are added to joined courses, they will appear here automatically from the database.</p>
</section>
<?php endif; ?>
<?php foreach ($dashboardPosts as $post): ?>
<?php
    $postId = (int) $post['blogpost_id'];
    $isLiked = ($post['current_user_reaction'] ?? '') === 'like';
    $likeCount = (int) ($post['like_count'] ?? 0);
    $commentCount = (int) ($post['comment_count'] ?? 0);
    $postImage = trim((string) ($post['image_url'] ?? ''));
    $postImageUrl = $postImage !== '' ? site_asset_url($postImage) : '';
    $forumId = (int) ($post['forum_id'] ?? 0);
    $courseId = (int) ($post['course_id'] ?? 0);
    $postHref = $forumId > 0 ? 'forum.php?forum_id=' . $forumId : ($courseId > 0 ? 'forum.php?course_id=' . $courseId : '#');
    $canInteractWithPost = (int) ($post['current_user_joined_course'] ?? 0) === 1;
?>
<article class="group flex gap-4 md:gap-6" id="post-<?php echo $postId; ?>">
<aside class="flex flex-col items-center gap-2">
<?php if ($canInteractWithPost): ?>
<form action="dashboard.php#post-<?php echo $postId; ?>" method="post">
<input name="action" type="hidden" value="toggle_like"/>
<input name="blogpost_id" type="hidden" value="<?php echo $postId; ?>"/>
<button aria-label="<?php echo $isLiked ? 'Unlike post' : 'Like post'; ?>" class="flex h-11 w-11 items-center justify-center rounded-xl <?php echo $isLiked ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface hover:bg-primary-container/30'; ?> transition-colors" type="submit">
<span class="material-symbols-outlined" style="<?php echo $isLiked ? "font-variation-settings: 'FILL' 1;" : ''; ?>">favorite</span>
</button>
</form>
<?php else: ?>
<a aria-label="Join this class to interact" class="flex h-11 w-11 items-center justify-center rounded-xl bg-surface-container text-on-surface-variant transition-colors hover:bg-primary-container/30 hover:text-primary" href="<?php echo htmlspecialchars($postHref, ENT_QUOTES, 'UTF-8'); ?>">
<span class="material-symbols-outlined">lock</span>
</a>
<?php endif; ?>
<span class="font-headline font-black text-primary"><?php echo htmlspecialchars(dashboardFormatCount($likeCount), ENT_QUOTES, 'UTF-8'); ?></span>
</aside>
<div class="flex-1 rounded-[2rem] bg-surface-container-lowest p-6 shadow-sm transition-all hover:shadow-md md:p-8">
<header class="mb-4 flex flex-wrap items-center gap-3">
<div class="flex h-7 w-7 items-center justify-center rounded-full bg-primary-container text-[10px] font-black text-on-primary-container">
<?php echo htmlspecialchars(substr(dashboardAuthorName($post), 0, 1), ENT_QUOTES, 'UTF-8'); ?>
</div>
<a class="text-xs font-black text-primary hover:underline" href="<?php echo htmlspecialchars($postHref, ENT_QUOTES, 'UTF-8'); ?>">
<?php echo htmlspecialchars(dashboardForumLabel($post), ENT_QUOTES, 'UTF-8'); ?>
</a>
<span class="text-[10px] text-on-surface-variant">Posted by <?php echo htmlspecialchars(dashboardAuthorName($post), ENT_QUOTES, 'UTF-8'); ?></span>
<span class="text-[10px] text-on-surface-variant"><?php echo htmlspecialchars(dashboardTimeAgo($post['blogpost_timestamp'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
<?php if (!$canInteractWithPost): ?>
<span class="rounded-full bg-secondary-container px-3 py-1 text-[10px] font-black uppercase tracking-widest text-secondary">Join class to interact</span>
<?php endif; ?>
</header>
<h2 class="mb-4 font-headline text-2xl font-bold text-on-surface transition-colors group-hover:text-primary">
<a href="<?php echo htmlspecialchars($postHref, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($post['blogpost_title'] ?? 'Untitled post', ENT_QUOTES, 'UTF-8'); ?></a>
</h2>
<p class="mb-6 leading-relaxed text-on-surface-variant"><?php echo nl2br(htmlspecialchars($post['blogpost_body'] ?? '', ENT_QUOTES, 'UTF-8')); ?></p>
<?php if ($postImageUrl !== ''): ?>
<div class="mb-6 overflow-hidden rounded-2xl bg-surface-container">
<img alt="Post attachment" class="max-h-[420px] w-full object-cover" src="<?php echo htmlspecialchars($postImageUrl, ENT_QUOTES, 'UTF-8'); ?>"/>
</div>
<?php endif; ?>
<footer class="flex flex-wrap items-center gap-5">
<a class="flex items-center gap-2 text-on-surface-variant transition-colors hover:text-primary" href="<?php echo htmlspecialchars($postHref, ENT_QUOTES, 'UTF-8'); ?>">
<span class="material-symbols-outlined text-lg">chat_bubble</span>
<span class="text-xs font-bold"><?php echo $canInteractWithPost ? $commentCount . ' Comments' : 'Preview Discussion'; ?></span>
</a>
<button class="flex items-center gap-2 <?php echo $canInteractWithPost ? 'text-on-surface-variant hover:text-primary' : 'cursor-not-allowed text-on-surface-variant/40'; ?> transition-colors" type="button" <?php echo $canInteractWithPost ? '' : 'disabled'; ?>>
<span class="material-symbols-outlined text-lg">bookmark</span>
<span class="text-xs font-bold">Save</span>
</button>
<?php if (!$canInteractWithPost): ?>
<a class="inline-flex items-center rounded-full bg-gradient-to-r from-primary to-primary-container px-4 py-2 text-[11px] font-black uppercase tracking-widest text-on-primary shadow-lg shadow-primary/20" href="<?php echo htmlspecialchars($postHref, ENT_QUOTES, 'UTF-8'); ?>">
Join Forum
</a>
<?php endif; ?>
<?php if (!empty($post['CourseName'])): ?>
<span class="ml-auto rounded-full bg-surface-container px-3 py-1 text-[10px] font-black uppercase tracking-widest text-on-surface-variant">
<?php echo htmlspecialchars(dashboardShortCourseName($post['CourseName']), ENT_QUOTES, 'UTF-8'); ?>
</span>
<?php endif; ?>
</footer>
</div>
</article>
<?php endforeach; ?>
</div>
</div>
</main>
<aside class="hidden xl:block w-80 p-8 pt-20">
<div class="sticky top-28 space-y-6">
<section class="rounded-3xl bg-surface-container p-6">
<h3 class="font-headline font-black text-on-surface uppercase tracking-tight text-sm mb-5 flex items-center gap-2">
<span class="material-symbols-outlined text-primary">bolt</span>
                        Trending Discussions
                    </h3>
<div class="space-y-4">
<?php if (empty($trendingPosts)): ?>
<div class="rounded-2xl bg-surface-container-lowest/70 p-4">
<p class="text-sm font-bold text-on-surface">No trending posts yet</p>
<p class="mt-1 text-[11px] text-on-surface-variant">Likes and comments will power this panel.</p>
</div>
<?php else: ?>
<?php foreach ($trendingPosts as $index => $trendingPost): ?>
<?php
    $trendingPostId = (int) $trendingPost['blogpost_id'];
    $trendingRankColor = $index === 0 ? 'text-primary' : ($index === 1 ? 'text-secondary' : 'text-tertiary');
?>
<a class="block rounded-2xl <?php echo $index === 0 ? 'bg-surface-container-lowest/70' : ''; ?> p-4 transition-colors hover:bg-surface-container-low" href="dashboard.php#post-<?php echo $trendingPostId; ?>">
<span class="<?php echo $trendingRankColor; ?> text-[10px] font-black uppercase tracking-widest">#<?php echo $index + 1; ?> <?php echo htmlspecialchars(dashboardShortCourseName($trendingPost['CourseName'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
<p class="mt-1 text-sm font-bold leading-snug text-on-surface"><?php echo htmlspecialchars($trendingPost['blogpost_title'] ?? 'Untitled post', ENT_QUOTES, 'UTF-8'); ?></p>
<p class="mt-1 text-[11px] text-on-surface-variant"><?php echo (int) ($trendingPost['comment_count'] ?? 0); ?> comments - <?php echo (int) ($trendingPost['like_count'] ?? 0); ?> likes</p>
</a>
<?php endforeach; ?>
<?php endif; ?>
</div>
</section>
<section class="rounded-3xl border border-outline-variant/20 bg-surface-container-lowest p-6">
<h3 class="font-headline font-black text-on-surface uppercase tracking-tight text-sm mb-5 flex items-center gap-2">
<span class="material-symbols-outlined text-secondary">tag</span>
                        Popular Topics
                    </h3>
<div class="flex flex-wrap gap-2">
<a class="rounded-full bg-primary/10 px-3 py-1.5 text-xs font-bold text-primary hover:bg-primary/20" href="#">#finals</a>
<a class="rounded-full bg-secondary-container/50 px-3 py-1.5 text-xs font-bold text-secondary hover:bg-secondary-container" href="#">#professors</a>
<a class="rounded-full bg-tertiary-container/50 px-3 py-1.5 text-xs font-bold text-tertiary hover:bg-tertiary-container" href="#">#study-groups</a>
<a class="rounded-full bg-surface-container px-3 py-1.5 text-xs font-bold text-on-surface-variant hover:text-primary" href="#">#parking</a>
<a class="rounded-full bg-surface-container px-3 py-1.5 text-xs font-bold text-on-surface-variant hover:text-primary" href="#">#lecture-notes</a>
</div>
</section>
<section class="rounded-3xl bg-gradient-to-br from-tertiary/10 to-transparent p-6 border border-tertiary-container/20">
<h3 class="font-headline font-black text-on-surface uppercase tracking-tight text-sm mb-5 flex items-center gap-2">
<span class="material-symbols-outlined text-tertiary">forum</span>
                        Active Class Forums
                    </h3>
<div class="space-y-4">
<?php if (empty($activeForums)): ?>
<div class="rounded-2xl p-3">
<p class="text-sm font-black text-on-surface">No active forums yet</p>
<p class="mt-1 text-[11px] text-on-surface-variant">Create forums for courses to populate this section.</p>
</div>
<?php else: ?>
<?php foreach ($activeForums as $forumIndex => $forum): ?>
<a class="flex items-center justify-between gap-4 rounded-2xl p-3 transition-colors hover:bg-surface-container-lowest" href="forum.php?forum_id=<?php echo (int) $forum['forum_id']; ?>">
<div>
<p class="text-sm font-black text-on-surface"><?php echo htmlspecialchars(dashboardShortCourseName($forum['CourseName'] ?? $forum['forum_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
<p class="text-[11px] text-on-surface-variant"><?php echo (int) ($forum['post_count'] ?? 0); ?> posts<?php echo !empty($forum['latest_post_at']) ? ' - ' . htmlspecialchars(dashboardTimeAgo($forum['latest_post_at']), ENT_QUOTES, 'UTF-8') : ''; ?></p>
</div>
<?php if ($forumIndex === 0): ?>
<span class="rounded-full bg-primary/10 px-2 py-1 text-[10px] font-black text-primary">LIVE</span>
<?php else: ?>
<span class="text-[10px] font-bold text-on-surface-variant">Open</span>
<?php endif; ?>
</a>
<?php endforeach; ?>
<?php endif; ?>
</div>
</section>
<?php if (!empty($trendingPosts)): ?>
<?php $hotPost = $trendingPosts[0]; ?>
<section class="rounded-3xl bg-primary p-6 text-on-primary shadow-xl shadow-primary/10">
<p class="text-[10px] font-black uppercase tracking-widest opacity-80">Campus Hot Post</p>
<h3 class="mt-2 font-headline text-xl font-black leading-tight"><?php echo htmlspecialchars($hotPost['blogpost_title'] ?? 'Campus discussion', ENT_QUOTES, 'UTF-8'); ?></h3>
<p class="mt-3 text-sm text-on-primary/80"><?php echo (int) ($hotPost['comment_count'] ?? 0); ?> comments and <?php echo (int) ($hotPost['like_count'] ?? 0); ?> likes so far.</p>
<a class="mt-5 inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-2 text-xs font-black hover:bg-white/25" href="dashboard.php#post-<?php echo (int) $hotPost['blogpost_id']; ?>">
                        View discussion
                        <span class="material-symbols-outlined text-sm">arrow_forward</span>
</a>
</section>
<?php endif; ?>
</div>
</aside>
</div>
<nav class="md:hidden fixed bottom-0 left-0 right-0 bg-surface/90 backdrop-blur-md border-t border-outline-variant/10 flex justify-around items-center h-16 px-4 z-50">
<a class="flex flex-col items-center gap-1 text-primary" href="dashboard.php">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">hub</span>
<span class="text-[10px] font-bold">Feed</span>
</a>
<a class="flex flex-col items-center gap-1 text-on-surface-variant" href="forum.php">
<span class="material-symbols-outlined">forum</span>
<span class="text-[10px] font-bold">Classes</span>
</a>
<a class="flex flex-col items-center gap-1 text-on-surface-variant" href="logout.php">
<span class="material-symbols-outlined">logout</span>
<span class="text-[10px] font-bold">Logout</span>
</a>
</nav>
<?php renderAccountMenuScript(); ?>
</body></html>

<?php require_once __DIR__ . '/includes/image-config.php'; ?>
<?php require_once __DIR__ . '/includes/dashboard-data.php'; ?>
<?php
$currentPage = 'home';
$headerCtaHref = 'login.php';
$headerCtaLabel = 'Log In';
$footerMarginClass = 'mt-0';
$forumPreviewPost = null;

try {
    $forumPreviewPost = dashboardPublicForumPreviewPost();
} catch (Throwable $error) {
    error_log('Unable to load homepage forum preview: ' . $error->getMessage());
}
?>
<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Scholarly Pulse | UWM Academic Community</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@700;800;900&amp;family=Manrope:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
              "surface-dim": "#ffc6ce",
              "outline-variant": "#dd9ca7",
              "tertiary-fixed-dim": "#ebc24a",
              "surface-variant": "#ffd2d8",
              "on-background": "#4d212a",
              "surface-container-highest": "#ffd2d8",
              "background": "#fff4f4",
              "surface-tint": "#b12029",
              "secondary-container": "#ffc5ae",
              "primary": "#b12029",
              "error": "#b02500",
              "on-surface": "#4d212a",
              "surface-container-high": "#ffd9de",
              "tertiary": "#715800",
              "surface-container-low": "#ffecee",
              "secondary": "#9b3f14",
              "on-surface-variant": "#814c56",
              "primary-container": "#ff7672",
              "primary-dim": "#a0101f",
              "on-secondary-container": "#802c00",
              "surface-container": "#ffe1e4",
              "surface-container-lowest": "#ffffff",
              "tertiary-container": "#fad056",
              "outline": "#a06771",
              "surface": "#fff4f4",
              "on-primary-container": "#4e0007",
              "on-secondary": "#ffefea",
              "on-primary": "#ffefee",
              "on-tertiary-container": "#5b4600"
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
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .glass-card {
            background: rgba(255, 210, 216, 0.6);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
    </style>
</head>
<body class="bg-background text-on-surface font-body selection:bg-secondary-container selection:text-on-secondary-container">
<?php require __DIR__ . '/header.php'; ?>
<main>
<section class="relative min-h-[760px] flex items-center pt-12 overflow-hidden">
<div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(255,118,114,0.20),_transparent_34%),radial-gradient(circle_at_bottom_left,_rgba(250,208,86,0.24),_transparent_32%)]"></div>
<div class="max-w-7xl mx-auto px-8 w-full grid grid-cols-1 lg:grid-cols-2 gap-12 items-center relative z-10">
<div>
<span class="inline-block px-4 py-2 bg-tertiary-container text-on-tertiary-container rounded-full text-sm font-black uppercase tracking-widest mb-6">UWM Academic Community</span>
<h1 class="font-headline font-black text-6xl md:text-7xl lg:text-8xl text-primary leading-[1.05] tracking-tight mb-8">
                        Connect with other students
                    </h1>
<p class="text-on-surface-variant text-xl md:text-2xl max-w-xl mb-10 leading-relaxed">
                        Join class forums, browse professor insights, share lecture highlights, and keep your academic community in one collaborative space.
                    </p>
<div class="flex flex-col sm:flex-row gap-4">
<a class="bg-primary text-on-primary px-10 py-5 rounded-full font-bold text-xl hover:shadow-xl hover:-translate-y-1 transition-all text-center" href="register.php">
                                Create Account
                            </a>
<a class="bg-surface-container-lowest text-primary border-2 border-primary/20 px-10 py-5 rounded-full font-bold text-xl hover:bg-primary/5 transition-colors text-center" href="login.php">
                                Log In
                            </a>
</div>
</div>
<div class="relative h-[430px] lg:h-[620px] flex items-center justify-center">
<div class="absolute inset-0 bg-primary-container/20 rounded-full blur-[100px] -z-10 animate-pulse"></div>
<img alt="Student focusing on school work using a laptop and notepad" class="w-full h-full drop-shadow-2xl object-cover rounded-[2.5rem]" src="<?php echo htmlspecialchars(site_image_url('hero_student'), ENT_QUOTES, 'UTF-8'); ?>"/>
</div>
</div>
</section>
<section class="py-24 px-8 max-w-7xl mx-auto" id="forums">
<div class="grid grid-cols-1 md:grid-cols-12 gap-8">
<div class="md:col-span-7 bg-surface-container-lowest p-10 rounded-[2.5rem] flex flex-col justify-between group overflow-hidden relative">
<div class="relative z-10">
<span class="inline-block px-4 py-1.5 bg-secondary-container text-on-secondary-container rounded-full text-sm font-bold mb-6">LIVE NOW</span>
<h2 class="font-headline text-4xl font-bold text-on-background mb-4">Class Forums</h2>
<p class="text-on-surface-variant text-lg max-w-md">Real-time discussions organized by course code. Ask questions, share resources, and find your study group.</p>
</div>
<div class="mt-12 flex flex-col gap-4">
<?php if ($forumPreviewPost !== null): ?>
<a class="block translate-x-4 rounded-[2rem] bg-surface-container-low p-5 transition-transform duration-500 group-hover:translate-x-0 hover:bg-surface-container" href="login.php">
<div class="flex items-start justify-between gap-4">
<div class="flex-1">
<p class="text-[11px] font-black uppercase tracking-widest text-primary"><?php echo htmlspecialchars(dashboardShortCourseName($forumPreviewPost['CourseName'] ?? ''), ENT_QUOTES, 'UTF-8'); ?> • <?php echo htmlspecialchars($forumPreviewPost['forum_name'] ?? 'Class Forum', ENT_QUOTES, 'UTF-8'); ?></p>
<h3 class="mt-3 font-headline text-2xl font-black text-on-background"><?php echo htmlspecialchars($forumPreviewPost['blogpost_title'] ?? 'Forum preview', ENT_QUOTES, 'UTF-8'); ?></h3>
<p class="mt-3 line-clamp-3 text-sm leading-relaxed text-on-surface-variant"><?php echo htmlspecialchars($forumPreviewPost['blogpost_body'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
<div class="mt-4 flex flex-wrap gap-4 text-xs font-bold text-on-surface-variant">
<span>By <?php echo htmlspecialchars(dashboardAuthorName($forumPreviewPost), ENT_QUOTES, 'UTF-8'); ?></span>
<span><?php echo (int) ($forumPreviewPost['comment_count'] ?? 0); ?> comments</span>
<span><?php echo htmlspecialchars(dashboardTimeAgo($forumPreviewPost['blogpost_timestamp'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
</div>
</div>
<span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">arrow_forward</span>
</div>
</a>
<?php else: ?>
<a class="block translate-x-4 rounded-[2rem] bg-surface-container-low p-5 transition-transform duration-500 group-hover:translate-x-0 hover:bg-surface-container" href="login.php">
<div class="flex items-center gap-4">
<div class="bg-primary-container/30 w-12 h-12 rounded-lg flex items-center justify-center">
<span class="material-symbols-outlined text-primary">forum</span>
</div>
<div class="flex-1">
<div class="h-2 w-32 bg-outline-variant/30 rounded-full mb-2"></div>
<div class="h-2 w-48 bg-outline-variant/10 rounded-full"></div>
</div>
<span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">arrow_forward</span>
</div>
</a>
<?php endif; ?>
</div>
</div>
<div class="md:col-span-5 bg-primary p-10 rounded-[2.5rem] text-on-primary flex flex-col justify-between relative overflow-hidden" id="ratings">
<div class="absolute -bottom-10 -right-10 w-64 h-64 bg-primary-container/20 rounded-full blur-3xl"></div>
<div>
<h2 class="font-headline text-4xl font-bold mb-4">Professor Ratings</h2>
<p class="opacity-90 text-lg">Read qualitative reviews and see verified teaching styles before you register.</p>
</div>
<div class="mt-8">
<div class="flex items-baseline gap-2 mb-6">
<span class="text-6xl font-black font-headline tracking-tighter">4.9</span>
<span class="text-xl opacity-80">/ 5.0 Avg</span>
</div>
<div class="flex gap-2">
<span class="px-4 py-2 bg-white/10 rounded-full border border-white/20 text-sm">Clear Syllabus</span>
<span class="px-4 py-2 bg-white/10 rounded-full border border-white/20 text-sm">Engaging</span>
</div>
</div>
</div>
<div class="md:col-span-12 lg:col-span-8 bg-surface-container rounded-[2.5rem] overflow-hidden grid md:grid-cols-2" id="clips">
<div class="p-10 flex flex-col justify-center">
<h2 class="font-headline text-4xl font-bold text-on-background mb-4">Lecture Clips</h2>
<p class="text-on-surface-variant text-lg mb-8">Watch highlights of important lecture moments and keep up with course material.</p>
<a class="flex items-center gap-2 font-bold text-primary group" href="register.php">
                            Join to Watch
                            <span class="material-symbols-outlined group-hover:translate-x-1 transition-transform">arrow_forward</span>
</a>
</div>
<div class="bg-surface-container-highest p-6 flex items-center justify-center relative min-h-[300px]">
<div class="w-full aspect-video rounded-2xl overflow-hidden shadow-2xl relative">
<img alt="Lecture clip preview" class="w-full h-full object-cover" src="<?php echo htmlspecialchars(site_image_url('lecture_clip'), ENT_QUOTES, 'UTF-8'); ?>"/>
<div class="absolute inset-0 flex items-center justify-center">
<div class="w-20 h-20 bg-primary/90 text-white rounded-full flex items-center justify-center shadow-lg backdrop-blur-md">
<span class="material-symbols-outlined text-4xl" style="font-variation-settings: 'FILL' 1;">play_arrow</span>
</div>
</div>
</div>
</div>
</div>
<div class="md:col-span-12 lg:col-span-4 bg-tertiary-container p-10 rounded-[2.5rem] flex flex-col justify-between">
<div class="w-16 h-16 bg-on-tertiary-container/10 rounded-2xl flex items-center justify-center mb-6">
<span class="material-symbols-outlined text-on-tertiary-container text-4xl">verified</span>
</div>
<h2 class="font-headline text-3xl font-bold text-on-tertiary-container mb-4 leading-tight">Verified Faculty Insights</h2>
<p class="text-on-tertiary-container/80 text-lg mb-8">Access resources provided directly by UWM faculty members.</p>
<a class="w-full py-4 bg-on-tertiary-container text-tertiary-container rounded-full font-bold text-center" href="login.php">Portal Login</a>
</div>
</div>
</section>
<section class="py-24 bg-surface-container-low" id="community">
<div class="max-w-7xl mx-auto px-8">
<div class="flex flex-col lg:flex-row items-center gap-16">
<div class="lg:w-1/2 flex justify-center">
<img alt="Student using a laptop" class="rounded-[3rem] shadow-2xl" src="<?php echo htmlspecialchars(site_image_url('community_student'), ENT_QUOTES, 'UTF-8'); ?>"/>
</div>
<div class="lg:w-1/2">
<span class="text-secondary font-bold tracking-widest uppercase text-sm mb-4 block">THE NETWORK</span>
<h2 class="font-headline text-5xl font-extrabold text-on-background mb-8 leading-tight">The UWM Community is Waiting for You.</h2>
<p class="text-on-surface-variant text-xl leading-relaxed mb-10">
                            Create an account to test the database-backed login flow and open your student dashboard.
                        </p>
<div class="flex flex-col sm:flex-row gap-4">
<a class="bg-primary text-on-primary px-10 py-5 rounded-full font-bold text-xl hover:shadow-xl hover:-translate-y-1 transition-all text-center" href="register.php">
                                Join UWM Community
                            </a>
<a class="bg-surface-container-lowest text-primary border-2 border-primary/20 px-10 py-5 rounded-full font-bold text-xl hover:bg-primary/5 transition-colors text-center" href="login.php">
                                I Already Have an Account
                            </a>
</div>
</div>
</div>
</div>
</section>
</main>
<?php require __DIR__ . '/footer.php'; ?>
</body></html>

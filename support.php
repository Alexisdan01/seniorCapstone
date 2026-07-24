<?php require_once __DIR__ . '/includes/image-config.php'; ?>
<?php
$currentPage = 'support';
$headerCtaHref = 'login.php';
$headerCtaLabel = 'Sign In';
$footerMarginClass = 'mt-0';
?>
<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Support Center | Scholarly Pulse</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@400;700;800;900&amp;family=Manrope:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
              "primary-fixed": "#ff7672",
              "tertiary": "#715800",
              "surface-variant": "#ffd2d8",
              "on-primary": "#ffefee",
              "on-secondary-container": "#802c00",
              "on-primary-fixed-variant": "#60000b",
              "tertiary-fixed": "#fad056",
              "surface-dim": "#ffc6ce",
              "secondary-fixed": "#ffc5ae",
              "inverse-surface": "#24020b",
              "on-tertiary-fixed": "#443400",
              "on-primary-fixed": "#000000",
              "on-secondary-fixed": "#601f00",
              "tertiary-fixed-dim": "#ebc24a",
              "on-error": "#ffefec",
              "surface-container-highest": "#ffd2d8",
              "on-background": "#4d212a",
              "error-container": "#f95630",
              "outline-variant": "#dd9ca7",
              "on-secondary": "#ffefea",
              "surface-container-high": "#ffd9de",
              "surface": "#fff4f4",
              "on-secondary-fixed-variant": "#8c3508",
              "outline": "#a06771",
              "on-primary-container": "#4e0007",
              "on-error-container": "#520c00",
              "on-tertiary-container": "#5b4600",
              "inverse-primary": "#ff5a5a",
              "secondary-fixed-dim": "#ffb193",
              "on-tertiary": "#fff1d6",
              "surface-container": "#ffe1e4",
              "tertiary-dim": "#634d00",
              "on-surface": "#4d212a",
              "inverse-on-surface": "#cb8c97",
              "surface-tint": "#b12029",
              "surface-bright": "#fff4f4",
              "primary-dim": "#a0101f",
              "secondary-container": "#ffc5ae",
              "on-surface-variant": "#814c56",
              "error": "#b02500",
              "surface-container-lowest": "#ffffff",
              "primary-container": "#ff7672",
              "primary": "#b12029",
              "primary-fixed-dim": "#ff5a5a",
              "error-dim": "#b92902",
              "secondary-dim": "#8b3407",
              "secondary": "#9b3f14",
              "surface-container-low": "#ffecee",
              "background": "#fff4f4",
              "tertiary-container": "#fad056",
              "on-tertiary-fixed-variant": "#664f00"
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
        .glass-panel {
            background: rgba(255, 210, 216, 0.6);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
    </style>
</head>
<body class="bg-surface text-on-surface font-body selection:bg-primary-container selection:text-on-primary-container">
<?php require __DIR__ . '/header.php'; ?>
<main>
<section class="relative py-24 px-6 overflow-hidden">
<div class="absolute inset-0 z-0">
<img alt="Modern university library interior" class="w-full h-full object-cover opacity-10" data-alt="Modern sun-drenched university library interior with large windows, wooden desks, and students studying in a quiet scholarly atmosphere" src="<?php echo htmlspecialchars(site_image_url('support_library'), ENT_QUOTES, 'UTF-8'); ?>"/>
<div class="absolute inset-0 bg-gradient-to-b from-surface/50 to-surface"></div>
</div>
<div class="relative z-10 max-w-4xl mx-auto text-center">
<h1 class="font-headline font-black text-5xl md:text-7xl text-on-background mb-8 tracking-tight">How can we help you today?</h1>
<div class="relative group">
<div class="absolute inset-y-0 left-6 flex items-center pointer-events-none">
<span class="material-symbols-outlined text-primary text-2xl">search</span>
</div>
<input class="w-full pl-16 pr-8 py-6 rounded-full bg-surface-container-lowest border-none shadow-xl shadow-primary/5 focus:ring-4 focus:ring-primary-container/20 text-lg font-medium transition-all group-hover:shadow-primary/10" placeholder="Search for articles, guides, or tutorials..." type="text"/>
</div>
<div class="mt-6 flex flex-wrap justify-center gap-3">
<span class="text-on-surface-variant font-semibold text-sm px-4 py-2 bg-surface-container-high rounded-full">Popular: Joining Subreddits</span>
<span class="text-on-surface-variant font-semibold text-sm px-4 py-2 bg-surface-container-high rounded-full">Professor Verification</span>
<span class="text-on-surface-variant font-semibold text-sm px-4 py-2 bg-surface-container-high rounded-full">Anonymous Posting</span>
</div>
</div>
</section>
<section class="max-w-7xl mx-auto px-6 py-12">
<div class="grid grid-cols-1 md:grid-cols-12 gap-6">
<div class="md:col-span-8 bg-surface-container-lowest rounded-3xl p-10 flex flex-col justify-between group cursor-pointer hover:shadow-2xl hover:shadow-primary/5 transition-all duration-300">
<div>
<div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
<span class="material-symbols-outlined text-primary text-4xl" data-weight="fill">shield_person</span>
</div>
<h3 class="font-headline font-bold text-3xl text-on-background mb-4">Account &amp; Security</h3>
<p class="text-on-surface-variant text-lg max-w-md leading-relaxed">Manage your credentials, two-factor authentication, and privacy settings to keep your academic profile secure.</p>
</div>
<div class="mt-8 flex gap-4">
<span class="text-primary font-bold inline-flex items-center gap-2 group-hover:translate-x-2 transition-transform">
                            Explore guides <span class="material-symbols-outlined">arrow_forward</span>
</span>
</div>
</div>
<div class="md:col-span-4 bg-secondary-container/30 rounded-3xl p-8 group cursor-pointer hover:bg-secondary-container/50 transition-all duration-300">
<div class="w-12 h-12 bg-secondary/10 rounded-xl flex items-center justify-center mb-6">
<span class="material-symbols-outlined text-secondary text-3xl">forum</span>
</div>
<h3 class="font-headline font-bold text-xl text-on-background mb-2">Class Forums</h3>
<p class="text-on-surface-variant text-sm leading-relaxed">Learn how to engage in course-specific discussions and share resources with peers.</p>
</div>
<div class="md:col-span-4 bg-tertiary-container/20 rounded-3xl p-8 group cursor-pointer hover:bg-tertiary-container/40 transition-all duration-300">
<div class="w-12 h-12 bg-tertiary/10 rounded-xl flex items-center justify-center mb-6">
<span class="material-symbols-outlined text-tertiary text-3xl">star_rate</span>
</div>
<h3 class="font-headline font-bold text-xl text-on-background mb-2">Professor Ratings</h3>
<p class="text-on-surface-variant text-sm leading-relaxed">Understanding our verification process and community guidelines for faculty feedback.</p>
</div>
<div class="md:col-span-8 bg-surface-container-low rounded-3xl p-8 flex items-center gap-8 group cursor-pointer hover:shadow-lg transition-all duration-300">
<div class="hidden sm:flex shrink-0 w-24 h-24 bg-white rounded-2xl items-center justify-center shadow-sm">
<span class="material-symbols-outlined text-primary text-5xl">handyman</span>
</div>
<div>
<h3 class="font-headline font-bold text-2xl text-on-background mb-2">Technical Support</h3>
<p class="text-on-surface-variant leading-relaxed">Experiencing glitches? Mobile app issues? Our tech team is here to help you troubleshoot.</p>
</div>
</div>
</div>
</section>
<section class="py-24 px-6 bg-surface-container-low">
<div class="max-w-4xl mx-auto">
<div class="text-center mb-16">
<h2 class="font-headline font-black text-4xl text-on-background mb-4">Frequently Asked Questions</h2>
<p class="text-on-surface-variant text-lg">Quick answers to the most common inquiries from our student community.</p>
</div>
<div class="space-y-4">
<div class="bg-surface-container-lowest rounded-2xl overflow-hidden shadow-sm">
<button class="w-full flex justify-between items-center p-6 text-left hover:bg-primary/5 transition-colors" type="button">
<span class="font-bold text-lg text-on-surface">How do I join a class subreddit?</span>
<span class="material-symbols-outlined text-primary">expand_more</span>
</button>
</div>
<div class="bg-surface-container-lowest rounded-2xl overflow-hidden shadow-md ring-1 ring-primary/10">
<button class="w-full flex justify-between items-center p-6 text-left bg-primary/5" type="button">
<span class="font-bold text-lg text-primary">How are professor ratings verified?</span>
<span class="material-symbols-outlined text-primary rotate-180">expand_more</span>
</button>
<div class="px-6 pb-6 text-on-surface-variant leading-relaxed">
<p>To ensure authenticity, all professor ratings require a verified university email address (.edu) and course completion confirmation. Our moderation team cross-references submissions with current semester schedules to maintain a high scholarly standard.</p>
</div>
</div>
<div class="bg-surface-container-lowest rounded-2xl overflow-hidden shadow-sm">
<button class="w-full flex justify-between items-center p-6 text-left hover:bg-primary/5 transition-colors" type="button">
<span class="font-bold text-lg text-on-surface">Can I post anonymously?</span>
<span class="material-symbols-outlined text-primary">expand_more</span>
</button>
</div>
<div class="bg-surface-container-lowest rounded-2xl overflow-hidden shadow-sm">
<button class="w-full flex justify-between items-center p-6 text-left hover:bg-primary/5 transition-colors" type="button">
<span class="font-bold text-lg text-on-surface">How do I reset my academic credentials?</span>
<span class="material-symbols-outlined text-primary">expand_more</span>
</button>
</div>
</div>
</div>
</section>
<section class="max-w-7xl mx-auto px-6 py-24">
<div class="bg-gradient-to-br from-primary to-primary-dim rounded-[3rem] p-12 md:p-20 relative overflow-hidden shadow-2xl">
<div class="absolute -top-24 -right-24 w-96 h-96 bg-white/10 rounded-full blur-3xl"></div>
<div class="absolute -bottom-24 -left-24 w-96 h-96 bg-black/10 rounded-full blur-3xl"></div>
<div class="relative z-10 grid md:grid-cols-2 gap-12 items-center">
<div>
<h2 class="font-headline font-black text-4xl md:text-5xl text-on-primary mb-6">Still need help?</h2>
<p class="text-on-primary/80 text-xl leading-relaxed mb-10">Our dedicated student support team is available 24/7 to ensure your academic journey remains uninterrupted.</p>
<div class="flex flex-col sm:flex-row gap-4">
<button class="bg-surface-container-lowest text-primary px-8 py-4 rounded-full font-bold text-lg shadow-xl hover:scale-105 transition-transform" type="button">
                                Contact Student Support
                            </button>
<button class="bg-primary-container text-on-primary-container border border-on-primary/20 px-8 py-4 rounded-full font-bold text-lg hover:bg-on-primary/10 transition-colors" type="button">
                                Submit a Ticket
                            </button>
</div>
</div>
<div class="hidden md:block">
<div class="glass-panel p-8 rounded-3xl border border-white/20">
<div class="flex items-center gap-4 mb-6">
<div class="w-12 h-12 rounded-full overflow-hidden border-2 border-white">
<img alt="Friendly university staff member" class="w-full h-full object-cover" data-alt="Friendly university staff member smiling, wearing professional attire in a modern academic office setting" src="<?php echo htmlspecialchars(site_image_url('support_staff'), ENT_QUOTES, 'UTF-8'); ?>"/>
</div>
<div>
<p class="font-bold text-on-primary">Sarah from Support</p>
<p class="text-xs text-on-primary/70">Average response time: 5 mins</p>
</div>
</div>
<div class="space-y-3">
<div class="bg-white/10 p-4 rounded-2xl rounded-tl-none">
<p class="text-sm text-on-primary">"Hello! I'm here to help you navigate the Scholarly Pulse platform. What's on your mind?"</p>
</div>
</div>
</div>
</div>
</div>
</div>
</section>
</main>
<?php require __DIR__ . '/footer.php'; ?>
</body></html>

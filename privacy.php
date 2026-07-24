<?php require_once __DIR__ . '/includes/image-config.php'; ?>
<?php
$currentPage = 'privacy';
$headerCtaHref = 'login.php';
$headerCtaLabel = 'Sign In';
$footerMarginClass = 'mt-0';
$lastUpdated = 'May 11, 2026';
?>
<!DOCTYPE html>

<html lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Privacy Policy | Scholarly Pulse</title>
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
              "error": "#b02500",
              "outline-variant": "#dd9ca7",
              "inverse-on-surface": "#cb8c97",
              "tertiary-fixed": "#fad056",
              "secondary-dim": "#8b3407",
              "inverse-primary": "#ff5a5a",
              "secondary-fixed": "#ffc5ae",
              "secondary-fixed-dim": "#ffb193",
              "primary-dim": "#a0101f",
              "surface-container-highest": "#ffd2d8",
              "primary-container": "#ff7672",
              "inverse-surface": "#24020b",
              "tertiary": "#715800",
              "tertiary-fixed-dim": "#ebc24a",
              "on-secondary": "#ffefea",
              "on-surface-variant": "#814c56",
              "on-background": "#4d212a",
              "on-secondary-container": "#802c00",
              "surface-container-low": "#ffecee",
              "surface-container": "#ffe1e4",
              "surface-variant": "#ffd2d8",
              "on-tertiary-fixed": "#443400",
              "on-primary": "#ffefee",
              "on-primary-container": "#4e0007",
              "on-secondary-fixed": "#601f00",
              "tertiary-container": "#fad056",
              "on-primary-fixed-variant": "#60000b",
              "surface-dim": "#ffc6ce",
              "secondary-container": "#ffc5ae",
              "surface": "#fff4f4",
              "on-tertiary-container": "#5b4600",
              "on-primary-fixed": "#000000",
              "primary-fixed-dim": "#ff5a5a",
              "primary": "#b12029",
              "error-container": "#f95630",
              "surface-container-high": "#ffd9de",
              "on-tertiary": "#fff1d6",
              "on-error": "#ffefec",
              "error-dim": "#b92902",
              "background": "#fff4f4",
              "surface-tint": "#b12029",
              "secondary": "#9b3f14",
              "surface-container-lowest": "#ffffff",
              "primary-fixed": "#ff7672",
              "on-tertiary-fixed-variant": "#664f00",
              "on-surface": "#4d212a",
              "on-error-container": "#520c00",
              "outline": "#a06771",
              "surface-bright": "#fff4f4",
              "tertiary-dim": "#634d00",
              "on-secondary-fixed-variant": "#8c3508"
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
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #ffecee; }
        ::-webkit-scrollbar-thumb { background: #dd9ca7; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #b12029; }
        article section {
            scroll-margin-top: 8rem;
        }
    </style>
</head>
<body class="bg-surface text-on-surface font-body selection:bg-primary-container selection:text-on-primary-container">
<?php require __DIR__ . '/header.php'; ?>
<main class="pt-16 pb-20 max-w-7xl mx-auto px-8 grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-12">
<aside class="hidden lg:block">
<div class="sticky top-32 space-y-2 border-l border-outline-variant/20 pl-6">
<p class="text-xs font-black uppercase tracking-widest text-on-surface-variant mb-4">UWM Forum Platform</p>
<a class="block py-2 text-sm font-bold text-primary border-l-2 border-primary -ml-[25px] pl-[23px] transition-all" href="#overview">Overview</a>
<a class="block py-2 text-sm font-medium text-on-surface-variant hover:text-primary transition-colors" href="#information-we-collect">Information We Collect</a>
<a class="block py-2 text-sm font-medium text-on-surface-variant hover:text-primary transition-colors" href="#how-we-use-information">How We Use Information</a>
<a class="block py-2 text-sm font-medium text-on-surface-variant hover:text-primary transition-colors" href="#sharing-and-disclosure">Sharing and Disclosure</a>
<a class="block py-2 text-sm font-medium text-on-surface-variant hover:text-primary transition-colors" href="#data-security">Data Security</a>
<a class="block py-2 text-sm font-medium text-on-surface-variant hover:text-primary transition-colors" href="#data-retention">Data Retention</a>
<a class="block py-2 text-sm font-medium text-on-surface-variant hover:text-primary transition-colors" href="#student-rights">Student Rights</a>
<a class="block py-2 text-sm font-medium text-on-surface-variant hover:text-primary transition-colors" href="#contact">Contact Information</a>
</div>
</aside>
<article class="space-y-16">
<header class="space-y-6">
<div class="inline-flex items-center gap-2 bg-tertiary-container/30 px-3 py-1 rounded-full text-on-tertiary-container text-xs font-bold tracking-tight">
<span class="material-symbols-outlined text-xs" style="font-variation-settings: 'FILL' 1;">verified_user</span>
                    LAST UPDATED: <?php echo htmlspecialchars($lastUpdated, ENT_QUOTES, 'UTF-8'); ?>
                </div>
<h1 class="font-headline text-5xl md:text-6xl font-black text-on-surface tracking-tighter leading-none">Privacy Policy</h1>
<p class="text-xl text-on-surface-variant leading-relaxed max-w-3xl">This Privacy Policy explains what information the UWM Forum Platform collects, how that information is used, and the steps we take to protect student and staff data while the platform is in use.</p>
</header>
<div class="relative rounded-3xl overflow-hidden aspect-[21/9] mb-12 shadow-2xl shadow-primary/5">
<img alt="Privacy and student data protection visual for the UWM Forum Platform" class="w-full h-full object-cover" src="<?php echo htmlspecialchars(site_image_url('privacy_image'), ENT_QUOTES, 'UTF-8'); ?>"/>
<div class="absolute inset-0 bg-gradient-to-t from-background/80 to-transparent"></div>
</div>
<section class="space-y-6" id="overview">
<h2 class="font-headline text-3xl font-extrabold text-on-surface">Overview</h2>
<div class="prose prose-on-surface text-lg text-on-surface-variant leading-relaxed">
<p>The UWM Forum Platform is intended for University of Wisconsin-Milwaukee students, faculty, and authorized staff. We collect only the information needed to operate the platform, support discussion features, maintain account security, and moderate the community appropriately.</p>
<p>By using this platform, you understand that forum activity, account details, and moderation records may be stored as part of the normal operation of the website.</p>
</div>
</section>
<section class="bg-surface-container-low p-10 rounded-[2.5rem] space-y-8" id="information-we-collect">
<div class="flex items-center gap-4">
<div class="p-3 bg-primary/10 rounded-2xl">
<span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">database</span>
</div>
<h2 class="font-headline text-3xl font-extrabold text-on-surface">Information We Collect</h2>
</div>
<div class="grid md:grid-cols-2 gap-8">
<div class="bg-surface-container-lowest p-6 rounded-2xl shadow-sm space-y-3 border border-outline-variant/10">
<h3 class="font-bold text-primary">Account Information</h3>
<p class="text-sm text-on-surface-variant">When you register, we may collect your name, UWM email address, encrypted password information, and account role.</p>
</div>
<div class="bg-surface-container-lowest p-6 rounded-2xl shadow-sm space-y-3 border border-outline-variant/10">
<h3 class="font-bold text-primary">Forum Activity</h3>
<p class="text-sm text-on-surface-variant">We store posts, comments, reactions, joined classes, and moderation actions connected to your account.</p>
</div>
<div class="bg-surface-container-lowest p-6 rounded-2xl shadow-sm space-y-3 border border-outline-variant/10">
<h3 class="font-bold text-primary">Technical Information</h3>
<p class="text-sm text-on-surface-variant">We may collect basic technical data such as login timestamps, session activity, and error logs to keep the platform stable and secure.</p>
</div>
<div class="bg-surface-container-lowest p-6 rounded-2xl shadow-sm space-y-3 border border-outline-variant/10">
<h3 class="font-bold text-primary">Support Requests</h3>
<p class="text-sm text-on-surface-variant">If you contact the project team, we may keep the message and related contact details so we can respond and document the issue.</p>
</div>
</div>
</section>
<section class="space-y-6" id="how-we-use-information">
<h2 class="font-headline text-3xl font-extrabold text-on-surface">How We Use Information</h2>
<div class="text-lg text-on-surface-variant leading-relaxed space-y-4">
<p>We use collected information only to operate and improve the UWM Forum Platform.</p>
<ul class="list-none space-y-3">
<li class="flex items-start gap-3"><span class="material-symbols-outlined text-secondary mt-1">check_circle</span><span>Verify user identity and restrict access to eligible UWM users.</span></li>
<li class="flex items-start gap-3"><span class="material-symbols-outlined text-secondary mt-1">check_circle</span><span>Provide forum features such as posting, commenting, joining classes, and role-based access.</span></li>
<li class="flex items-start gap-3"><span class="material-symbols-outlined text-secondary mt-1">check_circle</span><span>Support moderation, investigate misuse, and enforce platform rules.</span></li>
<li class="flex items-start gap-3"><span class="material-symbols-outlined text-secondary mt-1">check_circle</span><span>Diagnose technical problems, protect accounts, and improve platform reliability.</span></li>
</ul>
</div>
</section>
<section class="space-y-6" id="sharing-and-disclosure">
<h2 class="font-headline text-3xl font-extrabold text-on-surface">Sharing and Disclosure</h2>
<div class="bg-surface-container border border-outline-variant/30 p-8 rounded-3xl space-y-4">
<p class="text-on-surface-variant leading-relaxed">We do not sell personal information to third parties.</p>
<p class="text-on-surface-variant leading-relaxed">Information may be visible to other users when you post in forums, comment on discussions, or interact publicly within the platform.</p>
<p class="text-on-surface-variant leading-relaxed">Information may also be accessed by authorized moderators, administrators, or university project personnel when necessary for support, moderation, platform maintenance, or security review.</p>
</div>
</section>
<section class="relative group overflow-hidden bg-primary p-12 rounded-[2.5rem] text-on-primary shadow-2xl shadow-primary/20" id="data-security">
<div class="absolute -right-20 -top-20 w-64 h-64 bg-primary-container rounded-full opacity-20 blur-3xl group-hover:opacity-40 transition-opacity"></div>
<div class="relative z-10 space-y-6">
<h2 class="font-headline text-3xl font-extrabold">Data Security</h2>
<p class="text-on-primary/90 text-lg leading-relaxed max-w-3xl">We use reasonable technical and administrative safeguards to protect account data, reduce unauthorized access, and support secure sign-in and moderation workflows.</p>
<div class="flex flex-wrap gap-4">
<div class="bg-white/10 backdrop-blur-md px-4 py-2 rounded-xl text-xs font-bold border border-white/20">ROLE-BASED ACCESS</div>
<div class="bg-white/10 backdrop-blur-md px-4 py-2 rounded-xl text-xs font-bold border border-white/20">SESSION MANAGEMENT</div>
<div class="bg-white/10 backdrop-blur-md px-4 py-2 rounded-xl text-xs font-bold border border-white/20">ACCOUNT MONITORING</div>
</div>
</div>
</section>
<section class="space-y-6" id="data-retention">
<h2 class="font-headline text-3xl font-extrabold text-on-surface">Data Retention</h2>
<div class="text-lg text-on-surface-variant leading-relaxed space-y-4">
<p>We keep account and discussion data for as long as it is needed to operate the platform, support legitimate moderation decisions, maintain records of account activity, and satisfy project or university requirements.</p>
<p>When data is no longer needed, it may be removed, archived, or anonymized depending on the purpose it served and the technical needs of the platform.</p>
</div>
</section>
<section class="space-y-6" id="student-rights">
<div class="flex items-center gap-4">
<div class="w-12 h-1 bg-tertiary rounded-full"></div>
<h2 class="font-headline text-3xl font-extrabold text-on-surface">Student Rights and Expectations</h2>
</div>
<div class="bg-surface-container border border-outline-variant/30 p-8 rounded-3xl space-y-4">
<p class="font-bold text-on-surface flex items-center gap-2">
<span class="material-symbols-outlined text-tertiary">school</span>
Users should post only information they are comfortable sharing within the UWM community.
</p>
<p class="text-on-surface-variant leading-relaxed">You may request help with account access, incorrect profile information, or concerns about misuse of your content by contacting the platform team. Moderation and platform records may still be retained when needed for safety, enforcement, or administrative review.</p>
<div class="bg-surface-container-low p-4 rounded-xl text-sm italic border-l-4 border-tertiary text-on-surface-variant">
                        "Content shared in forums may be visible to other authorized UWM users depending on the class or forum in which it is posted."
                    </div>
</div>
</section>
<section class="space-y-8 border-t border-outline-variant/20 pt-16" id="contact">
<div>
<h2 class="font-headline text-3xl font-extrabold text-on-surface">Contact Information</h2>
<p class="text-on-surface-variant mt-2">If you have questions about this Privacy Policy or how data is handled on the platform, contact the project team below.</p>
</div>
<div class="grid sm:grid-cols-2 gap-6">
<div class="bg-surface-container-high p-8 rounded-3xl flex items-center gap-6 hover:shadow-lg transition-shadow">
<div class="bg-primary-container p-4 rounded-2xl">
<span class="material-symbols-outlined text-on-primary-container">mail</span>
</div>
<div>
<p class="text-xs font-bold text-primary tracking-widest uppercase">Email</p>
<p class="text-on-surface font-bold text-lg">example@uwm.edu</p>
</div>
</div>
<div class="bg-surface-container-high p-8 rounded-3xl flex items-center gap-6 hover:shadow-lg transition-shadow">
<div class="bg-secondary-container p-4 rounded-2xl">
<span class="material-symbols-outlined text-on-secondary-container">apartment</span>
</div>
<div>
<p class="text-xs font-bold text-secondary tracking-widest uppercase">Team</p>
<p class="text-on-surface font-bold text-lg">UWM Forum Project Team</p>
</div>
</div>
</div>
</section>
</article>
</main>
<?php require __DIR__ . '/footer.php'; ?>
<script>
        const sidebarLinks = Array.from(document.querySelectorAll('aside a[href^="#"]'));
        const privacySections = sidebarLinks
            .map((link) => document.querySelector(link.getAttribute('href')))
            .filter(Boolean);
        const activeLinkClass = 'block py-2 text-sm font-bold text-primary border-l-2 border-primary -ml-[25px] pl-[23px] transition-all';
        const inactiveLinkClass = 'block py-2 text-sm font-medium text-on-surface-variant hover:text-primary transition-colors';

        function updateActiveSection() {
            let currentSectionId = privacySections[0]?.id;
            const scrollMarker = 160;

            privacySections.forEach((section) => {
                if (section.getBoundingClientRect().top <= scrollMarker) {
                    currentSectionId = section.id;
                }
            });

            sidebarLinks.forEach((link) => {
                const linkTarget = link.getAttribute('href').slice(1);
                link.className = linkTarget === currentSectionId ? activeLinkClass : inactiveLinkClass;
            });
        }

        document.addEventListener('scroll', updateActiveSection, { passive: true });
        updateActiveSection();
    </script>
</body></html>

<?php require_once __DIR__ . '/includes/image-config.php'; ?>
<?php require_once __DIR__ . '/includes/auth.php'; ?>
<?php
redirectIfLoggedIn();

$currentPage = 'login';
$headerCtaHref = 'register.php';
$headerCtaLabel = 'Create Account';
$headerStickyClass = 'sticky top-0';
$footerMarginClass = 'mt-0';
$loginError = consumeAuthMessage();
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $loginError = 'Please enter your email and password.';
    } else {
        try {
            $user = findUserByEmail($email);

            if ($user !== null && verifyUserPassword($password, $user['pass'])) {
                logInUser((int) $user['user_id']);
                header('Location: dashboard.php');
                exit;
            }

            $loginError = 'The email or password you entered is incorrect.';
        } catch (Throwable $error) {
            $loginError = 'Unable to connect to the database. Please check includes/db.php.';
        }
    }
}
?>
<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Scholarly Pulse - Login</title>
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
              "primary-dim": "#a0101f",
              "on-secondary-fixed-variant": "#8c3508",
              "surface-container-high": "#ffd9de",
              "primary-fixed": "#ff7672",
              "on-tertiary-container": "#5b4600",
              "secondary": "#9b3f14",
              "surface-variant": "#ffd2d8",
              "error": "#b02500",
              "on-secondary-container": "#802c00",
              "error-container": "#f95630",
              "outline": "#a06771",
              "error-dim": "#b92902",
              "secondary-fixed-dim": "#ffb193",
              "on-error": "#ffefec",
              "surface-tint": "#b12029",
              "on-primary-container": "#4e0007",
              "inverse-surface": "#24020b",
              "on-surface-variant": "#814c56",
              "on-primary-fixed-variant": "#60000b",
              "primary-container": "#ff7672",
              "surface-container-low": "#ffecee",
              "surface-container": "#ffe1e4",
              "on-secondary": "#ffefea",
              "inverse-on-surface": "#cb8c97",
              "secondary-fixed": "#ffc5ae",
              "on-primary-fixed": "#000000",
              "on-error-container": "#520c00",
              "surface": "#fff4f4",
              "inverse-primary": "#ff5a5a",
              "on-tertiary": "#fff1d6",
              "secondary-container": "#ffc5ae",
              "tertiary-fixed": "#fad056",
              "surface-dim": "#ffc6ce",
              "on-secondary-fixed": "#601f00",
              "tertiary-dim": "#634d00",
              "on-background": "#4d212a",
              "surface-bright": "#fff4f4",
              "primary": "#b12029",
              "tertiary-fixed-dim": "#ebc24a",
              "on-tertiary-fixed": "#443400",
              "surface-container-highest": "#ffd2d8",
              "on-surface": "#4d212a",
              "on-primary": "#ffefee",
              "primary-fixed-dim": "#ff5a5a",
              "outline-variant": "#dd9ca7",
              "on-tertiary-fixed-variant": "#664f00",
              "background": "#fff4f4",
              "tertiary-container": "#fad056",
              "tertiary": "#715800",
              "surface-container-lowest": "#ffffff",
              "secondary-dim": "#8b3407"
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
            -webkit-backdrop-filter: blur(12px);
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="bg-surface font-body text-on-surface selection:bg-primary-container selection:text-on-primary-container">
<?php require __DIR__ . '/header.php'; ?>
<main class="grid grid-cols-1 lg:grid-cols-12 overflow-hidden min-h-[calc(100vh-88px)]">
<section class="hidden lg:flex lg:col-span-7 relative flex-col justify-between p-12 bg-surface-container-low overflow-hidden">
<div class="absolute -top-24 -left-24 w-96 h-96 bg-primary opacity-5 blur-[120px] rounded-full"></div>
<div class="absolute bottom-1/4 right-0 w-80 h-80 bg-tertiary opacity-10 blur-[100px] rounded-full"></div>
<div class="relative z-10">
<div class="inline-flex items-center gap-2 px-4 py-2 bg-primary/10 rounded-full text-primary font-label font-bold uppercase tracking-[0.2em] text-xs">Scholarly Login Portal</div>
</div>
<div class="relative z-10 max-w-xl">
<h1 class="font-headline font-extrabold text-5xl xl:text-7xl text-on-surface leading-tight tracking-tighter mb-8">
                    The heartbeat of <span class="text-primary italic">UWM</span> excellence.
                </h1>
<p class="font-body text-lg text-on-surface-variant leading-relaxed mb-12 opacity-90">
                    Connect with fellow researchers, access exclusive lecture archives, and join the digital salon where academic rigor meets energetic collaboration.
                </p>
<div class="glass-panel p-8 rounded-2xl flex gap-6 items-start border border-outline-variant/20">
<div class="flex-shrink-0">
<div class="w-14 h-14 rounded-full overflow-hidden border-2 border-primary/20">
<img alt="Featured Faculty" class="w-full h-full object-cover" data-alt="portrait of a confident university professor in a library setting with soft bokeh background and warm lighting" src="<?php echo htmlspecialchars(site_image_url('faculty_portrait'), ENT_QUOTES, 'UTF-8'); ?>"/>
</div>
</div>
<div>
<div class="flex items-center gap-2 mb-1">
<span class="text-tertiary material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">verified</span>
<span class="font-label text-xs uppercase tracking-widest text-tertiary font-bold">Faculty Insight</span>
</div>
<p class="font-body text-on-surface italic font-medium mb-2">"This platform has transformed how we bridge the gap between classroom theory and real-world research collaboration."</p>
<p class="font-label text-sm text-on-surface-variant">- Dr. Julian Vance, Dept. of Applied Sciences</p>
</div>
</div>
</div>
<div class="relative z-10">
<p class="text-sm text-on-surface-variant/60 font-medium">Secure access for the UWM academic community.</p>
</div>
</section>
<section class="col-span-1 lg:col-span-5 flex flex-col justify-center items-center p-6 sm:p-12 lg:p-20 bg-surface-container-lowest">
<div class="w-full max-w-md">
<div class="lg:hidden flex items-center justify-center gap-3 mb-12">
<div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center text-white">
<span class="material-symbols-outlined text-lg" style="font-variation-settings: 'FILL' 1;">school</span>
</div>
<span class="font-headline font-black text-xl text-primary tracking-tight">Scholarly Pulse</span>
</div>
<div class="mb-10 text-center lg:text-left">
<h2 class="font-headline font-bold text-3xl text-on-surface mb-3 tracking-tight">Welcome back</h2>
<p class="font-body text-on-surface-variant">Sign in to resume your academic journey.</p>
</div>
<button class="w-full flex items-center justify-center gap-3 bg-primary text-on-primary font-headline font-bold py-4 px-6 rounded-full shadow-sm hover:opacity-90 transition-all duration-200 active:scale-95 mb-8" type="button">
<span class="material-symbols-outlined">login</span>
                    Log in with UWM SSO
                </button>
<div class="relative flex items-center gap-4 mb-8">
<div class="flex-grow h-px bg-outline-variant/30"></div>
<span class="text-xs font-label uppercase tracking-widest text-on-surface-variant/50 font-bold whitespace-nowrap">or use credentials</span>
<div class="flex-grow h-px bg-outline-variant/30"></div>
</div>
<?php if ($loginError !== ''): ?>
<div class="mb-6 rounded-2xl bg-error-container/20 px-4 py-3 text-sm font-bold text-error">
<?php echo htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8'); ?>
</div>
<?php endif; ?>
<form action="login.php" class="space-y-6" method="post">
<div class="space-y-1.5">
<label class="block font-label text-sm font-bold text-on-surface ml-1" for="email">University Email</label>
<div class="relative group">
<input class="w-full bg-surface-container-low border-none rounded-2xl py-4 pl-12 pr-4 text-on-surface placeholder:text-on-surface-variant/40 focus:ring-2 focus:ring-primary/20 transition-all" id="email" name="email" placeholder="name@uwm.edu" type="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"/>
<span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant/50">mail</span>
</div>
</div>
<div class="space-y-1.5">
<div class="flex justify-between items-center">
<label class="block font-label text-sm font-bold text-on-surface ml-1" for="password">Password</label>
<a class="text-xs font-label font-bold text-primary hover:underline transition-all" href="#">Forgot password?</a>
</div>
<div class="relative group">
<input class="w-full bg-surface-container-low border-none rounded-2xl py-4 pl-12 pr-12 text-on-surface placeholder:text-on-surface-variant/40 focus:ring-2 focus:ring-primary/20 transition-all" id="password" name="password" placeholder="••••••••" type="password"/>
<span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant/50">lock</span>
<button class="absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant/50 hover:text-primary transition-colors" type="button">
<span class="material-symbols-outlined">visibility</span>
</button>
</div>
</div>
<button class="w-full py-4 bg-surface-container-highest text-on-surface font-headline font-bold rounded-2xl hover:bg-surface-variant transition-all duration-200 active:scale-95 border border-outline-variant/20" type="submit">
                        Continue
                    </button>
</form>
<div class="mt-10 text-center">
<p class="font-body text-on-surface-variant text-sm">
                        Don't have an account?
                        <a class="text-primary font-bold hover:underline transition-all ml-1" href="register.php">Register now</a>
</p>
</div>
<div class="mt-12 pt-8 border-t border-outline-variant/10 flex flex-col items-center gap-4">
<div class="flex items-center gap-2 px-3 py-1 bg-tertiary-container/30 rounded-full">
<span class="material-symbols-outlined text-tertiary text-sm" style="font-variation-settings: 'FILL' 1;">shield</span>
<span class="text-[10px] font-label font-black uppercase tracking-wider text-on-tertiary-container">Secure Academic Gateway</span>
</div>
<div class="flex gap-6">
<a class="text-[10px] font-label uppercase font-bold text-on-surface-variant/40 hover:text-on-surface-variant transition-colors" href="privacy.php">Privacy Policy</a>
<a class="text-[10px] font-label uppercase font-bold text-on-surface-variant/40 hover:text-on-surface-variant transition-colors" href="terms.php">Campus Terms</a>
<a class="text-[10px] font-label uppercase font-bold text-on-surface-variant/40 hover:text-on-surface-variant transition-colors" href="#">Help Desk</a>
</div>
</div>
</div>
</section>
</main>
<?php require __DIR__ . '/footer.php'; ?>
<div class="pointer-events-none fixed inset-0 z-50 opacity-[0.03] mix-blend-overlay">
<svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
<filter id="noiseFilter">
<feTurbulence baseFrequency="0.65" numOctaves="3" stitchTiles="stitch" type="fractalNoise"></feTurbulence>
</filter>
<rect filter="url(#noiseFilter)" height="100%" width="100%"></rect>
</svg>
</div>
</body></html>

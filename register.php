<?php
require_once __DIR__ . '/includes/auth.php';
redirectIfLoggedIn();

$currentPage = 'register';
$headerCtaHref = 'login.php';
$headerCtaLabel = 'Log In';
$headerStickyClass = 'sticky top-0';
$footerMarginClass = 'mt-0';
$registerError = '';
$fullName = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $acceptedTerms = isset($_POST['terms']);

    if ($fullName === '' || $email === '' || $password === '' || $confirmPassword === '') {
        $registerError = 'Please fill in every field.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $registerError = 'Please enter a valid email address.';
    } elseif (!preg_match('/@uwm\.edu$/i', $email)) {
        $registerError = 'Please register with a valid UWM email address ending in uwm.edu.';
    } elseif ($password !== $confirmPassword) {
        $registerError = 'The passwords do not match.';
    } elseif (strlen($password) < 8) {
        $registerError = 'Please use a password with at least 8 characters.';
    } elseif (!$acceptedTerms) {
        $registerError = 'Please agree to the terms before creating your account.';
    } else {
        try {
            if (findUserByEmail($email) !== null) {
                $registerError = 'An account already exists for that email.';
            } else {
                $userId = createUser($fullName, $email, $password);
                logInUser($userId);
                header('Location: dashboard.php');
                exit;
            }
        } catch (Throwable $error) {
            $registerError = 'Unable to create the account. Please check the database connection and users table.';
        }
    }
}
?>
<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Join Scholarly Pulse | Academic Community Platform</title>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@700;800;900&amp;family=Manrope:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
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
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            display: inline-block;
            line-height: 1;
            text-transform: none;
            letter-spacing: normal;
            word-wrap: normal;
            white-space: nowrap;
            direction: ltr;
        }
        .glass-effect {
            background: rgba(255, 210, 216, 0.6);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
    </style>
</head>
<body class="bg-surface font-body text-on-surface min-h-screen selection:bg-primary-container selection:text-on-primary-container">
<?php require __DIR__ . '/header.php'; ?>
<main class="max-w-[1200px] mx-auto px-6 grid grid-cols-1 lg:grid-cols-12 gap-12 items-center py-12 lg:py-24">
<div class="lg:col-span-5 space-y-8">
<div class="space-y-4">
<span class="inline-block px-4 py-1 rounded-full bg-tertiary-container text-on-tertiary-container text-xs font-bold font-label uppercase tracking-wider">
                    Join the Salon
                </span>
<h1 class="font-headline text-5xl lg:text-6xl font-extrabold text-on-surface leading-[1.1] tracking-tight">
                    Where Academic <br/><span class="text-primary">Rigor</span> Meets Community.
                </h1>
<p class="text-on-surface-variant text-lg leading-relaxed max-w-md">
                    Join thousands of UWM scholars sharing research, insights, and collaborative lectures in a premium digital space.
                </p>
</div>
<div class="grid grid-cols-2 gap-4">
<div class="bg-surface-container-lowest p-6 rounded-xl space-y-2">
<span class="material-symbols-outlined text-secondary text-3xl" data-icon="school">school</span>
<div class="text-2xl font-bold font-headline text-on-surface">12k+</div>
<div class="text-sm text-on-surface-variant">Active Researchers</div>
</div>
<div class="bg-surface-container p-6 rounded-xl space-y-2">
<span class="material-symbols-outlined text-tertiary text-3xl" data-icon="auto_stories">auto_stories</span>
<div class="text-2xl font-bold font-headline text-on-surface">450+</div>
<div class="text-sm text-on-surface-variant">Daily Papers</div>
</div>
</div>
</div>
<div class="lg:col-span-7 lg:pl-12">
<div class="bg-surface-container-low rounded-[2rem] p-8 lg:p-12 shadow-[0_24px_48px_rgba(77,33,42,0.04)] relative overflow-hidden">
<div class="absolute -top-24 -right-24 w-64 h-64 rounded-full bg-primary-container opacity-10 blur-3xl"></div>
<div class="relative z-10">
<div class="mb-10">
<h2 class="font-headline text-2xl font-bold text-on-surface mb-2">Create your account</h2>
<p class="text-on-surface-variant">Enter your details to start your scholarly journey.</p>
</div>
<?php if ($registerError !== ''): ?>
<div class="mb-6 rounded-2xl bg-error-container/20 px-4 py-3 text-sm font-bold text-error">
<?php echo htmlspecialchars($registerError, ENT_QUOTES, 'UTF-8'); ?>
</div>
<?php endif; ?>
<form action="register.php" class="space-y-6" method="post">
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
<div class="space-y-2">
<label class="block text-sm font-bold text-on-surface-variant ml-1" for="full_name">Full Name</label>
<input class="w-full px-5 py-4 bg-surface-container-lowest rounded-xl border-none ring-1 ring-outline-variant/30 focus:ring-2 focus:ring-primary transition-all placeholder:text-on-surface-variant/40" id="full_name" name="full_name" placeholder="Alex Morgan" type="text" value="<?php echo htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?>"/>
</div>
<div class="space-y-2">
<label class="block text-sm font-bold text-on-surface-variant ml-1" for="email">UWM Email</label>
<input class="w-full px-5 py-4 bg-surface-container-lowest rounded-xl border-none ring-1 ring-outline-variant/30 focus:ring-2 focus:ring-primary transition-all placeholder:text-on-surface-variant/40" id="email" name="email" placeholder="morgan@uwm.edu" type="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"/>
</div>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
<div class="space-y-2">
<label class="block text-sm font-bold text-on-surface-variant ml-1" for="password">Password</label>
<input class="w-full px-5 py-4 bg-surface-container-lowest rounded-xl border-none ring-1 ring-outline-variant/30 focus:ring-2 focus:ring-primary transition-all placeholder:text-on-surface-variant/40" id="password" name="password" placeholder="••••••••" type="password"/>
</div>
<div class="space-y-2">
<label class="block text-sm font-bold text-on-surface-variant ml-1" for="confirm_password">Confirm Password</label>
<input class="w-full px-5 py-4 bg-surface-container-lowest rounded-xl border-none ring-1 ring-outline-variant/30 focus:ring-2 focus:ring-primary transition-all placeholder:text-on-surface-variant/40" id="confirm_password" name="confirm_password" placeholder="••••••••" type="password"/>
</div>
</div>
<div class="flex items-start gap-3 py-2">
<div class="flex items-center h-5">
<input class="h-5 w-5 rounded border-outline-variant text-primary focus:ring-primary cursor-pointer" id="terms" name="terms" type="checkbox"/>
</div>
<label class="text-sm text-on-surface-variant leading-tight cursor-pointer" for="terms">
                                I agree to the <a class="text-primary font-bold hover:underline" href="terms.php">Terms of Service</a> and <a class="text-primary font-bold hover:underline" href="privacy.php">Privacy Policy</a> regarding academic data sharing.
                            </label>
</div>
<div class="pt-4 space-y-4">
<button class="w-full py-4 px-8 bg-gradient-to-r from-primary to-primary-container text-on-primary font-bold rounded-full shadow-lg shadow-primary/20 hover:opacity-90 active:scale-[0.98] transition-all flex justify-center items-center gap-2" type="submit">
<span>Create Account</span>
<span class="material-symbols-outlined text-lg" data-icon="arrow_forward">arrow_forward</span>
</button>
<div class="relative py-2">
<div aria-hidden="true" class="absolute inset-0 flex items-center">
<div class="w-full border-t border-outline-variant/20"></div>
</div>
<div class="relative flex justify-center text-sm">
<span class="px-4 bg-surface-container-low text-on-surface-variant font-medium uppercase tracking-widest text-[10px]">Or continue with</span>
</div>
</div>
<button class="w-full py-4 px-8 bg-surface-container-lowest text-on-surface font-bold rounded-full border border-outline-variant/20 hover:bg-surface-variant/30 transition-colors flex justify-center items-center gap-3" type="button">
<span class="material-symbols-outlined text-secondary" data-icon="account_balance">account_balance</span>
<span>Register with UWM SSO</span>
</button>
</div>
<div class="text-center pt-4">
<p class="text-on-surface-variant text-sm">
                                Already part of the community?
                                <a class="text-primary font-bold hover:underline ml-1" href="login.php">Log in here</a>
</p>
</div>
</form>
</div>
</div>
</div>
</main>
<div class="fixed bottom-0 left-0 w-full h-1/3 bg-gradient-to-t from-surface-container to-transparent -z-10 opacity-30"></div>
<div class="hidden lg:block fixed -bottom-24 -left-24 w-96 h-96 rounded-full bg-secondary opacity-5 blur-[120px]"></div>
<div class="hidden lg:block fixed -top-24 right-0 w-96 h-96 rounded-full bg-tertiary opacity-5 blur-[120px]"></div>
<?php require __DIR__ . '/footer.php'; ?>
</body></html>

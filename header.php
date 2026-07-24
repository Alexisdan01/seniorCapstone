<?php
$currentPage = $currentPage ?? '';
$headerLinks = $headerLinks ?? [
    ['href' => 'index.php#forums', 'label' => 'Class Forums', 'key' => 'forums'],
    ['href' => 'index.php#ratings', 'label' => 'Professor Ratings', 'key' => 'ratings'],
    ['href' => 'index.php#clips', 'label' => 'Lecture Clips', 'key' => 'clips'],
    ['href' => 'index.php#community', 'label' => 'Community', 'key' => 'community'],
    ['href' => 'support.php', 'label' => 'Support', 'key' => 'support'],
];
$headerCtaHref = $headerCtaHref ?? 'login.php';
$headerCtaLabel = $headerCtaLabel ?? 'Join UWM Community';
$headerStickyClass = $headerStickyClass ?? 'sticky top-0';
$headerNavClass = trim("z-50 bg-[#ffe1e4] dark:bg-slate-900 {$headerStickyClass}");
?>
<header>
<nav class="bg-[#fff4f4] dark:bg-slate-950 font-['Epilogue'] font-bold text-lg docked full-width <?php echo htmlspecialchars($headerNavClass, ENT_QUOTES, 'UTF-8'); ?> flat no shadows">
<div class="flex justify-between items-center w-full px-6 py-4 max-w-7xl mx-auto sm:px-8">
<a class="text-2xl font-black text-[#b12029] tracking-tight" href="index.php">Scholarly Pulse</a>
<div class="hidden md:flex items-center gap-8">
<?php foreach ($headerLinks as $link): ?>
<?php $isActive = ($currentPage !== '' && ($link['key'] ?? '') === $currentPage); ?>
<a class="<?php echo $isActive ? 'text-[#b12029]' : 'text-[#4d212a] dark:text-[#ffd2d8]'; ?> hover:text-[#9b3f14] transition-colors duration-200" href="<?php echo htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8'); ?></a>
<?php endforeach; ?>
</div>
<div class="flex items-center gap-4">
<a class="bg-gradient-to-r from-primary to-primary-container text-on-primary px-6 py-2.5 rounded-full font-bold scale-100 hover:scale-[1.02] active:scale-95 transition-transform inline-flex items-center justify-center text-center" href="<?php echo htmlspecialchars($headerCtaHref, ENT_QUOTES, 'UTF-8'); ?>">
<?php echo htmlspecialchars($headerCtaLabel, ENT_QUOTES, 'UTF-8'); ?>
</a>
</div>
</div>
</nav>
</header>

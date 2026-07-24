<?php
$footerMarginClass = $footerMarginClass ?? 'mt-20';
?>
<footer class="bg-[#ffe1e4] dark:bg-slate-900 font-['Manrope'] text-sm full-width border-t-0 w-full py-12 px-8 <?php echo htmlspecialchars($footerMarginClass, ENT_QUOTES, 'UTF-8'); ?> flex flex-col items-center gap-6">
<div class="flex flex-wrap justify-center gap-12 mb-8">
<div class="flex flex-col gap-4">
<a class="text-xl font-bold text-[#4d212a] dark:text-[#fff4f4]" href="index.php">Scholarly Pulse</a>
<p class="max-w-xs text-[#4d212a] opacity-80 leading-relaxed">Dedicated to fostering transparency and collaboration across the UWM academic landscape.</p>
</div>
<div class="flex flex-col gap-3">
<div class="font-bold text-[#b12029]">Explore</div>
<a class="text-[#4d212a] opacity-80 hover:underline hover:text-[#b12029] transition-opacity" href="index.php#forums">Class Forums</a>
<a class="text-[#4d212a] opacity-80 hover:underline hover:text-[#b12029] transition-opacity" href="index.php#ratings">Professor Ratings</a>
<a class="text-[#4d212a] opacity-80 hover:underline hover:text-[#b12029] transition-opacity" href="index.php#clips">Lecture Clips</a>
</div>
<div class="flex flex-col gap-3">
<div class="font-bold text-[#b12029]">Resources</div>
<a class="text-[#4d212a] opacity-80 hover:underline hover:text-[#b12029] transition-opacity" href="login.php">Faculty Portal</a>
<a class="text-[#4d212a] opacity-80 hover:underline hover:text-[#b12029] transition-opacity" href="support.php">Student Support</a>
<a class="text-[#4d212a] opacity-80 hover:underline hover:text-[#b12029] transition-opacity" href="terms.php">Terms of Service</a>
</div>
<div class="flex flex-col gap-3">
<div class="font-bold text-[#b12029]">Legal</div>
<a class="text-[#4d212a] opacity-80 hover:underline hover:text-[#b12029] transition-opacity" href="privacy.php">Privacy Policy</a>
<a class="text-[#4d212a] opacity-80 hover:underline hover:text-[#b12029] transition-opacity" href="cookies.php">Cookie Policy</a>
</div>
</div>
<div class="w-full max-w-7xl pt-8 border-t border-[#4d212a]/10 flex flex-col md:flex-row justify-between items-center gap-4">
<div class="text-[#4d212a] opacity-80">© 2024 Scholarly Pulse. A UWM Academic Initiative.</div>
<div class="flex gap-6">
<a class="w-10 h-10 rounded-full bg-surface-container-low flex items-center justify-center text-[#4d212a] hover:bg-primary-container transition-colors" href="index.php">
<span class="material-symbols-outlined text-lg">public</span>
</a>
<a class="w-10 h-10 rounded-full bg-surface-container-low flex items-center justify-center text-[#4d212a] hover:bg-primary-container transition-colors" href="login.php">
<span class="material-symbols-outlined text-lg">alternate_email</span>
</a>
</div>
</div>
</footer>

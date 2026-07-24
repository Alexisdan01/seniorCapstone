<?php

require_once __DIR__ . '/auth.php';

function renderAccountMenu(array $user, string $supportHref = 'support.php'): void
{
    $displayName = userDisplayName($user);
    $email = $user['email'] ?? '';
    $initials = strtoupper(substr($displayName, 0, 1));
    ?>
    <div class="relative">
        <button
            id="user-menu-button"
            type="button"
            aria-haspopup="true"
            aria-expanded="false"
            title="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
            class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-primary-container bg-primary font-headline font-black text-on-primary transition-transform hover:scale-105 active:scale-95"
        >
            <?php echo htmlspecialchars($initials, ENT_QUOTES, 'UTF-8'); ?>
        </button>
        <div id="user-menu" class="absolute right-0 top-12 hidden w-72 overflow-hidden rounded-3xl border border-outline-variant/20 bg-surface-container-lowest shadow-2xl shadow-primary/10">
            <div class="border-b border-outline-variant/20 p-5">
                <p class="font-headline text-lg font-black text-on-surface"><?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="mt-1 truncate text-sm text-on-surface-variant"><?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div class="p-2">
                <a class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-bold text-on-surface transition-colors hover:bg-surface-container" href="#">
                    <span class="material-symbols-outlined text-primary">person</span>
                    Profile
                </a>
                <a class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-bold text-on-surface transition-colors hover:bg-surface-container" href="#">
                    <span class="material-symbols-outlined text-primary">settings</span>
                    Settings
                </a>
                <a class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-bold text-on-surface transition-colors hover:bg-surface-container" href="<?php echo htmlspecialchars($supportHref, ENT_QUOTES, 'UTF-8'); ?>">
                    <span class="material-symbols-outlined text-primary">help</span>
                    Support
                </a>
                <a class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-bold text-error transition-colors hover:bg-error-container/20" href="logout.php">
                    <span class="material-symbols-outlined">logout</span>
                    Log Out
                </a>
            </div>
        </div>
    </div>
    <?php
}

function renderAccountMenuScript(): void
{
    ?>
    <script>
        const userMenuButton = document.getElementById('user-menu-button');
        const userMenu = document.getElementById('user-menu');

        if (userMenuButton && userMenu) {
            userMenuButton.addEventListener('click', () => {
                const isOpen = !userMenu.classList.contains('hidden');
                userMenu.classList.toggle('hidden', isOpen);
                userMenuButton.setAttribute('aria-expanded', String(!isOpen));
            });

            document.addEventListener('click', (event) => {
                if (!userMenu.contains(event.target) && !userMenuButton.contains(event.target)) {
                    userMenu.classList.add('hidden');
                    userMenuButton.setAttribute('aria-expanded', 'false');
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    userMenu.classList.add('hidden');
                    userMenuButton.setAttribute('aria-expanded', 'false');
                }
            });
        }
    </script>
    <?php
}

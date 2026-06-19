<template>
    <div class="flex items-center gap-2.5 px-1">
        <!-- Peristalsis ring logo: cherry gradient arc -->
        <svg
            class="size-[26px] shrink-0"
            viewBox="0 0 32 32"
            aria-hidden="true"
        >
            <defs>
                <linearGradient id="peristalsis-ring" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%" stop-color="#ff9aa8" />
                    <stop offset="45%" stop-color="#f23a51" />
                    <stop offset="100%" stop-color="#e02f46" />
                </linearGradient>
            </defs>
            <circle
                cx="16"
                cy="16"
                r="11"
                fill="none"
                stroke="url(#peristalsis-ring)"
                stroke-width="6"
                stroke-linecap="round"
                stroke-dasharray="58 12"
                transform="rotate(-90 16 16)"
            />
        </svg>
        <span class="text-base font-semibold tracking-tight text-sidebar-foreground">Peristalsis</span>
    </div>
</template>

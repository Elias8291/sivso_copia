import { Menu, Moon, Sun } from 'lucide-react';
import { useTheme } from '@/contexts/ThemeContext';

export default function DashboardHeader({ onMenuClick, header }) {
    const { isDarkMode, toggleTheme } = useTheme();

    return (
        <header className="sticky top-0 z-20 flex shrink-0 items-center justify-between gap-4 border-b border-zinc-200/50 bg-white/35 px-4 py-3 backdrop-blur-md dark:border-zinc-800/50 dark:bg-black/20 sm:px-8 lg:px-16 xl:px-20">
            <div className="flex min-w-0 flex-1 items-center gap-3">
                <button
                    type="button"
                    onClick={onMenuClick}
                    className="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-xl border border-zinc-200/90 text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800 lg:hidden"
                    aria-label="Abrir menú"
                >
                    <Menu className="size-5" aria-hidden />
                </button>
                <div className="min-w-0 text-zinc-900 dark:text-zinc-100">{header}</div>
            </div>
            <button
                type="button"
                onClick={toggleTheme}
                className="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-xl border border-zinc-200/90 text-zinc-600 transition hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800"
                aria-label={isDarkMode ? 'Modo claro' : 'Modo oscuro'}
            >
                {isDarkMode ? <Sun className="size-5" /> : <Moon className="size-5" />}
            </button>
        </header>
    );
}

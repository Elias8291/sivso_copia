import React from 'react';
import { createPortal } from 'react-dom';
import { Sun, Moon } from 'lucide-react';
import { Head } from '@inertiajs/react';
import { useTheme } from '../../contexts/ThemeContext';

export default function AuthLayout({
    children,
    subtitle = 'Ingrese su RFC para acceder',
    title = 'Inicio de Sesion',
    imageSrc = '/images/sivso-placeholder.svg',
}) {
    const { isDarkMode, toggleTheme } = useTheme();

    const themeButton = (
        <button
            type="button"
            onClick={(e) => {
                e.preventDefault();
                e.stopPropagation();
                toggleTheme();
            }}
            className="fixed z-[100] min-h-[44px] min-w-[44px] rounded-full border border-zinc-200/90 bg-white/95 p-3 shadow-sm ring-1 ring-black/[0.03] transition-colors hover:bg-zinc-50 touch-manipulation pointer-events-auto dark:border-zinc-700 dark:bg-zinc-900/95 dark:ring-white/[0.06] dark:hover:bg-zinc-800"
            style={{
                top: 'max(1rem, env(safe-area-inset-top, 0px))',
                right: 'max(1rem, env(safe-area-inset-right, 0px))',
            }}
            aria-label={isDarkMode ? 'Activar modo claro' : 'Activar modo oscuro'}
        >
            {isDarkMode ? (
                <Sun className="h-5 w-5 text-zinc-400" aria-hidden />
            ) : (
                <Moon className="h-5 w-5 text-zinc-500" aria-hidden />
            )}
        </button>
    );

    return (
        <>
            <Head title={title} />

            <div className="relative flex h-screen min-h-[100dvh] items-center justify-center overflow-x-hidden overflow-y-auto overscroll-none bg-gradient-to-br from-zinc-50 via-white to-zinc-100/90 p-0 transition-colors duration-300 dark:from-[#09090b] dark:via-[#0b0b0c] dark:to-zinc-900 lg:p-8">
                <div
                    className="pointer-events-none absolute inset-0 z-0 flex select-none items-center justify-center overflow-hidden"
                    aria-hidden
                >
                    <span
                        className="absolute font-black italic tracking-[0.35em] text-brand-gold/[0.07] blur-[22px] dark:text-brand-gold/[0.13] dark:blur-[28px]"
                        style={{ fontSize: 'clamp(5rem,20vw,16rem)' }}
                    >
                        SIVSO
                    </span>
                    <span
                        className="relative font-black italic tracking-[0.3em] text-brand-gold/[0.055] blur-[4px] dark:text-brand-gold-soft/[0.11] dark:blur-[6px]"
                        style={{ fontSize: 'clamp(4.5rem,18vw,14rem)' }}
                    >
                        SIVSO
                    </span>
                </div>

                <div
                    className="pointer-events-none absolute inset-0 z-[1] bg-[radial-gradient(ellipse_at_center,transparent_0%,rgba(0,0,0,0.03)_100%)] dark:bg-[radial-gradient(ellipse_at_center,transparent_40%,rgba(0,0,0,0.35)_100%)]"
                    aria-hidden
                />

                <main className="relative z-10 flex w-full max-w-5xl flex-col overflow-hidden border-0 bg-white/95 shadow-[0_1px_0_rgba(0,0,0,0.04)] backdrop-blur-[2px] dark:bg-[#0A0A0B]/95 dark:shadow-[0_1px_0_rgba(255,255,255,0.04)] lg:h-[600px] lg:flex-row lg:rounded-2xl lg:border lg:border-zinc-200/80 lg:dark:border-zinc-800/80">
                    <section className="relative h-[200px] w-full overflow-hidden bg-[#0c0c0e] lg:h-full lg:w-5/12">
                        <div className="absolute inset-x-0 top-0 z-20 flex items-center gap-2 px-4 py-3 lg:hidden">
                            <span className="text-sm font-black tracking-[0.22em] text-white/90">SIVSO</span>
                            <div className="h-0.5 w-5 rounded-full bg-brand-gold" />
                            <span className="text-[9px] font-medium uppercase tracking-[0.2em] text-white/40">Sistema de Vestuario</span>
                        </div>
                        <div className="pointer-events-none absolute bottom-[-1px] left-0 z-20 w-full lg:hidden">
                            <svg viewBox="0 0 1440 120" className="block h-auto w-full origin-bottom scale-[1.02]">
                                <path
                                    d="M0 120H1440V58.5C1185.5 18.5 868.5 0 720 0C571.5 0 254.5 18.5 0 58.5V120Z"
                                    className="fill-white transition-colors dark:fill-[#0A0A0B]"
                                />
                            </svg>
                        </div>
                        <img
                            src={imageSrc}
                            alt="Imagen de acceso"
                            className="absolute inset-0 h-full w-full object-cover"
                        />
                    </section>

                    <section className="relative z-10 flex w-full flex-col justify-center bg-white px-6 py-10 dark:bg-[#0A0A0B] md:px-12 lg:w-7/12 lg:px-16 lg:py-0">
                        <div className="mx-auto w-full max-w-sm">{children}</div>
                    </section>
                </main>

                {typeof document !== 'undefined'
                    ? createPortal(themeButton, document.body)
                    : null}
            </div>
        </>
    );
}

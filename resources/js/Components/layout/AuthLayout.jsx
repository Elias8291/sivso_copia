import React from 'react';
import { createPortal } from 'react-dom';
import { Sun, Moon } from 'lucide-react';
import { Head } from '@inertiajs/react';
import { useTheme } from '../../contexts/ThemeContext';

export default function AuthLayout({
    children,
    subtitle = 'Ingrese su RFC para acceder',
    title = 'Inicio de Sesion',
    imageSrc = null,
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
            className="fixed z-[100] min-h-[44px] min-w-[44px] rounded-full border-2 border-brand-gold/40 bg-gradient-to-br from-white/95 to-[#faf8f3]/95 p-3 shadow-[0_8px_24px_rgba(212,175,55,0.2)] ring-1 ring-brand-gold/20 transition-all hover:border-brand-gold/60 hover:shadow-[0_12px_32px_rgba(212,175,55,0.3)] hover:ring-brand-gold/40 touch-manipulation pointer-events-auto dark:border-brand-gold/30 dark:bg-gradient-to-br dark:from-[#1a1410]/95 dark:to-[#0f0c08]/95 dark:ring-brand-gold/20 dark:hover:border-brand-gold/50 dark:hover:shadow-[0_12px_32px_rgba(212,175,55,0.25)]"
            style={{
                top: 'max(1rem, env(safe-area-inset-top, 0px))',
                right: 'max(1rem, env(safe-area-inset-right, 0px))',
            }}
            aria-label={isDarkMode ? 'Activar modo claro' : 'Activar modo oscuro'}
        >
            {isDarkMode ? (
                <Sun className="h-5 w-5 text-brand-gold/70" aria-hidden />
            ) : (
                <Moon className="h-5 w-5 text-brand-gold/60" aria-hidden />
            )}
        </button>
    );

    return (
        <>
            <Head title={title} />

            {/* Fondo principal */}
            <div className="relative flex min-h-[100dvh] flex-col items-center justify-center overflow-x-hidden overflow-y-auto overscroll-none bg-gradient-to-br from-[#faf8f3] via-[#f5f1e8] to-[#ede7dd] transition-colors duration-500 dark:bg-gradient-to-br dark:from-[#0a0805] dark:via-[#0f0c08] dark:to-[#1a1410] lg:p-8">

                {/* Orbes ambientales de fondo - más dorados y brillantes */}
                <div className="pointer-events-none absolute inset-0 z-0 overflow-hidden" aria-hidden>
                    <div className="absolute -top-40 -right-40 h-[700px] w-[700px] rounded-full bg-gradient-to-br from-brand-gold/[0.15] to-brand-gold/[0.05] blur-[140px] dark:from-brand-gold/[0.12] dark:to-brand-gold/[0.04]" />
                    <div className="absolute -bottom-40 -left-40 h-[700px] w-[700px] rounded-full bg-gradient-to-tr from-brand-gold/[0.10] to-brand-gold/[0.02] blur-[140px] dark:from-brand-gold/[0.08] dark:to-brand-gold/[0.01]" />
                    <div className="absolute top-1/3 left-1/2 h-[500px] w-[500px] -translate-x-1/2 rounded-full bg-brand-gold/[0.04] blur-[100px] dark:bg-brand-gold/[0.02]" />
                </div>

                {/* SIVSO vertical decorativo en desktop */}
                <div
                    className="pointer-events-none absolute inset-y-0 right-6 z-[1] hidden select-none items-center lg:flex xl:right-12"
                    aria-hidden
                >
                    <span
                        className="font-extralight tracking-[0.45em] text-brand-gold/[0.18] transition-all duration-500 dark:text-brand-gold/[0.08]"
                        style={{
                            fontSize: 'clamp(4.5rem, 11vh, 8rem)',
                            writingMode: 'vertical-rl',
                            WebkitTextStroke: '1px rgba(212,175,55,0.7)',
                        }}
                    >
                        SIVSO
                    </span>
                </div>

                {/* Tarjeta principal */}
                <main className="relative z-10 flex w-full max-w-[900px] flex-col overflow-hidden bg-gradient-to-br from-white to-[#faf8f3] shadow-[0_25px_80px_-20px_rgba(212,175,55,0.15),0_0_0_1px_rgba(212,175,55,0.1)] dark:bg-gradient-to-br dark:from-[#1a1410] dark:to-[#0f0c08] dark:shadow-[0_25px_80px_-20px_rgba(212,175,55,0.1),0_0_0_1px_rgba(212,175,55,0.15)] lg:h-[620px] lg:flex-row lg:rounded-3xl">

                    {/* ─── Panel izquierdo: imagen + branding mínimo ─── */}
                    <section className="relative w-full shrink-0 overflow-hidden min-h-[280px] sm:min-h-[320px] lg:h-full lg:min-h-0 lg:w-[45%]">

                        {/* Fallback oscuro cuando no hay imagen */}
                        <div className="absolute inset-0 bg-[#0c0c0e]" />

                        {/* Imagen de fondo con encuadre estable en móvil */}
                        {imageSrc && (
                            <img
                                src={imageSrc}
                                alt=""
                                aria-hidden
                                className="absolute inset-0 h-full w-full object-cover object-[center_7%] sm:object-[center_6%] lg:object-[center_15%]"
                            />
                        )}

                        {/* Degradado base */}
                        <div className="absolute inset-0 bg-gradient-to-t from-black/55 via-black/5 to-transparent" />

                        {/* Móvil: desenfoca el fondo (imagen); el texto queda nítido encima */}
                        <div
                            className="absolute inset-0 z-[5] bg-black/20 backdrop-blur-sm lg:hidden"
                            aria-hidden
                        />

                        {/* Móvil: centrado sin cuadro; escritorio: solo texto elegante abajo */}
                        <div className="relative z-10 flex h-full min-h-[280px] flex-col items-center justify-end px-4 pb-12 sm:min-h-[320px] sm:px-6 lg:min-h-0 lg:items-start lg:justify-end lg:pb-8 lg:px-8">
                            {/* Móvil: sin fondo, solo texto limpio */}
                            <div className="w-full max-w-[340px] border-0 bg-transparent px-5 py-6 text-center shadow-none sm:max-w-[360px] sm:px-6 sm:py-7 lg:border-0 lg:bg-transparent lg:px-0 lg:py-0 lg:text-left lg:shadow-none">
                                <div className="space-y-2 lg:space-y-1">
                                    <div className="mb-2 lg:mb-0">
                                        <h1 className="text-[2.4rem] font-light uppercase tracking-[0.6em] text-brand-gold [text-shadow:0_2px_8px_rgba(212,175,55,0.2)] sm:text-[2.6rem] lg:text-[1.5rem] lg:[text-shadow:none] lg:text-brand-gold/85 lg:tracking-[0.35em] lg:font-light">
                                            SIVSO
                                        </h1>
                                    </div>
                                    
                                    <p className="text-[10px] font-light uppercase tracking-[0.15em] text-white/70 sm:text-[11px] lg:text-[10px] lg:text-brand-gold/75 lg:tracking-[0.12em] lg:font-light lg:leading-tight">
                                        Sistema Integral de Vestuario Sindicato
                                    </p>
                                </div>
                            </div>
                        </div>

                        {/* Wave móvil */}
                        <div className="pointer-events-none absolute bottom-[-1px] left-0 z-20 w-full lg:hidden">
                            <svg viewBox="0 0 1440 60" className="block h-auto w-full" preserveAspectRatio="none">
                                <path
                                    d="M0 60H1440V24C1200 6 960 0 720 0C480 0 240 6 0 24V60Z"
                                    className="fill-white dark:fill-[#09090b]"
                                />
                            </svg>
                        </div>
                    </section>

                    {/* ─── Panel derecho: formulario ─── */}
                    <section className="relative flex w-full flex-col justify-center bg-gradient-to-br from-white to-[#faf8f3] px-6 py-12 dark:bg-gradient-to-br dark:from-[#1a1410] dark:to-[#0f0c08] sm:px-8 sm:py-14 lg:w-[55%] lg:px-14 lg:py-0">

                        {/* Borde izquierdo decorativo solo en desktop */}
                        <div className="absolute left-0 top-1/2 hidden h-40 w-px -translate-y-1/2 bg-gradient-to-b from-transparent via-brand-gold/30 to-transparent lg:block" />

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

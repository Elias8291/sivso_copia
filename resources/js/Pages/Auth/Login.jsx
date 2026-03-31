import React, { useState } from 'react';
import { useForm } from '@inertiajs/react';
import { ThemeProvider } from '../../contexts/ThemeContext';
import AuthLayout from '../../components/layout/AuthLayout';
import PasswordInput from '../../components/ui/PasswordInput';

export default function Login() {
    const [loading, setLoading] = useState(false);
    const { data, setData, post, processing, errors } = useForm({
        rfc: '',
        password: '',
        remember: false,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        setLoading(true);
        post(route('login'), {
            onFinish: () => setLoading(false),
        });
    };

    return (
        <ThemeProvider>
            <AuthLayout
                title="Inicio de Sesion"
                subtitle="Ingrese su RFC para acceder"
                imageSrc="/images/login-2.png"
            >
                {(loading || processing) && (
                    <div className="fixed inset-0 z-[200] flex items-center justify-center bg-gradient-to-br from-white/90 to-[#faf8f3]/90 backdrop-blur-md dark:bg-gradient-to-br dark:from-[#0a0805]/90 dark:to-[#1a1410]/90">
                        <div className="flex flex-col items-center gap-4">
                            <span className="size-12 animate-spin rounded-full border-3 border-brand-gold/20 border-t-brand-gold shadow-[0_0_20px_rgba(212,175,55,0.4)] dark:border-brand-gold/15 dark:border-t-brand-gold" />
                            <p className="text-[12px] font-semibold uppercase tracking-[0.15em] text-brand-gold/80 dark:text-brand-gold/70">
                                Iniciando sesion...
                            </p>
                        </div>
                    </div>
                )}
                <header className="mb-8 text-center lg:text-left">
                    <div
                        className="mx-auto h-0.5 w-16 rounded-full bg-gradient-to-r from-transparent via-brand-gold/70 to-transparent lg:mx-0"
                        aria-hidden
                    />
                    <h2 className="mt-4 text-lg font-semibold uppercase tracking-[0.15em] text-zinc-900 dark:text-white">
                        Inicio de Sesión
                    </h2>
                </header>

                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="space-y-1.5">
                        <label className="ml-0.5 block text-[10px] font-bold uppercase tracking-[0.12em] text-zinc-900 dark:text-brand-gold/70">
                            RFC
                        </label>
                        <input
                            type="text"
                            name="rfc"
                            value={data.rfc}
                            onChange={(e) => setData('rfc', e.target.value.toUpperCase())}
                            placeholder="ABCD123456XYZ"
                            className="w-full rounded-xl border border-brand-gold/15 bg-white/50 px-4 py-3 text-sm uppercase tracking-wide text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-brand-gold/40 focus:ring-2 focus:ring-brand-gold/15 dark:border-brand-gold/10 dark:bg-zinc-900/30 dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-brand-gold/30"
                            autoComplete="off"
                        />
                        {errors.rfc && (
                            <p className="text-xs font-medium text-red-600 dark:text-red-400">{errors.rfc}</p>
                        )}
                    </div>
                    <div className="space-y-1.5">
                        <label className="ml-0.5 block text-[10px] font-bold uppercase tracking-[0.12em] text-zinc-900 dark:text-brand-gold/70">
                            Contraseña
                        </label>
                        <PasswordInput
                            name="password"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            placeholder="••••••••"
                        />
                        {errors.password && (
                            <p className="text-xs font-medium text-red-600 dark:text-red-400">{errors.password}</p>
                        )}
                    </div>
                    <label className="flex items-center gap-2 text-xs text-zinc-600 dark:text-zinc-400 cursor-pointer hover:text-brand-gold/80 transition">
                        <input
                            type="checkbox"
                            name="remember"
                            checked={data.remember}
                            onChange={(e) => setData('remember', e.target.checked)}
                            className="h-4 w-4 rounded border border-brand-gold/30 bg-white text-brand-gold focus:ring-2 focus:ring-brand-gold/15 dark:border-brand-gold/20 dark:bg-zinc-800 dark:text-brand-gold cursor-pointer"
                        />
                        Recordarme
                    </label>
                    <button
                        type="submit"
                        disabled={loading || processing}
                        className="w-full rounded-xl bg-gradient-to-r from-brand-gold/85 to-brand-gold/75 py-3 text-[11px] font-semibold uppercase tracking-[0.14em] text-white shadow-[0_8px_24px_rgba(212,175,55,0.3)] transition-all hover:shadow-[0_12px_32px_rgba(212,175,55,0.4)] hover:from-brand-gold to-brand-gold/90 disabled:cursor-not-allowed disabled:opacity-60 disabled:shadow-none dark:shadow-[0_8px_24px_rgba(212,175,55,0.25)]"
                    >
                        Iniciar Sesión
                    </button>
                </form>

                <footer className="mt-9 space-y-1 border-t border-brand-gold/10 pt-5 text-center dark:border-brand-gold/10">
                    <p className="text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-600 dark:text-zinc-400">
                        Secretaría de Administración Oaxaca
                    </p>
                    <p className="text-[10px] font-medium uppercase tracking-[0.14em] text-red-700 dark:text-red-500">
                        Gobierno de Oaxaca
                    </p>
                </footer>
            </AuthLayout>
        </ThemeProvider>
    );
}

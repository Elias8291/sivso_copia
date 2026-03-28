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
                    <div className="fixed inset-0 z-[200] flex items-center justify-center bg-white/80 backdrop-blur-sm dark:bg-[#09090b]/80">
                        <div className="flex flex-col items-center gap-3">
                            <span className="size-10 animate-spin rounded-full border-2 border-zinc-200 border-t-brand-gold dark:border-zinc-700 dark:border-t-brand-gold" />
                            <p className="text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">
                                Iniciando sesion...
                            </p>
                        </div>
                    </div>
                )}
                <header className="mb-7 text-center lg:text-left">
                    <div
                        className="mx-auto h-px w-10 rounded-full bg-gradient-to-r from-transparent via-brand-gold/90 to-transparent lg:mx-0"
                        aria-hidden
                    />
                    <h2 className="mt-3 text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">
                        Inicio de sesion
                    </h2>
                    <p className="mt-1 text-[13px] leading-relaxed text-zinc-500 dark:text-zinc-500">
                        Ingrese su RFC para acceder
                    </p>
                </header>

                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="space-y-1.5">
                        <label className="ml-0.5 block text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500 dark:text-zinc-400">
                            RFC
                        </label>
                        <input
                            type="text"
                            name="rfc"
                            value={data.rfc}
                            onChange={(e) => setData('rfc', e.target.value.toUpperCase())}
                            placeholder="ABCD123456XYZ"
                            className="w-full rounded-xl border border-zinc-200/90 bg-white/80 px-4 py-3 text-sm uppercase tracking-wide text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-brand-gold/40 focus:ring-2 focus:ring-brand-gold/15 dark:border-zinc-700/80 dark:bg-zinc-900/50 dark:text-white dark:placeholder:text-zinc-500"
                            autoComplete="off"
                        />
                        {errors.rfc && (
                            <p className="text-xs font-medium text-red-600 dark:text-red-400">{errors.rfc}</p>
                        )}
                    </div>
                    <div className="space-y-1.5">
                        <label className="ml-0.5 block text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500 dark:text-zinc-400">
                            Contrasena
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
                    <label className="flex items-center gap-2 text-xs text-zinc-600 dark:text-zinc-400">
                        <input
                            type="checkbox"
                            name="remember"
                            checked={data.remember}
                            onChange={(e) => setData('remember', e.target.checked)}
                            className="h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800"
                        />
                        Recordarme
                    </label>
                    <button
                        type="submit"
                        disabled={loading || processing}
                        className="w-full rounded-xl bg-zinc-900 py-3 text-[11px] font-semibold uppercase tracking-[0.14em] text-white transition-opacity hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-white dark:text-zinc-900"
                    >
                        Iniciar sesion
                    </button>
                </form>

                <footer className="mt-9 space-y-1 border-t border-zinc-200/70 pt-5 text-center dark:border-zinc-800/80">
                    <p className="text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500 dark:text-zinc-400">
                        Secretaria de Administracion Oaxaca
                    </p>
                    <p className="text-[10px] font-medium uppercase tracking-[0.14em] text-zinc-400 dark:text-zinc-500">
                        Gobierno de Oaxaca
                    </p>
                </footer>
            </AuthLayout>
        </ThemeProvider>
    );
}

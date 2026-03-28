import React, { useRef, useState } from 'react';
import { router, useForm } from '@inertiajs/react';
import { Eye, EyeOff } from 'lucide-react';
import AuthLayout from '../../components/layout/AuthLayout';
import PasswordInput from '../../components/ui/PasswordInput';
import { ThemeProvider } from '../../contexts/ThemeContext';

const inputClass =
    'w-full rounded-xl border border-zinc-200/90 bg-white/80 px-4 py-3 pr-11 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-brand-gold/40 focus:ring-2 focus:ring-brand-gold/15 dark:border-zinc-700/80 dark:bg-zinc-900/50 dark:text-white dark:placeholder:text-zinc-500';

export default function ForceChangePassword() {
    const passwordInput = useRef(null);
    const [showNew, setShowNew] = useState(false);
    const [showConfirm, setShowConfirm] = useState(false);

    const { data, setData, put, processing, errors, reset } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (e) => {
        e.preventDefault();
        put(route('password.update'), {
            preserveScroll: true,
            onSuccess: () => reset(),
            onError: (errs) => {
                if (errs.password) {
                    reset('password', 'password_confirmation');
                    passwordInput.current?.focus();
                }
                if (errs.current_password) {
                    reset('current_password');
                }
            },
        });
    };

    return (
        <ThemeProvider>
            <AuthLayout
                title="Cambiar contraseña"
                subtitle="Por seguridad debes definir una contraseña nueva antes de continuar"
                imageSrc="/images/login-2.png"
            >
                <header className="mb-7 text-center lg:text-left">
                    <div
                        className="mx-auto h-px w-10 rounded-full bg-gradient-to-r from-transparent via-brand-gold/90 to-transparent lg:mx-0"
                        aria-hidden
                    />
                    <h2 className="mt-3 text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">
                        Contraseña obligatoria
                    </h2>
                    <p className="mt-1 text-[13px] leading-relaxed text-zinc-500 dark:text-zinc-500">
                        Usa la contraseña con la que iniciaste sesión y elige una nueva.
                    </p>
                </header>

                <form onSubmit={submit} className="space-y-4">
                    <div className="space-y-1.5">
                        <label
                            htmlFor="current_password"
                            className="ml-0.5 block text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500 dark:text-zinc-400"
                        >
                            Contraseña actual
                        </label>
                        <PasswordInput
                            id="current_password"
                            name="current_password"
                            value={data.current_password}
                            onChange={(e) => setData('current_password', e.target.value)}
                            placeholder="••••••••"
                        />
                        {errors.current_password && (
                            <p className="text-xs font-medium text-red-600 dark:text-red-400">
                                {errors.current_password}
                            </p>
                        )}
                    </div>

                    <div className="space-y-1.5">
                        <label
                            htmlFor="password"
                            className="ml-0.5 block text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500 dark:text-zinc-400"
                        >
                            Nueva contraseña
                        </label>
                        <div className="relative">
                            <input
                                id="password"
                                ref={passwordInput}
                                type={showNew ? 'text' : 'password'}
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                autoComplete="new-password"
                                className={inputClass}
                                placeholder="••••••••"
                            />
                            <button
                                type="button"
                                onClick={() => setShowNew((v) => !v)}
                                className="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg p-1.5 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                                aria-label={showNew ? 'Ocultar contraseña' : 'Mostrar contraseña'}
                            >
                                {showNew ? (
                                    <EyeOff className="size-4" strokeWidth={2} aria-hidden />
                                ) : (
                                    <Eye className="size-4" strokeWidth={2} aria-hidden />
                                )}
                            </button>
                        </div>
                        {errors.password && (
                            <p className="text-xs font-medium text-red-600 dark:text-red-400">{errors.password}</p>
                        )}
                    </div>

                    <div className="space-y-1.5">
                        <label
                            htmlFor="password_confirmation"
                            className="ml-0.5 block text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500 dark:text-zinc-400"
                        >
                            Confirmar nueva contraseña
                        </label>
                        <div className="relative">
                            <input
                                id="password_confirmation"
                                type={showConfirm ? 'text' : 'password'}
                                value={data.password_confirmation}
                                onChange={(e) => setData('password_confirmation', e.target.value)}
                                autoComplete="new-password"
                                className={inputClass}
                                placeholder="••••••••"
                            />
                            <button
                                type="button"
                                onClick={() => setShowConfirm((v) => !v)}
                                className="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg p-1.5 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                                aria-label={
                                    showConfirm ? 'Ocultar confirmación' : 'Mostrar confirmación'
                                }
                            >
                                {showConfirm ? (
                                    <EyeOff className="size-4" strokeWidth={2} aria-hidden />
                                ) : (
                                    <Eye className="size-4" strokeWidth={2} aria-hidden />
                                )}
                            </button>
                        </div>
                        {errors.password_confirmation && (
                            <p className="text-xs font-medium text-red-600 dark:text-red-400">
                                {errors.password_confirmation}
                            </p>
                        )}
                    </div>

                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full rounded-xl bg-zinc-900 py-3 text-[11px] font-semibold uppercase tracking-[0.14em] text-white transition-opacity hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-white dark:text-zinc-900"
                    >
                        {processing ? 'Guardando…' : 'Guardar y continuar'}
                    </button>
                </form>

                <p className="mt-6 text-center">
                    <button
                        type="button"
                        onClick={() => router.post(route('logout'))}
                        className="text-[11px] font-medium text-zinc-500 underline decoration-zinc-300 underline-offset-2 transition hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200"
                    >
                        Cerrar sesión
                    </button>
                </p>

                <footer className="mt-9 space-y-1 border-t border-zinc-200/70 pt-5 text-center dark:border-zinc-800/80">
                    <p className="text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500 dark:text-zinc-400">
                        Secretaría de Administración Oaxaca
                    </p>
                </footer>
            </AuthLayout>
        </ThemeProvider>
    );
}

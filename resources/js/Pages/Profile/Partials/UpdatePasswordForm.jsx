import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Transition } from '@headlessui/react';
import { useForm } from '@inertiajs/react';
import { Eye, EyeOff } from 'lucide-react';
import { useRef, useState } from 'react';

const btnClass =
    'rounded-lg border-0 bg-zinc-900 px-5 py-2.5 text-[11px] font-bold uppercase tracking-wider text-white hover:bg-zinc-800 focus:ring-2 focus:ring-brand-gold/30 focus:ring-offset-2 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white dark:focus:ring-brand-gold/40';

const labelClass = '!text-[11px] !font-bold !uppercase !tracking-wider !text-zinc-500 dark:!text-zinc-400';

export default function UpdatePasswordForm({ inputClassName = '' }) {
    const passwordInput = useRef();
    const currentPasswordInput = useRef();
    const [showNewPassword, setShowNewPassword] = useState(false);
    const [showConfirmPassword, setShowConfirmPassword] = useState(false);

    const {
        data,
        setData,
        errors,
        put,
        reset,
        processing,
        recentlySuccessful,
    } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const updatePassword = (e) => {
        e.preventDefault();

        put(route('password.update'), {
            preserveScroll: true,
            onSuccess: () => reset(),
            onError: (errs) => {
                if (errs.password) {
                    reset('password', 'password_confirmation');
                    passwordInput.current.focus();
                }

                if (errs.current_password) {
                    reset('current_password');
                    currentPasswordInput.current.focus();
                }
            },
        });
    };

    return (
        <section className="w-full">
            <header className="border-b border-zinc-100 pb-5 dark:border-zinc-800/80">
                <p className="text-[10px] font-bold uppercase tracking-[0.18em] text-brand-gold dark:text-brand-gold-soft">
                    Seguridad
                </p>
                <h2 className="mt-1 text-lg font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                    Contraseña
                </h2>
                <p className="mt-1 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">
                    Usa una contraseña larga y distinta a las de otros sitios.
                </p>
            </header>

            <form onSubmit={updatePassword} className="mt-6 space-y-5">
                <div>
                    <InputLabel
                        htmlFor="current_password"
                        value="Contraseña actual"
                        className={labelClass}
                    />

                    <TextInput
                        id="current_password"
                        ref={currentPasswordInput}
                        value={data.current_password}
                        onChange={(e) =>
                            setData('current_password', e.target.value)
                        }
                        type="password"
                        className={inputClassName}
                        autoComplete="current-password"
                    />

                    <InputError
                        message={errors.current_password}
                        className="mt-2"
                    />
                </div>

                <div>
                    <InputLabel htmlFor="password" value="Nueva contraseña" className={labelClass} />

                    <div className="relative mt-1">
                        <TextInput
                            id="password"
                            ref={passwordInput}
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            type={showNewPassword ? 'text' : 'password'}
                            className={`${inputClassName} !mt-0 !pr-11`}
                            autoComplete="new-password"
                        />
                        <button
                            type="button"
                            onClick={() => setShowNewPassword((v) => !v)}
                            className="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg p-1.5 text-zinc-400 outline-none transition-colors hover:bg-zinc-100 hover:text-zinc-700 focus-visible:ring-2 focus-visible:ring-brand-gold/40 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                            aria-label={
                                showNewPassword
                                    ? 'Ocultar nueva contraseña'
                                    : 'Mostrar nueva contraseña'
                            }
                        >
                            {showNewPassword ? (
                                <EyeOff className="size-4 shrink-0" strokeWidth={2} aria-hidden />
                            ) : (
                                <Eye className="size-4 shrink-0" strokeWidth={2} aria-hidden />
                            )}
                        </button>
                    </div>

                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div>
                    <InputLabel
                        htmlFor="password_confirmation"
                        value="Confirmar nueva contraseña"
                        className={labelClass}
                    />

                    <div className="relative mt-1">
                        <TextInput
                            id="password_confirmation"
                            value={data.password_confirmation}
                            onChange={(e) =>
                                setData('password_confirmation', e.target.value)
                            }
                            type={showConfirmPassword ? 'text' : 'password'}
                            className={`${inputClassName} !mt-0 !pr-11`}
                            autoComplete="new-password"
                        />
                        <button
                            type="button"
                            onClick={() => setShowConfirmPassword((v) => !v)}
                            className="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg p-1.5 text-zinc-400 outline-none transition-colors hover:bg-zinc-100 hover:text-zinc-700 focus-visible:ring-2 focus-visible:ring-brand-gold/40 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                            aria-label={
                                showConfirmPassword
                                    ? 'Ocultar confirmación de contraseña'
                                    : 'Mostrar confirmación de contraseña'
                            }
                        >
                            {showConfirmPassword ? (
                                <EyeOff className="size-4 shrink-0" strokeWidth={2} aria-hidden />
                            ) : (
                                <Eye className="size-4 shrink-0" strokeWidth={2} aria-hidden />
                            )}
                        </button>
                    </div>

                    <InputError
                        message={errors.password_confirmation}
                        className="mt-2"
                    />
                </div>

                <div className="flex flex-wrap items-center gap-4 pt-1">
                    <PrimaryButton disabled={processing} className={btnClass}>
                        Actualizar contraseña
                    </PrimaryButton>

                    <Transition
                        show={recentlySuccessful}
                        enter="transition ease-in-out"
                        enterFrom="opacity-0"
                        leave="transition ease-in-out"
                        leaveTo="opacity-0"
                    >
                        <p className="text-sm font-medium text-emerald-600 dark:text-emerald-400">Guardado.</p>
                    </Transition>
                </div>
            </form>
        </section>
    );
}

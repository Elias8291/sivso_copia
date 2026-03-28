import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Transition } from '@headlessui/react';
import { Link, useForm, usePage } from '@inertiajs/react';

const btnClass =
    'rounded-lg border-0 bg-zinc-900 px-5 py-2.5 text-[11px] font-bold uppercase tracking-wider text-white hover:bg-zinc-800 focus:ring-2 focus:ring-brand-gold/30 focus:ring-offset-2 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white dark:focus:ring-brand-gold/40';

const labelClass = '!text-[11px] !font-bold !uppercase !tracking-wider !text-zinc-500 dark:!text-zinc-400';

export default function UpdateProfileInformation({
    mustVerifyEmail,
    status,
    inputClassName = '',
}) {
    const user = usePage().props.auth.user;

    const { data, setData, patch, errors, processing, recentlySuccessful } =
        useForm({
            name: user.name,
            email: user.email,
        });

    const submit = (e) => {
        e.preventDefault();

        patch(route('profile.update'));
    };

    return (
        <section className="w-full">
            <header className="border-b border-zinc-100 pb-5 dark:border-zinc-800/80">
                <p className="text-[10px] font-bold uppercase tracking-[0.18em] text-brand-gold dark:text-brand-gold-soft">
                    Perfil
                </p>
                <h2 className="mt-1 text-lg font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                    Datos personales
                </h2>
                <p className="mt-1 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">
                    Nombre y correo con el que accedes al sistema.
                </p>
            </header>

            <form onSubmit={submit} className="mt-6 space-y-5">
                <div>
                    <InputLabel htmlFor="name" value="Nombre" className={labelClass} />

                    <TextInput
                        id="name"
                        className={inputClassName}
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        required
                        isFocused
                        autoComplete="name"
                    />

                    <InputError className="mt-2" message={errors.name} />
                </div>

                <div>
                    <InputLabel htmlFor="email" value="Correo electrónico" className={labelClass} />

                    <TextInput
                        id="email"
                        type="email"
                        className={inputClassName}
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        required
                        autoComplete="username"
                    />

                    <InputError className="mt-2" message={errors.email} />
                </div>

                {mustVerifyEmail && user.email_verified_at === null && (
                    <div className="rounded-xl border border-amber-200/70 bg-amber-50/50 px-3 py-3 dark:border-amber-900/40 dark:bg-amber-950/25">
                        <p className="text-sm leading-relaxed text-zinc-700 dark:text-zinc-300">
                            Tu correo aún no está verificado.{' '}
                            <Link
                                href={route('verification.send')}
                                method="post"
                                as="button"
                                className="font-semibold text-brand-gold underline decoration-brand-gold/40 underline-offset-2 transition-colors hover:text-brand-gold/90 dark:text-brand-gold-soft"
                            >
                                Reenviar correo de verificación
                            </Link>
                            .
                        </p>

                        {status === 'verification-link-sent' && (
                            <p className="mt-2 text-sm font-medium text-emerald-600 dark:text-emerald-400">
                                Te enviamos un nuevo enlace de verificación.
                            </p>
                        )}
                    </div>
                )}

                <div className="flex flex-wrap items-center gap-4 pt-1">
                    <PrimaryButton disabled={processing} className={btnClass}>
                        Guardar cambios
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

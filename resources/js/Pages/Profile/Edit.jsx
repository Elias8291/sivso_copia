import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, usePage } from '@inertiajs/react';
import DeleteUserForm from './Partials/DeleteUserForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';

const inputProfile =
    '!mt-1 !block !w-full !rounded-xl !border-zinc-200/90 !bg-white !px-3.5 !py-2.5 !text-sm !text-zinc-900 !shadow-none transition-colors focus:!border-brand-gold/55 focus:!ring-2 focus:!ring-brand-gold/15 dark:!border-zinc-700 dark:!bg-zinc-900/60 dark:!text-zinc-100 dark:focus:!border-brand-gold/45';

export default function Edit({ mustVerifyEmail, status }) {
    const user = usePage().props.auth.user;

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                    <span>SIVSO</span>
                    <span className="text-zinc-300 dark:text-zinc-600">/</span>
                    <span className="text-zinc-900 dark:text-zinc-100">MI CUENTA</span>
                </div>
            }
        >
            <Head title="Mi cuenta" />

            <div className="mx-auto w-full max-w-2xl px-4 pb-14 pt-6 sm:px-6 lg:px-8">
                <header className="mb-8 border-b border-zinc-200/80 pb-6 dark:border-zinc-800/80">
                    <h1 className="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                        Mi cuenta
                    </h1>
                    <p className="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        Datos personales, contraseña y eliminación de la cuenta.
                    </p>
                    <dl className="mt-5 space-y-1 border-l-2 border-brand-gold/40 pl-3 dark:border-brand-gold/35">
                        <div>
                            <dt className="sr-only">Nombre</dt>
                            <dd className="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                {user?.name}
                            </dd>
                        </div>
                        <div>
                            <dt className="sr-only">Correo</dt>
                            <dd className="text-xs text-zinc-500 dark:text-zinc-400">{user?.email}</dd>
                        </div>
                    </dl>
                </header>

                <div className="space-y-5">
                    <section className="rounded-2xl border border-zinc-200/80 bg-white/90 p-6 shadow-sm shadow-zinc-900/[0.03] dark:border-zinc-800/80 dark:bg-zinc-900/45 dark:shadow-none sm:p-7">
                        <UpdateProfileInformationForm
                            mustVerifyEmail={mustVerifyEmail}
                            status={status}
                            inputClassName={inputProfile}
                        />
                    </section>

                    <section className="rounded-2xl border border-zinc-200/80 bg-white/90 p-6 shadow-sm shadow-zinc-900/[0.03] dark:border-zinc-800/80 dark:bg-zinc-900/45 dark:shadow-none sm:p-7">
                        <UpdatePasswordForm inputClassName={inputProfile} />
                    </section>

                    <section className="rounded-2xl border border-red-200/55 bg-red-50/[0.35] p-6 shadow-sm shadow-zinc-900/[0.02] dark:border-red-900/35 dark:bg-red-950/20 dark:shadow-none sm:p-7">
                        <DeleteUserForm inputClassName={inputProfile} />
                    </section>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

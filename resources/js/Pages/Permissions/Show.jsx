import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Key } from 'lucide-react';

export default function Show({ permission }) {
    const formatDate = (dateString) => {
        if (!dateString) return '—';
        return new Date(dateString).toLocaleDateString('es-MX', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                    <span>SIVSO</span>
                    <span className="text-zinc-300 dark:text-zinc-600">/</span>
                    <Link href={route('permissions.index')} className="hover:text-zinc-800 dark:hover:text-zinc-200 transition-colors">
                        PERMISOS
                    </Link>
                    <span className="text-zinc-300 dark:text-zinc-600">/</span>
                    <span className="text-zinc-900 dark:text-zinc-100">DETALLES</span>
                </div>
            }
        >
            <Head title={`Permiso - ${permission.name}`} />

            <div className="mx-auto w-full max-w-3xl space-y-8">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                            Detalles del Permiso
                        </h2>
                        <p className="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            Información del permiso.
                        </p>
                    </div>
                    <div className="flex items-center gap-3">
                        <Link
                            href={route('permissions.index')}
                            className="inline-flex items-center justify-center gap-2 rounded-md border border-zinc-200 bg-white px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-800 dark:bg-[#0A0A0B] dark:text-zinc-300 dark:hover:bg-zinc-900"
                        >
                            <ArrowLeft className="size-4" strokeWidth={2} />
                            Regresar
                        </Link>
                        <Link
                            href={route('permissions.edit', permission.id)}
                            className="inline-flex items-center justify-center gap-2 rounded-md bg-zinc-900 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white"
                        >
                            Editar Permiso
                        </Link>
                    </div>
                </div>

                <div className="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-[#0A0A0B]">
                    <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-6 py-6">
                        <div className="flex items-center gap-4">
                            <div className="flex size-14 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                                <Key className="size-6" strokeWidth={2} />
                            </div>
                            <div>
                                <h3 className="text-lg font-bold tracking-wide text-zinc-900 dark:text-zinc-100">
                                    {permission.name}
                                </h3>
                                <p className="mt-1 text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                    Creado el {formatDate(permission.created_at)}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

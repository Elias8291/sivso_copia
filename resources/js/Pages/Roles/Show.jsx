import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, ShieldCheck, Key } from 'lucide-react';

function InfoItem({ icon: Icon, label, value }) {
    return (
        <div className="flex items-start gap-4 p-4 transition-colors hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30">
            <div className="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                <Icon className="size-4" strokeWidth={2} />
            </div>
            <div className="flex flex-col min-w-0">
                <span className="text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                    {label}
                </span>
                <span className="mt-1 text-sm font-medium text-zinc-900 dark:text-zinc-100 break-words">
                    {value || '—'}
                </span>
            </div>
        </div>
    );
}

export default function Show({ role }) {
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
                    <Link href={route('roles.index')} className="hover:text-zinc-800 dark:hover:text-zinc-200 transition-colors">
                        ROLES
                    </Link>
                    <span className="text-zinc-300 dark:text-zinc-600">/</span>
                    <span className="text-zinc-900 dark:text-zinc-100">DETALLES</span>
                </div>
            }
        >
            <Head title={`Rol - ${role.name}`} />

            <div className="mx-auto w-full max-w-3xl space-y-8">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                            Detalles del Rol
                        </h2>
                        <p className="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            Información y permisos asignados.
                        </p>
                    </div>
                    <div className="flex items-center gap-3">
                        <Link
                            href={route('roles.index')}
                            className="inline-flex items-center justify-center gap-2 rounded-md border border-zinc-200 bg-white px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-800 dark:bg-[#0A0A0B] dark:text-zinc-300 dark:hover:bg-zinc-900"
                        >
                            <ArrowLeft className="size-4" strokeWidth={2} />
                            Regresar
                        </Link>
                        <Link
                            href={route('roles.edit', role.id)}
                            className="inline-flex items-center justify-center gap-2 rounded-md bg-zinc-900 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white"
                        >
                            Editar Rol
                        </Link>
                    </div>
                </div>

                <div className="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-[#0A0A0B]">
                    <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-zinc-200 px-6 py-6 dark:border-zinc-800">
                        <div className="flex items-center gap-4">
                            <div className="flex size-14 shrink-0 items-center justify-center rounded-full bg-brand-gold/10 text-brand-gold dark:bg-brand-gold/20 dark:text-brand-gold-soft">
                                <ShieldCheck className="size-6" strokeWidth={2} />
                            </div>
                            <div>
                                <h3 className="text-lg font-bold uppercase tracking-wide text-zinc-900 dark:text-zinc-100">
                                    {role.name}
                                </h3>
                                <p className="mt-1 text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                    Creado el {formatDate(role.created_at)}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="p-6 sm:p-8">
                        <div className="flex items-center gap-2 mb-6">
                            <Key className="size-4 text-zinc-400" strokeWidth={2} />
                            <h4 className="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                Permisos Asignados ({role.permissions.length})
                            </h4>
                        </div>
                        
                        {role.permissions.length > 0 ? (
                            <div className="flex flex-wrap gap-2">
                                {role.permissions.map((permission) => (
                                    <span 
                                        key={permission.id}
                                        className="inline-flex items-center rounded-md border border-zinc-200/80 bg-zinc-50 px-3 py-1.5 text-[11px] font-semibold tracking-wide text-zinc-700 dark:border-zinc-700/80 dark:bg-zinc-800/50 dark:text-zinc-300"
                                    >
                                        {permission.name}
                                    </span>
                                ))}
                            </div>
                        ) : (
                            <p className="text-sm text-zinc-500 dark:text-zinc-400 italic">
                                Este rol no tiene permisos asignados.
                            </p>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, User, Mail, CreditCard, Calendar, ShieldCheck, CheckCircle2, XCircle } from 'lucide-react';

function StatusBadge({ activo }) {
    return (
        <span className="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
            <span className={`size-1.5 rounded-full ${activo ? 'bg-zinc-900 dark:bg-zinc-100' : 'bg-zinc-400 dark:bg-zinc-500'}`} />
            {activo ? 'Activo' : 'Inactivo'}
        </span>
    );
}

function RoleBadge({ role }) {
    return (
        <span className="inline-flex items-center rounded-md bg-brand-gold/10 px-2 py-1 text-[9px] font-bold uppercase tracking-wider text-brand-gold dark:bg-brand-gold/20 dark:text-brand-gold-soft">
            {role}
        </span>
    );
}

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

export default function Show({ user }) {
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
                    <Link href={route('users.index')} className="hover:text-zinc-800 dark:hover:text-zinc-200 transition-colors">
                        USUARIOS
                    </Link>
                    <span className="text-zinc-300 dark:text-zinc-600">/</span>
                    <span className="text-zinc-900 dark:text-zinc-100">DETALLES</span>
                </div>
            }
        >
            <Head title={`Usuario - ${user.name}`} />

            <div className="mx-auto w-full max-w-3xl space-y-8">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                            Detalles del Usuario
                        </h2>
                        <p className="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            Información completa de la cuenta.
                        </p>
                    </div>
                    <div className="flex items-center gap-3">
                        <Link
                            href={route('users.index')}
                            className="inline-flex items-center justify-center gap-2 rounded-md border border-zinc-200 bg-white px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-800 dark:bg-[#0A0A0B] dark:text-zinc-300 dark:hover:bg-zinc-900"
                        >
                            <ArrowLeft className="size-4" strokeWidth={2} />
                            Regresar
                        </Link>
                        <Link
                            href={route('users.edit', user.id)}
                            className="inline-flex items-center justify-center gap-2 rounded-md bg-zinc-900 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white"
                        >
                            Editar Usuario
                        </Link>
                    </div>
                </div>

                <div className="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-[#0A0A0B]">
                    <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-zinc-200 px-6 py-6 dark:border-zinc-800">
                        <div className="flex items-center gap-4">
                            <div className="flex size-14 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-xl font-bold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                {user.name.charAt(0).toUpperCase()}
                            </div>
                            <div>
                                <h3 className="text-lg font-bold text-zinc-900 dark:text-zinc-100">
                                    {user.name}
                                </h3>
                                <div className="mt-1 flex items-center gap-3">
                                    <StatusBadge activo={user.activo} />
                                    <div className="flex flex-wrap gap-1.5">
                                        {/* Mock roles for now */}
                                        <RoleBadge role={user.id === 1 ? 'ADMIN' : 'EMPLEADO'} />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 divide-y divide-zinc-100 dark:divide-zinc-800/50 sm:grid-cols-2 sm:divide-x sm:divide-y-0">
                        <div className="flex flex-col divide-y divide-zinc-100 dark:divide-zinc-800/50">
                            <InfoItem icon={CreditCard} label="RFC" value={user.rfc} />
                            <InfoItem icon={Mail} label="Correo Electrónico" value={user.email} />
                        </div>
                        <div className="flex flex-col divide-y divide-zinc-100 dark:divide-zinc-800/50 border-t sm:border-t-0 border-zinc-100 dark:border-zinc-800/50">
                            <InfoItem icon={User} label="NUE" value={user.nue} />
                            <InfoItem icon={Calendar} label="Fecha de Registro" value={formatDate(user.created_at)} />
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

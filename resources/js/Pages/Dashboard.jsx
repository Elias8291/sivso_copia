import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowRight, Building2, Calendar, ChevronRight, MapPin, User, Users, Users2, Package, Shirt, ListTree, DollarSign } from 'lucide-react';

const cardBase =
    'group relative flex flex-col rounded-2xl border border-zinc-200/80 bg-white/90 p-5 shadow-sm shadow-zinc-900/[0.03] transition-all duration-200 hover:border-zinc-300 hover:shadow-md hover:shadow-zinc-900/[0.06] dark:border-zinc-800/80 dark:bg-zinc-900/40 dark:hover:border-zinc-700 dark:hover:bg-zinc-900/60';

const iconWrap =
    'mb-4 flex size-11 items-center justify-center rounded-xl bg-zinc-100 text-zinc-600 transition-colors group-hover:bg-brand-gold/10 group-hover:text-brand-gold dark:bg-zinc-800/80 dark:text-zinc-400 dark:group-hover:bg-brand-gold/15 dark:group-hover:text-brand-gold-soft';

function QuickCard({ href, title, description, icon: Icon, routePattern }) {
    const active = Boolean(route().current(routePattern));
    return (
        <Link
            href={href}
            className={`${cardBase} ${active ? 'ring-1 ring-brand-gold/35 dark:ring-brand-gold/25' : ''}`}
        >
            <div className={iconWrap}>
                <Icon className="size-5" strokeWidth={2} aria-hidden />
            </div>
            <h3 className="text-[13px] font-bold tracking-tight text-zinc-900 dark:text-zinc-100">{title}</h3>
            <p className="mt-1 flex-1 text-[11px] leading-relaxed text-zinc-500 dark:text-zinc-400">{description}</p>
            <span className="mt-4 inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider text-brand-gold opacity-0 transition-opacity group-hover:opacity-100 dark:text-brand-gold-soft">
                Abrir
                <ArrowRight className="size-3" strokeWidth={2} aria-hidden />
            </span>
        </Link>
    );
}

const fmtMoney = (v) =>
    v != null
        ? Number(v).toLocaleString('es-MX', { style: 'currency', currency: 'MXN', maximumFractionDigits: 0 })
        : '$0';

const fmtNum = (v) => Number(v ?? 0).toLocaleString('es-MX');

function StatCard({ label, value, icon: Icon, color = 'text-zinc-600 dark:text-zinc-400' }) {
    return (
        <div className="flex items-center gap-3 rounded-xl border border-zinc-200/80 bg-white/90 p-4 dark:border-zinc-800/80 dark:bg-zinc-900/40">
            <div className={`flex size-10 shrink-0 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800 ${color}`}>
                <Icon className="size-5" strokeWidth={2} />
            </div>
            <div>
                <p className="text-lg font-bold tabular-nums text-zinc-900 dark:text-zinc-100">{value}</p>
                <p className="text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{label}</p>
            </div>
        </div>
    );
}

export default function Dashboard({ stats = {}, ejercicio = 2025 }) {
    const { auth } = usePage().props;
    const user = auth?.user;
    const isSuperAdmin = Boolean(auth?.is_super_admin);
    const isSivsoAdministrator = Boolean(auth?.is_sivso_administrator);
    const permissions = Array.isArray(auth?.permissions) ? auth.permissions : [];
    const can = (name) => isSuperAdmin || isSivsoAdministrator || permissions.includes(name);
    const firstName = (user?.name || '').split(/\s+/)[0] || 'Usuario';

    const dateLabel = new Date().toLocaleDateString('es-MX', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });

    const onDelegation = Boolean(route().current('my-delegation.*'));

    const quick = [
        {
            href: route('profile.edit'),
            routePattern: 'profile.edit',
            title: 'Mi cuenta',
            description: 'Nombre, correo y contraseña.',
            icon: User,
        },
        {
            permission: 'Ver empleados',
            href: route('empleados.index'),
            routePattern: 'empleados.*',
            title: 'Empleados',
            description: 'Directorio y gestión de personal.',
            icon: Users2,
        },
        {
            permission: 'Ver periodos',
            href: route('periodos.index'),
            routePattern: 'periodos.*',
            title: 'Periodos',
            description: 'Ciclos y estado operativo.',
            icon: Calendar,
        },
        {
            permission: 'Ver dependencias',
            href: route('dependencias.index'),
            routePattern: 'dependencias.*',
            title: 'Dependencias',
            description: 'Unidades y organigrama.',
            icon: Building2,
        },
        {
            permission: 'Ver usuarios',
            href: route('users.index'),
            routePattern: 'users.*',
            title: 'Usuarios',
            description: 'Accesos, roles y permisos.',
            icon: Users,
        },
    ].filter((item) => !item.permission || can(item.permission));

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                    <span>SIVSO</span>
                    <span className="text-zinc-300 dark:text-zinc-600">/</span>
                    <span className="text-zinc-900 dark:text-zinc-100">INICIO</span>
                </div>
            }
        >
            <Head title="Inicio" />

            <div className="mx-auto w-full max-w-[1200px]">
                <header className="mb-8 border-b border-zinc-200/80 pb-8 dark:border-zinc-800/80">
                    <div className="flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <p className="text-[10px] font-bold uppercase tracking-[0.2em] text-brand-gold dark:text-brand-gold-soft">
                                Panel principal
                            </p>
                            <h1 className="mt-2 text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100 sm:text-3xl">
                                Hola, {firstName}
                            </h1>
                            <p className="mt-2 max-w-xl text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">
                                Elige un módulo para continuar. Mi delegación concentra el vestuario de tu equipo.
                            </p>
                        </div>
                        <p
                            className="text-right text-[11px] font-medium capitalize text-zinc-400 dark:text-zinc-500"
                            suppressHydrationWarning
                        >
                            {dateLabel}
                        </p>
                    </div>
                </header>

                {can('Ver mi delegación') && (
                    <section className="mb-8" aria-labelledby="dash-featured">
                        <h2 id="dash-featured" className="sr-only">
                            Acceso destacado
                        </h2>
                        <Link
                            href={route('my-delegation.index')}
                            className={`group flex flex-col gap-4 rounded-2xl border border-brand-gold/35 bg-gradient-to-br from-brand-gold/[0.09] via-white/90 to-white/90 p-6 shadow-sm shadow-brand-gold/10 transition-all hover:border-brand-gold/55 hover:shadow-md dark:from-brand-gold/10 dark:via-zinc-900/50 dark:to-zinc-900/40 dark:shadow-none sm:flex-row sm:items-center sm:justify-between sm:p-7 ${
                                onDelegation ? 'ring-1 ring-brand-gold/40 dark:ring-brand-gold/30' : ''
                            }`}
                        >
                            <div className="flex min-w-0 items-start gap-4 sm:items-center">
                                <div className="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-brand-gold/15 text-brand-gold dark:bg-brand-gold/20 dark:text-brand-gold-soft">
                                    <MapPin className="size-7" strokeWidth={2} aria-hidden />
                                </div>
                                <div className="min-w-0">
                                    <h3 className="text-lg font-bold text-zinc-900 dark:text-zinc-100">Mi delegación</h3>
                                    <p className="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                        Actualiza tallas, revisa el estado del vestuario y gestiona bajas desde un solo lugar.
                                    </p>
                                </div>
                            </div>
                            <span className="inline-flex shrink-0 items-center justify-center gap-2 self-start rounded-xl border border-brand-gold/40 bg-white/60 px-4 py-2.5 text-[11px] font-bold uppercase tracking-wider text-brand-gold transition-colors group-hover:bg-brand-gold group-hover:text-white dark:border-brand-gold/35 dark:bg-zinc-950/40 dark:text-brand-gold-soft dark:group-hover:bg-brand-gold dark:group-hover:text-zinc-900 sm:self-center">
                                Ir ahora
                                <ChevronRight className="size-4 transition-transform group-hover:translate-x-0.5" strokeWidth={2} />
                            </span>
                        </Link>
                    </section>
                )}

                {stats && (
                    <section aria-labelledby="dash-stats" className="mb-8">
                        <h2 id="dash-stats" className="mb-4 text-xs font-bold uppercase tracking-[0.14em] text-zinc-400 dark:text-zinc-500">
                            Ejercicio {ejercicio}
                        </h2>
                        <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            <StatCard label="Empleados" value={fmtNum(stats.empleados)} icon={Users2} color="text-blue-600 dark:text-blue-400" />
                            <StatCard label="Productos activos" value={fmtNum(stats.productos)} icon={Package} color="text-violet-600 dark:text-violet-400" />
                            <StatCard label="Solicitudes" value={fmtNum(stats.solicitudes)} icon={Shirt} color="text-emerald-600 dark:text-emerald-400" />
                            <StatCard label="Importe total" value={fmtMoney(stats.importe_total)} icon={DollarSign} color="text-amber-600 dark:text-amber-400" />
                        </div>
                        <div className="mt-3 grid gap-3 sm:grid-cols-3">
                            <StatCard label="Delegaciones" value={fmtNum(stats.delegaciones)} icon={MapPin} />
                            <StatCard label="Dependencias" value={fmtNum(stats.dependencias)} icon={Building2} />
                            <StatCard label="Delegados" value={fmtNum(stats.delegados)} icon={Users} />
                        </div>
                    </section>
                )}

                <section aria-labelledby="dash-quick">
                    <div className="mb-4 flex items-center justify-between gap-2">
                        <h2 id="dash-quick" className="text-xs font-bold uppercase tracking-[0.14em] text-zinc-400 dark:text-zinc-500">
                            Accesos rápidos
                        </h2>
                    </div>
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                        {quick.map((item) => (
                            <QuickCard key={item.routePattern} {...item} />
                        ))}
                    </div>
                </section>

                <p className="mt-10 text-center text-[11px] text-zinc-400 dark:text-zinc-600">
                    Sistema integral de vestuario · SIVSO
                </p>
            </div>
        </AuthenticatedLayout>
    );
}

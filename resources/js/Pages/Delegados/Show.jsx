import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Shirt, Users2 } from 'lucide-react';

const theadRow =
    'border-0 border-b-2 border-b-brand-gold/40 bg-zinc-50/80 dark:border-b-brand-gold/35 dark:bg-zinc-900/55';

export default function Show({ delegado, delegaciones = [], empleados = [], ejercicio }) {
    const empHref = (id) => {
        const u = route('empleados.show', id);
        return `${u}?delegado=${delegado.id}&anio=${ejercicio}`;
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                    <span>SIVSO</span>
                    <span className="text-zinc-300 dark:text-zinc-600">/</span>
                    <Link href={route('delegados.index')} className="hover:text-zinc-800 dark:hover:text-zinc-200">
                        Delegados
                    </Link>
                    <span className="text-zinc-300 dark:text-zinc-600">/</span>
                    <span className="font-medium text-zinc-900 dark:text-zinc-100">Detalle</span>
                </div>
            }
        >
            <Head title={`Delegado — ${delegado.nombre_completo}`} />

            <div className="mx-auto w-full max-w-[1600px] space-y-8">
                <div>
                    <Link
                        href={route('delegados.index')}
                        className="mb-3 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-zinc-500 transition-colors hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200"
                    >
                        <ArrowLeft className="size-4" strokeWidth={2} />
                        Volver a delegados
                    </Link>
                    <h2 className="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                        {delegado.nombre_completo}
                    </h2>
                    {delegado.nue ? (
                        <p className="mt-1 text-sm text-zinc-500 dark:text-zinc-400">NUE {delegado.nue}</p>
                    ) : null}
                    <div className="mt-4 flex flex-wrap gap-2">
                        {delegaciones.length === 0 && (
                            <span className="text-sm text-zinc-500 dark:text-zinc-400">Sin delegaciones asignadas.</span>
                        )}
                        {delegaciones.map((d) => (
                            <span
                                key={d.id}
                                className="inline-flex items-center rounded-md border border-brand-gold/25 bg-brand-gold/5 px-3 py-1 font-mono text-xs font-semibold text-zinc-800 dark:text-zinc-100"
                            >
                                {d.clave ?? d.codigo ?? d.id}
                            </span>
                        ))}
                    </div>
                </div>

                <div className="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-[#0A0A0B]">
                    <div className="border-b border-zinc-200 px-4 py-4 sm:px-6 dark:border-zinc-800">
                        <h3 className="flex items-center gap-2 text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                            <Users2 className="size-4 text-brand-gold" strokeWidth={1.75} />
                            Empleados en sus delegaciones
                            <span className="text-xs font-normal text-zinc-500 dark:text-zinc-400">
                                ({empleados.length}) · productos en {ejercicio}
                            </span>
                        </h3>
                    </div>
                    <div className="hidden overflow-x-auto md:block">
                        <table className="min-w-[900px] w-full text-left text-sm">
                            <thead>
                                <tr className={theadRow}>
                                    <th className="px-6 py-3 text-xs font-semibold text-zinc-600 dark:text-zinc-400">Empleado</th>
                                    <th className="px-6 py-3 text-xs font-semibold text-zinc-600 dark:text-zinc-400">NUE</th>
                                    <th className="px-6 py-3 text-xs font-semibold text-zinc-600 dark:text-zinc-400">Dependencia</th>
                                    <th className="px-6 py-3 text-xs font-semibold text-zinc-600 dark:text-zinc-400">Delegación</th>
                                    <th className="px-6 py-3 text-xs font-semibold text-zinc-600 dark:text-zinc-400">Productos</th>
                                    <th className="px-6 py-3 text-right text-xs font-semibold text-zinc-600 dark:text-zinc-400">Vestuario</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-zinc-100 dark:divide-zinc-800/50">
                                {empleados.length === 0 && (
                                    <tr>
                                        <td colSpan={6} className="px-6 py-12 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                            No hay empleados en las delegaciones de este delegado.
                                        </td>
                                    </tr>
                                )}
                                {empleados.map((e) => (
                                    <tr key={e.id} className="transition-colors hover:bg-zinc-50/80 dark:hover:bg-zinc-900/80">
                                        <td className="px-6 py-4 font-medium text-zinc-900 dark:text-zinc-100">{e.nombre_completo}</td>
                                        <td className="px-6 py-4 text-zinc-600 dark:text-zinc-300">{e.nue || '—'}</td>
                                        <td className="px-6 py-4 text-zinc-600 dark:text-zinc-300">{e.dependencia_nombre || '—'}</td>
                                        <td className="px-6 py-4 font-mono text-xs text-zinc-600 dark:text-zinc-300">
                                            {e.delegacion_clave || '—'}
                                        </td>
                                        <td className="px-6 py-4 font-medium tabular-nums text-zinc-800 dark:text-zinc-200">
                                            {e.productos_count ?? 0}
                                        </td>
                                        <td className="px-6 py-4 text-right">
                                            <Link
                                                href={empHref(e.id)}
                                                className="inline-flex items-center gap-1.5 rounded-md border border-brand-gold/30 bg-brand-gold/5 px-2 py-1.5 text-xs font-medium text-brand-gold transition-colors hover:bg-brand-gold/10 dark:text-brand-gold-soft"
                                            >
                                                <Shirt className="size-3.5" strokeWidth={2} />
                                                Ver vestuario
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    <div className="flex flex-col divide-y divide-zinc-100 md:hidden dark:divide-zinc-800/50">
                        {empleados.length === 0 && (
                            <div className="px-4 py-12 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                No hay empleados en las delegaciones de este delegado.
                            </div>
                        )}
                        {empleados.map((e) => (
                            <div key={e.id} className="space-y-2 p-4">
                                <div className="font-medium text-zinc-900 dark:text-zinc-100">{e.nombre_completo}</div>
                                <div className="text-xs text-zinc-500">NUE: {e.nue || '—'}</div>
                                <div className="text-xs text-zinc-600 dark:text-zinc-300">{e.dependencia_nombre || '—'}</div>
                                <div className="font-mono text-xs text-zinc-500">Del. {e.delegacion_clave || '—'}</div>
                                <div className="text-xs font-medium text-zinc-700 dark:text-zinc-200">
                                    Productos {ejercicio}: {e.productos_count ?? 0}
                                </div>
                                <Link
                                    href={empHref(e.id)}
                                    className="inline-flex items-center gap-1.5 text-xs font-medium text-brand-gold dark:text-brand-gold-soft"
                                >
                                    <Shirt className="size-3.5" strokeWidth={2} />
                                    Ver vestuario
                                </Link>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

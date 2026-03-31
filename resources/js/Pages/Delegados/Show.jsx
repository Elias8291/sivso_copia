import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Shirt, Users2 } from 'lucide-react';

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
                        DELEGADOS
                    </Link>
                    <span className="text-zinc-300 dark:text-zinc-600">/</span>
                    <span className="text-zinc-900 dark:text-zinc-100">DETALLE</span>
                </div>
            }
        >
            <Head title={`Delegado — ${delegado.nombre_completo}`} />

            <div className="mx-auto w-full max-w-[1600px] space-y-8">
                <div>
                    <Link
                        href={route('delegados.index')}
                        className="mb-3 inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-zinc-500 transition-colors hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200"
                    >
                        <ArrowLeft className="size-4" strokeWidth={2} />
                        Volver a delegados
                    </Link>
                    <h2 className="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                        {delegado.nombre_completo}
                    </h2>
                    <div className="mt-3 flex flex-wrap gap-2">
                        {delegaciones.length === 0 && (
                            <span className="text-sm text-zinc-500">Sin delegaciones asignadas.</span>
                        )}
                        {delegaciones.map((d) => (
                            <span
                                key={d.id}
                                className="inline-flex items-center rounded-full bg-zinc-100 px-3 py-1 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                            >
                                {d.clave || d.nombre || d.id}
                            </span>
                        ))}
                    </div>
                </div>

                <div className="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-[#0A0A0B]">
                    <div className="border-b border-zinc-200 px-4 py-4 sm:px-6 dark:border-zinc-800">
                        <h3 className="flex items-center gap-2 text-sm font-bold uppercase tracking-wider text-zinc-800 dark:text-zinc-200">
                            <Users2 className="size-4" strokeWidth={2} />
                            Empleados ({empleados.length}) — productos en {ejercicio}
                        </h3>
                    </div>
                    <div className="hidden overflow-x-auto md:block">
                        <table className="min-w-[900px] w-full text-left">
                            <thead>
                                <tr className="border-b border-zinc-200 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-900/50">
                                    <th className="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-zinc-500">
                                        Empleado
                                    </th>
                                    <th className="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-zinc-500">
                                        NUE
                                    </th>
                                    <th className="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-zinc-500">
                                        Dependencia
                                    </th>
                                    <th className="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-zinc-500">
                                        Del.
                                    </th>
                                    <th className="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-zinc-500">
                                        Productos
                                    </th>
                                    <th className="px-6 py-3 text-right text-[10px] font-bold uppercase tracking-wider text-zinc-500">
                                        Vestuario
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-zinc-100 dark:divide-zinc-800/50">
                                {empleados.length === 0 && (
                                    <tr>
                                        <td colSpan={6} className="px-6 py-12 text-center text-sm text-zinc-500">
                                            No hay empleados en estas delegaciones.
                                        </td>
                                    </tr>
                                )}
                                {empleados.map((e) => (
                                    <tr
                                        key={e.id}
                                        className="group transition-colors hover:bg-zinc-50/80 dark:hover:bg-zinc-900/80"
                                    >
                                        <td className="px-6 py-4 text-xs font-bold uppercase tracking-wide text-zinc-900 dark:text-zinc-100">
                                            {e.nombre_completo}
                                        </td>
                                        <td className="px-6 py-4 text-xs text-zinc-600 dark:text-zinc-300">
                                            {e.nue || '—'}
                                        </td>
                                        <td className="px-6 py-4 text-xs text-zinc-500 dark:text-zinc-400">
                                            {e.dependencia_nombre || '—'}
                                        </td>
                                        <td className="px-6 py-4 text-xs text-zinc-500 dark:text-zinc-400">
                                            {e.delegacion_clave || '—'}
                                        </td>
                                        <td className="px-6 py-4 text-xs font-semibold text-zinc-700 dark:text-zinc-200">
                                            {e.productos_count ?? 0}
                                        </td>
                                        <td className="px-6 py-4 text-right">
                                            <Link
                                                href={empHref(e.id)}
                                                className="inline-flex items-center gap-1.5 rounded-md px-2 py-1.5 text-[10px] font-bold uppercase tracking-wider text-emerald-600 transition-colors hover:bg-emerald-50 dark:text-emerald-500 dark:hover:bg-emerald-500/10"
                                            >
                                                <Shirt className="size-3.5" strokeWidth={2} />
                                                Ver
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    <div className="flex flex-col divide-y divide-zinc-100 md:hidden dark:divide-zinc-800/50">
                        {empleados.length === 0 && (
                            <div className="px-4 py-12 text-center text-sm text-zinc-500">
                                No hay empleados en estas delegaciones.
                            </div>
                        )}
                        {empleados.map((e) => (
                            <div key={e.id} className="space-y-2 p-4">
                                <div className="text-xs font-bold uppercase tracking-wide text-zinc-900 dark:text-zinc-100">
                                    {e.nombre_completo}
                                </div>
                                <div className="text-[10px] text-zinc-500">NUE: {e.nue || '—'}</div>
                                <div className="text-xs text-zinc-500">{e.dependencia_nombre || '—'}</div>
                                <div className="text-xs font-semibold text-zinc-700 dark:text-zinc-200">
                                    Productos {ejercicio}: {e.productos_count ?? 0}
                                </div>
                                <Link
                                    href={empHref(e.id)}
                                    className="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-500"
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
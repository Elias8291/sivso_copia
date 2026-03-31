import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Shirt } from 'lucide-react';

const money = (n) =>
    new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(Number(n ?? 0));

const estadoLabel = {
    borrador: 'Borrador',
    pre_muestreo: 'Pre-muestreo',
    en_muestreo: 'En muestreo',
    aprobado: 'Aprobado',
};

const estadoColor = {
    borrador: 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400',
    pre_muestreo: 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400',
    en_muestreo: 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400',
    aprobado: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400',
};

export default function Show({
    empleado,
    lineas,
    ejercicio,
    anioFiltro,
    soloEjercicio,
    anios = [],
    delegadoId,
}) {
    const withDelegado = (p) => {
        if (delegadoId != null && delegadoId !== '') {
            return { ...p, delegado: delegadoId };
        }
        return p;
    };

    const applyFilters = (patch) => {
        router.get(route('empleados.show', empleado.id), withDelegado(patch), {
            preserveState: true,
            replace: true,
        });
    };

    const backHref =
        delegadoId != null && delegadoId !== ''
            ? route('delegados.show', delegadoId)
            : route('empleados.index');

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                    <span>SIVSO</span>
                    <span className="text-zinc-300 dark:text-zinc-600">/</span>
                    <Link
                        href={
                            soloEjercicio
                                ? `${route('empleados.index')}?anio=${anioFiltro}`
                                : route('empleados.index')
                        }
                        className="hover:text-zinc-800 dark:hover:text-zinc-200"
                    >
                        EMPLEADOS
                    </Link>
                    <span className="text-zinc-300 dark:text-zinc-600">/</span>
                    <span className="text-zinc-900 dark:text-zinc-100">VESTUARIO</span>
                </div>
            }
        >
            <Head title={`Vestuario — ${empleado.nombre_completo}`} />

            <div className="mx-auto w-full max-w-[1600px] space-y-8">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <Link
                            href={backHref}
                            className="mb-3 inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-zinc-500 transition-colors hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200"
                        >
                            <ArrowLeft className="size-4" strokeWidth={2} />
                            Volver
                        </Link>
                        <h2 className="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                            {empleado.nombre_completo}
                        </h2>
                        <p className="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            NUE {empleado.nue || '—'}
                            {empleado.ur ? ` · UR ${empleado.ur}` : ''}
                            {empleado.dependencia_nombre ? ` · ${empleado.dependencia_nombre}` : ''}
                            {empleado.delegacion_clave ? ` · Del. ${empleado.delegacion_clave}` : ''}
                        </p>
                    </div>
                </div>

                <div className="flex flex-wrap items-end gap-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-[#0A0A0B]">
                    <label className="flex cursor-pointer items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                        <input
                            type="checkbox"
                            checked={!soloEjercicio}
                            onChange={(e) =>
                                e.target.checked
                                    ? applyFilters({ todos: 1 })
                                    : applyFilters({ anio: ejercicio })
                            }
                            className="rounded border-zinc-300 text-zinc-900 focus:ring-brand-gold dark:border-zinc-600"
                        />
                        Ver todos los ejercicios
                    </label>
                    {soloEjercicio && (
                        <div>
                            <label className="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                Año
                            </label>
                            <select
                                value={String(anioFiltro)}
                                onChange={(e) => applyFilters({ anio: e.target.value })}
                                className="rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100"
                            >
                                {(anios.length ? anios : [ejercicio]).map((a) => (
                                    <option key={a} value={a}>
                                        {a}
                                    </option>
                                ))}
                            </select>
                        </div>
                    )}
                </div>

                <div className="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-[#0A0A0B]">
                    <div className="border-b border-zinc-200 px-4 py-4 sm:px-6 dark:border-zinc-800">
                        <h3 className="flex items-center gap-2 text-sm font-bold uppercase tracking-wider text-zinc-800 dark:text-zinc-200">
                            <Shirt className="size-4" strokeWidth={2} />
                            Líneas de vestuario ({lineas.length})
                        </h3>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="min-w-[1100px] w-full text-left text-xs">
                            <thead>
                                <tr className="border-b border-zinc-200 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-900/50">
                                    <th className="px-4 py-3 font-bold uppercase tracking-wider text-zinc-500">Año</th>
                                    <th className="px-4 py-3 font-bold uppercase tracking-wider text-zinc-500">Partida</th>
                                    <th className="px-4 py-3 font-bold uppercase tracking-wider text-zinc-500">Producto</th>
                                    <th className="px-4 py-3 font-bold uppercase tracking-wider text-zinc-500">Talla</th>
                                    <th className="px-4 py-3 font-bold uppercase tracking-wider text-zinc-500">Cant.</th>
                                    <th className="px-4 py-3 font-bold uppercase tracking-wider text-zinc-500">Total</th>
                                    <th className="px-4 py-3 font-bold uppercase tracking-wider text-zinc-500">Estado</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-zinc-100 dark:divide-zinc-800/50">
                                {lineas.length === 0 && (
                                    <tr>
                                        <td colSpan={7} className="px-4 py-12 text-center text-sm text-zinc-500">
                                            Sin registros para este filtro.
                                        </td>
                                    </tr>
                                )}
                                {lineas.map((row) => (
                                    <tr key={row.solicitud_id} className="hover:bg-zinc-50/80 dark:hover:bg-zinc-900/80">
                                        <td className="px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200">
                                            {row.anio}
                                        </td>
                                        <td className="px-4 py-3">
                                            <span className="inline-flex items-center justify-center rounded bg-zinc-100 px-2 py-0.5 text-[11px] font-bold tabular-nums text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                                {row.no_partida ?? '—'}
                                            </span>
                                            {row.partida_especifica_codigo != null && (
                                                <span className="mt-0.5 block text-[10px] text-zinc-400 dark:text-zinc-500">
                                                    Esp. {row.partida_especifica_codigo}
                                                </span>
                                            )}
                                        </td>
                                        <td className="max-w-md px-4 py-3 text-zinc-600 dark:text-zinc-300">
                                            <div className="font-medium text-zinc-900 dark:text-zinc-100">
                                                {row.producto_descripcion || '—'}
                                            </div>
                                            {(row.producto_codigo || row.clave_para_ejercicio_snapshot) && (
                                                <div className="mt-0.5 text-[10px] text-zinc-500">
                                                    {[row.producto_codigo, row.clave_para_ejercicio_snapshot].filter(Boolean).join(' · ')}
                                                </div>
                                            )}
                                        </td>
                                        <td className="px-4 py-3 text-zinc-600 dark:text-zinc-300">{row.talla || '—'}</td>
                                        <td className="px-4 py-3 text-zinc-600 dark:text-zinc-300">{row.cantidad ?? '—'}</td>
                                        <td className="px-4 py-3 font-semibold text-zinc-800 dark:text-zinc-200">
                                            {money(row.importe_total)}
                                        </td>
                                        <td className="px-4 py-3">
                                            <span className={`inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider ${estadoColor[row.estado] || estadoColor.borrador}`}>
                                                {estadoLabel[row.estado] || row.estado || '—'}
                                            </span>
                                            {row.es_sustitucion ? (
                                                <span className="ml-1.5 text-[10px] font-medium text-amber-600 dark:text-amber-400">Sust.</span>
                                            ) : null}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
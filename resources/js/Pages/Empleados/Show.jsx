import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, FlaskConical, PackageCheck } from 'lucide-react';

const money = (n) =>
    new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(Number(n ?? 0));

const estadoLabel = {
    borrador: 'Borrador',
    pre_muestreo: 'Pre-muestreo',
    en_muestreo: 'En muestreo',
    aprobado: 'Para entrega',
    licitado: 'Muestra',
};

const estadoColor = {
    borrador: 'border border-zinc-200 bg-zinc-50 text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-zinc-400',
    pre_muestreo: 'border border-amber-200/80 bg-amber-50/90 text-amber-900 dark:border-amber-800/50 dark:bg-amber-950/25 dark:text-amber-200',
    en_muestreo: 'border border-zinc-200 bg-zinc-50 text-zinc-700 dark:border-zinc-600 dark:bg-zinc-900/50 dark:text-zinc-300',
    aprobado:
        'border border-brand-gold/30 bg-brand-gold/10 text-zinc-900 dark:border-brand-gold/35 dark:bg-brand-gold/10 dark:text-brand-gold-soft',
    licitado: 'border border-zinc-200 bg-white text-zinc-700 dark:border-zinc-600 dark:bg-zinc-800/40 dark:text-zinc-200',
};

function sumPiezas(items) {
    if (!Array.isArray(items)) {
        return 0;
    }
    return items.reduce((s, r) => s + (Number(r.piezas) || 0), 0);
}

function TablaTiposPrenda({ items, totalPiezas, vacio }) {
    if (!Array.isArray(items) || items.length === 0) {
        return <p className="text-sm text-zinc-500 dark:text-zinc-400">{vacio}</p>;
    }
    return (
        <div className="overflow-hidden rounded-md border border-zinc-200 dark:border-zinc-700/80">
            <table className="w-full text-left text-sm">
                <thead>
                    <tr className="border-b border-zinc-200 bg-zinc-50/90 dark:border-zinc-700 dark:bg-zinc-900/60">
                        <th className="px-3 py-2 font-medium text-zinc-600 dark:text-zinc-400">Tipo de prenda</th>
                        <th className="px-3 py-2 text-right font-medium text-zinc-600 dark:text-zinc-400">Piezas</th>
                    </tr>
                </thead>
                <tbody className="divide-y divide-zinc-100 bg-white dark:divide-zinc-800 dark:bg-zinc-950/20">
                    {items.map((row) => (
                        <tr key={row.nombre} className="hover:bg-zinc-50/60 dark:hover:bg-zinc-900/40">
                            <td className="px-3 py-2 text-zinc-800 dark:text-zinc-200">{row.nombre}</td>
                            <td className="px-3 py-2 text-right tabular-nums font-medium text-zinc-900 dark:text-zinc-100">
                                {row.piezas}
                            </td>
                        </tr>
                    ))}
                </tbody>
                <tfoot>
                    <tr className="border-t border-zinc-200 bg-zinc-50/80 dark:border-zinc-700 dark:bg-zinc-900/50">
                        <td className="px-3 py-2 text-xs font-medium text-zinc-600 dark:text-zinc-400">Total</td>
                        <td className="px-3 py-2 text-right text-xs font-semibold tabular-nums text-zinc-900 dark:text-zinc-100">
                            {totalPiezas}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    );
}

/**
 * Resumen fijo, sobrio: dos cuadros paralelos con tabla simple (sin gráficos ni desplegables).
 */
function ResumenTiposPrendaContexto({ entrega, muestra }) {
    const te = sumPiezas(entrega);
    const tm = sumPiezas(muestra);
    if (te === 0 && tm === 0) {
        return null;
    }

    return (
        <section
            className="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-[#0A0A0B]"
            aria-labelledby="resumen-tipos-prenda-heading"
        >
            <div className="border-l-4 border-brand-gold px-5 py-4 sm:px-6">
                <h2
                    id="resumen-tipos-prenda-heading"
                    className="text-base font-semibold tracking-tight text-zinc-900 dark:text-zinc-100"
                >
                    Cuántas piezas hay por tipo de prenda
                </h2>
                <p className="mt-1.5 max-w-3xl text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">
                    Cada fila es una categoría del catálogo (por ejemplo camisa o pantalón). Los números deben coincidir con la columna «Tipo de prenda» en las tablas de abajo. La
                    primera columna usa la <span className="font-medium text-zinc-800 dark:text-zinc-200">cotización aplicada</span>; la segunda, el{' '}
                    <span className="font-medium text-zinc-800 dark:text-zinc-200">texto de licitación</span>.
                </p>
            </div>
            <div className="grid gap-0 border-t border-zinc-200 dark:border-zinc-800 md:grid-cols-2">
                <div className="border-b border-zinc-200 p-5 sm:p-6 md:border-b-0 md:border-r dark:border-zinc-800">
                    <h3 className="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Cotización — para entrega</h3>
                    <p className="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        Total: <span className="font-semibold tabular-nums text-zinc-800 dark:text-zinc-200">{te}</span> pieza{te !== 1 ? 's' : ''}
                    </p>
                    <div className="mt-4">
                        <TablaTiposPrenda items={entrega} totalPiezas={te} vacio="No hay prendas clasificadas en esta vista." />
                    </div>
                </div>
                <div className="p-5 sm:p-6">
                    <h3 className="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Licitación — vista muestra</h3>
                    <p className="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        Total: <span className="font-semibold tabular-nums text-zinc-800 dark:text-zinc-200">{tm}</span> pieza{tm !== 1 ? 's' : ''}
                    </p>
                    <div className="mt-4">
                        <TablaTiposPrenda items={muestra} totalPiezas={tm} vacio="No hay prendas clasificadas en esta vista." />
                    </div>
                </div>
            </div>
        </section>
    );
}

function VestuarioTable({ lineas, emptyMessage }) {
    return (
        <div className="overflow-x-auto">
            <table className="min-w-[1220px] w-full text-left text-xs">
                <thead>
                    <tr className="border-0 border-b-2 border-b-brand-gold/40 bg-zinc-50/80 dark:border-b-brand-gold/35 dark:bg-zinc-900/55">
                        <th className="px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-400">Año</th>
                        <th className="px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-400">Partida</th>
                        <th className="min-w-[8.5rem] px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-400">
                            Tipo de prenda
                        </th>
                        <th className="px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-400">Producto</th>
                        <th className="px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-400">Talla</th>
                        <th className="px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-400">Cant.</th>
                        <th className="px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-400">Total</th>
                        <th className="px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-400">Tipo</th>
                    </tr>
                </thead>
                <tbody className="divide-y divide-zinc-100 dark:divide-zinc-800/50">
                    {lineas.length === 0 && (
                        <tr>
                            <td colSpan={8} className="px-4 py-12 text-center text-sm text-zinc-500">
                                {emptyMessage}
                            </td>
                        </tr>
                    )}
                    {lineas.map((row) => {
                        const cat = row.clasificacion ?? 'Sin clasificar';
                        return (
                        <tr
                            key={row.solicitud_id}
                            className="transition-colors hover:bg-zinc-50/80 dark:hover:bg-zinc-900/80"
                        >
                            <td className="px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200">{row.anio}</td>
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
                            <td className="max-w-[11rem] px-4 py-3 align-top text-sm text-zinc-700 dark:text-zinc-300">
                                {cat}
                            </td>
                            <td className="max-w-md px-4 py-3 text-zinc-600 dark:text-zinc-300">
                                <div className="font-medium text-zinc-900 dark:text-zinc-100">{row.producto_descripcion || '—'}</div>
                                {row.producto_codigo ? (
                                    <div className="mt-0.5 text-[10px] text-zinc-500">Clave: {row.producto_codigo}</div>
                                ) : null}
                            </td>
                            <td className="px-4 py-3 text-zinc-600 dark:text-zinc-300">{row.talla || '—'}</td>
                            <td className="px-4 py-3 text-zinc-600 dark:text-zinc-300">{row.cantidad ?? '—'}</td>
                            <td className="px-4 py-3 font-semibold text-zinc-800 dark:text-zinc-200">{money(row.importe_total)}</td>
                            <td className="px-4 py-3">
                                <span
                                    className={`inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider ${estadoColor[row.estado] || estadoColor.borrador}`}
                                >
                                    {estadoLabel[row.estado] || row.estado || '—'}
                                </span>
                                {row.es_sustitucion ? (
                                    <span className="ml-1.5 text-[10px] font-medium text-zinc-500 dark:text-zinc-400">Sust.</span>
                                ) : null}
                            </td>
                        </tr>
                        );
                    })}
                </tbody>
            </table>
        </div>
    );
}

export default function Show({
    empleado,
    lineas_cotizado = [],
    lineas_licitacion = [],
    resumen_entrega_clasificacion = [],
    resumen_muestra_clasificacion = [],
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

    const totalAsignaciones = lineas_licitacion.length;

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
                    <span className="font-medium text-zinc-900 dark:text-zinc-100">Vestuario</span>
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

                <div className="flex flex-wrap items-end gap-4 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-[#0A0A0B]">
                    <label className="flex cursor-pointer items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                        <input
                            type="checkbox"
                            checked={!soloEjercicio}
                            onChange={(e) =>
                                e.target.checked
                                    ? applyFilters({ todos: 1, anio: anioFiltro })
                                    : applyFilters({ anio: anioFiltro })
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

                <p className="text-sm text-zinc-600 dark:text-zinc-400">
                    {soloEjercicio ? (
                        <>
                            Vestuario del ejercicio <span className="font-semibold text-zinc-900 dark:text-zinc-100">{anioFiltro}</span>. La primera lista son las prendas{' '}
                            <strong>que quedaron para entrega</strong> (precio y descripción según lo cotizado). La segunda muestra <strong>las mismas líneas</strong> con el
                            producto tal como figuró en la <strong>licitación</strong>, para ver lo que correspondía a muestra o al catálogo previo.
                        </>
                    ) : (
                        <>Vestuario de todos los ejercicios registrados para esta persona.</>
                    )}{' '}
                    <span className="text-zinc-500 dark:text-zinc-500">
                        ({lineas_cotizado.length} para entrega · {totalAsignaciones} líneas en total en la vista de muestra)
                    </span>
                </p>

                <ResumenTiposPrendaContexto entrega={resumen_entrega_clasificacion} muestra={resumen_muestra_clasificacion} />

                <div className="space-y-10">
                    <div className="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-[#0A0A0B]">
                        <div className="border-b border-zinc-200 px-4 py-4 sm:px-6 dark:border-zinc-800">
                            <h3 className="flex items-center gap-2 text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                <PackageCheck className="size-4 text-brand-gold" strokeWidth={1.75} />
                                Prendas para entrega
                                <span className="ml-1 text-xs font-normal normal-case text-zinc-500">({lineas_cotizado.length})</span>
                            </h3>
                            <p className="mt-2 max-w-3xl text-xs leading-relaxed text-zinc-500 dark:text-zinc-400">
                                Lo que sí quedó amarrado a surtido: descripción, clave de producto e importe según la cotización aplicada. En «Tipo» verás «Para entrega».
                            </p>
                        </div>
                        <VestuarioTable
                            lineas={lineas_cotizado}
                            emptyMessage={
                                soloEjercicio
                                    ? `No hay prendas registradas para entrega en ${anioFiltro}.`
                                    : 'No hay prendas registradas para entrega en el periodo consultado.'
                            }
                        />
                    </div>

                    <div className="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-[#0A0A0B]">
                        <div className="border-b border-zinc-200 px-4 py-4 sm:px-6 dark:border-zinc-800">
                            <h3 className="flex items-center gap-2 text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                <FlaskConical className="size-4 text-brand-gold" strokeWidth={1.75} />
                                Vista muestra (licitación)
                                <span className="ml-1 text-xs font-normal normal-case text-zinc-500">({lineas_licitacion.length})</span>
                            </h3>
                            <p className="mt-2 max-w-3xl text-xs leading-relaxed text-zinc-500 dark:text-zinc-400">
                                Mismas asignaciones del ejercicio, pero mostrando el producto tal como estaba en la licitación (precio y texto de catálogo de esa etapa). Sirve
                                para distinguir lo que fue muestra o referencia de licitación frente a lo cotizado arriba. En «Tipo» verás «Muestra».
                            </p>
                        </div>
                        <VestuarioTable
                            lineas={lineas_licitacion}
                            emptyMessage={
                                soloEjercicio
                                    ? `Sin asignaciones de vestuario para ${anioFiltro}.`
                                    : 'Sin asignaciones en el periodo consultado.'
                            }
                        />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

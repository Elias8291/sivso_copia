import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Users, MapPin } from 'lucide-react';
import { useState, useEffect, useRef } from 'react';
import SearchInput from '@/Components/SearchInput';
import FilterBar from '@/Components/FilterBar';
import FilterSelect from '@/Components/FilterSelect';

function Pagination({ paginator }) {
    if (!paginator?.links?.length) {
        return null;
    }

    const from = paginator.from ?? 0;
    const to = paginator.to ?? 0;
    const total = paginator.total ?? 0;

    return (
        <div className="flex flex-col gap-3 border-t border-zinc-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800">
            <p className="text-xs text-zinc-500 dark:text-zinc-400">
                {total === 0 ? 'Sin resultados' : `Mostrando ${from}–${to} de ${total}`}
            </p>
            <div className="flex flex-wrap items-center gap-1">
                {paginator.links.map((link, i) => {
                    const label = String(link.label).replace('&laquo;', '«').replace('&raquo;', '»');
                    if (!link.url) {
                        return (
                            <span
                                key={i}
                                className="inline-flex min-w-8 items-center justify-center rounded-md px-2 py-1 text-xs font-medium text-zinc-400 dark:text-zinc-600"
                            >
                                {label}
                            </span>
                        );
                    }
                    return (
                        <Link
                            key={i}
                            href={link.url}
                            preserveScroll
                            className={`inline-flex min-w-8 items-center justify-center rounded-md px-2 py-1 text-xs font-medium transition-colors ${
                                link.active
                                    ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900'
                                    : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800'
                            }`}
                        >
                            {label}
                        </Link>
                    );
                })}
            </div>
        </div>
    );
}

const theadRow =
    'border-0 border-b-2 border-b-brand-gold/40 bg-zinc-50/80 dark:border-b-brand-gold/35 dark:bg-zinc-900/55';

export default function Index({ dependencias, filters = {} }) {
    const [buscar, setBuscar] = useState(filters.buscar ?? '');
    const [empleadosFilter, setEmpleadosFilter] = useState(filters.empleados ?? 'Todos');
    const skipFirst = useRef(true);

    useEffect(() => {
        if (skipFirst.current) {
            skipFirst.current = false;
            return;
        }
        const id = setTimeout(() => {
            router.get(
                route('dependencias.index'),
                { buscar, empleados: empleadosFilter },
                { preserveState: true, replace: true }
            );
        }, 350);
        return () => clearTimeout(id);
    }, [buscar, empleadosFilter]);

    const rows = Array.isArray(dependencias?.data) ? dependencias.data : [];

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                    <span>SIVSO</span>
                    <span className="text-zinc-300 dark:text-zinc-600">/</span>
                    <span className="font-medium text-zinc-900 dark:text-zinc-100">Dependencias</span>
                </div>
            }
        >
            <Head title="Dependencias" />

            <div className="mx-auto w-full max-w-[1600px] space-y-8">
                <div>
                    <h2 className="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">Dependencias</h2>
                    <p className="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                        Unidades responsables (UR) y su relación con delegaciones y personal.
                    </p>
                </div>

                <FilterBar>
                    <div className="w-full sm:max-w-md">
                        <SearchInput
                            value={buscar}
                            onChange={setBuscar}
                            placeholder="Nombre, nombre corto o número de UR…"
                        />
                    </div>
                    <div className="w-full sm:w-48">
                        <FilterSelect
                            label="Empleados"
                            value={empleadosFilter}
                            onChange={setEmpleadosFilter}
                            options={[
                                { value: 'Todos', label: 'Todos' },
                                { value: 'ConEmpleados', label: 'Con empleados' },
                                { value: 'SinEmpleados', label: 'Sin empleados' },
                            ]}
                        />
                    </div>
                </FilterBar>

                <div className="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-[#0A0A0B]">
                    <div className="border-b border-zinc-200 px-4 py-4 sm:px-6 dark:border-zinc-800">
                        <h3 className="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                            Resultados
                            <span className="ml-2 text-xs font-normal text-zinc-500 dark:text-zinc-400">
                                ({dependencias?.total ?? rows.length} registros)
                            </span>
                        </h3>
                    </div>

                    <div className="hidden overflow-x-auto md:block">
                        <table className="min-w-[800px] w-full text-left text-sm">
                            <thead>
                                <tr className={theadRow}>
                                    <th className="px-6 py-3 text-xs font-semibold text-zinc-600 dark:text-zinc-400">Dependencia</th>
                                    <th className="px-6 py-3 text-xs font-semibold text-zinc-600 dark:text-zinc-400">UR</th>
                                    <th className="px-6 py-3 text-xs font-semibold text-zinc-600 dark:text-zinc-400">Delegaciones</th>
                                    <th className="px-6 py-3 text-xs font-semibold text-zinc-600 dark:text-zinc-400">Empleados</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-zinc-100 dark:divide-zinc-800/50">
                                {rows.length === 0 && (
                                    <tr>
                                        <td colSpan={4} className="px-6 py-12 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                            No se encontraron dependencias.
                                        </td>
                                    </tr>
                                )}
                                {rows.map((item) => (
                                    <tr key={item.id} className="transition-colors hover:bg-zinc-50/80 dark:hover:bg-zinc-900/80">
                                        <td className="px-6 py-4">
                                            <div className="font-medium text-zinc-900 dark:text-zinc-100">{item.nombre}</div>
                                            {item.nombre_corto ? (
                                                <div className="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">{item.nombre_corto}</div>
                                            ) : null}
                                        </td>
                                        <td className="px-6 py-4">
                                            <span className="inline-flex rounded bg-zinc-100 px-2 py-0.5 text-xs font-semibold tabular-nums text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200">
                                                {item.ur ?? '—'}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4">
                                            <span className="inline-flex items-center gap-1.5 text-zinc-700 dark:text-zinc-200">
                                                <MapPin className="size-3.5 text-brand-gold" strokeWidth={1.75} />
                                                <span className="font-medium tabular-nums">{item.delegaciones_count}</span>
                                            </span>
                                        </td>
                                        <td className="px-6 py-4">
                                            <span className="inline-flex items-center gap-1.5 text-zinc-700 dark:text-zinc-200">
                                                <Users className="size-3.5 text-brand-gold" strokeWidth={1.75} />
                                                <span className="font-medium tabular-nums">{item.empleados_count}</span>
                                            </span>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    <div className="flex flex-col divide-y divide-zinc-100 md:hidden dark:divide-zinc-800/50">
                        {rows.length === 0 && (
                            <div className="px-4 py-12 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                No se encontraron dependencias.
                            </div>
                        )}
                        {rows.map((item) => (
                            <div key={item.id} className="space-y-2 p-4">
                                <div className="font-medium text-zinc-900 dark:text-zinc-100">{item.nombre}</div>
                                {item.nombre_corto ? <div className="text-xs text-zinc-500">{item.nombre_corto}</div> : null}
                                <div className="text-xs text-zinc-500">UR {item.ur ?? '—'}</div>
                                <div className="flex items-center gap-4 text-xs">
                                    <span className="flex items-center gap-1.5 text-zinc-700 dark:text-zinc-200">
                                        <MapPin className="size-3.5 text-brand-gold" strokeWidth={1.75} />
                                        {item.delegaciones_count} delegaciones
                                    </span>
                                    <span className="flex items-center gap-1.5 text-zinc-700 dark:text-zinc-200">
                                        <Users className="size-3.5 text-brand-gold" strokeWidth={1.75} />
                                        {item.empleados_count} empleados
                                    </span>
                                </div>
                            </div>
                        ))}
                    </div>

                    <Pagination paginator={dependencias} />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

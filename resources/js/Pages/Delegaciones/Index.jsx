import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Search, ChevronRight, Users, UserCheck, Building2 } from 'lucide-react';
import { useState } from 'react';
import SearchInput from '@/Components/SearchInput';
import FilterBar from '@/Components/FilterBar';
import FilterSelect from '@/Components/FilterSelect';

function Pagination({ paginator }) {
    if (!paginator?.links?.length) return null;

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
                            <span key={i} className="inline-flex min-w-8 items-center justify-center rounded-md px-2 py-1 text-xs font-medium text-zinc-400 dark:text-zinc-600">
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

export default function Index({ delegaciones, filters = {} }) {
    const [buscar, setBuscar] = useState(filters.buscar ?? '');
    const [empleadosFilter, setEmpleadosFilter] = useState('Todos');
    const [delegadosFilter, setDelegadosFilter] = useState('Todos');
    const allRows = Array.isArray(delegaciones?.data) ? delegaciones.data : [];

    const rows = allRows.filter(item => {
        const q = buscar.toLowerCase();
        const matchSearch = buscar.trim() === '' || (
            item.nombre?.toLowerCase().includes(q) ||
            item.clave?.toLowerCase().includes(q)
        );

        let matchEmpleados = true;
        if (empleadosFilter === 'ConEmpleados') {
            matchEmpleados = (item.empleados_count ?? 0) > 0;
        } else if (empleadosFilter === 'SinEmpleados') {
            matchEmpleados = (item.empleados_count ?? 0) === 0;
        }

        let matchDelegados = true;
        if (delegadosFilter === 'ConDelegados') {
            matchDelegados = (item.delegados_count ?? 0) > 0;
        } else if (delegadosFilter === 'SinDelegados') {
            matchDelegados = (item.delegados_count ?? 0) === 0;
        }

        return matchSearch && matchEmpleados && matchDelegados;
    });

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                    <span>SIVSO</span>
                    <span className="text-zinc-300 dark:text-zinc-600">/</span>
                    <span className="text-zinc-900 dark:text-zinc-100">DELEGACIONES</span>
                </div>
            }
        >
            <Head title="Delegaciones" />

            <div className="mx-auto w-full max-w-[1600px] space-y-8">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                            Delegaciones
                        </h2>
                        <p className="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            Catálogo de delegaciones registradas en el sistema.
                        </p>
                    </div>
                </div>

                <FilterBar>
                    <div className="w-full sm:max-w-md">
                        <SearchInput 
                            value={buscar} 
                            onChange={setBuscar}
                            placeholder="Clave o nombre..."
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
                                { value: 'SinEmpleados', label: 'Sin empleados' }
                            ]}
                        />
                    </div>
                    <div className="w-full sm:w-48">
                        <FilterSelect
                            label="Delegados"
                            value={delegadosFilter}
                            onChange={setDelegadosFilter}
                            options={[
                                { value: 'Todos', label: 'Todos' },
                                { value: 'ConDelegados', label: 'Con delegados' },
                                { value: 'SinDelegados', label: 'Sin delegados' }
                            ]}
                        />
                    </div>
                </FilterBar>

                <div className="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-[#0A0A0B]">
                    <div className="border-b border-zinc-200 px-4 py-4 sm:px-6 dark:border-zinc-800">
                        <h3 className="text-sm font-bold uppercase tracking-wider text-zinc-800 dark:text-zinc-200">
                            Resultados ({rows.length})
                        </h3>
                    </div>

                    <div className="hidden overflow-x-auto md:block">
                        <table className="min-w-[900px] w-full text-left">
                            <thead>
                                <tr className="border-b border-zinc-200 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-900/50">
                                    <th className="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Delegación</th>
                                    <th className="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">UR</th>
                                    <th className="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Dependencias</th>
                                    <th className="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Empleados</th>
                                    <th className="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Delegados</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-zinc-100 dark:divide-zinc-800/50">
                                {rows.length === 0 && (
                                    <tr>
                                        <td colSpan={5} className="px-6 py-12 text-center text-sm text-zinc-500">
                                            No se encontraron delegaciones.
                                        </td>
                                    </tr>
                                )}
                                {rows.map((item) => (
                                    <tr key={item.id} className="group transition-colors hover:bg-zinc-50/80 dark:hover:bg-zinc-900/80">
                                        <td className="px-6 py-4">
                                            <div className="flex flex-col">
                                                <span className="text-xs font-bold uppercase tracking-wide text-zinc-900 dark:text-zinc-100">
                                                    {item.nombre || item.clave}
                                                </span>
                                                {item.nombre && (
                                                    <span className="text-[10px] font-medium tracking-wider text-zinc-500 dark:text-zinc-400">
                                                        Clave: {item.clave}
                                                    </span>
                                                )}
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 text-xs font-medium text-zinc-600 dark:text-zinc-300">
                                            {item.ur || '—'}
                                        </td>
                                        <td className="px-6 py-4">
                                            <span className="inline-flex items-center gap-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-200">
                                                <Building2 className="size-3.5 text-zinc-400" strokeWidth={2} />
                                                {item.dependencias_count}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4">
                                            <span className="inline-flex items-center gap-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-200">
                                                <Users className="size-3.5 text-zinc-400" strokeWidth={2} />
                                                {item.empleados_count}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4">
                                            <span className="inline-flex items-center gap-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-200">
                                                <UserCheck className="size-3.5 text-zinc-400" strokeWidth={2} />
                                                {item.delegados_count}
                                            </span>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    <div className="flex flex-col divide-y divide-zinc-100 md:hidden dark:divide-zinc-800/50">
                        {rows.length === 0 && (
                            <div className="px-4 py-12 text-center text-sm text-zinc-500">
                                No se encontraron delegaciones.
                            </div>
                        )}
                        {rows.map((item) => (
                            <div key={item.id} className="space-y-1.5 p-4">
                                <div className="text-xs font-bold uppercase tracking-wide text-zinc-900 dark:text-zinc-100">
                                    {item.nombre || item.clave}
                                </div>
                                {item.nombre && (
                                    <div className="text-[10px] text-zinc-500">Clave: {item.clave}</div>
                                )}
                                {item.ur && (
                                    <div className="text-[10px] text-zinc-500">UR: {item.ur}</div>
                                )}
                                <div className="flex items-center gap-4">
                                    <span className="flex items-center gap-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-200">
                                        <Building2 className="size-3.5 text-zinc-400" strokeWidth={2} />
                                        {item.dependencias_count} dep.
                                    </span>
                                    <span className="flex items-center gap-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-200">
                                        <Users className="size-3.5 text-zinc-400" strokeWidth={2} />
                                        {item.empleados_count} emp.
                                    </span>
                                    <span className="flex items-center gap-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-200">
                                        <UserCheck className="size-3.5 text-zinc-400" strokeWidth={2} />
                                        {item.delegados_count} del.
                                    </span>
                                </div>
                            </div>
                        ))}
                    </div>

                    <Pagination paginator={delegaciones} />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

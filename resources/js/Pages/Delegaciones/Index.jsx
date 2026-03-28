import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Search, Plus, Eye, Edit3, Trash2, ChevronDown, AlertTriangle, MapPin } from 'lucide-react';
import { useState, useMemo } from 'react';

function StatusBadge({ activo }) {
    return (
        <span className="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
            <span className={`size-1.5 rounded-full ${activo ? 'bg-zinc-900 dark:bg-zinc-100' : 'bg-zinc-400 dark:bg-zinc-500'}`} />
            {activo ? 'Activo' : 'Inactivo'}
        </span>
    );
}

export default function Index({ delegaciones = [] }) {
    const [search, setSearch] = useState('');
    const [statusFilter, setStatusFilter] = useState('Todos');
    const [itemToDelete, setItemToDelete] = useState(null);
    const { delete: destroy, processing } = useForm();

    const confirmDelete = (item) => {
        setItemToDelete(item);
    };

    const handleDelete = () => {
        if (itemToDelete) {
            destroy(route('delegaciones.destroy', itemToDelete.id), {
                onSuccess: () => setItemToDelete(null),
            });
        }
    };

    const filtered = useMemo(() => {
        let result = delegaciones;
        
        if (statusFilter !== 'Todos') {
            const isActive = statusFilter === 'Activos';
            result = result.filter(u => !!u.activo === isActive);
        }

        if (!search.trim()) return result;
        const q = search.toLowerCase();
        return result.filter(
            (u) =>
                u.nombre?.toLowerCase().includes(q) ||
                u.clave?.toLowerCase().includes(q)
        );
    }, [delegaciones, search, statusFilter]);

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
                {/* Header Section */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                            Delegaciones
                        </h2>
                        <p className="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            Gestión de las delegaciones del sistema.
                        </p>
                    </div>
                    <Link href={'#'} className="inline-flex items-center justify-center gap-2 rounded-md bg-zinc-900 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white">
                        <Plus className="size-4" strokeWidth={2} />
                        Nueva Delegación
                    </Link>
                </div>

                {/* Filters */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-end">
                    <div className="w-full sm:max-w-md">
                        <label className="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Buscar Delegación
                        </label>
                        <div className="relative">
                            <Search className="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-zinc-400" strokeWidth={2} />
                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Nombre o Clave..."
                                className="w-full rounded-lg border border-zinc-200 bg-white/80 py-2.5 pl-10 pr-4 text-sm text-zinc-900 outline-none transition-all placeholder:text-zinc-400 focus:border-brand-gold focus:ring-1 focus:ring-brand-gold dark:border-zinc-800 dark:bg-zinc-900/80 dark:text-zinc-100 dark:placeholder:text-zinc-500"
                            />
                        </div>
                    </div>
                    <div className="w-full sm:w-48">
                        <label className="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Estado
                        </label>
                        <div className="relative">
                            <MapPin className="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
                            <select
                                value={statusFilter}
                                onChange={(e) => setStatusFilter(e.target.value)}
                                className="w-full appearance-none rounded-lg border border-zinc-200 bg-white/80 py-2.5 pl-10 pr-10 text-sm font-medium text-zinc-700 outline-none transition-all focus:border-brand-gold focus:ring-1 focus:ring-brand-gold dark:border-zinc-800 dark:bg-zinc-900/80 dark:text-zinc-300"
                            >
                                <option value="Todos">Todos</option>
                                <option value="Activos">Activos</option>
                                <option value="Inactivos">Inactivos</option>
                            </select>
                            <ChevronDown className="pointer-events-none absolute right-3.5 top-1/2 size-4 -translate-y-1/2 text-zinc-400" strokeWidth={2} />
                        </div>
                    </div>
                </div>

                {/* Table / Cards */}
                <div className="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-[#0A0A0B]">
                    <div className="border-b border-zinc-200 px-4 py-4 sm:px-6 dark:border-zinc-800">
                        <h3 className="text-sm font-bold uppercase tracking-wider text-zinc-800 dark:text-zinc-200">
                            Delegaciones ({filtered.length})
                        </h3>
                    </div>
                    
                    {/* Desktop Table */}
                    <div className="hidden md:block overflow-x-auto">
                        <table className="w-full text-left min-w-[800px]">
                            <thead>
                                <tr className="border-b border-zinc-200 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-900/50">
                                    <th className="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Delegación</th>
                                    <th className="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Sede</th>
                                    <th className="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Estado</th>
                                    <th className="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-zinc-100 dark:divide-zinc-800/50">
                                {filtered.length === 0 && (
                                    <tr>
                                        <td colSpan={4} className="px-6 py-12 text-center text-sm text-zinc-500">
                                            No se encontraron delegaciones.
                                        </td>
                                    </tr>
                                )}
                                {filtered.map((item) => (
                                    <tr key={item.id} className="group transition-colors hover:bg-zinc-50/80 dark:hover:bg-zinc-900/80">
                                        <td className="px-6 py-4">
                                            <div className="flex flex-col">
                                                <span className="text-xs font-bold uppercase tracking-wide text-zinc-900 dark:text-zinc-100">
                                                    {item.nombre}
                                                </span>
                                                <span className="text-[10px] font-medium tracking-wider text-zinc-500 dark:text-zinc-400">
                                                    Clave: {item.clave || '—'}
                                                </span>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4">
                                            <span className="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                                {item.sede || '—'}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4">
                                            <StatusBadge activo={item.activo} />
                                        </td>
                                        <td className="px-6 py-4 text-right">
                                            <div className="flex items-center justify-end gap-2">
                                                <Link href={'#'} className="rounded p-1.5 text-emerald-600 transition-colors hover:bg-emerald-50 dark:text-emerald-500 dark:hover:bg-emerald-500/10" title="Ver detalles">
                                                    <Eye className="size-4" strokeWidth={2} />
                                                </Link>
                                                <Link href={'#'} className="rounded p-1.5 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-500 dark:hover:bg-zinc-800 dark:hover:text-zinc-300" title="Editar">
                                                    <Edit3 className="size-4" strokeWidth={2} />
                                                </Link>
                                                <button onClick={() => confirmDelete(item)} className="rounded p-1.5 text-zinc-400 transition-colors hover:bg-red-50 hover:text-red-600 dark:text-zinc-500 dark:hover:bg-red-500/10 dark:hover:text-red-500" title="Eliminar">
                                                    <Trash2 className="size-4" strokeWidth={2} />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {/* Mobile List */}
                    <div className="md:hidden flex flex-col divide-y divide-zinc-100 dark:divide-zinc-800/50">
                        {filtered.length === 0 && (
                            <div className="px-4 py-12 text-center text-sm text-zinc-500">
                                No se encontraron delegaciones.
                            </div>
                        )}
                        {filtered.map((item) => (
                            <div key={item.id} className="flex flex-col gap-3 p-4 transition-colors hover:bg-zinc-50/80 dark:hover:bg-zinc-900/80">
                                <div className="flex items-start justify-between gap-2">
                                    <div className="flex flex-col min-w-0">
                                        <span className="text-xs font-bold uppercase tracking-wide text-zinc-900 dark:text-zinc-100 truncate">
                                            {item.nombre}
                                        </span>
                                        <span className="text-[10px] font-medium tracking-wider text-zinc-500 dark:text-zinc-400 truncate">
                                            Clave: {item.clave || '—'}
                                        </span>
                                    </div>
                                    <StatusBadge activo={item.activo} />
                                </div>
                                
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs font-medium text-zinc-500 dark:text-zinc-400 truncate">
                                        {item.sede || '—'}
                                    </span>
                                </div>

                                <div className="flex items-center justify-end gap-2 pt-2 mt-1 border-t border-zinc-100/50 dark:border-zinc-800/30">
                                    <Link href={'#'} className="flex items-center gap-1.5 rounded-md px-2 py-1.5 text-[10px] font-bold uppercase tracking-wider text-emerald-600 transition-colors hover:bg-emerald-50 dark:text-emerald-500 dark:hover:bg-emerald-500/10">
                                        <Eye className="size-3.5" strokeWidth={2} />
                                        Ver
                                    </Link>
                                    <Link href={'#'} className="flex items-center gap-1.5 rounded-md px-2 py-1.5 text-[10px] font-bold uppercase tracking-wider text-zinc-500 transition-colors hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                        <Edit3 className="size-3.5" strokeWidth={2} />
                                        Editar
                                    </Link>
                                    <button onClick={() => confirmDelete(item)} className="flex items-center gap-1.5 rounded-md px-2 py-1.5 text-[10px] font-bold uppercase tracking-wider text-zinc-500 transition-colors hover:bg-red-50 hover:text-red-600 dark:text-zinc-400 dark:hover:bg-red-500/10 dark:hover:text-red-500">
                                        <Trash2 className="size-3.5" strokeWidth={2} />
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>

            {/* Modal de Eliminación */}
            {itemToDelete && (
                <div className="relative z-[100]" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div className="fixed inset-0 bg-zinc-900/40 transition-opacity dark:bg-black/60" onClick={() => setItemToDelete(null)}></div>

                    <div className="fixed inset-0 z-[101] w-screen overflow-y-auto">
                        <div className="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                            <div className="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all dark:bg-zinc-900 sm:my-8 sm:w-full sm:max-w-lg border border-zinc-200/50 dark:border-zinc-800/50">
                                <div className="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 dark:bg-zinc-900">
                                    <div className="sm:flex sm:items-start">
                                        <div className="mx-auto flex size-12 shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:size-10 dark:bg-red-500/10">
                                            <AlertTriangle className="size-6 text-red-600 dark:text-red-500" strokeWidth={1.5} />
                                        </div>
                                        <div className="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                            <h3 className="text-base font-bold text-zinc-900 dark:text-zinc-100" id="modal-title">
                                                Eliminar Delegación
                                            </h3>
                                            <div className="mt-2">
                                                <p className="text-sm text-zinc-500 dark:text-zinc-400">
                                                    ¿Estás seguro de que deseas eliminar la delegación <span className="font-bold text-zinc-700 dark:text-zinc-300">{itemToDelete.nombre}</span>? Esta acción no se puede deshacer y todos sus datos serán eliminados permanentemente del sistema.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div className="bg-zinc-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 dark:bg-zinc-800/50 border-t border-zinc-100 dark:border-zinc-800/50">
                                    <button
                                        type="button"
                                        onClick={handleDelete}
                                        disabled={processing}
                                        className="inline-flex w-full justify-center rounded-md bg-red-600 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto disabled:opacity-50 transition-colors"
                                    >
                                        Eliminar
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => setItemToDelete(null)}
                                        className="mt-3 inline-flex w-full justify-center rounded-md bg-white px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-zinc-700 shadow-sm ring-1 ring-inset ring-zinc-300 hover:bg-zinc-50 sm:mt-0 sm:w-auto dark:bg-zinc-800 dark:text-zinc-300 dark:ring-zinc-700 dark:hover:bg-zinc-700 transition-colors"
                                    >
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}

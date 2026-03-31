import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm as useInertiaForm } from '@inertiajs/react';
import { Search, Shirt, ChevronRight, Plus, Edit3, Trash2, AlertTriangle, CheckCircle2, Loader2 } from 'lucide-react';
import { useState, useCallback, useEffect } from 'react';
import SearchInput from '@/Components/SearchInput';
import FilterBar from '@/Components/FilterBar';
import FilterSelect from '@/Components/FilterSelect';

function Pagination({ paginator }) {
    if (!paginator || !paginator.links?.length) {
        return null;
    }

    const from = paginator.from ?? 0;
    const to = paginator.to ?? 0;
    const total = paginator.total ?? 0;

    return (
        <div className="flex flex-col gap-3 border-t border-zinc-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800">
            <p className="text-xs text-zinc-500 dark:text-zinc-400">
                {total === 0
                    ? 'Sin resultados'
                    : `Mostrando ${from}–${to} de ${total}`}
            </p>
            <div className="flex flex-wrap items-center gap-1">
                {paginator.links.map((link, i) => {
                    const label = String(link.label)
                        .replace('&laquo;', '«')
                        .replace('&raquo;', '»');
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

function FormEmpleadoModal({ empleado, delegaciones, dependencias, onClose }) {
    const isEdit = !!empleado;

    const [form, setForm] = useState({
        nombre: empleado?.nombre || '',
        apellido_paterno: empleado?.apellido_paterno || '',
        apellido_materno: empleado?.apellido_materno || '',
        nue: empleado?.nue || '',
        dependencia_id: empleado?.dependencia_id || '',
        delegacion_id: empleado?.delegacion_id || '',
    });
    const [saving, setSaving] = useState(false);
    const [errors, setErrors] = useState({});

    useEffect(() => {
        const onKey = (e) => {
            if (e.key === 'Escape') onClose();
        };
        document.addEventListener('keydown', onKey);
        return () => document.removeEventListener('keydown', onKey);
    }, [onClose]);

    const set = (field) => (e) => {
        setForm(prev => ({ ...prev, [field]: e.target.value }));
        setErrors(prev => ({ ...prev, [field]: null }));
    };

    const handleGuardar = () => {
        setSaving(true);

        const options = {
            onSuccess: () => { setSaving(false); onClose(); },
            onError: (err) => { setErrors(err); setSaving(false); },
            preserveScroll: true,
        };

        if (isEdit) {
            router.put(route('empleados.update', empleado.id), form, options);
        } else {
            router.post(route('empleados.store'), form, options);
        }
    };

    const inputClass =
        'w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:border-brand-gold focus:ring-1 focus:ring-brand-gold dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100';
    const labelClass =
        'mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400';

    return (
        <div className="relative z-[100]" aria-labelledby="empleado-modal-title" role="dialog" aria-modal="true">
            <div className="fixed inset-0 bg-zinc-900/40 transition-opacity dark:bg-black/60" onClick={onClose} />
            <div className="fixed inset-0 z-[101] w-screen overflow-y-auto">
                <div className="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div className="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all dark:bg-zinc-900 sm:my-8 sm:w-full sm:max-w-3xl border border-zinc-200/50 dark:border-zinc-800/50">
                        <div className="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 dark:bg-zinc-900">
                            <div>
                                <h3 className="text-base font-bold text-zinc-900 dark:text-zinc-100" id="empleado-modal-title">
                                    {isEdit ? 'Editar Empleado' : 'Nuevo Empleado'}
                                </h3>
                                <p className="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                    {isEdit
                                        ? 'Actualiza los datos del empleado.'
                                        : 'Completa los datos del nuevo empleado.'}
                                </p>
                            </div>

                            <div className="mt-4 space-y-4">
                                <div>
                                    <label className={labelClass}>Nombre</label>
                                    <input type="text" value={form.nombre} onChange={set('nombre')} className={inputClass} />
                                    {errors.nombre && <p className="mt-1 text-xs text-red-500">{errors.nombre}</p>}
                                </div>
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <label className={labelClass}>Apellido Paterno</label>
                                        <input type="text" value={form.apellido_paterno} onChange={set('apellido_paterno')} className={inputClass} />
                                        {errors.apellido_paterno && <p className="mt-1 text-xs text-red-500">{errors.apellido_paterno}</p>}
                                    </div>
                                    <div>
                                        <label className={labelClass}>Apellido Materno</label>
                                        <input type="text" value={form.apellido_materno} onChange={set('apellido_materno')} className={inputClass} />
                                        {errors.apellido_materno && <p className="mt-1 text-xs text-red-500">{errors.apellido_materno}</p>}
                                    </div>
                                </div>
                                <div>
                                    <label className={labelClass}>NUE</label>
                                    <input type="text" value={form.nue} onChange={set('nue')} className={`${inputClass} uppercase`} />
                                    {errors.nue && <p className="mt-1 text-xs text-red-500">{errors.nue}</p>}
                                </div>
                                <div>
                                    <label className={labelClass}>Dependencia</label>
                                    <select value={form.dependencia_id} onChange={set('dependencia_id')} className={inputClass}>
                                        <option value="">Seleccionar dependencia...</option>
                                        {dependencias.map(dep => (
                                            <option key={dep.id} value={dep.id}>{dep.nombre}</option>
                                        ))}
                                    </select>
                                    {errors.dependencia_id && <p className="mt-1 text-xs text-red-500">{errors.dependencia_id}</p>}
                                </div>
                                <div>
                                    <label className={labelClass}>Delegación (Opcional)</label>
                                    <select value={form.delegacion_id} onChange={set('delegacion_id')} className={inputClass}>
                                        <option value="">Sin delegación</option>
                                        {delegaciones.map(del => (
                                            <option key={del.id} value={del.id}>{del.nombre}</option>
                                        ))}
                                    </select>
                                    {errors.delegacion_id && <p className="mt-1 text-xs text-red-500">{errors.delegacion_id}</p>}
                                </div>
                            </div>
                        </div>
                        <div className="bg-zinc-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 dark:bg-zinc-800/50 border-t border-zinc-100 dark:border-zinc-800/50">
                            <button
                                type="button"
                                onClick={handleGuardar}
                                disabled={saving}
                                className="inline-flex w-full justify-center rounded-md bg-zinc-900 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white sm:ml-3 sm:w-auto disabled:opacity-50 transition-colors"
                            >
                                {saving ? (
                                    <Loader2 className="size-4 animate-spin mr-2" />
                                ) : (
                                    <CheckCircle2 className="size-4 mr-2" />
                                )}
                                {isEdit ? 'Actualizar' : 'Crear Empleado'}
                            </button>
                            <button
                                type="button"
                                onClick={onClose}
                                className="mt-3 inline-flex w-full justify-center rounded-md bg-white px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-zinc-700 shadow-sm ring-1 ring-inset ring-zinc-300 hover:bg-zinc-50 sm:mt-0 sm:w-auto dark:bg-zinc-800 dark:text-zinc-300 dark:ring-zinc-700 dark:hover:bg-zinc-700 transition-colors"
                            >
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

export default function Index({
    empleados,
    ejercicio,
    anios_disponibles = [],
    filters = {},
    delegaciones,
    dependencias,
}) {
    const [buscar, setBuscar] = useState(filters.buscar ?? '');
    const [dependenciaFilter, setDependenciaFilter] = useState('Todas');
    const [delegacionFilter, setDelegacionFilter] = useState('Todas');
    const [isCreating, setIsCreating] = useState(false);
    const [empleadoAEditar, setAEditar] = useState(null);
    const [empleadoToDelete, setEmpleadoToDelete] = useState(null);
    const allRows = Array.isArray(empleados?.data) ? empleados.data : [];

    const closeEmpleadoFormModal = useCallback(() => {
        setIsCreating(false);
        setAEditar(null);
    }, []);

    const { delete: destroy, processing } = useInertiaForm();

    const handleDelete = () => {
        if (empleadoToDelete) {
            destroy(route('empleados.destroy', empleadoToDelete.id), {
                onSuccess: () => setEmpleadoToDelete(null),
                preserveScroll: true,
            });
        }
    };

    const rows = allRows.filter(item => {
        const q = buscar.toLowerCase();
        const matchSearch = buscar.trim() === '' || (
            item.nombre_listado?.toLowerCase().includes(q) ||
            item.nombre?.toLowerCase().includes(q) ||
            item.apellido_paterno?.toLowerCase().includes(q) ||
            item.apellido_materno?.toLowerCase().includes(q) ||
            item.nue?.toLowerCase().includes(q) ||
            item.puesto?.toLowerCase().includes(q) ||
            item.dependencia?.toLowerCase().includes(q) ||
            item.delegacion?.toLowerCase().includes(q)
        );

        const matchDependencia = dependenciaFilter === 'Todas' || 
            item.dependencia_id?.toString() === dependenciaFilter;

        const matchDelegacion = delegacionFilter === 'Todas' || 
            item.delegacion_id?.toString() === delegacionFilter;

        return matchSearch && matchDependencia && matchDelegacion;
    });

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                    <span>SIVSO</span>
                    <span className="text-zinc-300 dark:text-zinc-600">/</span>
                    <span className="text-zinc-900 dark:text-zinc-100">EMPLEADOS</span>
                </div>
            }
        >
            <Head title="Empleados" />

            <div className="mx-auto w-full max-w-[1600px] space-y-8">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                            Empleados
                        </h2>
                        <p className="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            Catálogo desde copiasivso. Productos según el ejercicio seleccionado.
                        </p>
                    </div>
                    <button onClick={() => setIsCreating(true)} className="inline-flex items-center justify-center gap-2 rounded-md bg-zinc-900 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white">
                        <Plus className="size-4" strokeWidth={2} />
                        Nuevo Empleado
                    </button>
                </div>

                <FilterBar>
                    <div className="w-full sm:max-w-md">
                        <SearchInput 
                            value={buscar} 
                            onChange={setBuscar}
                            placeholder="Nombre, NUE, dependencia o delegación..."
                        />
                    </div>
                    <div className="w-full sm:w-36">
                        <FilterSelect
                            label="Ejercicio"
                            value={String(ejercicio)}
                            onChange={(v) => {
                                router.get(
                                    route('empleados.index'),
                                    { anio: v },
                                    {
                                        preserveState: true,
                                        preserveScroll: true,
                                        replace: true,
                                    }
                                );
                            }}
                            options={(anios_disponibles.length ? anios_disponibles : [ejercicio]).map((a) => ({
                                value: String(a),
                                label: String(a),
                            }))}
                        />
                    </div>
                    <div className="w-full sm:w-48">
                        <FilterSelect
                            label="Dependencia"
                            value={dependenciaFilter}
                            onChange={setDependenciaFilter}
                            options={[
                                { value: 'Todas', label: 'Todas' },
                                ...dependencias.map(d => ({ value: d.id.toString(), label: d.nombre }))
                            ]}
                        />
                    </div>
                    <div className="w-full sm:w-48">
                        <FilterSelect
                            label="Delegación"
                            value={delegacionFilter}
                            onChange={setDelegacionFilter}
                            options={[
                                { value: 'Todas', label: 'Todas' },
                                ...delegaciones.map(d => ({ value: d.id.toString(), label: d.nombre }))
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
                                    <th className="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        Empleado
                                    </th>
                                    <th className="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        NUE
                                    </th>
                                    <th className="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        Productos ({ejercicio})
                                    </th>
                                    <th className="px-6 py-3 text-right text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-zinc-100 dark:divide-zinc-800/50">
                                {rows.length === 0 && (
                                    <tr>
                                        <td colSpan={5} className="px-6 py-12 text-center text-sm text-zinc-500">
                                            No se encontraron empleados.
                                        </td>
                                    </tr>
                                )}
                                {rows.map((item) => (
                                    <tr
                                        key={item.id}
                                        className="group transition-colors hover:bg-zinc-50/80 dark:hover:bg-zinc-900/80"
                                    >
                                        <td className="px-6 py-4">
                                            <span className="text-xs font-bold uppercase tracking-wide text-zinc-900 dark:text-zinc-100">
                                                {item.nombre_listado ?? item.nombre}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 text-xs font-medium text-zinc-600 dark:text-zinc-300">
                                            {item.nue || '—'}
                                        </td>
                                        <td className="px-6 py-4 text-xs font-semibold text-zinc-700 dark:text-zinc-200">
                                            {item.productos_count ?? 0}
                                        </td>
                                        <td className="px-6 py-4 text-right">
                                            <div className="flex items-center justify-end gap-2">
                                                <Link
                                                    href={`${route('empleados.show', item.id)}?anio=${ejercicio}`}
                                                    className="inline-flex items-center gap-1.5 rounded-md px-2 py-1.5 text-[10px] font-bold uppercase tracking-wider text-emerald-600 transition-colors hover:bg-emerald-50 dark:text-emerald-500 dark:hover:bg-emerald-500/10"
                                                >
                                                    <Shirt className="size-3.5" strokeWidth={2} />
                                                    Ver
                                                </Link>
                                                <button onClick={() => setAEditar(item)} className="rounded p-1.5 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-500 dark:hover:bg-zinc-800 dark:hover:text-zinc-300" title="Editar">
                                                    <Edit3 className="size-4" strokeWidth={2} />
                                                </button>
                                                <button onClick={() => setEmpleadoToDelete(item)} className="rounded p-1.5 text-zinc-400 transition-colors hover:bg-red-50 hover:text-red-600 dark:text-zinc-500 dark:hover:bg-red-500/10 dark:hover:text-red-500" title="Eliminar">
                                                    <Trash2 className="size-4" strokeWidth={2} />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    <div className="flex flex-col divide-y divide-zinc-100 md:hidden dark:divide-zinc-800/50">
                        {rows.length === 0 && (
                            <div className="px-4 py-12 text-center text-sm text-zinc-500">
                                No se encontraron empleados.
                            </div>
                        )}
                        {rows.map((item) => (
                            <div key={item.id} className="space-y-2 p-4">
                                <div className="text-xs font-bold uppercase tracking-wide text-zinc-900 dark:text-zinc-100">
                                    {item.nombre_listado ?? item.nombre}
                                </div>
                                <div className="text-[10px] text-zinc-500">NUE: {item.nue || '—'}</div>
                                <div className="text-xs text-zinc-500">{item.puesto || '—'}</div>
                                <div className="text-xs font-semibold text-zinc-700 dark:text-zinc-200">
                                    Productos {ejercicio}: {item.productos_count ?? 0}
                                </div>
                                <Link
                                    href={`${route('empleados.show', item.id)}?anio=${ejercicio}`}
                                    className="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-500"
                                >
                                    <Shirt className="size-3.5" strokeWidth={2} />
                                    Ver vestuario
                                </Link>
                            </div>
                        ))}
                    </div>

                    <Pagination paginator={empleados} />
                </div>
            </div>

            {(isCreating || empleadoAEditar) && (
                <FormEmpleadoModal
                    key={empleadoAEditar ? `edit-${empleadoAEditar.id}` : 'create'}
                    empleado={empleadoAEditar}
                    delegaciones={delegaciones}
                    dependencias={dependencias}
                    onClose={closeEmpleadoFormModal}
                />
            )}

            {empleadoToDelete && (
                <div className="relative z-[100]" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div className="fixed inset-0 bg-zinc-900/40 transition-opacity dark:bg-black/60" onClick={() => setEmpleadoToDelete(null)}></div>

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
                                                Eliminar Empleado
                                            </h3>
                                            <div className="mt-2">
                                                <p className="text-sm text-zinc-500 dark:text-zinc-400">
                                                    ¿Estás seguro de que deseas eliminar a <span className="font-bold text-zinc-700 dark:text-zinc-300">{empleadoToDelete.nombre_listado ?? empleadoToDelete.nombre}</span>? Esta acción no se puede deshacer.
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
                                        onClick={() => setEmpleadoToDelete(null)}
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
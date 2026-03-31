import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Plus, Edit3, Trash2, CheckCircle2, Loader2, AlertTriangle, FileText } from 'lucide-react';
import { useState, useCallback } from 'react';
import SearchInput from '@/Components/SearchInput';
import FilterBar from '@/Components/FilterBar';
import FilterSelect from '@/Components/FilterSelect';

const inputCls = 'w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:border-brand-gold focus:ring-1 focus:ring-brand-gold dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100';
const labelCls = 'mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400';

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
                            <span key={i} className="inline-flex min-w-8 items-center justify-center rounded-md px-2 py-1 text-xs font-medium text-zinc-400 dark:text-zinc-600">{label}</span>
                        );
                    }
                    return (
                        <Link key={i} href={link.url} preserveScroll className={`inline-flex min-w-8 items-center justify-center rounded-md px-2 py-1 text-xs font-medium transition-colors ${link.active ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800'}`}>{label}</Link>
                    );
                })}
            </div>
        </div>
    );
}

function FormModal({ item, partidas, anio, onClose }) {
    const isEdit = !!item;
    const [form, setForm] = useState({
        partida_id: item?.partida_id || '',
        anio: item?.anio || anio,
        clave: item?.clave || '',
        descripcion: item?.descripcion || '',
    });
    const [saving, setSaving] = useState(false);
    const [errors, setErrors] = useState({});

    const set = (field) => (e) => {
        setForm((prev) => ({ ...prev, [field]: e.target.value }));
        setErrors((prev) => ({ ...prev, [field]: null }));
    };

    const handleGuardar = () => {
        if (!form.clave.trim()) { setErrors({ clave: 'Requerido.' }); return; }
        if (!form.partida_id) { setErrors({ partida_id: 'Requerido.' }); return; }
        setSaving(true);
        const opts = {
            onSuccess: () => { setSaving(false); onClose(); },
            onError: (err) => { setErrors(err); setSaving(false); },
            preserveScroll: true,
        };
        if (isEdit) {
            router.put(route('partidas-especificas.update', item.id), form, opts);
        } else {
            router.post(route('partidas-especificas.store'), form, opts);
        }
    };

    return (
        <div className="relative z-[100]" role="dialog" aria-modal="true">
            <div className="fixed inset-0 bg-zinc-900/40 dark:bg-black/60" onClick={onClose} />
            <div className="fixed inset-0 z-[101] w-screen overflow-y-auto">
                <div className="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div className="relative w-full transform overflow-hidden rounded-2xl bg-white text-left shadow-xl dark:bg-zinc-900 sm:my-8 sm:max-w-2xl border border-zinc-200/50 dark:border-zinc-800/50">
                        <div className="bg-white px-6 pb-4 pt-6 dark:bg-zinc-900">
                            <h3 className="text-base font-bold text-zinc-900 dark:text-zinc-100">
                                {isEdit ? 'Editar Línea Presupuestal' : 'Nueva Línea Presupuestal'}
                            </h3>
                            <p className="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                {isEdit ? 'Actualiza los datos del artículo.' : 'Registra un nuevo artículo presupuestal.'}
                            </p>
                            <div className="mt-5 space-y-4">
                                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <label className={labelCls}>Partida (no. presupuestal) *</label>
                                        <select value={form.partida_id} onChange={set('partida_id')} className={inputCls}>
                                            <option value="">Seleccionar partida…</option>
                                            {partidas.map((p) => (
                                                <option key={p.id} value={p.id}>
                                                    {p.no_partida}{p.descripcion ? ` — ${String(p.descripcion).slice(0, 60)}` : ''}
                                                </option>
                                            ))}
                                        </select>
                                        {errors.partida_id && <p className="mt-1 text-xs text-red-500">{errors.partida_id}</p>}
                                    </div>
                                    <div>
                                        <label className={labelCls}>Año *</label>
                                        <input type="number" value={form.anio} onChange={set('anio')} className={inputCls} min="2020" max="2040" />
                                        {errors.anio && <p className="mt-1 text-xs text-red-500">{errors.anio}</p>}
                                    </div>
                                </div>
                                <div>
                                    <label className={labelCls}>Clave *</label>
                                    <input type="text" value={form.clave} onChange={set('clave')} className={inputCls} placeholder="Ej. PMC06" />
                                    {errors.clave && <p className="mt-1 text-xs text-red-500">{errors.clave}</p>}
                                </div>
                                <div>
                                    <label className={labelCls}>Descripción</label>
                                    <textarea value={form.descripcion} onChange={set('descripcion')} rows={3} className={inputCls} placeholder="Descripción de la línea presupuestal…" />
                                    {errors.descripcion && <p className="mt-1 text-xs text-red-500">{errors.descripcion}</p>}
                                </div>
                            </div>
                        </div>
                        <div className="bg-zinc-50 px-6 py-3 sm:flex sm:flex-row-reverse dark:bg-zinc-800/50 border-t border-zinc-100 dark:border-zinc-800/50">
                            <button type="button" onClick={handleGuardar} disabled={saving} className="inline-flex w-full items-center justify-center gap-2 rounded-md bg-zinc-900 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white sm:ml-3 sm:w-auto disabled:opacity-50 transition-colors">
                                {saving ? <Loader2 className="size-4 animate-spin" /> : <CheckCircle2 className="size-4" />}
                                {isEdit ? 'Actualizar' : 'Crear'}
                            </button>
                            <button type="button" onClick={onClose} className="mt-3 inline-flex w-full justify-center rounded-md bg-white px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-zinc-700 shadow-sm ring-1 ring-inset ring-zinc-300 hover:bg-zinc-50 sm:mt-0 sm:w-auto dark:bg-zinc-800 dark:text-zinc-300 dark:ring-zinc-700 dark:hover:bg-zinc-700 transition-colors">
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
    partidas_especificas,
    partidas,
    ejercicio,
    anio,
    anios_disponibles = [],
    filters = {},
}) {
    const [buscar, setBuscar] = useState(filters.buscar ?? '');
    const [showCrear, setShowCrear] = useState(false);
    const [editItem, setEditItem] = useState(null);
    const [deleteItem, setDeleteItem] = useState(null);
    const [deleting, setDeleting] = useState(false);

    const allRows = Array.isArray(partidas_especificas?.data) ? partidas_especificas.data : [];
    const partidaIdFiltro = filters.partida_id != null && filters.partida_id !== '' ? String(filters.partida_id) : '';

    const rows = buscar.trim() === ''
        ? allRows
        : allRows.filter((item) => {
            const q = buscar.toLowerCase();
            return (
                item.clave?.toLowerCase().includes(q) ||
                item.descripcion?.toLowerCase().includes(q)
            );
        });

    const navigate = (params) => {
        router.get(route('partidas-especificas.index'), params, { preserveState: true, replace: true });
    };

    const handleDelete = () => {
        if (!deleteItem) return;
        setDeleting(true);
        router.delete(route('partidas-especificas.destroy', deleteItem.id), {
            preserveScroll: true,
            onSuccess: () => { setDeleteItem(null); setDeleting(false); },
            onError: () => setDeleting(false),
        });
    };

    const closeForm = useCallback(() => {
        setShowCrear(false);
        setEditItem(null);
    }, []);

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                    <span>SIVSO</span>
                    <span className="text-zinc-300 dark:text-zinc-600">/</span>
                    <span className="text-zinc-900 dark:text-zinc-100">LÍNEAS PRESUPUESTALES</span>
                </div>
            }
        >
            <Head title="Líneas Presupuestales" />

            <div className="mx-auto w-full max-w-[1600px] space-y-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                            Líneas Presupuestales
                        </h2>
                        <p className="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            Catálogo de artículos por partida y ejercicio.
                        </p>
                    </div>
                    <button
                        onClick={() => setShowCrear(true)}
                        className="inline-flex items-center justify-center gap-2 rounded-md bg-zinc-900 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white"
                    >
                        <Plus className="size-4" strokeWidth={2} />
                        Nueva Línea
                    </button>
                </div>

                <FilterBar>
                    <div className="w-full sm:max-w-md">
                        <SearchInput
                            value={buscar}
                            onChange={setBuscar}
                            placeholder="Clave, descripción o no. partida…"
                        />
                    </div>
                    <div className="w-full sm:w-36">
                        <FilterSelect
                            label="Ejercicio"
                            value={String(anio)}
                            onChange={(v) => navigate({ anio: v, partida_id: partidaIdFiltro || undefined })}
                            options={anios_disponibles.map((a) => ({ value: String(a), label: String(a) }))}
                        />
                    </div>
                    <div className="w-full sm:w-56">
                        <FilterSelect
                            label="Partida"
                            value={partidaIdFiltro}
                            onChange={(v) => navigate({ anio, partida_id: v || undefined })}
                            options={[
                                { value: '', label: 'Todas las partidas' },
                                ...partidas.map((p) => ({
                                    value: String(p.id),
                                    label: `${p.no_partida}${p.descripcion ? ` — ${String(p.descripcion).slice(0, 40)}` : ''}`,
                                })),
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
                        <table className="min-w-[700px] w-full text-left">
                            <thead>
                                <tr className="border-b border-zinc-200 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-900/50">
                                    <th className="px-4 py-3 text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Clave</th>
                                    <th className="px-4 py-3 text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Descripción</th>
                                    <th className="px-4 py-3 text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Productos Total</th>
                                    <th className="px-4 py-3 text-right text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Acciones</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-zinc-100 dark:divide-zinc-800/50">
                                {rows.length === 0 && (
                                    <tr>
                                        <td colSpan={4} className="px-6 py-12 text-center text-sm text-zinc-500">
                                            No se encontraron líneas presupuestales.
                                        </td>
                                    </tr>
                                )}
                                {rows.map((item) => (
                                    <tr key={item.id} className="group transition-colors hover:bg-zinc-50/80 dark:hover:bg-zinc-900/80">
                                        <td className="px-4 py-3">
                                            <span className="text-xs font-bold uppercase tracking-wide text-zinc-900 dark:text-zinc-100">
                                                {item.clave}
                                            </span>
                                        </td>
                                        <td className="max-w-sm px-4 py-3">
                                            <p className="text-xs text-zinc-600 line-clamp-2 dark:text-zinc-300">
                                                {item.descripcion || '—'}
                                            </p>
                                        </td>
                                        <td className="px-4 py-3">
                                            <span className="inline-flex items-center gap-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-200">
                                                <FileText className="size-3.5 text-zinc-400" strokeWidth={2} />
                                                {item.productos_count}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            <div className="flex items-center justify-end gap-1">
                                                <button
                                                    onClick={() => setEditItem(item)}
                                                    className="rounded p-1.5 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-500 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                                                    title="Editar"
                                                >
                                                    <Edit3 className="size-4" strokeWidth={2} />
                                                </button>
                                                <button
                                                    onClick={() => setDeleteItem(item)}
                                                    className="rounded p-1.5 text-zinc-400 transition-colors hover:bg-red-50 hover:text-red-600 dark:text-zinc-500 dark:hover:bg-red-500/10 dark:hover:text-red-500"
                                                    title="Eliminar"
                                                >
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
                                No se encontraron líneas presupuestales.
                            </div>
                        )}
                        {rows.map((item) => (
                            <div key={item.id} className="space-y-1.5 p-4">
                                <div className="flex items-center gap-2">
                                    <span className="text-xs font-bold uppercase tracking-wide text-zinc-900 dark:text-zinc-100">
                                        {item.clave}
                                    </span>
                                </div>
                                <p className="text-xs text-zinc-600 line-clamp-2 dark:text-zinc-300">
                                    {item.descripcion || '—'}
                                </p>
                                <div className="flex items-center gap-3 pt-1">
                                    <span className="text-[10px] text-zinc-500">{item.productos_count} productos</span>
                                    <button onClick={() => setEditItem(item)} className="text-[10px] font-bold uppercase tracking-wider text-zinc-500 hover:text-zinc-700 dark:text-zinc-400">
                                        Editar
                                    </button>
                                    <button onClick={() => setDeleteItem(item)} className="text-[10px] font-bold uppercase tracking-wider text-red-500 hover:text-red-700">
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                        ))}
                    </div>

                    <Pagination paginator={partidas_especificas} />
                </div>
            </div>

            {(showCrear || editItem) && (
                <FormModal
                    key={editItem ? `edit-${editItem.id}` : 'create'}
                    item={editItem}
                    partidas={partidas}
                    anio={anio}
                    onClose={closeForm}
                />
            )}

            {deleteItem && (
                <div className="relative z-[100]" role="dialog" aria-modal="true">
                    <div className="fixed inset-0 bg-zinc-900/40 dark:bg-black/60" onClick={() => setDeleteItem(null)} />
                    <div className="fixed inset-0 z-[101] w-screen overflow-y-auto">
                        <div className="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                            <div className="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl dark:bg-zinc-900 sm:my-8 sm:w-full sm:max-w-lg border border-zinc-200/50 dark:border-zinc-800/50">
                                <div className="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 dark:bg-zinc-900">
                                    <div className="sm:flex sm:items-start">
                                        <div className="mx-auto flex size-12 shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:size-10 dark:bg-red-500/10">
                                            <AlertTriangle className="size-6 text-red-600 dark:text-red-500" strokeWidth={1.5} />
                                        </div>
                                        <div className="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                            <h3 className="text-base font-bold text-zinc-900 dark:text-zinc-100">
                                                Eliminar Línea Presupuestal
                                            </h3>
                                            <div className="mt-2">
                                                <p className="text-sm text-zinc-500 dark:text-zinc-400">
                                                    ¿Eliminar <span className="font-bold text-zinc-700 dark:text-zinc-300">{deleteItem.clave}</span>?
                                                    {deleteItem.productos_count > 0 && (
                                                        <span className="mt-1 block text-red-500">Tiene {deleteItem.productos_count} productos asociados, no se podrá eliminar.</span>
                                                    )}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div className="bg-zinc-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 dark:bg-zinc-800/50 border-t border-zinc-100 dark:border-zinc-800/50">
                                    <button type="button" onClick={handleDelete} disabled={deleting} className="inline-flex w-full justify-center rounded-md bg-red-600 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto disabled:opacity-50 transition-colors">
                                        Eliminar
                                    </button>
                                    <button type="button" onClick={() => setDeleteItem(null)} className="mt-3 inline-flex w-full justify-center rounded-md bg-white px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-zinc-700 shadow-sm ring-1 ring-inset ring-zinc-300 hover:bg-zinc-50 sm:mt-0 sm:w-auto dark:bg-zinc-800 dark:text-zinc-300 dark:ring-zinc-700 dark:hover:bg-zinc-700 transition-colors">
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

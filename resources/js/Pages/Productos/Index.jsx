import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Plus, CheckCircle2, Loader2, Copy, AlertCircle, LayoutList, Hash, ChevronDown } from 'lucide-react';
import { useState, useMemo, Fragment } from 'react';
import SearchInput from '@/Components/SearchInput';
import FilterBar from '@/Components/FilterBar';
import FilterSelect from '@/Components/FilterSelect';

const inputCls = 'w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:border-brand-gold focus:ring-1 focus:ring-brand-gold dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100';
const labelCls = 'mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400';

const fmtPrecio = (v) =>
    v != null
        ? Number(v).toLocaleString('es-MX', { style: 'currency', currency: 'MXN' })
        : '—';

function Pagination({ paginator }) {
    if (!paginator?.links?.length) return null;
    const from = paginator.from ?? 0;
    const to   = paginator.to   ?? 0;
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

function NuevoProductoModal({ partidas, tipos, ejercicio, onClose }) {
    const empty = {
        descripcion: '', partida_id: '', tipo_partida_especifica_id: '',
        marca: '', unidad_medida: '', codigo: '', medida: '',
        precio_unitario: '', proveedor: '',
    };
    const [form, setForm]     = useState(empty);
    const [saving, setSaving] = useState(false);
    const [errors, setErrors] = useState({});

    const set = (field) => (e) => {
        setForm(prev => ({ ...prev, [field]: e.target.value }));
        setErrors(prev => ({ ...prev, [field]: null }));
    };

    const validate = () => {
        const e = {};
        if (!form.descripcion.trim())            e.descripcion = 'Requerido.';
        if (!form.partida_id)                    e.partida_id  = 'Requerido.';
        if (!form.tipo_partida_especifica_id)    e.tipo_partida_especifica_id = 'Requerido.';
        if (!form.precio_unitario)               e.precio_unitario = 'Requerido.';
        else if (isNaN(Number(form.precio_unitario)) || Number(form.precio_unitario) < 0)
                                                 e.precio_unitario = 'Debe ser un número positivo.';
        return e;
    };

    const handleGuardar = () => {
        const e = validate();
        if (Object.keys(e).length) { setErrors(e); return; }
        setSaving(true);
        router.post(route('productos.store'), form, {
            onSuccess: () => { setSaving(false); onClose(); },
            onError:   (err) => { setErrors(err); setSaving(false); },
            preserveScroll: true,
        });
    };

    return (
        <div className="relative z-[100]" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div className="fixed inset-0 bg-zinc-900/40 dark:bg-black/60" onClick={onClose} />
            <div className="fixed inset-0 z-[101] w-screen overflow-y-auto">
                <div className="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div className="relative w-full transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all dark:bg-zinc-900 sm:my-8 sm:max-w-2xl border border-zinc-200/50 dark:border-zinc-800/50">
                        <div className="bg-white px-6 pb-4 pt-6 dark:bg-zinc-900">
                            <h3 className="text-base font-bold text-zinc-900 dark:text-zinc-100" id="modal-title">
                                Nuevo Producto — Ejercicio {ejercicio}
                            </h3>
                            <p className="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                Registra el producto y su precio para el ejercicio actual.
                            </p>

                            <div className="mt-5 space-y-4">
                                <div>
                                    <label className={labelCls}>Descripción *</label>
                                    <input type="text" value={form.descripcion} onChange={set('descripcion')} className={inputCls} placeholder="Ej. PANTALÓN TIPO CARGO..." />
                                    {errors.descripcion && <p className="mt-1 text-xs text-red-500">{errors.descripcion}</p>}
                                </div>

                                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <label className={labelCls}>Partida (no. presupuestal) *</label>
                                        <select value={form.partida_id} onChange={set('partida_id')} className={inputCls}>
                                            <option value="">Seleccionar partida…</option>
                                            {partidas.map(p => (
                                                <option key={p.id} value={p.id}>
                                                    {p.no_partida}
                                                    {p.descripcion ? ` — ${String(p.descripcion).slice(0, 72)}${String(p.descripcion).length > 72 ? '…' : ''}` : ''}
                                                    {p.clave_partida ? ` · ${String(p.clave_partida).slice(0, 24)}` : ''}
                                                </option>
                                            ))}
                                        </select>
                                        {errors.partida_id && <p className="mt-1 text-xs text-red-500">{errors.partida_id}</p>}
                                    </div>
                                    <div>
                                        <label className={labelCls}>Tipo partida específica (código catálogo) *</label>
                                        <select value={form.tipo_partida_especifica_id} onChange={set('tipo_partida_especifica_id')} className={inputCls}>
                                            <option value="">Seleccionar tipo…</option>
                                            {tipos.map(t => (
                                                <option key={t.id} value={t.id}>
                                                    {t.codigo} — {t.nombre}
                                                </option>
                                            ))}
                                        </select>
                                        {errors.tipo_partida_especifica_id && <p className="mt-1 text-xs text-red-500">{errors.tipo_partida_especifica_id}</p>}
                                    </div>
                                </div>

                                <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
                                    <div className="sm:col-span-2">
                                        <label className={labelCls}>Marca</label>
                                        <input type="text" value={form.marca} onChange={set('marca')} className={inputCls} placeholder="Ej. STANLEY" />
                                    </div>
                                    <div>
                                        <label className={labelCls}>Unidad</label>
                                        <input type="text" value={form.unidad_medida} onChange={set('unidad_medida')} className={inputCls} placeholder="PAR / PZA" />
                                    </div>
                                    <div>
                                        <label className={labelCls}>Medida</label>
                                        <input type="text" value={form.medida} onChange={set('medida')} className={inputCls} placeholder="M / L / XL" />
                                    </div>
                                </div>

                                <div>
                                    <label className={labelCls}>Código interno</label>
                                    <input type="text" value={form.codigo} onChange={set('codigo')} className={inputCls} placeholder="Opcional" />
                                </div>

                                <div className="border-t border-zinc-100 pt-4 dark:border-zinc-800">
                                    <p className="mb-3 text-[10px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                                        Precio Ejercicio {ejercicio}
                                    </p>
                                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <label className={labelCls}>Precio Unitario (MXN) *</label>
                                            <input type="number" step="0.01" min="0" value={form.precio_unitario} onChange={set('precio_unitario')} className={inputCls} placeholder="0.00" />
                                            {errors.precio_unitario && <p className="mt-1 text-xs text-red-500">{errors.precio_unitario}</p>}
                                        </div>
                                        <div>
                                            <label className={labelCls}>Proveedor</label>
                                            <input type="text" value={form.proveedor} onChange={set('proveedor')} className={inputCls} placeholder="Opcional" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="bg-zinc-50 px-6 py-3 sm:flex sm:flex-row-reverse dark:bg-zinc-800/50 border-t border-zinc-100 dark:border-zinc-800/50">
                            <button
                                type="button"
                                onClick={handleGuardar}
                                disabled={saving}
                                className="inline-flex w-full items-center justify-center gap-2 rounded-md bg-zinc-900 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white sm:ml-3 sm:w-auto disabled:opacity-50 transition-colors"
                            >
                                {saving ? <Loader2 className="size-4 animate-spin" /> : <CheckCircle2 className="size-4" />}
                                Guardar Producto
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

function ActivarModal({ producto, ejercicio, onClose }) {
    const [form, setForm]     = useState({ precio_unitario: '', proveedor: '' });
    const [saving, setSaving] = useState(false);
    const [errors, setErrors] = useState({});

    const set = (field) => (e) => {
        setForm(prev => ({ ...prev, [field]: e.target.value }));
        setErrors(prev => ({ ...prev, [field]: null }));
    };

    const handleGuardar = () => {
        if (!form.precio_unitario || isNaN(Number(form.precio_unitario)) || Number(form.precio_unitario) < 0) {
            setErrors({ precio_unitario: 'Ingresa un precio válido.' });
            return;
        }
        setSaving(true);
        router.post(route('productos.activar', producto.id), form, {
            onSuccess: () => { setSaving(false); onClose(); },
            onError:   (err) => { setErrors(err); setSaving(false); },
            preserveScroll: true,
        });
    };

    return (
        <div className="relative z-[100]" aria-labelledby="modal-activar" role="dialog" aria-modal="true">
            <div className="fixed inset-0 bg-zinc-900/40 dark:bg-black/60" onClick={onClose} />
            <div className="fixed inset-0 z-[101] w-screen overflow-y-auto">
                <div className="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div className="relative w-full transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all dark:bg-zinc-900 sm:my-8 sm:max-w-lg border border-zinc-200/50 dark:border-zinc-800/50">
                        <div className="bg-white px-6 pb-4 pt-6 dark:bg-zinc-900">
                            <h3 className="text-base font-bold text-zinc-900 dark:text-zinc-100" id="modal-activar">
                                Copiar a ejercicio {ejercicio}
                            </h3>
                            <p className="mt-1 text-sm text-zinc-500 dark:text-zinc-400 line-clamp-2">
                                {producto.descripcion}
                            </p>

                            <div className="mt-5 space-y-4">
                                <div>
                                    <label className={labelCls}>Precio Unitario {ejercicio} (MXN) *</label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        autoFocus
                                        value={form.precio_unitario}
                                        onChange={set('precio_unitario')}
                                        className={inputCls}
                                        placeholder={`Precio anterior: ${fmtPrecio(producto.precio_unitario)}`}
                                    />
                                    {errors.precio_unitario && <p className="mt-1 text-xs text-red-500">{errors.precio_unitario}</p>}
                                </div>
                                <div>
                                    <label className={labelCls}>Proveedor</label>
                                    <input
                                        type="text"
                                        value={form.proveedor}
                                        onChange={set('proveedor')}
                                        className={inputCls}
                                        placeholder={producto.proveedor || 'Opcional'}
                                    />
                                </div>
                            </div>
                        </div>

                        <div className="bg-zinc-50 px-6 py-3 sm:flex sm:flex-row-reverse dark:bg-zinc-800/50 border-t border-zinc-100 dark:border-zinc-800/50">
                            <button
                                type="button"
                                onClick={handleGuardar}
                                disabled={saving}
                                className="inline-flex w-full items-center justify-center gap-2 rounded-md bg-zinc-900 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white sm:ml-3 sm:w-auto disabled:opacity-50 transition-colors"
                            >
                                {saving ? <Loader2 className="size-4 animate-spin" /> : <Copy className="size-4" />}
                                Copiar
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

function buildProductosGrouped(items) {
    const byPartida = new Map();
    for (const it of items) {
        const pid = it.partida_id;
        if (!byPartida.has(pid)) {
            byPartida.set(pid, {
                no_partida: it.no_partida,
                partida_descripcion: it.partida_descripcion,
                clave_partida: it.clave_partida,
                tipos: new Map(),
            });
        }
        const g = byPartida.get(pid);
        const tid = it.tipo_partida_especifica_id ?? 'none';
        if (!g.tipos.has(tid)) {
            g.tipos.set(tid, {
                tipo_codigo: it.tipo_codigo,
                tipo_nombre: it.tipo_nombre,
                items: [],
            });
        }
        g.tipos.get(tid).items.push(it);
    }
    const sortedPartidas = [...byPartida.entries()].sort(
        (a, b) => (Number(a[1].no_partida) || 0) - (Number(b[1].no_partida) || 0)
    );
    return sortedPartidas.map(([partidaId, block]) => ({
        partidaId,
        no_partida: block.no_partida,
        partida_descripcion: block.partida_descripcion,
        clave_partida: block.clave_partida,
        tipoGroups: [...block.tipos.entries()]
            .sort((a, b) => String(a[1].tipo_codigo ?? '').localeCompare(String(b[1].tipo_codigo ?? ''), undefined, { numeric: true }))
            .map(([tipoKey, t]) => ({ tipoKey, ...t })),
    }));
}

export default function Index({ productos, partidas, tipos, ejercicio, anio, filters = {} }) {
    const [buscar, setBuscar] = useState(filters.buscar ?? '');
    const [showCrear, setShowCrear] = useState(false);
    const [activarItem, setActivarItem] = useState(null);
    const [expandedPartidas, setExpandedPartidas] = useState(new Set());
    const allRows = Array.isArray(productos?.data) ? productos.data : [];
    const partidaIdFiltro = filters.partida_id != null && filters.partida_id !== '' ? String(filters.partida_id) : '';

    const esAnioActual = anio === ejercicio;
    const anioAnterior = ejercicio - 1;

    const rows = useMemo(() => {
        if (!buscar.trim()) return allRows;
        const q = buscar.toLowerCase();
        return allRows.filter((item) => {
            return (
                item.descripcion?.toLowerCase().includes(q) ||
                item.marca?.toLowerCase().includes(q) ||
                item.codigo?.toLowerCase().includes(q) ||
                item.no_partida?.toString().includes(q) ||
                item.partida_descripcion?.toLowerCase().includes(q) ||
                item.clave_partida?.toLowerCase().includes(q) ||
                item.tipo_codigo?.toString().includes(q)
            );
        });
    }, [allRows, buscar]);

    const grouped = useMemo(() => buildProductosGrouped(rows), [rows]);

    const switchAnio = (a) => {
        router.get(
            route('productos.index'),
            {
                anio: a,
                buscar: buscar.trim() || undefined,
                partida_id: partidaIdFiltro || undefined,
            },
            { preserveState: true, replace: true }
        );
    };

    const setPartidaFiltro = (v) => {
        router.get(
            route('productos.index'),
            {
                anio,
                buscar: buscar.trim() || undefined,
                partida_id: v || undefined,
            },
            { preserveState: true, replace: true }
        );
    };

    // Auto-expand todas las partidas
    useMemo(() => {
        if (grouped.length > 0) {
            const allIds = new Set(grouped.map(b => b.partidaId));
            setExpandedPartidas(allIds);
        }
    }, [grouped]);

    const partidaSelectOptions = [
        { value: '', label: 'Todas las partidas' },
        ...partidas.map((p) => ({
            value: String(p.id),
            label: `${p.no_partida}${p.descripcion ? ` — ${String(p.descripcion).slice(0, 48)}${String(p.descripcion).length > 48 ? '…' : ''}` : ''}`,
        })),
    ];

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                    <span>SIVSO</span>
                    <span className="text-zinc-300 dark:text-zinc-600">/</span>
                    <span className="text-zinc-900 dark:text-zinc-100">PRODUCTOS</span>
                </div>
            }
        >
            <Head title="Productos" />

            <div className="mx-auto w-full max-w-6xl space-y-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-3xl font-bold text-zinc-900 dark:text-zinc-100">
                            Productos
                        </h1>
                        <p className="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            Catálogo de productos por partida presupuestal
                        </p>
                    </div>
                    {esAnioActual && (
                        <button
                            onClick={() => setShowCrear(true)}
                            className="inline-flex items-center justify-center gap-2 rounded-lg bg-zinc-900 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white"
                        >
                            <Plus className="size-4" strokeWidth={2} />
                            Nuevo Producto
                        </button>
                    )}
                </div>

                <div className="flex items-center gap-2 rounded-lg border border-zinc-200 bg-zinc-50 p-1 w-fit dark:border-zinc-800 dark:bg-zinc-900">
                    <button
                        onClick={() => switchAnio(anioAnterior)}
                        className={`rounded-md px-3 py-1.5 text-xs font-bold uppercase tracking-wider transition-colors ${
                            !esAnioActual
                                ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-800 dark:text-zinc-100'
                                : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'
                        }`}
                    >
                        {anioAnterior}
                    </button>
                    <button
                        onClick={() => switchAnio(ejercicio)}
                        className={`rounded-md px-3 py-1.5 text-xs font-bold uppercase tracking-wider transition-colors ${
                            esAnioActual
                                ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-800 dark:text-zinc-100'
                                : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'
                        }`}
                    >
                        {ejercicio}
                    </button>
                </div>

                <FilterBar>
                    <div className="grid w-full gap-4 sm:grid-cols-2">
                        <SearchInput
                            value={buscar}
                            onChange={setBuscar}
                            placeholder="Buscar producto, código, marca…"
                        />
                        <FilterSelect
                            label="Partida"
                            value={partidaIdFiltro}
                            onChange={setPartidaFiltro}
                            options={partidaSelectOptions}
                        />
                    </div>
                </FilterBar>

                <div className="space-y-3">
                    {grouped.length === 0 ? (
                        <div className="rounded-lg border border-zinc-200 bg-white p-12 text-center dark:border-zinc-800 dark:bg-zinc-900">
                            <p className="text-sm text-zinc-500 dark:text-zinc-400">
                                No hay productos en esta vista
                            </p>
                        </div>
                    ) : (
                        grouped.map((block) => (
                            <div
                                key={block.partidaId}
                                className="rounded-lg border border-zinc-200 bg-white overflow-hidden dark:border-zinc-800 dark:bg-zinc-900"
                            >
                                <button
                                    disabled
                                    className="w-full px-4 py-3 flex items-center justify-between hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors border-b border-zinc-100 dark:border-zinc-800 cursor-default"
                                >
                                    <div className="flex-1 text-left">
                                        <div className="flex items-center gap-2">
                                            <span className="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                                                Partida {block.no_partida}
                                            </span>
                                            {block.clave_partida && (
                                                <span className="text-xs text-zinc-500 dark:text-zinc-400">
                                                    {block.clave_partida}
                                                </span>
                                            )}
                                        </div>
                                        {block.partida_descripcion && (
                                            <p className="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400 line-clamp-1">
                                                {block.partida_descripcion}
                                            </p>
                                        )}
                                    </div>
                                    <ChevronDown
                                        className={`size-4 text-zinc-400 transition-transform ${
                                            expandedPartidas.has(block.partidaId) ? 'rotate-180' : ''
                                        }`}
                                        strokeWidth={2}
                                    />
                                </button>

                                {expandedPartidas.has(block.partidaId) && (
                                    <div className="divide-y divide-zinc-100 dark:divide-zinc-800">
                                        {block.tipoGroups.map((tg) => (
                                            <Fragment key={`${block.partidaId}-${tg.tipoKey}`}>
                                                {tg.tipoKey !== 'none' && (
                                                    <div className="px-4 py-2.5 bg-zinc-50 dark:bg-zinc-800/30 border-b border-zinc-100 dark:border-zinc-800">
                                                        <div className="flex items-baseline gap-2">
                                                            <span className="text-xs font-bold text-zinc-700 dark:text-zinc-200 bg-zinc-200 dark:bg-zinc-700 px-2 py-0.5 rounded">
                                                                {tg.tipo_codigo}
                                                            </span>
                                                            {tg.tipo_nombre && (
                                                                <p className="text-xs text-zinc-600 dark:text-zinc-400">
                                                                    {tg.tipo_nombre}
                                                                </p>
                                                            )}
                                                        </div>
                                                    </div>
                                                )}
                                                {tg.items.map((item) => (
                                                    <div
                                                        key={item.id}
                                                        className="px-4 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors"
                                                    >
                                                        <div className="flex items-start justify-between gap-4">
                                                            <div className="flex-1 min-w-0">
                                                                <p className="text-sm font-semibold text-zinc-900 dark:text-zinc-100 line-clamp-2">
                                                                    {item.descripcion}
                                                                </p>
                                                                <div className="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-xs text-zinc-500 dark:text-zinc-400">
                                                                    {item.marca && <span>{item.marca}</span>}
                                                                    {item.medida && <span>{item.medida}</span>}
                                                                    {item.unidad_medida && <span>{item.unidad_medida}</span>}
                                                                    {item.codigo && <span className="text-zinc-400 dark:text-zinc-500">#{item.codigo}</span>}
                                                                </div>
                                                            </div>
                                                            <div className="flex flex-col items-end gap-2 whitespace-nowrap">
                                                                <span className="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                                                                    {fmtPrecio(item.precio_unitario)}
                                                                </span>
                                                                {!esAnioActual && (
                                                                    <div>
                                                                        {item.ya_en_ejercicio_actual ? (
                                                                            <span className="text-xs font-semibold text-emerald-600 dark:text-emerald-500">
                                                                                ✓ En {ejercicio}
                                                                            </span>
                                                                        ) : (
                                                                            <button
                                                                                type="button"
                                                                                onClick={() => setActivarItem(item)}
                                                                                className="text-xs font-semibold text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200 transition-colors"
                                                                            >
                                                                                Copiar
                                                                            </button>
                                                                        )}
                                                                    </div>
                                                                )}
                                                            </div>
                                                        </div>
                                                        {item.proveedor && (
                                                            <p className="mt-2 text-xs text-zinc-400 dark:text-zinc-500">
                                                                Proveedor: {item.proveedor}
                                                            </p>
                                                        )}
                                                    </div>
                                                ))}
                                            </Fragment>
                                        ))}
                                    </div>
                                )}
                            </div>
                        ))
                    )}
                </div>

                <Pagination paginator={productos} />
            </div>

            {showCrear && (
                <NuevoProductoModal
                    partidas={partidas}
                    tipos={tipos}
                    ejercicio={ejercicio}
                    onClose={() => setShowCrear(false)}
                />
            )}

            {activarItem && (
                <ActivarModal
                    producto={activarItem}
                    ejercicio={ejercicio}
                    onClose={() => setActivarItem(null)}
                />
            )}
        </AuthenticatedLayout>
    );
}

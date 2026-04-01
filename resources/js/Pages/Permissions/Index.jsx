import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { Search, Plus, Eye, Edit3, Trash2, Key, AlertTriangle, CheckCircle2, Loader2 } from 'lucide-react';
import { useState, useMemo, useEffect, useCallback } from 'react';

const inputClass =
    'w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:border-brand-gold focus:ring-1 focus:ring-brand-gold dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100';
const labelClass =
    'mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400';

function FormPermissionModal({ permission, onClose }) {
    const isEdit = !!permission;
    const [form, setForm] = useState({
        name: permission?.name || '',
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

    const setName = (e) => {
        setForm({ name: e.target.value });
        setErrors((prev) => ({ ...prev, name: null }));
    };

    const handleGuardar = () => {
        setSaving(true);
        const payload = { name: form.name.trim() };
        const options = {
            onSuccess: () => {
                setSaving(false);
                onClose();
            },
            onError: (err) => {
                setErrors(err);
                setSaving(false);
            },
            preserveScroll: true,
        };
        if (isEdit) {
            router.put(route('permissions.update', permission.id), payload, options);
        } else {
            router.post(route('permissions.store'), payload, options);
        }
    };

    return (
        <div className="relative z-[100]" aria-labelledby="permission-form-modal-title" role="dialog" aria-modal="true">
            <div className="fixed inset-0 bg-zinc-900/40 transition-opacity dark:bg-black/60" onClick={onClose} />
            <div className="fixed inset-0 z-[101] w-screen overflow-y-auto">
                <div className="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div className="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all dark:bg-zinc-900 sm:my-8 sm:w-full sm:max-w-lg border border-zinc-200/50 dark:border-zinc-800/50">
                        <div className="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 dark:bg-zinc-900">
                            <div>
                                <h3
                                    className="text-base font-bold text-zinc-900 dark:text-zinc-100"
                                    id="permission-form-modal-title"
                                >
                                    {isEdit ? 'Editar Permiso' : 'Nuevo Permiso'}
                                </h3>
                                <p className="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                    {isEdit
                                        ? 'Actualiza el nombre del permiso (texto claro en español, p. ej. Ver reportes).'
                                        : 'Registra un permiso nuevo para asignarlo a roles. Usa un nombre descriptivo en español.'}
                                </p>
                            </div>

                            <div className="mt-4 space-y-4">
                                <div>
                                    <label className={labelClass}>Nombre del Permiso</label>
                                    <input
                                        type="text"
                                        value={form.name}
                                        onChange={setName}
                                        placeholder="ej. Ver reportes de inventario"
                                        className={inputClass}
                                    />
                                    {errors.name && <p className="mt-1 text-xs text-red-500">{errors.name}</p>}
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
                                {isEdit ? 'Actualizar' : 'Crear Permiso'}
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

export default function Index({ permissions }) {
    const [search, setSearch] = useState('');
    const [permissionToDelete, setPermissionToDelete] = useState(null);
    const [isCreating, setIsCreating] = useState(false);
    const [permissionToEdit, setPermissionToEdit] = useState(null);

    const closePermissionFormModal = useCallback(() => {
        setIsCreating(false);
        setPermissionToEdit(null);
    }, []);

    const { delete: destroy, processing } = useForm();

    const confirmDelete = (p) => {
        setPermissionToDelete(p);
    };

    const handleDelete = () => {
        if (permissionToDelete) {
            destroy(route('permissions.destroy', permissionToDelete.id), {
                onSuccess: () => setPermissionToDelete(null),
            });
        }
    };

    const filtered = useMemo(() => {
        if (!search.trim()) return permissions;
        const q = search.toLowerCase();
        return permissions.filter((p) => p.name?.toLowerCase().includes(q));
    }, [permissions, search]);

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                    <span>SIVSO</span>
                    <span className="text-zinc-300 dark:text-zinc-600">/</span>
                    <span className="text-zinc-900 dark:text-zinc-100">PERMISOS</span>
                </div>
            }
        >
            <Head title="Permisos" />

            <div className="mx-auto w-full max-w-[1600px] space-y-8">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">Permisos</h2>
                        <p className="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            Gestión de permisos individuales del sistema.
                        </p>
                    </div>
                    <button
                        type="button"
                        onClick={() => setIsCreating(true)}
                        className="inline-flex items-center justify-center gap-2 rounded-md bg-zinc-900 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white"
                    >
                        <Plus className="size-4" strokeWidth={2} />
                        Nuevo Permiso
                    </button>
                </div>

                <div className="flex flex-col gap-4 sm:flex-row sm:items-end">
                    <div className="w-full sm:max-w-md">
                        <label className="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Buscar Permiso
                        </label>
                        <div className="relative">
                            <Search
                                className="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-zinc-400"
                                strokeWidth={2}
                            />
                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Nombre del permiso..."
                                className="w-full rounded-lg border border-zinc-200 bg-white/80 py-2.5 pl-10 pr-4 text-sm text-zinc-900 outline-none transition-all placeholder:text-zinc-400 focus:border-brand-gold focus:ring-1 focus:ring-brand-gold dark:border-zinc-800 dark:bg-zinc-900/80 dark:text-zinc-100 dark:placeholder:text-zinc-500"
                            />
                        </div>
                    </div>
                </div>

                <div className="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-[#0A0A0B]">
                    <div className="border-b border-zinc-200 px-4 py-4 sm:px-6 dark:border-zinc-800">
                        <h3 className="text-sm font-bold uppercase tracking-wider text-zinc-800 dark:text-zinc-200">
                            Permisos ({filtered.length})
                        </h3>
                    </div>

                    <div className="hidden md:block overflow-x-auto">
                        <table className="w-full text-left min-w-[600px]">
                            <thead>
                                <tr className="border-b border-zinc-200 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-900/50">
                                    <th className="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        Permiso
                                    </th>
                                    <th className="px-6 py-3 text-right text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-zinc-100 dark:divide-zinc-800/50">
                                {filtered.length === 0 && (
                                    <tr>
                                        <td colSpan={2} className="px-6 py-12 text-center text-sm text-zinc-500">
                                            No se encontraron permisos.
                                        </td>
                                    </tr>
                                )}
                                {filtered.map((p) => (
                                    <tr
                                        key={p.id}
                                        className="group transition-colors hover:bg-zinc-50/80 dark:hover:bg-zinc-900/80"
                                    >
                                        <td className="px-6 py-4">
                                            <div className="flex items-center gap-3">
                                                <div className="flex size-8 shrink-0 items-center justify-center rounded-lg bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                                                    <Key className="size-4" strokeWidth={2} />
                                                </div>
                                                <span className="text-xs font-bold tracking-wide text-zinc-900 dark:text-zinc-100">
                                                    {p.name}
                                                </span>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 text-right">
                                            <div className="flex items-center justify-end gap-2">
                                                <Link
                                                    href={route('permissions.show', p.id)}
                                                    className="rounded p-1.5 text-emerald-600 transition-colors hover:bg-emerald-50 dark:text-emerald-500 dark:hover:bg-emerald-500/10"
                                                    title="Ver detalles"
                                                >
                                                    <Eye className="size-4" strokeWidth={2} />
                                                </Link>
                                                <button
                                                    type="button"
                                                    onClick={() => setPermissionToEdit(p)}
                                                    className="rounded p-1.5 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-500 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                                                    title="Editar"
                                                >
                                                    <Edit3 className="size-4" strokeWidth={2} />
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={() => confirmDelete(p)}
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

                    <div className="md:hidden flex flex-col divide-y divide-zinc-100 dark:divide-zinc-800/50">
                        {filtered.length === 0 && (
                            <div className="px-4 py-12 text-center text-sm text-zinc-500">No se encontraron permisos.</div>
                        )}
                        {filtered.map((p) => (
                            <div
                                key={p.id}
                                className="flex flex-col gap-3 p-4 transition-colors hover:bg-zinc-50/80 dark:hover:bg-zinc-900/80"
                            >
                                <div className="flex items-center gap-3">
                                    <div className="flex size-8 shrink-0 items-center justify-center rounded-lg bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                                        <Key className="size-4" strokeWidth={2} />
                                    </div>
                                    <div className="flex min-w-0 flex-col">
                                        <span className="truncate text-xs font-bold tracking-wide text-zinc-900 dark:text-zinc-100">
                                            {p.name}
                                        </span>
                                    </div>
                                </div>

                                <div className="mt-1 flex items-center justify-end gap-2 border-t border-zinc-100/50 pt-2 dark:border-zinc-800/30">
                                    <Link
                                        href={route('permissions.show', p.id)}
                                        className="flex items-center gap-1.5 rounded-md px-2 py-1.5 text-[10px] font-bold uppercase tracking-wider text-emerald-600 transition-colors hover:bg-emerald-50 dark:text-emerald-500 dark:hover:bg-emerald-500/10"
                                    >
                                        <Eye className="size-3.5" strokeWidth={2} />
                                        Ver
                                    </Link>
                                    <button
                                        type="button"
                                        onClick={() => setPermissionToEdit(p)}
                                        className="flex items-center gap-1.5 rounded-md px-2 py-1.5 text-[10px] font-bold uppercase tracking-wider text-zinc-500 transition-colors hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                                    >
                                        <Edit3 className="size-3.5" strokeWidth={2} />
                                        Editar
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => confirmDelete(p)}
                                        className="flex items-center gap-1.5 rounded-md px-2 py-1.5 text-[10px] font-bold uppercase tracking-wider text-zinc-500 transition-colors hover:bg-red-50 hover:text-red-600 dark:text-zinc-400 dark:hover:bg-red-500/10 dark:hover:text-red-500"
                                    >
                                        <Trash2 className="size-3.5" strokeWidth={2} />
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>

            {(isCreating || permissionToEdit) && (
                <FormPermissionModal
                    key={permissionToEdit ? `edit-${permissionToEdit.id}` : 'create'}
                    permission={permissionToEdit}
                    onClose={closePermissionFormModal}
                />
            )}

            {permissionToDelete && (
                <div className="relative z-[100]" aria-labelledby="permission-delete-modal-title" role="dialog" aria-modal="true">
                    <div
                        className="fixed inset-0 bg-zinc-900/40 transition-opacity dark:bg-black/60"
                        onClick={() => setPermissionToDelete(null)}
                    />
                    <div className="fixed inset-0 z-[101] w-screen overflow-y-auto">
                        <div className="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                            <div className="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all dark:bg-zinc-900 sm:my-8 sm:w-full sm:max-w-lg border border-zinc-200/50 dark:border-zinc-800/50">
                                <div className="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 dark:bg-zinc-900">
                                    <div className="sm:flex sm:items-start">
                                        <div className="mx-auto flex size-12 shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:size-10 dark:bg-red-500/10">
                                            <AlertTriangle className="size-6 text-red-600 dark:text-red-500" strokeWidth={1.5} />
                                        </div>
                                        <div className="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                            <h3
                                                className="text-base font-bold text-zinc-900 dark:text-zinc-100"
                                                id="permission-delete-modal-title"
                                            >
                                                Eliminar Permiso
                                            </h3>
                                            <div className="mt-2">
                                                <p className="text-sm text-zinc-500 dark:text-zinc-400">
                                                    ¿Estás seguro de que deseas eliminar el permiso{' '}
                                                    <span className="font-bold text-zinc-700 dark:text-zinc-300">
                                                        {permissionToDelete.name}
                                                    </span>
                                                    ? Esta acción no se puede deshacer.
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
                                        onClick={() => setPermissionToDelete(null)}
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

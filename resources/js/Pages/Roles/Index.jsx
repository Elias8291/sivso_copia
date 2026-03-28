import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { Search, Plus, Eye, Edit3, Trash2, ShieldCheck, AlertTriangle, CheckCircle2, Loader2 } from 'lucide-react';
import { useState, useMemo, useEffect, useCallback } from 'react';

const inputClass =
    'w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:border-brand-gold focus:ring-1 focus:ring-brand-gold dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100';
const labelClass =
    'mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400';

function FormRoleModal({ role, permissionsList, onClose }) {
    const isEdit = !!role;
    const [form, setForm] = useState({
        name: role?.name || '',
        permissions: Array.isArray(role?.permission_ids) ? [...role.permission_ids] : [],
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
        setForm((prev) => ({ ...prev, name: e.target.value.toUpperCase() }));
        setErrors((prev) => ({ ...prev, name: null }));
    };

    const togglePermission = (id) => {
        setForm((prev) => ({
            ...prev,
            permissions: prev.permissions.includes(id)
                ? prev.permissions.filter((p) => p !== id)
                : [...prev.permissions, id],
        }));
        setErrors((prev) => ({ ...prev, permissions: null }));
    };

    const handleGuardar = () => {
        setSaving(true);
        const payload = {
            name: form.name.trim(),
            permissions: form.permissions,
        };
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
            router.put(route('roles.update', role.id), payload, options);
        } else {
            router.post(route('roles.store'), payload, options);
        }
    };

    return (
        <div className="relative z-[100]" aria-labelledby="role-form-modal-title" role="dialog" aria-modal="true">
            <div className="fixed inset-0 bg-zinc-900/40 transition-opacity dark:bg-black/60" onClick={onClose} />
            <div className="fixed inset-0 z-[101] w-screen overflow-y-auto">
                <div className="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div className="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all dark:bg-zinc-900 sm:my-8 sm:w-full sm:max-w-3xl border border-zinc-200/50 dark:border-zinc-800/50">
                        <div className="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 dark:bg-zinc-900">
                            <div>
                                <h3
                                    className="text-base font-bold text-zinc-900 dark:text-zinc-100"
                                    id="role-form-modal-title"
                                >
                                    {isEdit ? 'Editar Rol' : 'Nuevo Rol'}
                                </h3>
                                <p className="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                    {isEdit
                                        ? 'Modifica el nombre del rol y sus permisos asignados.'
                                        : 'Define el nombre del rol y selecciona los permisos que aplican.'}
                                </p>
                            </div>

                            <div className="mt-4 space-y-4">
                                <div>
                                    <label className={labelClass}>Nombre del Rol</label>
                                    <input type="text" value={form.name} onChange={setName} className={`${inputClass} uppercase`} />
                                    {errors.name && <p className="mt-1 text-xs text-red-500">{errors.name}</p>}
                                </div>
                                <div>
                                    <label className={labelClass}>Permisos Asignados</label>
                                    {permissionsList.length === 0 ? (
                                        <p className="text-xs text-zinc-500 dark:text-zinc-400">No hay permisos en el sistema.</p>
                                    ) : (
                                        <div className="max-h-56 overflow-y-auto rounded-lg border border-zinc-200/80 p-3 dark:border-zinc-800/80">
                                            <div className="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                                {permissionsList.map((p) => (
                                                    <label
                                                        key={p.id}
                                                        className="flex cursor-pointer items-center gap-3 rounded-lg border border-zinc-200/60 p-2.5 transition-colors hover:bg-zinc-50 dark:border-zinc-800/60 dark:hover:bg-zinc-900/50"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            checked={form.permissions.includes(p.id)}
                                                            onChange={() => togglePermission(p.id)}
                                                            className="size-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:checked:bg-zinc-100 dark:focus:ring-zinc-100"
                                                        />
                                                        <span className="text-left text-xs font-medium text-zinc-700 dark:text-zinc-300">
                                                            {p.name}
                                                        </span>
                                                    </label>
                                                ))}
                                            </div>
                                        </div>
                                    )}
                                    {errors.permissions && <p className="mt-1 text-xs text-red-500">{errors.permissions}</p>}
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
                                {isEdit ? 'Actualizar' : 'Crear Rol'}
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

export default function Index({ roles, permissions: permissionsList }) {
    const [search, setSearch] = useState('');
    const [roleToDelete, setRoleToDelete] = useState(null);
    const [isCreating, setIsCreating] = useState(false);
    const [roleToEdit, setRoleToEdit] = useState(null);

    const closeRoleFormModal = useCallback(() => {
        setIsCreating(false);
        setRoleToEdit(null);
    }, []);

    const { delete: destroy, processing } = useForm();

    const confirmDelete = (role) => {
        setRoleToDelete(role);
    };

    const handleDelete = () => {
        if (roleToDelete) {
            destroy(route('roles.destroy', roleToDelete.id), {
                onSuccess: () => setRoleToDelete(null),
            });
        }
    };

    const filtered = useMemo(() => {
        if (!search.trim()) return roles;
        const q = search.toLowerCase();
        return roles.filter((r) => r.name?.toLowerCase().includes(q));
    }, [roles, search]);

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                    <span>SIVSO</span>
                    <span className="text-zinc-300 dark:text-zinc-600">/</span>
                    <span className="text-zinc-900 dark:text-zinc-100">ROLES</span>
                </div>
            }
        >
            <Head title="Roles" />

            <div className="mx-auto w-full max-w-[1600px] space-y-8">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">Roles</h2>
                        <p className="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            Gestión de roles y niveles de acceso.
                        </p>
                    </div>
                    <button
                        type="button"
                        onClick={() => setIsCreating(true)}
                        className="inline-flex items-center justify-center gap-2 rounded-md bg-zinc-900 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white"
                    >
                        <Plus className="size-4" strokeWidth={2} />
                        Nuevo Rol
                    </button>
                </div>

                <div className="flex flex-col gap-4 sm:flex-row sm:items-end">
                    <div className="w-full sm:max-w-md">
                        <label className="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Buscar Rol
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
                                placeholder="Nombre del rol..."
                                className="w-full rounded-lg border border-zinc-200 bg-white/80 py-2.5 pl-10 pr-4 text-sm text-zinc-900 outline-none transition-all placeholder:text-zinc-400 focus:border-brand-gold focus:ring-1 focus:ring-brand-gold dark:border-zinc-800 dark:bg-zinc-900/80 dark:text-zinc-100 dark:placeholder:text-zinc-500"
                            />
                        </div>
                    </div>
                </div>

                <div className="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-[#0A0A0B]">
                    <div className="border-b border-zinc-200 px-4 py-4 sm:px-6 dark:border-zinc-800">
                        <h3 className="text-sm font-bold uppercase tracking-wider text-zinc-800 dark:text-zinc-200">
                            Roles ({filtered.length})
                        </h3>
                    </div>

                    <div className="hidden md:block overflow-x-auto">
                        <table className="w-full text-left min-w-[600px]">
                            <thead>
                                <tr className="border-b border-zinc-200 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-900/50">
                                    <th className="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        Rol
                                    </th>
                                    <th className="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        Permisos Asignados
                                    </th>
                                    <th className="px-6 py-3 text-right text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-zinc-100 dark:divide-zinc-800/50">
                                {filtered.length === 0 && (
                                    <tr>
                                        <td colSpan={3} className="px-6 py-12 text-center text-sm text-zinc-500">
                                            No se encontraron roles.
                                        </td>
                                    </tr>
                                )}
                                {filtered.map((r) => (
                                    <tr
                                        key={r.id}
                                        className="group transition-colors hover:bg-zinc-50/80 dark:hover:bg-zinc-900/80"
                                    >
                                        <td className="px-6 py-4">
                                            <div className="flex items-center gap-3">
                                                <div className="flex size-8 shrink-0 items-center justify-center rounded-lg bg-brand-gold/10 text-brand-gold dark:bg-brand-gold/20 dark:text-brand-gold-soft">
                                                    <ShieldCheck className="size-4" strokeWidth={2} />
                                                </div>
                                                <span className="text-xs font-bold uppercase tracking-wide text-zinc-900 dark:text-zinc-100">
                                                    {r.name}
                                                </span>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4">
                                            <span className="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-1 text-[10px] font-bold tracking-wider text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                                {r.permissions_count} permisos
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 text-right">
                                            <div className="flex items-center justify-end gap-2">
                                                <Link
                                                    href={route('roles.show', r.id)}
                                                    className="rounded p-1.5 text-emerald-600 transition-colors hover:bg-emerald-50 dark:text-emerald-500 dark:hover:bg-emerald-500/10"
                                                    title="Ver detalles"
                                                >
                                                    <Eye className="size-4" strokeWidth={2} />
                                                </Link>
                                                <button
                                                    type="button"
                                                    onClick={() => setRoleToEdit(r)}
                                                    className="rounded p-1.5 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-500 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                                                    title="Editar"
                                                >
                                                    <Edit3 className="size-4" strokeWidth={2} />
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={() => confirmDelete(r)}
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
                            <div className="px-4 py-12 text-center text-sm text-zinc-500">No se encontraron roles.</div>
                        )}
                        {filtered.map((r) => (
                            <div
                                key={r.id}
                                className="flex flex-col gap-3 p-4 transition-colors hover:bg-zinc-50/80 dark:hover:bg-zinc-900/80"
                            >
                                <div className="flex items-center gap-3">
                                    <div className="flex size-8 shrink-0 items-center justify-center rounded-lg bg-brand-gold/10 text-brand-gold dark:bg-brand-gold/20 dark:text-brand-gold-soft">
                                        <ShieldCheck className="size-4" strokeWidth={2} />
                                    </div>
                                    <div className="flex min-w-0 flex-col">
                                        <span className="truncate text-xs font-bold uppercase tracking-wide text-zinc-900 dark:text-zinc-100">
                                            {r.name}
                                        </span>
                                        <span className="text-[10px] font-medium tracking-wider text-zinc-500 dark:text-zinc-400">
                                            {r.permissions_count} permisos
                                        </span>
                                    </div>
                                </div>

                                <div className="mt-1 flex items-center justify-end gap-2 border-t border-zinc-100/50 pt-2 dark:border-zinc-800/30">
                                    <Link
                                        href={route('roles.show', r.id)}
                                        className="flex items-center gap-1.5 rounded-md px-2 py-1.5 text-[10px] font-bold uppercase tracking-wider text-emerald-600 transition-colors hover:bg-emerald-50 dark:text-emerald-500 dark:hover:bg-emerald-500/10"
                                    >
                                        <Eye className="size-3.5" strokeWidth={2} />
                                        Ver
                                    </Link>
                                    <button
                                        type="button"
                                        onClick={() => setRoleToEdit(r)}
                                        className="flex items-center gap-1.5 rounded-md px-2 py-1.5 text-[10px] font-bold uppercase tracking-wider text-zinc-500 transition-colors hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                                    >
                                        <Edit3 className="size-3.5" strokeWidth={2} />
                                        Editar
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => confirmDelete(r)}
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

            {(isCreating || roleToEdit) && (
                <FormRoleModal
                    key={roleToEdit ? `edit-${roleToEdit.id}` : 'create'}
                    role={roleToEdit}
                    permissionsList={permissionsList}
                    onClose={closeRoleFormModal}
                />
            )}

            {roleToDelete && (
                <div className="relative z-[100]" aria-labelledby="role-delete-modal-title" role="dialog" aria-modal="true">
                    <div
                        className="fixed inset-0 bg-zinc-900/40 transition-opacity dark:bg-black/60"
                        onClick={() => setRoleToDelete(null)}
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
                                                id="role-delete-modal-title"
                                            >
                                                Eliminar Rol
                                            </h3>
                                            <div className="mt-2">
                                                <p className="text-sm text-zinc-500 dark:text-zinc-400">
                                                    ¿Estás seguro de que deseas eliminar el rol{' '}
                                                    <span className="font-bold text-zinc-700 dark:text-zinc-300">
                                                        {roleToDelete.name}
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
                                        onClick={() => setRoleToDelete(null)}
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

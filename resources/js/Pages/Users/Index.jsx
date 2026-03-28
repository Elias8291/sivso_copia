import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm as useInertiaForm, router } from '@inertiajs/react';
import { Search, Plus, Eye, Edit3, Trash2, ChevronDown, AlertTriangle, CheckCircle2, Loader2 } from 'lucide-react';
import { useState, useMemo, useEffect, useCallback } from 'react';

/* ── Badges ──────────────────────────────────────────────── */
function StatusBadge({ activo }) {
    return (
        <span className="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
            <span className={`size-1.5 rounded-full ${activo ? 'bg-zinc-900 dark:bg-zinc-100' : 'bg-zinc-400 dark:bg-zinc-500'}`} />
            {activo ? 'Activo' : 'Inactivo'}
        </span>
    );
}

function RoleBadge({ role }) {
    return (
        <span className="inline-flex items-center rounded-md bg-brand-gold/10 px-2 py-1 text-[9px] font-bold uppercase tracking-wider text-brand-gold dark:bg-brand-gold/20 dark:text-brand-gold-soft">
            {role}
        </span>
    );
}

// Simple user icon for the select input
function UserIcon(props) {
    return (
        <svg
            {...props}
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="2"
            strokeLinecap="round"
            strokeLinejoin="round"
        >
            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
            <circle cx="12" cy="7" r="4" />
        </svg>
    );
}

/* ── Modal: Crear/Editar Usuario ─────────────────────────── */
function FormUserModal({ user, roles, onClose }) {
    const isEdit = !!user;

    const [form, setForm] = useState({
        name: user?.name || '',
        rfc: user?.rfc || '',
        nue: user?.nue || '',
        email: user?.email || '',
        password: '',
        password_confirmation: '',
        activo: user ? (user.activo ? '1' : '0') : '1',
        roles: Array.isArray(user?.roles) ? [...user.roles] : [],
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

    const handleRoleToggle = (role) => {
        setForm(prev => {
            const has = prev.roles.includes(role);
            return {
                ...prev,
                roles: has ? prev.roles.filter(r => r !== role) : [...prev.roles, role]
            };
        });
    };

    const handleGuardar = () => {
        setSaving(true);
        const payload = {
            ...form,
            activo: form.activo === '1'
        };

        if (isEdit && !payload.password) {
            delete payload.password;
            delete payload.password_confirmation;
        }

        const options = {
            onSuccess: () => { setSaving(false); onClose(); },
            onError: (err) => { setErrors(err); setSaving(false); },
            preserveScroll: true,
        };

        if (isEdit) {
            router.put(route('users.update', user.id), payload, options);
        } else {
            router.post(route('users.store'), payload, options);
        }
    };

    const inputClass =
        'w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:border-brand-gold focus:ring-1 focus:ring-brand-gold dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100';
    const labelClass =
        'mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400';

    return (
        <div className="relative z-[100]" aria-labelledby="user-modal-title" role="dialog" aria-modal="true">
            <div className="fixed inset-0 bg-zinc-900/40 transition-opacity dark:bg-black/60" onClick={onClose} />
            <div className="fixed inset-0 z-[101] w-screen overflow-y-auto">
                <div className="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div className="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all dark:bg-zinc-900 sm:my-8 sm:w-full sm:max-w-3xl border border-zinc-200/50 dark:border-zinc-800/50">
                        <div className="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 dark:bg-zinc-900">
                            <div>
                                <h3 className="text-base font-bold text-zinc-900 dark:text-zinc-100" id="user-modal-title">
                                    {isEdit ? 'Editar Usuario' : 'Nuevo Usuario'}
                                </h3>
                                <p className="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                    {isEdit
                                        ? 'Actualiza los datos y roles. La contraseña es opcional.'
                                        : 'Completa el perfil, credenciales y roles del usuario.'}
                                </p>
                            </div>

                            <div className="mt-4 space-y-4">
                                <div>
                                    <label className={labelClass}>Nombre Completo</label>
                                    <input type="text" value={form.name} onChange={set('name')} className={inputClass} />
                                    {errors.name && <p className="mt-1 text-xs text-red-500">{errors.name}</p>}
                                </div>
                                <div>
                                    <label className={labelClass}>Correo Electrónico</label>
                                    <input type="email" value={form.email} onChange={set('email')} className={inputClass} />
                                    {errors.email && <p className="mt-1 text-xs text-red-500">{errors.email}</p>}
                                </div>
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <label className={labelClass}>RFC</label>
                                        <input type="text" value={form.rfc} onChange={set('rfc')} className={`${inputClass} uppercase`} />
                                        {errors.rfc && <p className="mt-1 text-xs text-red-500">{errors.rfc}</p>}
                                    </div>
                                    <div>
                                        <label className={labelClass}>NUE</label>
                                        <input type="text" value={form.nue} onChange={set('nue')} className={`${inputClass} uppercase`} />
                                        {errors.nue && <p className="mt-1 text-xs text-red-500">{errors.nue}</p>}
                                    </div>
                                </div>
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <label className={labelClass}>{isEdit ? 'Nueva Contraseña' : 'Contraseña'}</label>
                                        <input
                                            type="password"
                                            value={form.password}
                                            onChange={set('password')}
                                            placeholder={isEdit ? 'Dejar en blanco si no se cambia' : ''}
                                            className={inputClass}
                                        />
                                        {errors.password && <p className="mt-1 text-xs text-red-500">{errors.password}</p>}
                                    </div>
                                    <div>
                                        <label className={labelClass}>Confirmar</label>
                                        <input
                                            type="password"
                                            value={form.password_confirmation}
                                            onChange={set('password_confirmation')}
                                            className={inputClass}
                                        />
                                        {errors.password_confirmation && (
                                            <p className="mt-1 text-xs text-red-500">{errors.password_confirmation}</p>
                                        )}
                                    </div>
                                </div>
                                <div>
                                    <label className={labelClass}>Estado</label>
                                    <select value={form.activo} onChange={set('activo')} className={inputClass}>
                                        <option value="1">Activo — permitir acceso al sistema</option>
                                        <option value="0">Inactivo — bloquear acceso temporalmente</option>
                                    </select>
                                </div>
                                <div>
                                    <label className={labelClass}>Roles Asignados</label>
                                    {roles.length === 0 ? (
                                        <p className="text-xs text-zinc-500 dark:text-zinc-400">No hay roles definidos en el sistema.</p>
                                    ) : (
                                        <div className="flex flex-wrap gap-2 pt-1">
                                            {roles.map((role) => {
                                                const isSelected = form.roles.includes(role);
                                                return (
                                                    <label
                                                        key={role}
                                                        className={`cursor-pointer inline-flex items-center rounded-lg border px-3 py-1.5 text-xs font-bold uppercase tracking-wider transition-colors ${
                                                            isSelected
                                                                ? 'border-brand-gold bg-brand-gold/10 text-brand-gold dark:bg-brand-gold/20'
                                                                : 'border-zinc-200 bg-zinc-50 text-zinc-400 hover:bg-zinc-100 dark:border-zinc-700/60 dark:bg-zinc-800 dark:text-zinc-500 hover:dark:bg-zinc-700'
                                                        }`}
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            className="sr-only"
                                                            checked={isSelected}
                                                            onChange={() => handleRoleToggle(role)}
                                                        />
                                                        {role}
                                                    </label>
                                                );
                                            })}
                                        </div>
                                    )}
                                    {errors.roles && <p className="mt-1 text-xs text-red-500">{errors.roles}</p>}
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
                                {isEdit ? 'Actualizar' : 'Crear Usuario'}
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

export default function Index({ users, roles }) {
    const [search, setSearch] = useState('');
    const [statusFilter, setStatusFilter] = useState('Todos');
    
    const [isCreating, setIsCreating] = useState(false);
    const [userAEditar, setAEditar] = useState(null);
    const [userToDelete, setUserToDelete] = useState(null);

    const closeUserFormModal = useCallback(() => {
        setIsCreating(false);
        setAEditar(null);
    }, []);

    const { delete: destroy, processing } = useInertiaForm();

    const handleDelete = () => {
        if (userToDelete) {
            destroy(route('users.destroy', userToDelete.id), {
                onSuccess: () => setUserToDelete(null),
                preserveScroll: true,
            });
        }
    };

    const filtered = useMemo(() => {
        let result = users;
        
        if (statusFilter !== 'Todos') {
            const isActive = statusFilter === 'Activos';
            result = result.filter(u => !!u.activo === isActive);
        }

        if (!search.trim()) return result;
        const q = search.toLowerCase();
        return result.filter(
            (u) =>
                u.name?.toLowerCase().includes(q) ||
                u.rfc?.toLowerCase().includes(q) ||
                u.email?.toLowerCase().includes(q)
        );
    }, [users, search, statusFilter]);

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                    <span>SIVSO</span>
                    <span className="text-zinc-300 dark:text-zinc-600">/</span>
                    <span className="text-zinc-900 dark:text-zinc-100">USUARIOS</span>
                </div>
            }
        >
            <Head title="Usuarios" />

            <div className="mx-auto w-full max-w-[1600px] space-y-8">
                {/* Header Section */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                            Usuarios
                        </h2>
                        <p className="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            Gestión de cuentas de acceso al sistema y roles.
                        </p>
                    </div>
                    <button onClick={() => setIsCreating(true)} className="inline-flex items-center justify-center gap-2 rounded-md bg-zinc-900 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white">
                        <Plus className="size-4" strokeWidth={2} />
                        Nuevo Usuario
                    </button>
                </div>

                {/* Filters */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-end">
                    <div className="w-full sm:max-w-md">
                        <label className="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Buscar Usuario
                        </label>
                        <div className="relative">
                            <Search className="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-zinc-400" strokeWidth={2} />
                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Nombre, RFC o correo..."
                                className="w-full rounded-lg border border-zinc-200 bg-white/80 py-2.5 pl-10 pr-4 text-sm text-zinc-900 outline-none transition-all placeholder:text-zinc-400 focus:border-brand-gold focus:ring-1 focus:ring-brand-gold dark:border-zinc-800 dark:bg-zinc-900/80 dark:text-zinc-100 dark:placeholder:text-zinc-500"
                            />
                        </div>
                    </div>
                    <div className="w-full sm:w-48">
                        <label className="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Estado de Cuenta
                        </label>
                        <div className="relative">
                            <UserIcon className="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
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
                            Usuarios ({filtered.length})
                        </h3>
                    </div>
                    
                    {/* Desktop Table */}
                    <div className="hidden md:block overflow-x-auto">
                        <table className="w-full text-left min-w-[800px]">
                            <thead>
                                <tr className="border-b border-zinc-200 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-900/50">
                                    <th className="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Usuario</th>
                                    <th className="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Correo</th>
                                    <th className="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Roles</th>
                                    <th className="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Estado</th>
                                    <th className="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-zinc-100 dark:divide-zinc-800/50">
                                {filtered.length === 0 && (
                                    <tr>
                                        <td colSpan={5} className="px-6 py-12 text-center text-sm text-zinc-500">
                                            No se encontraron usuarios.
                                        </td>
                                    </tr>
                                )}
                                {filtered.map((user) => (
                                    <tr key={user.id} className="group transition-colors hover:bg-zinc-50/80 dark:hover:bg-zinc-900/80">
                                        <td className="px-6 py-4">
                                            <div className="flex flex-col">
                                                <span className="text-xs font-bold uppercase tracking-wide text-zinc-900 dark:text-zinc-100">
                                                    {user.name}
                                                </span>
                                                <span className="text-[10px] font-medium tracking-wider text-zinc-500 dark:text-zinc-400">
                                                    {user.rfc}
                                                </span>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4">
                                            <span className="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                                {user.email || '—'}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="flex flex-wrap gap-1.5">
                                                {user.roles && user.roles.length > 0 ? (
                                                    user.roles.map(r => <RoleBadge key={r} role={r} />)
                                                ) : (
                                                    <span className="text-[10px] text-zinc-400 dark:text-zinc-600">SIN ROL</span>
                                                )}
                                            </div>
                                        </td>
                                        <td className="px-6 py-4">
                                            <StatusBadge activo={user.activo} />
                                        </td>
                                        <td className="px-6 py-4 text-right">
                                            <div className="flex items-center justify-end gap-2">
                                                <Link href={route('users.show', user.id)} className="rounded p-1.5 text-emerald-600 transition-colors hover:bg-emerald-50 dark:text-emerald-500 dark:hover:bg-emerald-500/10" title="Ver detalles">
                                                    <Eye className="size-4" strokeWidth={2} />
                                                </Link>
                                                <button onClick={() => setAEditar(user)} className="rounded p-1.5 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-500 dark:hover:bg-zinc-800 dark:hover:text-zinc-300" title="Editar">
                                                    <Edit3 className="size-4" strokeWidth={2} />
                                                </button>
                                                <button onClick={() => setUserToDelete(user)} className="rounded p-1.5 text-zinc-400 transition-colors hover:bg-red-50 hover:text-red-600 dark:text-zinc-500 dark:hover:bg-red-500/10 dark:hover:text-red-500" title="Eliminar">
                                                    <Trash2 className="size-4" strokeWidth={2} />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {/* Mobile List omitted for brevity but standard logic implies it would duplicate the desktop loop styling layout */}
                </div>
            </div>

            {/* Modals de Formulario (Crear/Editar) */}
            {(isCreating || userAEditar) && (
                <FormUserModal
                    key={userAEditar ? `edit-${userAEditar.id}` : 'create'}
                    user={userAEditar}
                    roles={roles}
                    onClose={closeUserFormModal}
                />
            )}

            {/* Modal de Eliminación */}
            {userToDelete && (
                <div className="relative z-[100]" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div className="fixed inset-0 bg-zinc-900/40 transition-opacity dark:bg-black/60" onClick={() => setUserToDelete(null)}></div>

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
                                                Eliminar Usuario
                                            </h3>
                                            <div className="mt-2">
                                                <p className="text-sm text-zinc-500 dark:text-zinc-400">
                                                    ¿Estás seguro de que deseas eliminar a <span className="font-bold text-zinc-700 dark:text-zinc-300">{userToDelete.name}</span>? Esta acción no se puede deshacer y todos sus datos serán eliminados permanentemente.
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
                                        onClick={() => setUserToDelete(null)}
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

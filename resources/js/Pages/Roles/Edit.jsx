import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';

export default function Edit({ role, permissions }) {
    const { data, setData, put, processing, errors } = useForm({
        name: role.name,
        permissions: role.permissions,
    });

    const submit = (e) => {
        e.preventDefault();
        put(route('roles.update', role.id));
    };

    const togglePermission = (id) => {
        if (data.permissions.includes(id)) {
            setData('permissions', data.permissions.filter((p) => p !== id));
        } else {
            setData('permissions', [...data.permissions, id]);
        }
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                    <span>SIVSO</span>
                    <span className="text-zinc-300 dark:text-zinc-600">/</span>
                    <Link href={route('roles.index')} className="hover:text-zinc-800 dark:hover:text-zinc-200 transition-colors">
                        ROLES
                    </Link>
                    <span className="text-zinc-300 dark:text-zinc-600">/</span>
                    <span className="text-zinc-900 dark:text-zinc-100">EDITAR</span>
                </div>
            }
        >
            <Head title={`Editar Rol - ${role.name}`} />

            <div className="mx-auto w-full max-w-3xl space-y-8">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                            Editar Rol
                        </h2>
                        <p className="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            Modifica el nombre y los permisos del rol.
                        </p>
                    </div>
                    <Link
                        href={route('roles.index')}
                        className="inline-flex items-center justify-center gap-2 rounded-md border border-zinc-200 bg-white px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-800 dark:bg-[#0A0A0B] dark:text-zinc-300 dark:hover:bg-zinc-900"
                    >
                        <ArrowLeft className="size-4" strokeWidth={2} />
                        Regresar
                    </Link>
                </div>

                <div className="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-[#0A0A0B]">
                    <form onSubmit={submit} className="p-6 sm:p-8">
                        <div className="space-y-6">
                            <div>
                                <label className="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    Nombre del Rol
                                </label>
                                <input
                                    type="text"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value.toUpperCase())}
                                    className="w-full rounded-lg border border-zinc-200 bg-white/80 py-2.5 px-4 text-sm uppercase text-zinc-900 outline-none transition-all focus:border-brand-gold focus:ring-1 focus:ring-brand-gold dark:border-zinc-800 dark:bg-zinc-900/80 dark:text-zinc-100"
                                />
                                {errors.name && <p className="mt-1 text-xs text-red-500">{errors.name}</p>}
                            </div>

                            <div>
                                <label className="mb-3 block text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    Permisos Asignados
                                </label>
                                <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                    {permissions.map((permission) => (
                                        <label key={permission.id} className="flex items-center gap-3 rounded-lg border border-zinc-200/60 p-3 transition-colors hover:bg-zinc-50 dark:border-zinc-800/60 dark:hover:bg-zinc-900/50 cursor-pointer">
                                            <input
                                                type="checkbox"
                                                checked={data.permissions.includes(permission.id)}
                                                onChange={() => togglePermission(permission.id)}
                                                className="size-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:checked:bg-zinc-100 dark:focus:ring-zinc-100"
                                            />
                                            <span className="text-xs font-medium text-zinc-700 dark:text-zinc-300">
                                                {permission.name}
                                            </span>
                                        </label>
                                    ))}
                                </div>
                                {errors.permissions && <p className="mt-2 text-xs text-red-500">{errors.permissions}</p>}
                            </div>
                        </div>

                        <div className="mt-8 flex items-center justify-end gap-4 border-t border-zinc-200 pt-6 dark:border-zinc-800">
                            <Link
                                href={route('roles.index')}
                                className="text-sm font-bold uppercase tracking-wider text-zinc-500 transition-colors hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200"
                            >
                                Cancelar
                            </Link>
                            <button
                                type="submit"
                                disabled={processing}
                                className="inline-flex items-center justify-center gap-2 rounded-md bg-zinc-900 px-6 py-2.5 text-xs font-bold uppercase tracking-wider text-white transition-colors hover:bg-zinc-800 disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white"
                            >
                                <Save className="size-4" strokeWidth={2} />
                                Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

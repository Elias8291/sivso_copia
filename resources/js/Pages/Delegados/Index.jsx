import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import {
    Search, ChevronRight, Plus, Eye, EyeOff,
    UserCheck, UserX, CheckCircle2, Loader2,
    Link2, Link2Off, UserPlus,
} from 'lucide-react';
import { useState, useEffect, useRef, useMemo } from 'react';
import SearchInput from '@/Components/SearchInput';
import FilterBar from '@/Components/FilterBar';
import FilterSelect from '@/Components/FilterSelect';

const inputCls  = 'w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:border-brand-gold focus:ring-1 focus:ring-brand-gold dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100';
const labelCls  = 'mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400';
const tabActive = 'rounded-md bg-white px-4 py-2 text-xs font-bold uppercase tracking-wider text-zinc-900 shadow-sm dark:bg-zinc-800 dark:text-zinc-100 transition-colors';
const tabInact  = 'rounded-md px-4 py-2 text-xs font-bold uppercase tracking-wider text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200 transition-colors';

function Field({ label, error, children }) {
    return (
        <div>
            <label className={labelCls}>{label}</label>
            {children}
            {error && <p className="mt-1 text-xs text-red-500">{error}</p>}
        </div>
    );
}

function ModalShell({ title, subtitle, onClose, children, footer }) {
    return (
        <div className="relative z-[100]" role="dialog" aria-modal="true">
            <div className="fixed inset-0 bg-zinc-900/40 dark:bg-black/60" onClick={onClose} />
            <div className="fixed inset-0 z-[101] w-screen overflow-y-auto">
                <div className="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div className="relative w-full transform overflow-hidden rounded-2xl bg-white text-left shadow-xl dark:bg-zinc-900 sm:my-8 sm:max-w-2xl border border-zinc-200/50 dark:border-zinc-800/50">
                        <div className="bg-white px-6 pb-4 pt-6 dark:bg-zinc-900">
                            <h3 className="text-base font-bold text-zinc-900 dark:text-zinc-100">{title}</h3>
                            {subtitle && <p className="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{subtitle}</p>}
                            {children}
                        </div>
                        {footer}
                    </div>
                </div>
            </div>
        </div>
    );
}

function ModalFooter({ onConfirm, onClose, saving, confirmLabel, confirmIcon: Icon }) {
    return (
        <div className="bg-zinc-50 px-6 py-3 sm:flex sm:flex-row-reverse dark:bg-zinc-800/50 border-t border-zinc-100 dark:border-zinc-800/50">
            <button
                type="button"
                onClick={onConfirm}
                disabled={saving}
                className="inline-flex w-full items-center justify-center gap-2 rounded-md bg-zinc-900 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white sm:ml-3 sm:w-auto disabled:opacity-50 transition-colors"
            >
                {saving ? <Loader2 className="size-4 animate-spin" /> : <Icon className="size-4" />}
                {confirmLabel}
            </button>
            <button
                type="button"
                onClick={onClose}
                className="mt-3 inline-flex w-full justify-center rounded-md bg-white px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-zinc-700 shadow-sm ring-1 ring-inset ring-zinc-300 hover:bg-zinc-50 sm:mt-0 sm:w-auto dark:bg-zinc-800 dark:text-zinc-300 dark:ring-zinc-700 dark:hover:bg-zinc-700 transition-colors"
            >
                Cancelar
            </button>
        </div>
    );
}

function BuscarPorUR({ onSeleccionar, defaultUr }) {
    const [ur, setUr]             = useState(defaultUr ? String(defaultUr) : '');
    const [resultados, setResult] = useState([]);
    const [cargando, setCargando] = useState(false);
    const [abierto, setAbierto]   = useState(false);
    const wrapRef                 = useRef(null);

    useEffect(() => {
        if (defaultUr) setUr(String(defaultUr));
    }, [defaultUr]);

    useEffect(() => {
        const id = setTimeout(async () => {
            const n = parseInt(ur, 10);
            if (!n || ur.trim() === '') { setResult([]); setAbierto(false); return; }
            setCargando(true);
            try {
                const res  = await fetch(`/empleados/lookup?ur=${n}`, { headers: { Accept: 'application/json' } });
                const data = await res.json();
                setResult(data);
                setAbierto(data.length > 0);
            } catch {
                setResult([]);
            } finally {
                setCargando(false);
            }
        }, 380);
        return () => clearTimeout(id);
    }, [ur]);

    useEffect(() => {
        const handler = (e) => { if (wrapRef.current && !wrapRef.current.contains(e.target)) setAbierto(false); };
        document.addEventListener('mousedown', handler);
        return () => document.removeEventListener('mousedown', handler);
    }, []);

    const elegir = (emp) => {
        onSeleccionar(emp);
        setUr('');
        setResult([]);
        setAbierto(false);
    };

    return (
        <div ref={wrapRef} className="relative">
            <label className={labelCls}>Buscar por UR</label>
            <div className="relative">
                <Search className="pointer-events-none absolute left-3 top-1/2 size-3.5 -translate-y-1/2 text-zinc-400" strokeWidth={2} />
                <input
                    type="number"
                    value={ur}
                    onChange={(e) => setUr(e.target.value)}
                    onFocus={() => resultados.length > 0 && setAbierto(true)}
                    placeholder="Número de UR..."
                    className={`${inputCls} pl-8`}
                />
                {cargando && <Loader2 className="absolute right-3 top-1/2 size-3.5 -translate-y-1/2 animate-spin text-zinc-400" />}
            </div>

            {abierto && resultados.length > 0 && (
                <div className="absolute z-50 mt-1 w-full overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
                    <p className="border-b border-zinc-100 px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-zinc-400 dark:border-zinc-800 dark:text-zinc-500">
                        {resultados.length} empleado{resultados.length !== 1 ? 's' : ''} en UR {ur}
                    </p>
                    <ul className="max-h-48 overflow-y-auto">
                        {resultados.map((emp, i) => (
                            <li key={i}>
                                <button
                                    type="button"
                                    onClick={() => elegir(emp)}
                                    className="flex w-full items-center gap-3 px-3 py-2 text-left text-xs transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800"
                                >
                                    <span className="shrink-0 rounded bg-zinc-100 px-1.5 py-0.5 text-[10px] font-bold tracking-wider text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                        {emp.nue}
                                    </span>
                                    <span className="truncate text-zinc-700 dark:text-zinc-300">{emp.nombre}</span>
                                </button>
                            </li>
                        ))}
                    </ul>
                </div>
            )}
        </div>
    );
}

function CrearUsuarioFields({ nombre, form, setForm, errors, defaultUr }) {
    const [showPwd, setShowPwd] = useState(false);

    const set = (field) => (e) =>
        setForm(prev => ({ ...prev, [field]: e.target.value }));

    const alSeleccionarEmpleado = (emp) => {
        setForm(prev => ({
            ...prev,
            nue:  emp.nue  || prev.nue,
            name: prev.name || emp.nombre,
        }));
    };

    return (
        <div className="mt-4 space-y-3 rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 dark:border-zinc-800 dark:bg-zinc-900/30">
            <p className="text-[10px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                Datos de acceso
            </p>

            <BuscarPorUR onSeleccionar={alSeleccionarEmpleado} defaultUr={defaultUr} />

            <Field label="Nombre *" error={errors.name || errors.user_nombre}>
                <input
                    type="text"
                    value={form.name ?? nombre}
                    onChange={set('name')}
                    className={inputCls}
                    placeholder="Nombre de usuario"
                />
            </Field>
            <div className="grid grid-cols-2 gap-3">
                <Field label="RFC" error={errors.rfc || errors.user_rfc}>
                    <input
                        type="text"
                        value={form.rfc ?? ''}
                        onChange={set('rfc')}
                        className={inputCls}
                        placeholder="Opcional"
                    />
                </Field>
                <Field label="NUE" error={errors.nue || errors.user_nue}>
                    <input
                        type="text"
                        value={form.nue ?? ''}
                        onChange={set('nue')}
                        className={inputCls}
                        placeholder="Opcional"
                    />
                </Field>
            </div>
            <Field label="Correo electrónico" error={errors.email || errors.user_email}>
                <input
                    type="email"
                    value={form.email ?? ''}
                    onChange={set('email')}
                    className={inputCls}
                    placeholder="Opcional"
                />
            </Field>
            <Field label="Contraseña * (mín. 8 caracteres)" error={errors.password || errors.user_password}>
                <div className="relative">
                    <input
                        type={showPwd ? 'text' : 'password'}
                        value={form.password ?? ''}
                        onChange={set('password')}
                        className={`${inputCls} pr-10`}
                        placeholder="••••••••"
                    />
                    <button
                        type="button"
                        onClick={() => setShowPwd(v => !v)}
                        className="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 transition-colors"
                        tabIndex={-1}
                    >
                        {showPwd
                            ? <EyeOff className="size-4" strokeWidth={2} />
                            : <Eye className="size-4" strokeWidth={2} />
                        }
                    </button>
                </div>
            </Field>
            <p className="text-[10px] text-zinc-400 dark:text-zinc-500">
                El usuario deberá cambiar su contraseña al primer inicio de sesión.
            </p>
        </div>
    );
}

function NuevoDelegadoModal({ delegaciones, usuarios, onClose }) {
    const [form, setForm]         = useState({ nombre_completo: '', delegacion_ids: [], modo_usuario: 'ninguno', user_id: '' });
    const [userForm, setUserForm] = useState({ name: '', rfc: '', nue: '', email: '', password: '' });
    const [saving, setSaving]     = useState(false);
    const [errors, setErrors]     = useState({});

    const urPrincipalSeleccionado = useMemo(() =>
        delegaciones
            .filter(d => form.delegacion_ids.includes(d.id))
            .map(d => d.ur_principal)
            .find(Boolean) ?? null
    , [delegaciones, form.delegacion_ids]);

    const set = (field) => (e) => {
        const val = e.target.value;
        setForm(prev => {
            const next = { ...prev, [field]: val };
            if (field === 'nombre_completo' && prev.modo_usuario === 'nuevo') {
                setUserForm(u => ({ ...u, name: val }));
            }
            return next;
        });
        setErrors(prev => ({ ...prev, [field]: null }));
    };

    const setModo = (modo) => {
        setForm(prev => ({ ...prev, modo_usuario: modo }));
        if (modo === 'nuevo') {
            setUserForm(u => ({ ...u, name: form.nombre_completo }));
        }
    };

    const toggleDel = (id) =>
        setForm(prev => ({
            ...prev,
            delegacion_ids: prev.delegacion_ids.includes(id)
                ? prev.delegacion_ids.filter(x => x !== id)
                : [...prev.delegacion_ids, id],
        }));

    const handleGuardar = () => {
        if (!form.nombre_completo.trim()) { setErrors({ nombre_completo: 'Requerido.' }); return; }
        setSaving(true);
        const payload = {
            nombre_completo:  form.nombre_completo,
            delegacion_ids:   form.delegacion_ids,
            modo_usuario:     form.modo_usuario,
            user_id:          form.modo_usuario === 'existente' ? form.user_id : null,
            user_nombre:      form.modo_usuario === 'nuevo' ? userForm.name : null,
            user_rfc:         form.modo_usuario === 'nuevo' ? userForm.rfc : null,
            user_nue:         form.modo_usuario === 'nuevo' ? userForm.nue : null,
            user_email:       form.modo_usuario === 'nuevo' ? userForm.email : null,
            user_password:    form.modo_usuario === 'nuevo' ? userForm.password : null,
        };
        router.post(route('delegados.store'), payload, {
            onSuccess: () => { setSaving(false); onClose(); },
            onError:   (err) => { setErrors(err); setSaving(false); },
            preserveScroll: true,
        });
    };

    const disponibles = usuarios.filter(u => !u.delegado_id);

    return (
        <ModalShell
            title="Nuevo Delegado"
            subtitle="Registra el delegado, sus delegaciones y el acceso al sistema."
            onClose={onClose}
            footer={<ModalFooter onConfirm={handleGuardar} onClose={onClose} saving={saving} confirmLabel="Guardar" confirmIcon={CheckCircle2} />}
        >
            <div className="mt-5 space-y-4">
                <Field label="Nombre Completo *" error={errors.nombre_completo}>
                    <input
                        type="text"
                        value={form.nombre_completo}
                        onChange={set('nombre_completo')}
                        className={inputCls}
                        placeholder="Ej. JUAN PÉREZ GARCÍA"
                    />
                </Field>

                <div>
                    <label className={labelCls}>Delegaciones que gestiona</label>
                    {delegaciones.length === 0 ? (
                        <p className="text-xs text-zinc-400">No hay delegaciones registradas.</p>
                    ) : (
                        <div className="mt-1.5 max-h-36 overflow-y-auto rounded-lg border border-zinc-200 bg-zinc-50 p-2 dark:border-zinc-800 dark:bg-zinc-900/50">
                            {delegaciones.map(d => (
                                <label key={d.id} className="flex cursor-pointer items-center gap-2.5 rounded-md px-2 py-1.5 text-xs font-medium text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <input
                                        type="checkbox"
                                        checked={form.delegacion_ids.includes(d.id)}
                                        onChange={() => toggleDel(d.id)}
                                        className="size-3.5 accent-zinc-900 dark:accent-zinc-100"
                                    />
                                    <span className="font-bold">{d.clave}</span>
                                    {d.nombre && <span className="truncate text-zinc-400 dark:text-zinc-500">— {d.nombre}</span>}
                                </label>
                            ))}
                        </div>
                    )}
                </div>

                <div>
                    <label className={labelCls}>Usuario del sistema</label>
                    <div className="flex items-center gap-1 rounded-lg border border-zinc-200 bg-zinc-50 p-1 dark:border-zinc-800 dark:bg-zinc-900">
                        {['ninguno', 'existente', 'nuevo'].map(m => (
                            <button
                                key={m}
                                type="button"
                                onClick={() => setModo(m)}
                                className={form.modo_usuario === m ? tabActive : tabInact}
                            >
                                {m === 'ninguno' ? 'Sin usuario' : m === 'existente' ? 'Existente' : 'Crear nuevo'}
                            </button>
                        ))}
                    </div>

                    {form.modo_usuario === 'existente' && (
                        <div className="mt-3">
                            <select value={form.user_id} onChange={set('user_id')} className={inputCls}>
                                <option value="">Seleccionar usuario...</option>
                                {disponibles.map(u => (
                                    <option key={u.id} value={u.id}>
                                        {u.name}{u.nue ? ` · ${u.nue}` : ''}
                                    </option>
                                ))}
                            </select>
                            {disponibles.length === 0 && (
                                <p className="mt-1 text-xs text-zinc-400">Todos los usuarios ya tienen delegado asignado.</p>
                            )}
                        </div>
                    )}

                    {form.modo_usuario === 'nuevo' && (
                        <CrearUsuarioFields
                            nombre={form.nombre_completo}
                            form={userForm}
                            setForm={setUserForm}
                            errors={errors}
                            defaultUr={urPrincipalSeleccionado}
                        />
                    )}
                </div>
            </div>
        </ModalShell>
    );
}

function GestionarUsuarioModal({ delegado, usuarios, onClose }) {
    const tieneUsuario = !!delegado.user_id;
    const [modo, setModo]       = useState(tieneUsuario ? 'existente' : 'nuevo');
    const [userId, setUserId]   = useState(delegado.user_id ? String(delegado.user_id) : '');
    const [userForm, setUserForm] = useState({ name: delegado.nombre, rfc: '', nue: '', email: '', password: '' });
    const [saving, setSaving]   = useState(false);
    const [errors, setErrors]   = useState({});

    const disponibles = usuarios.filter(u => !u.delegado_id || u.id === delegado.user_id);

    const handleGuardar = () => {
        setSaving(true);
        setErrors({});

        if (modo === 'nuevo') {
            router.post(route('delegados.crear-usuario', delegado.id), userForm, {
                onSuccess: () => { setSaving(false); onClose(); },
                onError:   (err) => { setErrors(err); setSaving(false); },
                preserveScroll: true,
            });
        } else {
            if (!userId) { setErrors({ userId: 'Selecciona un usuario.' }); setSaving(false); return; }
            router.post(route('delegados.asociar-usuario', delegado.id), { user_id: userId }, {
                onSuccess: () => { setSaving(false); onClose(); },
                onError:   (err) => { setErrors(err); setSaving(false); },
                preserveScroll: true,
            });
        }
    };

    return (
        <ModalShell
            title="Gestionar Usuario"
            subtitle={delegado.nombre}
            onClose={onClose}
            footer={<ModalFooter onConfirm={handleGuardar} onClose={onClose} saving={saving} confirmLabel={modo === 'nuevo' ? 'Crear y Asociar' : 'Asociar'} confirmIcon={modo === 'nuevo' ? UserPlus : Link2} />}
        >
            <div className="mt-5 space-y-4">
                {tieneUsuario && (
                    <div className="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2.5 text-xs text-amber-700 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-400">
                        Vinculado actualmente a <strong>{delegado.user_name}</strong>. Al guardar se reemplazará.
                    </div>
                )}

                <div>
                    <label className={labelCls}>Opción</label>
                    <div className="flex items-center gap-1 rounded-lg border border-zinc-200 bg-zinc-50 p-1 dark:border-zinc-800 dark:bg-zinc-900">
                        <button type="button" onClick={() => setModo('existente')} className={modo === 'existente' ? tabActive : tabInact}>
                            Seleccionar existente
                        </button>
                        <button type="button" onClick={() => { setModo('nuevo'); setUserForm(u => ({ ...u, name: delegado.nombre })); }} className={modo === 'nuevo' ? tabActive : tabInact}>
                            Crear nuevo usuario
                        </button>
                    </div>
                </div>

                {modo === 'existente' && (
                    <Field label="Usuario *" error={errors.userId || errors.user_id}>
                        <select value={userId} onChange={(e) => { setUserId(e.target.value); setErrors({}); }} className={inputCls} autoFocus>
                            <option value="">Seleccionar...</option>
                            {disponibles.map(u => (
                                <option key={u.id} value={u.id}>
                                    {u.name}{u.nue ? ` · ${u.nue}` : ''}{u.id === delegado.user_id ? ' (actual)' : ''}
                                </option>
                            ))}
                        </select>
                        {disponibles.length === 0 && (
                            <p className="mt-1 text-xs text-zinc-400">No hay usuarios disponibles sin delegado.</p>
                        )}
                    </Field>
                )}

                {modo === 'nuevo' && (
                    <CrearUsuarioFields
                        nombre={delegado.nombre}
                        form={userForm}
                        setForm={setUserForm}
                        errors={errors}
                        defaultUr={delegado.ur_principal}
                    />
                )}
            </div>
        </ModalShell>
    );
}

function UsuarioBadge({ user_id, user_name, user_activo }) {
    if (!user_id) {
        return (
            <span className="inline-flex items-center gap-1.5 text-xs text-zinc-400 dark:text-zinc-500">
                <UserX className="size-3.5" strokeWidth={2} />
                Sin usuario
            </span>
        );
    }
    return (
        <span className={`inline-flex items-center gap-1.5 text-xs font-semibold ${user_activo ? 'text-emerald-600 dark:text-emerald-500' : 'text-zinc-500 dark:text-zinc-400'}`}>
            <UserCheck className="size-3.5" strokeWidth={2} />
            {user_name}
        </span>
    );
}

export default function Index({ delegados = [], usuarios = [], delegaciones = [], filters = {} }) {
    const [buscar, setBuscar]           = useState(filters.buscar ?? '');
    const [delegacionFilter, setDelegacionFilter] = useState('Todas');
    const [usuarioFilter, setUsuarioFilter] = useState('Todos');
    const [showCrear, setShowCrear]     = useState(false);
    const [gestionarItem, setGestionarItem] = useState(null);

    const filteredDelegados = buscar.trim() === ''
        ? delegados
        : delegados.filter(item => {
            const q = buscar.toLowerCase();
            return (
                item.nombre?.toLowerCase().includes(q) ||
                item.delegacion?.toLowerCase().includes(q) ||
                item.user_name?.toLowerCase().includes(q)
            );
        });

    const finalDelegados = filteredDelegados.filter(item => {
        const matchDelegacion = delegacionFilter === 'Todas' || item.delegacion === delegacionFilter;
        
        let matchUsuario = true;
        if (usuarioFilter === 'ConUsuario') {
            matchUsuario = !!item.user_name;
        } else if (usuarioFilter === 'SinUsuario') {
            matchUsuario = !item.user_name;
        }

        return matchDelegacion && matchUsuario;
    });

    const desasociar = (delegado) => {
        if (!confirm(`¿Desasociar el usuario "${delegado.user_name}" de ${delegado.nombre}?`)) return;
        router.delete(route('delegados.desasociar-usuario', delegado.id), { preserveScroll: true });
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                    <span>SIVSO</span>
                    <span className="text-zinc-300 dark:text-zinc-600">/</span>
                    <span className="text-zinc-900 dark:text-zinc-100">DELEGADOS</span>
                </div>
            }
        >
            <Head title="Delegados" />

            <div className="mx-auto w-full max-w-[1600px] space-y-8">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                            Delegados
                        </h2>
                        <p className="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            Gestión de delegados y sus usuarios del sistema.
                        </p>
                    </div>
                    <button
                        onClick={() => setShowCrear(true)}
                        className="inline-flex items-center justify-center gap-2 rounded-md bg-zinc-900 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white"
                    >
                        <Plus className="size-4" strokeWidth={2} />
                        Nuevo Delegado
                    </button>
                </div>

                <FilterBar>
                    <div className="w-full sm:max-w-md">
                        <SearchInput 
                            value={buscar} 
                            onChange={setBuscar}
                            placeholder="Nombre, delegación o usuario..."
                        />
                    </div>
                    <div className="w-full sm:w-48">
                        <FilterSelect
                            label="Delegación"
                            value={delegacionFilter}
                            onChange={setDelegacionFilter}
                            options={[
                                { value: 'Todas', label: 'Todas' },
                                ...delegaciones.map(d => ({ value: d.nombre, label: d.nombre }))
                            ]}
                        />
                    </div>
                    <div className="w-full sm:w-48">
                        <FilterSelect
                            label="Usuario"
                            value={usuarioFilter}
                            onChange={setUsuarioFilter}
                            options={[
                                { value: 'Todos', label: 'Todos' },
                                { value: 'ConUsuario', label: 'Con usuario' },
                                { value: 'SinUsuario', label: 'Sin usuario' }
                            ]}
                        />
                    </div>
                </FilterBar>

                <div className="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-[#0A0A0B]">
                    <div className="border-b border-zinc-200 px-4 py-4 sm:px-6 dark:border-zinc-800">
                        <h3 className="text-sm font-bold uppercase tracking-wider text-zinc-800 dark:text-zinc-200">
                            Delegados ({finalDelegados.length})
                        </h3>
                    </div>

                    <div className="hidden overflow-x-auto md:block">
                        <table className="min-w-[900px] w-full text-left">
                            <thead>
                                <tr className="border-b border-zinc-200 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-900/50">
                                    <th className="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Delegado</th>
                                    <th className="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Delegaciones</th>
                                    <th className="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Empleados</th>
                                    <th className="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Usuario</th>
                                    <th className="px-6 py-3 text-right text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Acciones</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-zinc-100 dark:divide-zinc-800/50">
                                {finalDelegados.length === 0 && (
                                    <tr>
                                        <td colSpan={5} className="px-6 py-12 text-center text-sm text-zinc-500">
                                            No se encontraron delegados.
                                        </td>
                                    </tr>
                                )}
                                {finalDelegados.map((item) => (
                                    <tr key={item.id} className="group transition-colors hover:bg-zinc-50/80 dark:hover:bg-zinc-900/80">
                                        <td className="px-6 py-4">
                                            <span className="text-xs font-bold uppercase tracking-wide text-zinc-900 dark:text-zinc-100">
                                                {item.nombre}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 text-xs text-zinc-500 dark:text-zinc-400">
                                            {item.delegacion}
                                        </td>
                                        <td className="px-6 py-4 text-xs font-semibold text-zinc-700 dark:text-zinc-200">
                                            {item.empleados_count ?? 0}
                                        </td>
                                        <td className="px-6 py-4">
                                            <UsuarioBadge
                                                user_id={item.user_id}
                                                user_name={item.user_name}
                                                user_activo={item.user_activo}
                                            />
                                        </td>
                                        <td className="px-6 py-4 text-right">
                                            <div className="flex items-center justify-end gap-1">
                                                <Link
                                                    href={route('delegados.show', item.id)}
                                                    className="rounded p-1.5 text-emerald-600 transition-colors hover:bg-emerald-50 dark:text-emerald-500 dark:hover:bg-emerald-500/10"
                                                    title="Ver detalle"
                                                >
                                                    <Eye className="size-4" strokeWidth={2} />
                                                </Link>
                                                <button
                                                    onClick={() => setGestionarItem(item)}
                                                    className="rounded p-1.5 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-500 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                                                    title={item.user_id ? 'Cambiar usuario' : 'Asignar usuario'}
                                                >
                                                    <UserPlus className="size-4" strokeWidth={2} />
                                                </button>
                                                {item.user_id && (
                                                    <button
                                                        onClick={() => desasociar(item)}
                                                        className="rounded p-1.5 text-zinc-400 transition-colors hover:bg-red-50 hover:text-red-600 dark:text-zinc-500 dark:hover:bg-red-500/10 dark:hover:text-red-500"
                                                        title="Desasociar usuario"
                                                    >
                                                        <Link2Off className="size-4" strokeWidth={2} />
                                                    </button>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    <div className="flex flex-col divide-y divide-zinc-100 md:hidden dark:divide-zinc-800/50">
                        {finalDelegados.length === 0 && (
                            <div className="px-4 py-12 text-center text-sm text-zinc-500">
                                No se encontraron delegados.
                            </div>
                        )}
                        {finalDelegados.map((item) => (
                            <div key={item.id} className="space-y-2 p-4">
                                <div className="text-xs font-bold uppercase tracking-wide text-zinc-900 dark:text-zinc-100">
                                    {item.nombre}
                                </div>
                                <div className="text-xs text-zinc-500 dark:text-zinc-400">{item.delegacion}</div>
                                <div className="text-xs font-semibold text-zinc-700 dark:text-zinc-200">
                                    Empleados: {item.empleados_count ?? 0}
                                </div>
                                <UsuarioBadge user_id={item.user_id} user_name={item.user_name} user_activo={item.user_activo} />
                                <div className="flex items-center gap-3 pt-1">
                                    <Link href={route('delegados.show', item.id)} className="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-500">
                                        <Eye className="size-3.5" strokeWidth={2} />
                                        Ver
                                    </Link>
                                    <button onClick={() => setGestionarItem(item)} className="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        <UserPlus className="size-3.5" strokeWidth={2} />
                                        {item.user_id ? 'Cambiar' : 'Asignar'}
                                    </button>
                                    {item.user_id && (
                                        <button onClick={() => desasociar(item)} className="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-red-500 dark:text-red-400">
                                            <Link2Off className="size-3.5" strokeWidth={2} />
                                            Desasociar
                                        </button>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>

            {showCrear && (
                <NuevoDelegadoModal
                    delegaciones={delegaciones}
                    usuarios={usuarios}
                    onClose={() => setShowCrear(false)}
                />
            )}

            {gestionarItem && (
                <GestionarUsuarioModal
                    delegado={gestionarItem}
                    usuarios={usuarios}
                    onClose={() => setGestionarItem(null)}
                />
            )}
        </AuthenticatedLayout>
    );
}

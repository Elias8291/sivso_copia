import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { Info, MapPin, Search, SlidersHorizontal, Users, DollarSign } from 'lucide-react';
import React, { useState, useMemo, useCallback } from 'react';
import { FILTERS } from './Components/constants';
import EmployeeAccordion from './Components/EmployeeAccordion';

function computeStatus(wardrobeItems, selections) {
    const ids = wardrobeItems.map((i) => i.id);
    const total = ids.length;
    if (total === 0) return 'Pendiente';
    let filled = 0;
    for (const id of ids) {
        if (selections[id]) filled++;
    }
    if (filled === total) return 'Completado';
    if (filled > 0) return 'En progreso';
    return 'Pendiente';
}

const fmtMoney = (v) =>
    v != null ? Number(v).toLocaleString('es-MX', { style: 'currency', currency: 'MXN' }) : '$0.00';

export default function Index({
    employees: initialEmployees,
    delegation_name,
    delegaciones = [],
    delegacion_activa_id,
    ejercicio,
    bajas = { total: 0, importe: 0 },
    dependencias = [],
    delegaciones_por_ur = {},
}) {
    const [search, setSearch] = useState('');
    const [statusFilter, setStatusFilter] = useState('all');
    const [employees, setEmployees] = useState(initialEmployees);
    const [savingId, setSavingId] = useState(null);
    const [delegation] = useState(delegation_name);

    const stats = useMemo(
        () => ({
            total: employees.length,
            completed: employees.filter((e) => e.status === 'Completado').length,
            inProgress: employees.filter((e) => e.status === 'En progreso').length,
            pending: employees.filter((e) => e.status === 'Pendiente').length,
            baja: employees.filter((e) => e.status === 'Baja').length,
        }),
        [employees]
    );

    const filtered = useMemo(() => {
        const q = search.trim().toLowerCase();
        return employees
            .filter(
                (e) =>
                    !q ||
                    e.name?.toLowerCase().includes(q) ||
                    e.nue?.toLowerCase().includes(q) ||
                    e.dependencia?.toLowerCase().includes(q)
            )
            .filter((e) => statusFilter === 'all' || e.status === statusFilter);
    }, [employees, search, statusFilter]);

    const sortedFiltered = useMemo(() => {
        const order = { Pendiente: 0, 'En progreso': 1, Completado: 2, Baja: 3 };
        return [...filtered].sort((a, b) => {
            const d = order[a.status] - order[b.status];
            if (d !== 0) return d;
            return (a.name || '').localeCompare(b.name || '', 'es');
        });
    }, [filtered]);

    const handleSave = useCallback((empId, newSelections, onDone) => {
        setSavingId(empId);

        const tallas = {};
        for (const [solId, talla] of Object.entries(newSelections)) {
            tallas[solId] = talla || '';
        }

        router.post(
            route('my-delegation.save-tallas'),
            { empleado_id: empId, tallas },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setEmployees((prev) =>
                        prev.map((emp) => {
                            if (emp.id !== empId) return emp;
                            const newStatus = computeStatus(emp.wardrobeItems, newSelections);
                            return { ...emp, selections: newSelections, status: newStatus };
                        })
                    );
                    setSavingId(null);
                    onDone?.();
                },
                onError: () => {
                    setSavingId(null);
                },
            }
        );
    }, []);

    const handleEdit = useCallback(
        (empId, form) => {
            setEmployees((prev) =>
                prev
                    .map((emp) =>
                        emp.id !== empId ? emp : { ...emp, name: form.name, position: form.position }
                    )
                    .filter((emp) => form.delegation === delegation || emp.id !== empId)
            );
        },
        [delegation]
    );

    const handleRequestBaja = useCallback((empId, newEmployee) => {
        setEmployees((prev) => {
            const leaving = prev.find((e) => e.id === empId);
            const updated = prev.filter((e) => e.id !== empId);
            if (newEmployee && leaving) {
                updated.push({
                    id: Date.now(),
                    name: newEmployee.name,
                    rfc: newEmployee.rfc ?? '',
                    position: newEmployee.position,
                    wardrobeItems: leaving.wardrobeItems,
                    selections: Object.fromEntries(leaving.wardrobeItems.map((i) => [i.id, ''])),
                    status: 'Pendiente',
                });
            }
            return updated;
        });
    }, []);

    const switchDelegacion = (delId) => {
        router.get(
            route('my-delegation.index'),
            { delegacion: delId },
            { preserveState: false }
        );
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                    <span>SIVSO</span>
                    <span className="text-zinc-300 dark:text-zinc-600">/</span>
                    <span className="text-zinc-900 dark:text-zinc-100">MI DELEGACIÓN</span>
                </div>
            }
        >
            <Head title="Mi Delegación" />
            <div className="mx-auto w-full max-w-[1600px] space-y-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                            Mi delegación
                        </h2>
                        <p className="mt-1 max-w-xl text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">
                            Vestuario de tus empleados — ejercicio{' '}
                            <span className="font-semibold text-zinc-700 dark:text-zinc-300">{ejercicio}</span>.
                            Usa{' '}
                            <span className="font-medium text-zinc-600 dark:text-zinc-300">Actualizar tallas</span> en cada tarjeta.
                        </p>
                    </div>
                    <div className="flex items-center gap-2 self-start sm:self-auto">
                        {delegaciones.length > 1 ? (
                            <select
                                value={delegacion_activa_id}
                                onChange={(e) => switchDelegacion(e.target.value)}
                                className="rounded-lg border border-zinc-200/70 bg-white/70 px-3 py-2 text-xs font-bold uppercase tracking-wider text-zinc-700 dark:border-zinc-800/70 dark:bg-zinc-900/50 dark:text-zinc-300"
                            >
                                {delegaciones.map((d) => (
                                    <option key={d.id} value={d.id}>
                                        {d.clave}{d.nombre ? ` — ${d.nombre}` : ''}
                                    </option>
                                ))}
                            </select>
                        ) : (
                            <div className="inline-flex items-center gap-2 rounded-lg border border-zinc-200/70 bg-white/70 px-3 py-2 dark:border-zinc-800/70 dark:bg-zinc-900/50">
                                <MapPin className="size-4 shrink-0 text-brand-gold" strokeWidth={2} />
                                <span className="text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300">
                                    {delegation}
                                </span>
                            </div>
                        )}
                    </div>
                </div>

                <div className="flex items-start gap-3 rounded-xl border border-zinc-200/60 bg-white/60 px-3 py-3 dark:border-zinc-800/60 dark:bg-zinc-900/40 sm:px-4">
                    <Info className="mt-0.5 size-4 shrink-0 text-brand-gold/90" strokeWidth={2} />
                    <p className="text-[12px] leading-relaxed text-zinc-600 dark:text-zinc-400">
                        <span className="font-semibold text-zinc-800 dark:text-zinc-200">Cómo usar esta pantalla:</span>{' '}
                        la lista prioriza a quienes deben completar tallas. En cada tarjeta,{' '}
                        <span className="font-semibold text-zinc-700 dark:text-zinc-300">Actualizar tallas</span> abre el
                        vestuario; al guardar, los cambios se persisten en la base de datos.
                    </p>
                </div>

                <div className="flex flex-wrap items-center gap-x-5 gap-y-2 rounded-xl border border-zinc-200/50 bg-white/50 px-4 py-3 text-[12px] dark:border-zinc-800/50 dark:bg-zinc-900/35">
                    <span className="font-semibold tabular-nums text-zinc-900 dark:text-zinc-100">{stats.total}</span>
                    <span className="text-zinc-500 dark:text-zinc-400">en lista</span>
                    <span className="hidden h-3 w-px bg-zinc-200 dark:bg-zinc-700 sm:block" aria-hidden />
                    <span className="inline-flex items-center gap-1.5">
                        <span className="size-1.5 rounded-full bg-emerald-500" />
                        <span className="tabular-nums font-semibold text-zinc-800 dark:text-zinc-200">{stats.completed}</span>
                        <span className="text-zinc-500 dark:text-zinc-400">listos</span>
                    </span>
                    <span className="inline-flex items-center gap-1.5">
                        <span className="size-1.5 rounded-full bg-brand-gold" />
                        <span className="tabular-nums font-semibold text-zinc-800 dark:text-zinc-200">
                            {stats.inProgress}
                        </span>
                        <span className="text-zinc-500 dark:text-zinc-400">en curso</span>
                    </span>
                    <span className="inline-flex items-center gap-1.5">
                        <span className="size-1.5 rounded-full bg-zinc-400 dark:bg-zinc-500" />
                        <span className="tabular-nums font-semibold text-zinc-800 dark:text-zinc-200">{stats.pending}</span>
                        <span className="text-zinc-500 dark:text-zinc-400">sin empezar</span>
                    </span>
                    {stats.baja > 0 && (
                        <span className="inline-flex items-center gap-1.5">
                            <span className="size-1.5 rounded-full bg-red-500" />
                            <span className="tabular-nums font-semibold text-zinc-800 dark:text-zinc-200">{stats.baja}</span>
                            <span className="text-zinc-500 dark:text-zinc-400">bajas</span>
                        </span>
                    )}
                </div>

                {bajas.total > 0 && (
                    <div className="flex items-center gap-3 rounded-xl border border-amber-200/60 bg-amber-50/50 px-4 py-3 dark:border-amber-800/40 dark:bg-amber-900/10">
                        <div className="flex size-9 shrink-0 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-500/15">
                            <DollarSign className="size-4.5 text-amber-600 dark:text-amber-400" strokeWidth={2} />
                        </div>
                        <div>
                            <p className="text-[10px] font-bold uppercase tracking-wider text-amber-700/80 dark:text-amber-400/80">
                                Disponible por bajas
                            </p>
                            <p className="text-lg font-bold tabular-nums text-amber-800 dark:text-amber-300">
                                {fmtMoney(bajas.importe)}
                            </p>
                        </div>
                        <span className="ml-auto rounded-full bg-amber-100 px-2.5 py-1 text-[10px] font-bold tabular-nums text-amber-700 dark:bg-amber-500/15 dark:text-amber-400">
                            {bajas.total} {bajas.total === 1 ? 'producto' : 'productos'} en baja
                        </span>
                    </div>
                )}

                <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div className="relative w-full sm:max-w-sm">
                        <Search
                            className="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-zinc-400"
                            strokeWidth={2}
                        />
                        <input
                            type="text"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Buscar por nombre o NUE…"
                            className="w-full rounded-lg border border-zinc-200/70 bg-white/90 py-2.5 pl-9 pr-3 text-[13px] text-zinc-900 outline-none transition-all placeholder:text-zinc-400 focus:border-brand-gold/50 focus:ring-2 focus:ring-brand-gold/15 dark:border-zinc-800/70 dark:bg-zinc-900/70 dark:text-zinc-100 dark:placeholder:text-zinc-500"
                        />
                    </div>
                    <div className="flex flex-wrap items-center gap-1.5">
                        <SlidersHorizontal className="mr-0.5 size-3.5 text-zinc-400" strokeWidth={2} aria-hidden />
                        {FILTERS.map((f) => (
                            <button
                                key={f.value}
                                type="button"
                                onClick={() => setStatusFilter(f.value)}
                                className={`rounded-lg px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider transition-all ${
                                    statusFilter === f.value
                                        ? 'bg-zinc-900 text-white shadow-sm dark:bg-zinc-100 dark:text-zinc-900'
                                        : 'bg-zinc-100/90 text-zinc-500 hover:bg-zinc-200/90 dark:bg-zinc-800/70 dark:text-zinc-400 dark:hover:bg-zinc-700/70'
                                }`}
                            >
                                {f.label}
                            </button>
                        ))}
                    </div>
                    <span className="text-[11px] text-zinc-400 dark:text-zinc-500 sm:ml-auto">
                        Mostrando {sortedFiltered.length} de {stats.total}
                    </span>
                </div>

                {sortedFiltered.length === 0 ? (
                    <div className="flex flex-col items-center justify-center rounded-xl border border-dashed border-zinc-200/70 bg-white/40 py-16 dark:border-zinc-800/60 dark:bg-zinc-900/25">
                        <Users className="size-9 text-zinc-300 dark:text-zinc-600" strokeWidth={1.5} />
                        <p className="mt-3 text-sm font-medium text-zinc-600 dark:text-zinc-400">Nadie coincide con el filtro</p>
                        <p className="mt-1 text-center text-xs text-zinc-400 dark:text-zinc-500">
                            Cambia la búsqueda o el filtro de estado
                        </p>
                    </div>
                ) : (
                    <div className="space-y-2.5">
                        {sortedFiltered.map((employee) => (
                            <EmployeeAccordion
                                key={employee.id}
                                employee={employee}
                                delegation={delegation}
                                dependencias={dependencias}
                                delegaciones={delegaciones}
                                delegacionesPorUr={delegaciones_por_ur}
                                delegacionActivaId={delegacion_activa_id}
                                onSave={handleSave}
                                onEdit={handleEdit}
                                onRequestBaja={handleRequestBaja}
                                isSaving={savingId === employee.id}
                            />
                        ))}
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}

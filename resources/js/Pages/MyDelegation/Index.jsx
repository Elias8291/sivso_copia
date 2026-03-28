import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { Info, MapPin, Search, SlidersHorizontal, Users } from 'lucide-react';
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

export default function Index({ employees: initialEmployees, delegation_name }) {
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
                    e.position?.toLowerCase().includes(q)
            )
            .filter((e) => statusFilter === 'all' || e.status === statusFilter);
    }, [employees, search, statusFilter]);

    const sortedFiltered = useMemo(() => {
        const order = { Pendiente: 0, 'En progreso': 1, Completado: 2 };
        return [...filtered].sort((a, b) => {
            const d = order[a.status] - order[b.status];
            if (d !== 0) return d;
            return (a.name || '').localeCompare(b.name || '', 'es');
        });
    }, [filtered]);

    const handleSave = useCallback((empId, newSelections, onDone) => {
        setSavingId(empId);
        setTimeout(() => {
            setEmployees((prev) =>
                prev.map((emp) => {
                    if (emp.id !== empId) return emp;
                    const newStatus = computeStatus(emp.wardrobeItems, newSelections);
                    return { ...emp, selections: newSelections, status: newStatus };
                })
            );
            setSavingId(null);
            onDone?.();
        }, 450);
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
                            Cada persona tiene su propio vestuario. Usa{' '}
                            <span className="font-medium text-zinc-600 dark:text-zinc-300">Actualizar tallas</span> en cada tarjeta
                            para ver prendas y elegir tallas.
                        </p>
                    </div>
                    <div className="inline-flex items-center gap-2 self-start rounded-lg border border-zinc-200/70 bg-white/70 px-3 py-2 dark:border-zinc-800/70 dark:bg-zinc-900/50 sm:self-auto">
                        <MapPin className="size-4 shrink-0 text-brand-gold" strokeWidth={2} />
                        <span className="text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300">
                            {delegation}
                        </span>
                    </div>
                </div>

                <div className="flex items-start gap-3 rounded-xl border border-zinc-200/60 bg-white/60 px-3 py-3 dark:border-zinc-800/60 dark:bg-zinc-900/40 sm:px-4">
                    <Info className="mt-0.5 size-4 shrink-0 text-brand-gold/90" strokeWidth={2} />
                    <p className="text-[12px] leading-relaxed text-zinc-600 dark:text-zinc-400">
                        <span className="font-semibold text-zinc-800 dark:text-zinc-200">Cómo usar esta pantalla:</span>{' '}
                        la lista prioriza a quienes deben completar tallas. En cada tarjeta,{' '}
                        <span className="font-semibold text-zinc-700 dark:text-zinc-300">Actualizar tallas</span> abre el
                        vestuario; los botones <span className="font-semibold text-zinc-700 dark:text-zinc-300">Editar</span> y{' '}
                        <span className="font-semibold text-zinc-700 dark:text-zinc-300">Baja</span> son para datos generales o bajas.
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
                </div>

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
                            placeholder="Buscar por nombre o puesto…"
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

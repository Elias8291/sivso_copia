import React, { useState, useMemo, useEffect } from 'react';
import { router } from '@inertiajs/react';
import { ArrowRight, CheckCircle2, Loader2, RefreshCw, ArrowRightLeft, Building2 } from 'lucide-react';
import { formatMXN } from './utils';
import DelegationModalShell, {
    delegationBtnPrimary,
    delegationBtnSecondary,
    delegationInputClass,
    delegationLabelClass,
} from './DelegationModalShell';

export default function CambioModal({
    employee,
    dependencias = [],
    delegaciones = [],
    delegacionesPorUr = {},
    delegacionActivaId,
    onClose,
    onConfirm,
}) {
    const [step, setStep] = useState('tipo');
    const [mismaUr, setMismaUr] = useState(null);
    const [submitting, setSubmit] = useState(false);
    const [errors, setErrors] = useState({});
    const [delegacionDestinoId, setDelegacionDestinoId] = useState(delegacionActivaId ?? '');
    const [newEmp, setNewEmp] = useState({
        nombre: '',
        apellido_paterno: '',
        apellido_materno: '',
        nue: '',
    });

    const items = employee.wardrobeItems.map((i) => ({ ...i, price: i.price ?? 0 }));
    const totalImporte = items.reduce((s, i) => s + i.price, 0);
    const canSubmit = newEmp.nombre.trim() && newEmp.apellido_paterno.trim() && delegacionDestinoId;
    const setField = (f) => (e) => {
        setNewEmp((p) => ({ ...p, [f]: e.target.value }));
        setErrors((p) => ({ ...p, [f]: null }));
    };

    const depsEnUr = useMemo(() => {
        if (!employee.ur) return [];
        return dependencias.filter((d) => String(d.ur) === String(employee.ur));
    }, [dependencias, employee.ur]);

    const delsEnUr = useMemo(() => {
        if (employee.ur == null || employee.ur === '') {
            return delegaciones;
        }
        const list = delegacionesPorUr[String(employee.ur)];
        return Array.isArray(list) ? list : [];
    }, [employee.ur, delegacionesPorUr, delegaciones]);

    useEffect(() => {
        if (delsEnUr.length === 0) {
            setDelegacionDestinoId('');
            return;
        }
        setDelegacionDestinoId((prev) => {
            if (prev && delsEnUr.some((d) => String(d.id) === String(prev))) {
                return prev;
            }
            const preferred = delsEnUr.find((d) => String(d.id) === String(delegacionActivaId));
            return preferred ? preferred.id : delsEnUr[0].id;
        });
    }, [delsEnUr, delegacionActivaId, employee.id]);

    const handleSubmit = () => {
        if (!canSubmit) return;
        setSubmit(true);
        router.post(route('my-delegation.baja'), {
            empleado_id: employee.id,
            tipo: mismaUr ? 'misma_ur' : 'otra_ur',
            delegacion_destino_id: delegacionDestinoId,
            reemplazo: newEmp,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                setSubmit(false);
                setStep('done');
                onConfirm(employee.id, mismaUr ? { ...newEmp, transferido: true } : null);
            },
            onError: (err) => { setErrors(err); setSubmit(false); },
        });
    };

    if (step === 'done') {
        const delDestino = delsEnUr.find((d) => String(d.id) === String(delegacionDestinoId))
            ?? delegaciones.find((d) => String(d.id) === String(delegacionDestinoId));
        return (
            <DelegationModalShell
                ariaTitleId="cambio-done-modal"
                title="Cambio registrado"
                subtitle={`El cambio de ${employee.name} fue procesado.`}
                onClose={onClose}
                maxWidthClass="sm:max-w-lg"
                footer={
                    <div className="flex w-full justify-center sm:col-span-full">
                        <button type="button" onClick={onClose} className={`${delegationBtnSecondary} sm:mt-0`}>Cerrar</button>
                    </div>
                }
            >
                <div className="mt-4 flex flex-col items-center gap-3 text-center">
                    <div className="flex size-12 items-center justify-center rounded-full bg-emerald-500/10 dark:bg-emerald-500/15">
                        <CheckCircle2 className="size-6 text-emerald-600 dark:text-emerald-400" strokeWidth={2} />
                    </div>
                    {mismaUr ? (
                        <p className="text-sm text-zinc-500 dark:text-zinc-400">
                            El recurso ({formatMXN(totalImporte)}) fue transferido al reemplazo en la misma UR.
                        </p>
                    ) : (
                        <p className="text-sm text-zinc-500 dark:text-zinc-400">
                            Reemplazo registrado en <span className="font-semibold text-zinc-700 dark:text-zinc-300">{delDestino?.nombre || delDestino?.clave || 'otra delegación'}</span>. Se debe reasignar recurso.
                        </p>
                    )}
                </div>
            </DelegationModalShell>
        );
    }

    if (step === 'datos') {
        return (
            <DelegationModalShell
                ariaTitleId="cambio-datos-modal"
                title="Datos del reemplazo"
                subtitle={`Reemplazo de ${employee.name}${mismaUr ? ' (misma UR — se transfiere recurso)' : ' (otra UR/delegación — sin recurso)'}.`}
                onClose={onClose}
                maxWidthClass="sm:max-w-2xl"
                footer={
                    <>
                        <button type="button" onClick={handleSubmit} disabled={!canSubmit || submitting} className={delegationBtnPrimary}>
                            {submitting ? <Loader2 className="mr-2 size-4 animate-spin" /> : <CheckCircle2 className="mr-2 size-4" strokeWidth={2} />}
                            Confirmar cambio
                        </button>
                        <button type="button" onClick={() => setStep('tipo')} className={delegationBtnSecondary}>Atrás</button>
                    </>
                }
            >
                <div className="mt-4 space-y-4">
                    {mismaUr ? (
                        <p className="rounded-lg border border-emerald-200/80 bg-emerald-50/80 px-3 py-2 text-xs text-emerald-700 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-400">
                            <RefreshCw className="mr-1.5 inline size-3.5 align-text-bottom" strokeWidth={2} />
                            Misma UR — los {items.length} productos ({formatMXN(totalImporte)}) se transfieren automáticamente.
                        </p>
                    ) : (
                        <p className="rounded-lg border border-amber-200/80 bg-amber-50/80 px-3 py-2 text-xs text-amber-700 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-400">
                            <ArrowRightLeft className="mr-1.5 inline size-3.5 align-text-bottom" strokeWidth={2} />
                            Otra UR/delegación — se registra sin recurso. Productos del empleado anterior quedan en baja.
                        </p>
                    )}

                    <div>
                        <label className={delegationLabelClass}>
                            Delegación destino *
                            {employee.ur && <span className="ml-1 font-normal text-zinc-400">(delegaciones en UR {employee.ur})</span>}
                        </label>
                        <select
                            value={delegacionDestinoId}
                            onChange={(e) => setDelegacionDestinoId(e.target.value)}
                            className={delegationInputClass}
                            disabled={delsEnUr.length === 0}
                        >
                            <option value="">
                                {delsEnUr.length === 0 && employee.ur
                                    ? 'Sin delegaciones para esta UR'
                                    : 'Seleccionar delegación…'}
                            </option>
                            {delsEnUr.map((d) => (
                                <option key={d.id} value={d.id}>
                                    {d.clave}{d.nombre ? ` — ${d.nombre}` : ''}
                                    {String(d.id) === String(delegacionActivaId) ? ' (actual)' : ''}
                                </option>
                            ))}
                        </select>
                        {employee.ur && delsEnUr.length === 0 && (
                            <p className="mt-1 text-xs text-amber-600 dark:text-amber-400">
                                No hay delegaciones tuyas vinculadas a dependencias con UR {employee.ur}. Revisa dependencia–delegación en catálogos.
                            </p>
                        )}
                        {errors.delegacion_destino_id && <p className="mt-1 text-xs text-red-500">{errors.delegacion_destino_id}</p>}
                    </div>

                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label className={delegationLabelClass}>Nombre *</label>
                            <input type="text" value={newEmp.nombre} onChange={setField('nombre')} placeholder="Nombre(s)" className={delegationInputClass} />
                            {errors['reemplazo.nombre'] && <p className="mt-1 text-xs text-red-500">{errors['reemplazo.nombre']}</p>}
                        </div>
                        <div>
                            <label className={delegationLabelClass}>NUE</label>
                            <input type="text" value={newEmp.nue} onChange={setField('nue')} placeholder="Opcional" className={delegationInputClass} />
                        </div>
                    </div>
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label className={delegationLabelClass}>Apellido paterno *</label>
                            <input type="text" value={newEmp.apellido_paterno} onChange={setField('apellido_paterno')} className={delegationInputClass} />
                        </div>
                        <div>
                            <label className={delegationLabelClass}>Apellido materno</label>
                            <input type="text" value={newEmp.apellido_materno} onChange={setField('apellido_materno')} className={delegationInputClass} />
                        </div>
                    </div>
                </div>
            </DelegationModalShell>
        );
    }

    return (
        <DelegationModalShell
            ariaTitleId="cambio-tipo-modal"
            title="Cambio de UR / Delegación"
            subtitle={`${employee.name} · UR: ${employee.ur || '—'}`}
            onClose={onClose}
            maxWidthClass="sm:max-w-lg"
            footer={
                <>
                    <button type="button" onClick={() => { if (mismaUr !== null) setStep('datos'); }} disabled={mismaUr === null} className={delegationBtnPrimary}>
                        <ArrowRight className="mr-2 size-4" strokeWidth={2.5} />
                        Continuar
                    </button>
                    <button type="button" onClick={onClose} className={delegationBtnSecondary}>Cancelar</button>
                </>
            }
        >
            <div className="mt-4 space-y-4">
                {depsEnUr.length > 0 && (
                    <div className="rounded-lg border border-zinc-200/70 bg-zinc-50/80 px-3 py-2.5 dark:border-zinc-800/70 dark:bg-zinc-900/40">
                        <div className="mb-1.5 flex items-center gap-1.5">
                            <Building2 className="size-3.5 text-zinc-400" strokeWidth={2} />
                            <span className="text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                Dependencias en UR {employee.ur}
                            </span>
                        </div>
                        <ul className="space-y-1">
                            {depsEnUr.map((d) => (
                                <li key={d.id} className="flex items-center gap-2 text-xs text-zinc-700 dark:text-zinc-300">
                                    <span className="size-1 shrink-0 rounded-full bg-brand-gold/70" />
                                    <span className="truncate">{d.nombre || `Dependencia #${d.id}`}</span>
                                </li>
                            ))}
                        </ul>
                    </div>
                )}

                <p className="text-xs leading-relaxed text-zinc-500 dark:text-zinc-400">
                    El empleado cambia de ubicación. Si es en la <span className="font-semibold text-zinc-700 dark:text-zinc-300">misma UR</span> los productos se transfieren al reemplazo.
                    Si es a <span className="font-semibold text-zinc-700 dark:text-zinc-300">otra UR o delegación</span>, los productos quedan libres y al reemplazo se le asignarán nuevos.
                </p>
                <div className="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    {[
                        { val: true, label: 'Misma UR', desc: 'Se transfiere recurso', Icon: RefreshCw },
                        { val: false, label: 'Otra UR / Delegación', desc: 'Reasignar recurso', Icon: ArrowRightLeft },
                    ].map(({ val, label, desc, Icon: Ic }) => (
                        <button
                            key={String(val)}
                            type="button"
                            onClick={() => setMismaUr(val)}
                            className={`flex flex-col items-center gap-1.5 rounded-lg border py-4 text-center transition-all ${
                                mismaUr === val
                                    ? 'border-brand-gold/50 bg-brand-gold/10 text-zinc-900 ring-1 ring-brand-gold/25 dark:text-zinc-100'
                                    : 'border-zinc-200 bg-white text-zinc-500 hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400'
                            }`}
                        >
                            <Ic className={`size-5 ${mismaUr === val ? 'text-brand-gold' : 'text-zinc-400'}`} strokeWidth={2} />
                            <span className="text-xs font-bold uppercase tracking-wider">{label}</span>
                            <span className="text-[10px] text-zinc-400 dark:text-zinc-500">{desc}</span>
                        </button>
                    ))}
                </div>
            </div>
        </DelegationModalShell>
    );
}

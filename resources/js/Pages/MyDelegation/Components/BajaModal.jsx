import React, { useState } from 'react';
import { router } from '@inertiajs/react';
import { ArrowRight, CheckCircle2, Loader2, AlertTriangle, UserPlus, X } from 'lucide-react';
import { formatMXN } from './utils';
import DelegationModalShell, {
    delegationBtnPrimary,
    delegationBtnSecondary,
    delegationInputClass,
    delegationLabelClass,
} from './DelegationModalShell';

export default function BajaModal({ employee, onClose, onConfirm }) {
    const [step, setStep] = useState('confirm');
    const [hasReplacement, setHas] = useState(null);
    const [submitting, setSubmit] = useState(false);
    const [errors, setErrors] = useState({});
    const [newEmp, setNewEmp] = useState({
        nombre: '',
        apellido_paterno: '',
        apellido_materno: '',
        nue: '',
    });

    const items = employee.wardrobeItems.map((i) => ({ ...i, price: i.price ?? 0 }));
    const totalFreed = items.reduce((s, i) => s + i.price, 0);
    const canSubmitNew = newEmp.nombre.trim() && newEmp.apellido_paterno.trim();
    const setField = (f) => (e) => {
        setNewEmp((p) => ({ ...p, [f]: e.target.value }));
        setErrors((p) => ({ ...p, [f]: null }));
    };

    const handleNext = () => {
        if (hasReplacement === null) return;
        if (hasReplacement) return setStep('new-employee');
        setSubmit(true);
        router.post(route('my-delegation.baja'), {
            empleado_id: employee.id,
            tipo: 'sin_reemplazo',
        }, {
            preserveScroll: true,
            onSuccess: () => { setSubmit(false); setStep('done'); onConfirm(employee.id, null); },
            onError: (err) => { setErrors(err); setSubmit(false); },
        });
    };

    const handleSubmitNew = () => {
        if (!canSubmitNew) return;
        setSubmit(true);
        router.post(route('my-delegation.baja'), {
            empleado_id: employee.id,
            tipo: 'misma_ur',
            reemplazo: newEmp,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                setSubmit(false);
                setStep('done');
                onConfirm(employee.id, { ...newEmp, transferido: true });
            },
            onError: (err) => { setErrors(err); setSubmit(false); },
        });
    };

    if (step === 'done') {
        return (
            <DelegationModalShell
                ariaTitleId="baja-done-modal-title"
                title="Baja registrada"
                subtitle={`La baja de ${employee.name} fue procesada.`}
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
                    {hasReplacement ? (
                        <p className="text-sm text-zinc-500 dark:text-zinc-400">
                            El recurso (<span className="font-semibold tabular-nums text-zinc-900 dark:text-zinc-100">{formatMXN(totalFreed)}</span>) fue transferido al reemplazo.
                        </p>
                    ) : (
                        <p className="text-sm text-zinc-500 dark:text-zinc-400">
                            <span className="font-semibold tabular-nums text-zinc-900 dark:text-zinc-100">{formatMXN(totalFreed)}</span>{' '}
                            quedan disponibles para reasignar en la delegación.
                        </p>
                    )}
                </div>
            </DelegationModalShell>
        );
    }

    if (step === 'new-employee') {
        return (
            <DelegationModalShell
                ariaTitleId="baja-reemplazo-modal-title"
                title="Registrar reemplazo"
                subtitle={`Nueva persona en lugar de ${employee.name} (misma UR — se transfiere recurso).`}
                onClose={onClose}
                maxWidthClass="sm:max-w-2xl"
                footer={
                    <>
                        <button type="button" onClick={handleSubmitNew} disabled={!canSubmitNew || submitting} className={delegationBtnPrimary}>
                            {submitting ? <Loader2 className="mr-2 size-4 animate-spin" /> : <CheckCircle2 className="mr-2 size-4" strokeWidth={2} />}
                            Confirmar
                        </button>
                        <button type="button" onClick={() => setStep('confirm')} className={delegationBtnSecondary}>Atrás</button>
                    </>
                }
            >
                <div className="mt-4 space-y-4">
                    <p className="rounded-lg border border-emerald-200/80 bg-emerald-50/80 px-3 py-2 text-xs text-emerald-700 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-400">
                        Los {items.length} productos ({formatMXN(totalFreed)}) se transfieren automáticamente al nuevo empleado con tallas vacías.
                    </p>
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
                    {errors.general && <p className="text-xs text-red-500">{errors.general}</p>}
                </div>
            </DelegationModalShell>
        );
    }

    return (
        <DelegationModalShell
            ariaTitleId="baja-confirm-modal-title"
            title="Solicitud de baja"
            subtitle={`${employee.name} · UR: ${employee.ur || '—'}`}
            onClose={onClose}
            maxWidthClass="sm:max-w-2xl"
            footer={
                <>
                    <button type="button" onClick={handleNext} disabled={hasReplacement === null || submitting} className={delegationBtnPrimary}>
                        {submitting ? <Loader2 className="mr-2 size-4 animate-spin" /> : <ArrowRight className="mr-2 size-4" strokeWidth={2.5} />}
                        {hasReplacement ? 'Continuar' : 'Confirmar baja'}
                    </button>
                    <button type="button" onClick={onClose} className={delegationBtnSecondary}>Cancelar</button>
                </>
            }
        >
            <div className="mt-4 space-y-4">
                <div>
                    <p className={delegationLabelClass}>Productos asignados</p>
                    <p className="text-lg font-bold tabular-nums text-zinc-900 dark:text-zinc-100">{formatMXN(totalFreed)}</p>
                    <ul className="mt-2 max-h-48 divide-y divide-zinc-100 overflow-y-auto rounded-lg border border-zinc-200/70 dark:divide-zinc-800 dark:border-zinc-800/70">
                        {items.map((item) => (
                            <li key={item.id} className="flex items-center justify-between gap-3 bg-white px-3 py-2 text-sm dark:bg-zinc-900/40">
                                <span className="min-w-0 truncate text-zinc-700 dark:text-zinc-300">
                                    <span className="mr-2 text-[10px] font-bold uppercase tracking-wider text-zinc-400">{item.type}</span>
                                    {item.name}
                                </span>
                                <span className="shrink-0 tabular-nums text-zinc-500 dark:text-zinc-400">{formatMXN(item.price)}</span>
                            </li>
                        ))}
                    </ul>
                </div>
                <div>
                    <p className={delegationLabelClass}>¿Alguien ocupará su lugar?</p>
                    <div className="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        {[
                            { val: true, label: 'Sí, hay reemplazo', desc: 'Se transfiere el recurso', Icon: UserPlus },
                            { val: false, label: 'No, solo baja', desc: 'Recurso queda libre', Icon: X },
                        ].map(({ val, label, desc, Icon: Ic }) => (
                            <button
                                key={String(val)}
                                type="button"
                                onClick={() => setHas(val)}
                                className={`flex flex-col items-center gap-1.5 rounded-lg border py-3 text-center transition-all ${
                                    hasReplacement === val
                                        ? 'border-brand-gold/50 bg-brand-gold/10 text-zinc-900 ring-1 ring-brand-gold/25 dark:text-zinc-100'
                                        : 'border-zinc-200 bg-white text-zinc-500 hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400'
                                }`}
                            >
                                <Ic className={`size-5 ${hasReplacement === val ? 'text-brand-gold' : 'text-zinc-400'}`} strokeWidth={2} />
                                <span className="text-xs font-bold uppercase tracking-wider">{label}</span>
                                <span className="text-[10px] text-zinc-400 dark:text-zinc-500">{desc}</span>
                            </button>
                        ))}
                    </div>
                </div>
                {errors.general && <p className="text-xs text-red-500">{errors.general}</p>}
            </div>
        </DelegationModalShell>
    );
}

import React, { useState } from 'react';
import { ArrowRight, CheckCircle2, Loader2, UserPlus, X } from 'lucide-react';
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
    const [newEmp, setNewEmp] = useState({ name: '', position: employee.position || '' });

    const freedItems = employee.wardrobeItems.map((i) => ({ ...i, price: i.price ?? 0 }));
    const totalFreed = freedItems.reduce((s, i) => s + i.price, 0);
    const canSubmitNew = newEmp.name.trim() && newEmp.position.trim();
    const setField = (f) => (e) => setNewEmp((p) => ({ ...p, [f]: e.target.value }));

    const finalize = (newEmployee) => {
        onConfirm(employee.id, newEmployee);
        setStep('done');
        setSubmit(false);
    };

    const handleConfirmBaja = () => {
        if (hasReplacement === null) return;
        if (hasReplacement) return setStep('new-employee');
        setSubmit(true);
        setTimeout(() => finalize(null), 500);
    };

    const handleSubmitNew = () => {
        if (!canSubmitNew) return;
        setSubmit(true);
        setTimeout(() => finalize(newEmp), 500);
    };

    if (step === 'done') {
        return (
            <DelegationModalShell
                ariaTitleId="baja-done-modal-title"
                title="Solicitud registrada"
                subtitle={`La baja de ${employee.name} fue enviada para revisión administrativa.`}
                onClose={onClose}
                maxWidthClass="sm:max-w-lg"
                footer={
                    <div className="flex w-full justify-center sm:col-span-full">
                        <button type="button" onClick={onClose} className={`${delegationBtnSecondary} sm:mt-0`}>
                            Cerrar
                        </button>
                    </div>
                }
            >
                <div className="mt-4 flex flex-col items-center gap-3 text-center">
                    <div className="flex size-12 items-center justify-center rounded-full bg-emerald-500/10 dark:bg-emerald-500/15">
                        <CheckCircle2 className="size-6 text-emerald-600 dark:text-emerald-400" strokeWidth={2} />
                    </div>
                    <p className="text-sm text-zinc-500 dark:text-zinc-400">
                        <span className="font-semibold tabular-nums text-zinc-900 dark:text-zinc-100">
                            {formatMXN(totalFreed)}
                        </span>{' '}
                        quedan disponibles para reasignar en la delegación.
                    </p>
                </div>
            </DelegationModalShell>
        );
    }

    if (step === 'new-employee') {
        return (
            <DelegationModalShell
                ariaTitleId="baja-reemplazo-modal-title"
                title="Registrar reemplazo"
                subtitle={`Nuevo trabajador en el lugar de ${employee.name}.`}
                onClose={onClose}
                maxWidthClass="sm:max-w-2xl"
                footer={
                    <>
                        <button
                            type="button"
                            onClick={handleSubmitNew}
                            disabled={!canSubmitNew || submitting}
                            className={delegationBtnPrimary}
                        >
                            {submitting ? (
                                <Loader2 className="mr-2 size-4 animate-spin" />
                            ) : (
                                <CheckCircle2 className="mr-2 size-4" strokeWidth={2} />
                            )}
                            Confirmar y registrar
                        </button>
                        <button type="button" onClick={() => setStep('confirm')} className={delegationBtnSecondary}>
                            Atrás
                        </button>
                    </>
                }
            >
                <div className="mt-4 space-y-4">
                    <p className="rounded-lg border border-zinc-200/80 bg-zinc-50/80 px-3 py-2 text-xs text-zinc-600 dark:border-zinc-800 dark:bg-zinc-900/50 dark:text-zinc-400">
                        Presupuesto vinculado al puesto:{' '}
                        <span className="font-bold tabular-nums text-brand-gold">{formatMXN(totalFreed)}</span>
                    </p>
                    <div>
                        <label className={delegationLabelClass}>Nombre completo</label>
                        <input
                            type="text"
                            value={newEmp.name}
                            onChange={setField('name')}
                            placeholder="Nombre del nuevo trabajador"
                            className={delegationInputClass}
                        />
                    </div>
                    <div>
                        <label className={delegationLabelClass}>Puesto</label>
                        <input
                            type="text"
                            value={newEmp.position}
                            onChange={setField('position')}
                            placeholder="Puesto"
                            className={delegationInputClass}
                        />
                    </div>
                </div>
            </DelegationModalShell>
        );
    }

    return (
        <DelegationModalShell
            ariaTitleId="baja-confirm-modal-title"
            title="Solicitud de baja"
            subtitle={`${employee.name} · ${employee.position}`}
            onClose={onClose}
            maxWidthClass="sm:max-w-2xl"
            footer={
                <>
                    <button
                        type="button"
                        onClick={handleConfirmBaja}
                        disabled={hasReplacement === null || submitting}
                        className={delegationBtnPrimary}
                    >
                        {submitting ? (
                            <Loader2 className="mr-2 size-4 animate-spin" />
                        ) : (
                            <ArrowRight className="mr-2 size-4" strokeWidth={2.5} />
                        )}
                        {hasReplacement ? 'Continuar' : 'Confirmar baja'}
                    </button>
                    <button type="button" onClick={onClose} className={delegationBtnSecondary}>
                        Cancelar
                    </button>
                </>
            }
        >
            <div className="mt-4 space-y-4">
                <div>
                    <p className={delegationLabelClass}>Presupuesto que quedará libre</p>
                    <p className="text-lg font-bold tabular-nums text-zinc-900 dark:text-zinc-100">{formatMXN(totalFreed)}</p>
                    <ul className="mt-2 divide-y divide-zinc-100 overflow-hidden rounded-lg border border-zinc-200/70 dark:divide-zinc-800 dark:border-zinc-800/70">
                        {freedItems.map((item) => (
                            <li
                                key={item.id}
                                className="flex items-center justify-between gap-3 bg-white px-3 py-2 text-sm dark:bg-zinc-900/40"
                            >
                                <span className="min-w-0 truncate text-zinc-700 dark:text-zinc-300">
                                    <span className="mr-2 text-[10px] font-bold uppercase tracking-wider text-zinc-400">
                                        {item.type}
                                    </span>
                                    {item.name}
                                </span>
                                <span className="shrink-0 tabular-nums text-zinc-500 dark:text-zinc-400">
                                    {formatMXN(item.price)}
                                </span>
                            </li>
                        ))}
                    </ul>
                    <p className="mt-1.5 text-[11px] text-zinc-400 dark:text-zinc-500">
                        Podrá reasignarse a otros trabajadores de la delegación.
                    </p>
                </div>
                <p className="text-xs leading-relaxed text-zinc-500 dark:text-zinc-400">
                    La solicitud será revisada por el área administrativa antes de aplicarse en el registro oficial.
                </p>
                <div>
                    <p className={delegationLabelClass}>¿Alguien ocupará su lugar?</p>
                    <div className="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        {[
                            { val: true, label: 'Sí, hay reemplazo', Icon: UserPlus },
                            { val: false, label: 'No, solo baja', Icon: X },
                        ].map(({ val, label, Icon: Ic }) => (
                            <button
                                key={String(val)}
                                type="button"
                                onClick={() => setHas(val)}
                                className={`flex items-center justify-center gap-2 rounded-lg border py-2.5 text-xs font-bold uppercase tracking-wider transition-all ${
                                    hasReplacement === val
                                        ? 'border-brand-gold/50 bg-brand-gold/10 text-zinc-900 ring-1 ring-brand-gold/25 dark:text-zinc-100'
                                        : 'border-zinc-200 bg-white text-zinc-500 hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400'
                                }`}
                            >
                                <Ic
                                    className={`size-4 ${hasReplacement === val ? 'text-brand-gold' : 'text-zinc-400'}`}
                                    strokeWidth={2}
                                />
                                {label}
                            </button>
                        ))}
                    </div>
                </div>
            </div>
        </DelegationModalShell>
    );
}

import React, { useState } from 'react';
import { Building2, CheckCircle2, ChevronDown, Loader2 } from 'lucide-react';
import { DELEGACIONES } from './constants';
import DelegationModalShell, {
    delegationBtnPrimary,
    delegationBtnSecondary,
    delegationInputClass,
    delegationLabelClass,
} from './DelegationModalShell';

export default function EditModal({ employee, currentDelegation, onClose, onSave }) {
    const [form, setForm] = useState({
        name: employee.name || '',
        position: employee.position || '',
        delegation: currentDelegation,
    });
    const [saving, setSaving] = useState(false);
    const set = (f) => (e) => setForm((p) => ({ ...p, [f]: e.target.value }));
    const canSave = form.name.trim() && form.position.trim() && form.delegation;
    const changed = form.delegation !== currentDelegation;

    const handleSave = () => {
        if (!canSave) return;
        setSaving(true);
        setTimeout(() => {
            onSave(employee.id, form);
            setSaving(false);
        }, 400);
    };

    return (
        <DelegationModalShell
            ariaTitleId="edit-employee-modal-title"
            title="Editar trabajador"
            subtitle="Actualiza nombre, puesto o delegación. Los cambios quedan sujetos a revisión administrativa si aplica."
            onClose={onClose}
            maxWidthClass="sm:max-w-2xl"
            footer={
                <>
                    <button type="button" onClick={handleSave} disabled={!canSave || saving} className={delegationBtnPrimary}>
                        {saving ? (
                            <Loader2 className="mr-2 size-4 animate-spin" />
                        ) : (
                            <CheckCircle2 className="mr-2 size-4" strokeWidth={2} />
                        )}
                        Actualizar
                    </button>
                    <button type="button" onClick={onClose} className={delegationBtnSecondary}>
                        Cancelar
                    </button>
                </>
            }
        >
            <div className="mt-4 space-y-4">
                <div>
                    <label className={delegationLabelClass}>Nombre completo</label>
                    <input type="text" value={form.name} onChange={set('name')} className={delegationInputClass} />
                </div>
                <div>
                    <label className={delegationLabelClass}>Puesto</label>
                    <input type="text" value={form.position} onChange={set('position')} className={delegationInputClass} />
                </div>
                <div>
                    <div className="mb-2 flex items-center gap-2">
                        <Building2 className="size-4 text-brand-gold" strokeWidth={2} />
                        <span className="text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            Delegación asignada
                        </span>
                    </div>
                    <div className="relative">
                        <select
                            value={form.delegation}
                            onChange={set('delegation')}
                            className={`${delegationInputClass} cursor-pointer appearance-none pr-10`}
                        >
                            {DELEGACIONES.map((d) => (
                                <option key={d} value={d}>
                                    {d}
                                </option>
                            ))}
                        </select>
                        <ChevronDown
                            className="pointer-events-none absolute right-3 top-1/2 size-4 -translate-y-1/2 text-zinc-400"
                            strokeWidth={2}
                        />
                    </div>
                    {changed ? (
                        <p className="mt-2 text-xs leading-relaxed text-zinc-500 dark:text-zinc-400">
                            Si cambias de delegación, esta persona{' '}
                            <span className="font-semibold text-zinc-700 dark:text-zinc-300">dejará de listarse aquí</span>{' '}
                            hasta que administración confirme el traslado.
                        </p>
                    ) : null}
                </div>
            </div>
        </DelegationModalShell>
    );
}

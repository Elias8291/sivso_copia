import React, { useMemo, useState } from 'react';
import { CheckCircle2, ChevronDown, ChevronUp, Loader2, Pencil, Save, UserMinus, ArrowRightLeft } from 'lucide-react';
import { CLS } from './constants';
import { statusCfg } from './utils';
import ItemRow from './ItemRow';
import EditModal from './EditModal';
import BajaModal from './BajaModal';
import CambioModal from './CambioModal';

export default function EmployeeAccordion({
    employee,
    delegation,
    dependencias = [],
    delegaciones = [],
    delegacionesPorUr = {},
    delegacionActivaId,
    onSave,
    onEdit,
    onRequestBaja,
    isSaving,
    defaultOpen,
}) {
    const [open, setOpen] = useState(defaultOpen ?? false);
    const [draft, setDraft] = useState({ ...employee.selections });
    const [justSaved, setJustSaved] = useState(false);
    const [showBaja, setShowBaja] = useState(false);
    const [showCambio, setShowCambio] = useState(false);
    const [showEdit, setShowEdit] = useState(false);

    const cfg = statusCfg(employee.status);
    const { filled, total, missing } = useMemo(() => {
        const items = employee.wardrobeItems;
        const t = items.length;
        let f = 0;
        for (const i of items) {
            if (draft[i.id]) f++;
        }
        return { filled: f, total: t, missing: t - f };
    }, [employee.wardrobeItems, draft]);

    const allFilled = total > 0 && filled === total;
    const isDirty = employee.wardrobeItems.some((i) => draft[i.id] !== employee.selections[i.id]);

    const handleSave = () => {
        if (!isDirty || isSaving) return;
        onSave(employee.id, draft, () => {
            setJustSaved(true);
            setTimeout(() => setJustSaved(false), 2200);
        });
    };

    const hintClosed =
        !open && total > 0
            ? missing === 0
                ? 'Vestuario completo'
                : missing === total
                  ? `${total} ${total === 1 ? 'prenda' : 'prendas'} por asignar`
                  : `Faltan ${missing} ${missing === 1 ? 'talla' : 'tallas'}`
            : '';

    const isBaja = employee.status === 'Baja';

    return (
        <>
            <div
                className={`overflow-hidden rounded-xl border transition-[border-color,box-shadow] duration-200 ${
                    isBaja
                        ? 'border-red-200/60 bg-red-50/30 dark:border-red-900/30 dark:bg-red-950/10'
                        : open
                            ? 'border-zinc-300/80 shadow-md shadow-zinc-900/5 dark:border-zinc-600/50 dark:shadow-none bg-white dark:bg-zinc-900/60'
                            : 'border-zinc-200/80 hover:border-zinc-300/90 dark:border-zinc-800/80 dark:hover:border-zinc-700/80 bg-white dark:bg-zinc-900/60'
                }`}
            >
                <div className="flex flex-wrap items-start gap-2 px-3 py-2.5 sm:flex-nowrap sm:items-center sm:gap-3 sm:px-4 sm:py-3">
                    <div className="flex min-w-0 flex-1 basis-[min(100%,14rem)] flex-col sm:basis-0">
                        <div className="flex w-full items-center gap-3 sm:gap-3.5">
                            <div className="relative shrink-0">
                                <div className={`flex size-10 items-center justify-center rounded-xl text-sm font-bold ring-2 sm:size-11 ${
                                    isBaja
                                        ? 'bg-gradient-to-br from-red-100 to-red-200/90 text-red-700 ring-red-50 dark:from-red-900/40 dark:to-red-900/30 dark:text-red-400 dark:ring-red-950/50'
                                        : 'bg-gradient-to-br from-zinc-100 to-zinc-200/90 text-zinc-700 ring-white dark:from-zinc-800 dark:to-zinc-800/80 dark:text-zinc-200 dark:ring-zinc-900/80'
                                }`}>
                                    {employee.name.charAt(0).toUpperCase()}
                                </div>
                                {isSaving && (
                                    <Loader2 className="absolute -right-0.5 -top-0.5 size-3.5 animate-spin text-brand-gold" />
                                )}
                            </div>
                            <div className="min-w-0 flex-1">
                                <div className="flex flex-wrap items-center gap-2">
                                    <span className="text-[13px] font-bold leading-tight text-zinc-900 dark:text-zinc-100">
                                        {employee.name}
                                    </span>
                                    <span
                                        className={`inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider ${cfg.badge} ${cfg.text}`}
                                    >
                                        <span className={`size-1.5 shrink-0 rounded-full ${cfg.dot}`} />
                                        {employee.status}
                                    </span>
                                </div>
                                <p className="mt-0.5 truncate text-[10px] font-medium text-zinc-400 dark:text-zinc-500">
                                    {employee.position}
                                </p>
                                <div className="mt-1 flex flex-wrap items-center gap-x-3 gap-y-0.5 text-[10px] text-zinc-500 dark:text-zinc-400">
                                    <span className="tabular-nums">
                                        <span className="font-semibold text-zinc-700 dark:text-zinc-300">{filled}</span>
                                        <span className="text-zinc-400">/{total}</span> prendas
                                    </span>
                                    {!open && hintClosed ? (
                                        <span className="text-zinc-400 dark:text-zinc-500">· {hintClosed}</span>
                                    ) : null}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="ml-auto flex w-full shrink-0 flex-wrap items-center justify-end gap-1.5 sm:ml-0 sm:w-auto sm:self-center">
                        {isBaja ? (
                            <span className="inline-flex items-center gap-1.5 rounded-lg border border-red-200/60 bg-red-50/80 px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-wider text-red-600 dark:border-red-900/40 dark:bg-red-950/20 dark:text-red-400">
                                Dado de baja
                            </span>
                        ) : (
                        <>
                        {total > 0 ? (
                            <button
                                type="button"
                                onClick={() => setOpen((p) => !p)}
                                aria-expanded={open}
                                aria-controls={open ? `wardrobe-panel-${employee.id}` : undefined}
                                id={`wardrobe-trigger-${employee.id}`}
                                className={
                                    open
                                        ? `${CLS.chipBtn} order-first sm:order-none`
                                        : 'order-first inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-brand-gold/45 bg-brand-gold/[0.09] px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-wider text-brand-gold shadow-sm transition-all hover:border-brand-gold/65 hover:bg-brand-gold/15 dark:border-brand-gold/35 dark:bg-brand-gold/12 dark:text-brand-gold-soft dark:hover:bg-brand-gold/18 sm:order-none sm:w-auto'
                                }
                            >
                                {open ? (
                                    <>
                                        <ChevronUp className="size-3.5" strokeWidth={2} aria-hidden />
                                        <span>Ocultar</span>
                                    </>
                                ) : (
                                    <>
                                        <ChevronDown className="size-3.5" strokeWidth={2} aria-hidden />
                                        <span>Actualizar tallas</span>
                                    </>
                                )}
                            </button>
                        ) : null}
                        <button
                            type="button"
                            onClick={(e) => {
                                e.stopPropagation();
                                setShowEdit(true);
                            }}
                            className={CLS.chipBtn}
                            title="Editar datos del trabajador"
                        >
                            <Pencil className="size-3.5" strokeWidth={2} />
                            <span>Editar</span>
                        </button>
                        <button
                            type="button"
                            onClick={(e) => {
                                e.stopPropagation();
                                setShowCambio(true);
                            }}
                            className={`${CLS.chipBtn} hover:border-blue-200 hover:bg-blue-50/80 hover:text-blue-700 dark:hover:border-blue-900/50 dark:hover:bg-blue-950/30 dark:hover:text-blue-400`}
                            title="Cambio de UR o delegación"
                        >
                            <ArrowRightLeft className="size-3.5" strokeWidth={2} />
                            <span>Cambio</span>
                        </button>
                        <button
                            type="button"
                            onClick={(e) => {
                                e.stopPropagation();
                                setShowBaja(true);
                            }}
                            className={`${CLS.chipBtn} hover:border-red-200 hover:bg-red-50/80 hover:text-red-700 dark:hover:border-red-900/50 dark:hover:bg-red-950/30 dark:hover:text-red-400`}
                            title="Dar de baja"
                        >
                            <UserMinus className="size-3.5" strokeWidth={2} />
                            <span>Baja</span>
                        </button>
                        </>
                        )}
                    </div>
                </div>

                {open && !isBaja && (
                    <div
                        id={`wardrobe-panel-${employee.id}`}
                        role="region"
                        aria-labelledby={`wardrobe-trigger-${employee.id}`}
                        className="border-t border-zinc-100 bg-zinc-50/40 px-3 pb-3 pt-2 dark:border-zinc-800/60 dark:bg-zinc-950/25 sm:px-4 sm:pb-4 sm:pt-2.5"
                    >
                        <p className="mb-2 text-[10px] leading-relaxed text-zinc-500 dark:text-zinc-400">
                            Elige talla en cada fila y pulsa <span className="font-medium text-zinc-700 dark:text-zinc-300">Guardar</span>.
                        </p>
                        <ul className="divide-y divide-zinc-200/80 dark:divide-zinc-800/80">
                            {employee.wardrobeItems.map((item) => (
                                <li
                                    key={item.id}
                                    className="transition-colors first:rounded-t-lg last:rounded-b-lg hover:bg-white/60 dark:hover:bg-zinc-900/40"
                                >
                                    <ItemRow
                                        item={item}
                                        value={draft[item.id]}
                                        onChange={(size) => {
                                            setDraft((p) => ({ ...p, [item.id]: size }));
                                            setJustSaved(false);
                                        }}
                                        disabled={isSaving}
                                    />
                                </li>
                            ))}
                        </ul>
                        <div className="mt-4 flex flex-col gap-2 rounded-lg border border-zinc-200/60 bg-white/90 px-3 py-3 dark:border-zinc-800/60 dark:bg-zinc-900/50 sm:flex-row sm:items-center sm:justify-end sm:gap-3 sm:px-4">
                            {justSaved ? (
                                <p className="order-2 text-center text-[11px] text-emerald-600 dark:text-emerald-400 sm:order-1 sm:mr-auto sm:text-left">
                                    <CheckCircle2 className="mr-1 inline size-3 align-text-bottom" strokeWidth={2.5} />
                                    Guardado
                                </p>
                            ) : isDirty ? (
                                <p className="order-2 text-center text-[11px] text-zinc-400 sm:order-1 sm:mr-auto sm:text-left">
                                    Hay cambios sin guardar.
                                </p>
                            ) : (
                                <p className="order-2 text-center text-[11px] text-zinc-400 dark:text-zinc-500 sm:order-1 sm:mr-auto sm:text-left">
                                    Selecciona las tallas que deseas actualizar.
                                </p>
                            )}
                            <button
                                type="button"
                                onClick={handleSave}
                                disabled={!isDirty || isSaving}
                                title={!isDirty ? 'Sin cambios' : undefined}
                                className="order-1 inline-flex w-full items-center justify-center gap-2 rounded-md bg-zinc-900 px-4 py-2 text-[11px] font-bold uppercase tracking-wider text-white transition-colors hover:bg-zinc-800 disabled:cursor-not-allowed disabled:opacity-40 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white sm:order-2 sm:w-auto"
                            >
                                {isSaving ? (
                                    <Loader2 className="size-3.5 animate-spin" />
                                ) : justSaved ? (
                                    <CheckCircle2 className="size-3.5" strokeWidth={2.5} />
                                ) : (
                                    <Save className="size-3.5" strokeWidth={2} />
                                )}
                                {justSaved ? 'Guardado' : 'Guardar'}
                            </button>
                        </div>
                    </div>
                )}
            </div>

            {showEdit && (
                <EditModal
                    employee={employee}
                    currentDelegation={delegation}
                    onClose={() => setShowEdit(false)}
                    onSave={(id, f) => {
                        onEdit(id, f);
                        setShowEdit(false);
                    }}
                />
            )}
            {showBaja && (
                <BajaModal
                    employee={employee}
                    onClose={() => setShowBaja(false)}
                    onConfirm={(id, ne) => {
                        onRequestBaja(id, ne);
                    }}
                />
            )}
            {showCambio && (
                <CambioModal
                    employee={employee}
                    dependencias={dependencias}
                    delegaciones={delegaciones}
                    delegacionesPorUr={delegacionesPorUr}
                    delegacionActivaId={delegacionActivaId}
                    onClose={() => setShowCambio(false)}
                    onConfirm={(id, ne) => {
                        onRequestBaja(id, ne);
                    }}
                />
            )}
        </>
    );
}

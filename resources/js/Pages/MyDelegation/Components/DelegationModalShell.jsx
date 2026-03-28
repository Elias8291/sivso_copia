import React, { useEffect } from 'react';

/**
 * Misma envoltura que el modal de nuevo/editar usuario (Periodos/Usuarios).
 */
export default function DelegationModalShell({
    ariaTitleId,
    title,
    subtitle,
    onClose,
    children,
    footer,
    maxWidthClass = 'sm:max-w-2xl',
}) {
    useEffect(() => {
        const onKey = (e) => {
            if (e.key === 'Escape') onClose();
        };
        document.addEventListener('keydown', onKey);
        return () => document.removeEventListener('keydown', onKey);
    }, [onClose]);

    return (
        <div className="relative z-[100]" role="dialog" aria-modal="true" aria-labelledby={ariaTitleId}>
            <div
                className="fixed inset-0 bg-zinc-900/40 transition-opacity dark:bg-black/60"
                onClick={onClose}
            />
            <div className="fixed inset-0 z-[101] w-screen overflow-y-auto">
                <div className="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div
                        className={`relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all dark:bg-zinc-900 sm:my-8 sm:w-full border border-zinc-200/50 dark:border-zinc-800/50 ${maxWidthClass}`}
                    >
                        <div className="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 dark:bg-zinc-900">
                            <div>
                                <h3
                                    className="text-base font-bold text-zinc-900 dark:text-zinc-100"
                                    id={ariaTitleId}
                                >
                                    {title}
                                </h3>
                                {subtitle ? (
                                    <p className="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{subtitle}</p>
                                ) : null}
                            </div>
                            {children}
                        </div>
                        {footer ? (
                            <div className="border-t border-zinc-100 bg-zinc-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:items-center sm:px-6 dark:border-zinc-800/50 dark:bg-zinc-800/50">
                                {footer}
                            </div>
                        ) : null}
                    </div>
                </div>
            </div>
        </div>
    );
}

export const delegationInputClass =
    'w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:border-brand-gold focus:ring-1 focus:ring-brand-gold dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100';

export const delegationLabelClass =
    'mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400';

export const delegationBtnPrimary =
    'inline-flex w-full items-center justify-center rounded-md bg-zinc-900 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white shadow-sm transition-colors hover:bg-zinc-800 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white sm:ml-3 sm:w-auto';

export const delegationBtnSecondary =
    'mt-3 inline-flex w-full items-center justify-center rounded-md bg-white px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-zinc-700 shadow-sm ring-1 ring-inset ring-zinc-300 transition-colors hover:bg-zinc-50 sm:mt-0 sm:w-auto dark:bg-zinc-800 dark:text-zinc-300 dark:ring-zinc-700 dark:hover:bg-zinc-700';

export const FILTERS = [
    { label: 'Todos',        value: 'all' },
    { label: 'Por terminar', value: 'En progreso' },
    { label: 'Sin empezar',  value: 'Pendiente' },
    { label: 'Listos',       value: 'Completado' },
];

/** @todo conectar con backend */
export const DELEGACIONES = ['Delegación Centro','Delegación Norte','Delegación Sur','Delegación Oriente','Delegación Poniente','Delegación Aeropuerto'];

export const CLS = {
    input:   'w-full rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none transition-all placeholder:text-zinc-400 focus:border-brand-gold/60 focus:ring-4 focus:ring-brand-gold/10 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:placeholder:text-zinc-600',
    btnGhost:'flex-1 rounded-xl border border-zinc-200 bg-white py-2.5 text-sm font-semibold text-zinc-600 hover:bg-zinc-50 transition-colors dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300',
    btnGold: 'flex flex-1 items-center justify-center gap-2 rounded-xl bg-brand-gold py-2.5 text-sm font-bold text-white shadow-sm hover:bg-brand-gold/90 disabled:cursor-not-allowed disabled:opacity-40 transition-all',
    label:   'mb-1.5 block text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500',
    chipBtn: 'shrink-0 flex items-center gap-1.5 rounded-lg border border-zinc-200/60 bg-zinc-50/60 px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-wider text-zinc-500 transition-all hover:bg-zinc-100 hover:border-zinc-300 hover:text-zinc-700 dark:border-zinc-700/60 dark:bg-zinc-800/60 dark:text-zinc-400 dark:hover:bg-zinc-700/60',
    modalWrap:'fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/30 backdrop-blur-sm',
    modalBox: 'relative w-full max-w-lg overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-2xl dark:border-zinc-800 dark:bg-zinc-950 max-h-[90vh] overflow-y-auto',
    closeBtn: 'absolute right-4 top-5 rounded-lg p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-700 transition-colors dark:hover:bg-zinc-800 dark:hover:text-zinc-200',
    divider:  'border-t border-zinc-100 dark:border-zinc-800',
    section:  'p-7 space-y-6',
    eyebrow:  'text-[10px] font-bold uppercase tracking-[0.2em] text-brand-gold',
    subLabel: 'text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500',
    infoBox:  'rounded-xl border border-zinc-200/60 bg-zinc-50/80 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-900/40',
};

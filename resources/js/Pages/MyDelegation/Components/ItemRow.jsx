import React from 'react';

export default function ItemRow({ item, value, onChange, disabled }) {
    const has = !!value;

    return (
        <div className="flex flex-col gap-2.5 py-3.5 sm:flex-row sm:items-start sm:justify-between sm:gap-6 sm:py-4">
            <div className="min-w-0 flex-1">
                <p className="text-[13px] font-semibold leading-snug text-zinc-900 dark:text-zinc-100">
                    {item.name}
                    {item.type ? (
                        <span className="font-normal text-zinc-400 dark:text-zinc-500"> · {item.type}</span>
                    ) : null}
                </p>
                {item.description ? (
                    <p className="mt-1.5 text-[12px] leading-relaxed text-zinc-500 dark:text-zinc-400">{item.description}</p>
                ) : null}
            </div>
            <div className="shrink-0 sm:w-[7.5rem] sm:pt-0.5">
                <label className="mb-1 block text-[9px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 sm:sr-only">
                    Talla
                </label>
                <select
                    value={value || ''}
                    onChange={(e) => onChange(e.target.value)}
                    disabled={disabled}
                    className={`w-full rounded-lg border bg-white px-3 py-2 text-[13px] font-medium outline-none transition-all disabled:opacity-50 dark:bg-zinc-900 ${
                        has
                            ? 'border-zinc-200 text-zinc-900 shadow-sm focus:border-brand-gold/50 focus:ring-2 focus:ring-brand-gold/15 dark:border-zinc-600 dark:text-zinc-100'
                            : 'border-dashed border-zinc-300 text-zinc-400 focus:border-brand-gold/40 focus:ring-2 focus:ring-brand-gold/10 dark:border-zinc-600 dark:text-zinc-500'
                    }`}
                >
                    <option value="" disabled>
                        Elegir…
                    </option>
                    {item.sizes.map((s) => (
                        <option key={s} value={s}>
                            {s}
                        </option>
                    ))}
                </select>
            </div>
        </div>
    );
}

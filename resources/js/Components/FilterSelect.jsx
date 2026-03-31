import { ChevronDown } from 'lucide-react';

export default function FilterSelect({ label, value, onChange, options, icon: Icon }) {
    return (
        <div className="w-full">
            <label className="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                {label}
            </label>
            <div className="relative">
                {Icon && <Icon className="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-zinc-400" strokeWidth={2} />}
                <select
                    value={value}
                    onChange={(e) => onChange(e.target.value)}
                    className={`w-full appearance-none rounded-lg border border-zinc-200 bg-white/80 py-2.5 ${Icon ? 'pl-10' : 'pl-3'} pr-10 text-sm font-medium text-zinc-700 outline-none transition-all focus:border-brand-gold focus:ring-1 focus:ring-brand-gold dark:border-zinc-800 dark:bg-zinc-900/80 dark:text-zinc-300`}
                >
                    {options.map((opt, idx) => (
                        <option key={`${opt.value}-${idx}`} value={opt.value}>
                            {opt.label}
                        </option>
                    ))}
                </select>
                <ChevronDown className="pointer-events-none absolute right-3.5 top-1/2 size-4 -translate-y-1/2 text-zinc-400" strokeWidth={2} />
            </div>
        </div>
    );
}

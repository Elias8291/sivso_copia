import { useState } from 'react';

export default function PasswordInput({
    name,
    value,
    onChange,
    placeholder = '',
    id,
    autoComplete = 'current-password',
}) {
    const [showPassword, setShowPassword] = useState(false);

    return (
        <div className="relative">
            <input
                id={id}
                type={showPassword ? 'text' : 'password'}
                name={name}
                value={value}
                onChange={onChange}
                placeholder={placeholder}
                className="w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 pr-24 text-sm text-zinc-900 outline-none transition-all placeholder:text-zinc-400 focus:ring-2 focus:ring-brand-gold/25 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white dark:placeholder:text-zinc-600"
                autoComplete={autoComplete}
            />
            <button
                type="button"
                onClick={() => setShowPassword((current) => !current)}
                className="absolute right-3 top-1/2 -translate-y-1/2 rounded-md px-2 py-1 text-[11px] font-semibold uppercase tracking-[0.08em] text-zinc-500 transition hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200"
            >
                {showPassword ? 'Ocultar' : 'Mostrar'}
            </button>
        </div>
    );
}

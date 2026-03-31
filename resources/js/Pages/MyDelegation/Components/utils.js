import { Briefcase, Footprints, Shirt } from 'lucide-react';

export const formatMXN = (n) => new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(n);

export const statusCfg = (s) => {
    if (s === 'Baja') {
        return {
            dot: 'bg-red-500',
            text: 'text-red-700 dark:text-red-400',
            badge: 'bg-red-500/10 dark:bg-red-500/15',
        };
    }
    if (s === 'Completado') {
        return {
            dot: 'bg-emerald-500',
            text: 'text-emerald-700 dark:text-emerald-400',
            badge: 'bg-emerald-500/10 dark:bg-emerald-500/15',
        };
    }
    if (s === 'En progreso') {
        return {
            dot: 'bg-brand-gold',
            text: 'text-amber-800 dark:text-brand-gold-soft',
            badge: 'bg-brand-gold/12 dark:bg-brand-gold/15',
        };
    }
    return {
        dot: 'bg-zinc-400 dark:bg-zinc-500',
        text: 'text-zinc-600 dark:text-zinc-400',
        badge: 'bg-zinc-200/80 dark:bg-zinc-700/50',
    };
};

export const itemIcon = (t) => t === 'Calzado' ? Footprints : t === 'Inferior' ? Briefcase : Shirt;

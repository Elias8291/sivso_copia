import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import {
    Shirt,
    Briefcase,
    Footprints,
    Info,
    Save,
    CheckCircle2,
    AlertCircle,
    Building2,
    BriefcaseBusiness,
    ArrowLeft
} from 'lucide-react';

const getItemIcon = (type) => {
    switch (type) {
        case 'Prenda Superior': return Shirt;
        case 'Prenda Inferior': return Briefcase;
        case 'Calzado': return Footprints;
        case 'Exterior': return Shirt;
        default: return Shirt;
    }
};

export default function Show({ employee, wardrobeItems }) {
    const [selections, setSelections] = useState(() => {
        const initial = {};
        wardrobeItems.forEach(item => {
            initial[item.id] = item.current_size;
        });
        return initial;
    });

    const [isSaving, setIsSaving] = useState(false);
    const [saved, setSaved] = useState(false);

    const missingSelections = Object.values(selections).some(size => !size);

    const handleSizeChange = (id, size) => {
        setSelections(prev => ({ ...prev, [id]: size }));
        setSaved(false);
    };

    const handleSave = () => {
        setIsSaving(true);
        setTimeout(() => {
            setIsSaving(false);
            setSaved(true);
        }, 800);
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                    <span>SIVSO</span>
                    <span className="text-zinc-300 dark:text-zinc-600">/</span>
                    <Link href={route('my-delegation.index')} className="hover:text-zinc-800 dark:hover:text-zinc-200 transition-colors">
                        MI DELEGACIÓN
                    </Link>
                    <span className="text-zinc-300 dark:text-zinc-600">/</span>
                    <span className="text-zinc-900 dark:text-zinc-100">VESTUARIO</span>
                </div>
            }
        >
            <Head title={`Vestuario - ${employee.name}`} />

            <div className="mx-auto w-full max-w-[1600px] space-y-8">
                {/* Header Section */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                            Vestuario de Trabajador
                        </h2>
                        <p className="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            Gestiona las tallas para este empleado.
                        </p>
                    </div>
                    <div className="flex items-center gap-3">
                        <Link
                            href={route('my-delegation.index')}
                            className="inline-flex items-center justify-center gap-2 rounded-md border border-zinc-200 bg-white px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-800 dark:bg-[#0A0A0B] dark:text-zinc-300 dark:hover:bg-zinc-900"
                        >
                            <ArrowLeft className="size-4" strokeWidth={2} />
                            Regresar
                        </Link>
                        <button
                            onClick={handleSave}
                            disabled={isSaving || missingSelections}
                            className="inline-flex items-center justify-center gap-2 rounded-md bg-brand-gold px-6 py-2.5 text-xs font-bold uppercase tracking-wider text-white transition-all hover:bg-brand-gold/90 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-brand-gold/90 dark:hover:bg-brand-gold"
                        >
                            {isSaving ? (
                                <span className="size-4 animate-spin rounded-full border-2 border-white/20 border-t-white" />
                            ) : (
                                <Save className="size-4" strokeWidth={2} />
                            )}
                            {saved ? 'Guardado' : 'Guardar Selección'}
                        </button>
                    </div>
                </div>

                {/* Info Banner */}
                <div className="flex items-start gap-3 rounded-lg border-l-2 border-brand-gold bg-gradient-to-r from-brand-gold/10 to-transparent p-4 dark:from-brand-gold/5">
                    <Info className="mt-0.5 size-5 shrink-0 text-brand-gold dark:text-brand-gold-soft" strokeWidth={2} />
                    <div>
                        <h4 className="text-sm font-bold text-brand-gold dark:text-brand-gold-soft">
                            Periodo de Selección Abierto
                        </h4>
                        <p className="mt-1 text-sm text-zinc-700 dark:text-zinc-300">
                            Tienes hasta el <span className="font-bold">{employee.deadline}</span> para confirmar las tallas de este trabajador.
                        </p>
                    </div>
                </div>

                <div className="grid grid-cols-1 gap-8 lg:grid-cols-12">
                    {/* Columna Izquierda: Datos del Empleado */}
                    <div className="lg:col-span-4 xl:col-span-3 space-y-6">
                        <div className="overflow-hidden rounded-2xl border border-zinc-200/50 bg-white/40 shadow-sm backdrop-blur-xl dark:border-zinc-800/50 dark:bg-zinc-900/40">
                            <div className="border-b border-zinc-200/50 px-6 py-4 dark:border-zinc-800/50">
                                <h3 className="text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    Información del Empleado
                                </h3>
                            </div>
                            <div className="p-6 space-y-6">
                                <div className="flex items-center gap-4">
                                    <div className="flex size-12 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-lg font-bold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                        {employee.name.charAt(0)}
                                    </div>
                                    <div className="min-w-0">
                                        <p className="truncate text-sm font-bold text-zinc-900 dark:text-zinc-100">
                                            {employee.name}
                                        </p>
                                        <div className="mt-1 flex items-center gap-1.5">
                                            <span className={`size-1.5 rounded-full ${missingSelections ? 'bg-amber-500' : 'bg-emerald-500'}`} />
                                            <span className="text-[11px] font-medium text-zinc-600 dark:text-zinc-400">
                                                {missingSelections ? 'Faltan Tallas' : 'Completado'}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div className="space-y-4 pt-4 border-t border-zinc-200/50 dark:border-zinc-800/50">
                                    <div>
                                        <div className="flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                            <Building2 className="size-3.5" />
                                            Dependencia
                                        </div>
                                        <p className="mt-1 text-[13px] font-medium text-zinc-900 dark:text-zinc-100">
                                            {employee.department}
                                        </p>
                                    </div>
                                    <div>
                                        <div className="flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                            <BriefcaseBusiness className="size-3.5" />
                                            Puesto
                                        </div>
                                        <p className="mt-1 text-[13px] font-medium text-zinc-900 dark:text-zinc-100">
                                            {employee.position}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Columna Derecha: Grid de Prendas (List View) */}
                    <div className="lg:col-span-8 xl:col-span-9">
                        <div className="overflow-hidden rounded-2xl border border-zinc-200/50 bg-white/40 shadow-sm backdrop-blur-xl dark:border-zinc-800/50 dark:bg-zinc-900/40">
                            <div className="flex items-center justify-between border-b border-zinc-200/50 px-6 py-4 dark:border-zinc-800/50">
                                <p className="text-[10px] font-bold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">
                                    Prendas Asignadas
                                </p>
                            </div>
                            <div className="divide-y divide-zinc-100/80 dark:divide-zinc-800/50">
                                {wardrobeItems.map((item) => {
                                    const ItemIcon = getItemIcon(item.type);
                                    const hasSize = !!selections[item.id];

                                    return (
                                        <div
                                            key={item.id}
                                            className={`flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 transition-colors hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 ${!hasSize ? 'bg-amber-50/30 dark:bg-amber-900/5' : ''
                                                }`}
                                        >
                                            <div className="flex items-start gap-4">
                                                <div className={`flex size-10 shrink-0 items-center justify-center rounded-xl ${hasSize
                                                    ? 'bg-zinc-100/80 text-zinc-600 dark:bg-zinc-800/50 dark:text-zinc-400'
                                                    : 'bg-amber-100/50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-500'
                                                    }`}>
                                                    <ItemIcon className="size-5" strokeWidth={1.5} />
                                                </div>
                                                <div>
                                                    <div className="flex items-center gap-2">
                                                        <h4 className="text-[13px] font-bold text-zinc-900 dark:text-zinc-100">
                                                            {item.name}
                                                        </h4>
                                                        <span className="hidden sm:inline-flex rounded-md bg-zinc-100 px-1.5 py-0.5 text-[8px] font-bold uppercase tracking-wider text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                                                            {item.type}
                                                        </span>
                                                    </div>
                                                    <p className="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                                        {item.description}
                                                    </p>
                                                </div>
                                            </div>

                                            <div className="w-full sm:w-48 shrink-0">
                                                <div className="relative">
                                                    <select
                                                        value={selections[item.id] || ''}
                                                        onChange={(e) => handleSizeChange(item.id, e.target.value)}
                                                        className={`w-full appearance-none rounded-xl border py-2.5 pl-3 pr-10 text-sm font-semibold outline-none transition-all duration-150 focus:ring-2 dark:bg-zinc-900/60 ${hasSize
                                                            ? 'border-zinc-200 bg-white/80 text-zinc-900 focus:border-brand-gold focus:ring-brand-gold/20 dark:border-zinc-700 dark:text-zinc-100 dark:focus:border-brand-gold/60 dark:focus:ring-brand-gold/10'
                                                            : 'border-amber-300 bg-amber-50 text-zinc-500 focus:border-amber-500 focus:ring-amber-500/20 dark:border-amber-700/50 dark:bg-amber-950/20 dark:text-zinc-400'
                                                            }`}
                                                    >
                                                        <option value="" disabled>Seleccionar talla…</option>
                                                        {item.sizes.map((size) => (
                                                            <option key={size} value={size}>{size}</option>
                                                        ))}
                                                    </select>
                                                    <div className={`pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 transition-colors ${hasSize ? 'text-zinc-400 dark:text-zinc-500' : 'text-amber-500'
                                                        }`}>
                                                        <svg className="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                                                        </svg>
                                                    </div>
                                                </div>
                                                {!hasSize && (
                                                    <p className="mt-1.5 flex items-center gap-1 text-[10px] font-semibold text-amber-600 dark:text-amber-500">
                                                        <AlertCircle className="size-3" strokeWidth={2.5} />
                                                        Requerido
                                                    </p>
                                                )}
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

import React, { useEffect, useState } from 'react';

/** Íconos SVG inline minimalistas */
const Icon = {
    grid: (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.5} className="h-3.5 w-3.5">
            <rect x="3" y="3" width="7" height="7" rx="1" />
            <rect x="14" y="3" width="7" height="7" rx="1" />
            <rect x="3" y="14" width="7" height="7" rx="1" />
            <rect x="14" y="14" width="7" height="7" rx="1" />
        </svg>
    ),
    shirt: (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.5} className="h-3.5 w-3.5">
            <path d="M20.38 3.46L16 2a4 4 0 01-8 0L3.62 3.46a2 2 0 00-1.34 2.23l.58 3.57a1 1 0 001.04.74H5v9a2 2 0 002 2h10a2 2 0 002-2V10h1.1a1 1 0 001.04-.74l.58-3.57a2 2 0 00-1.34-2.23z" />
        </svg>
    ),
    users: (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.5} className="h-3.5 w-3.5">
            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
            <circle cx="9" cy="7" r="4" />
            <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" />
        </svg>
    ),
    box: (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.5} className="h-3.5 w-3.5">
            <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z" />
            <polyline points="3.27 6.96 12 12.01 20.73 6.96" />
            <line x1="12" y1="22.08" x2="12" y2="12" />
        </svg>
    ),
    bell: (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.5} className="h-3.5 w-3.5">
            <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0" />
        </svg>
    ),
};

const sideItems = [
    { label: 'Dashboard', icon: Icon.grid, active: true },
    { label: 'Vestuario', icon: Icon.shirt },
    { label: 'Empleados', icon: Icon.users },
    { label: 'Productos', icon: Icon.box },
];

/** Barra de progreso animada */
function Bar({ w, delay = 0, color = 'bg-brand-gold/70' }) {
    const [width, setWidth] = useState(0);
    useEffect(() => {
        const t = setTimeout(() => setWidth(w), 400 + delay);
        return () => clearTimeout(t);
    }, [w, delay]);
    return (
        <div className="h-1 w-full overflow-hidden rounded-full bg-white/10">
            <div
                className={`h-full rounded-full ${color} transition-all duration-700 ease-out`}
                style={{ width: `${width}%` }}
            />
        </div>
    );
}

export default function DashboardPreview() {
    const [tick, setTick] = useState(0);

    // parpadeo subtle en la notificación cada 3 s
    useEffect(() => {
        const id = setInterval(() => setTick((t) => t + 1), 3000);
        return () => clearInterval(id);
    }, []);

    return (
        <div className="relative h-full w-full overflow-hidden bg-[#0c0c0e] select-none" aria-hidden>
            {/* Fondo gradiente muy sutil */}
            <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,rgba(212,175,55,0.06)_0%,transparent_60%)]" />

            <div className="flex h-full w-full">
                {/* ─── SIDEBAR ─────────────────────────── */}
                <aside className="flex h-full w-[56px] shrink-0 flex-col items-center border-r border-white/[0.06] bg-[#0f0f11] py-4">
                    {/* Logo marca */}
                    <div className="mb-6 flex h-7 w-7 items-center justify-center rounded-lg bg-brand-gold/20">
                        <span className="text-[8px] font-black tracking-wider text-brand-gold">S</span>
                    </div>

                    {/* Nav items */}
                    <nav className="flex flex-1 flex-col items-center gap-1">
                        {sideItems.map(({ label, icon, active }) => (
                            <div
                                key={label}
                                title={label}
                                className={`group relative flex h-8 w-8 items-center justify-center rounded-lg transition-all duration-200 ${
                                    active
                                        ? 'bg-brand-gold/15 text-brand-gold'
                                        : 'text-white/25 hover:bg-white/[0.05] hover:text-white/50'
                                }`}
                            >
                                {icon}
                                {active && (
                                    <span className="absolute left-0 top-1/2 h-3 w-0.5 -translate-y-1/2 rounded-r-full bg-brand-gold" />
                                )}
                            </div>
                        ))}
                    </nav>

                    {/* Avatar bottom */}
                    <div className="mt-auto flex h-7 w-7 items-center justify-center rounded-full bg-white/10">
                        <span className="text-[8px] font-semibold text-white/60">JL</span>
                    </div>
                </aside>

                {/* ─── MAIN ────────────────────────────── */}
                <div className="flex flex-1 flex-col overflow-hidden">
                    {/* ─── HEADER ──────────────────────── */}
                    <header className="flex h-9 shrink-0 items-center justify-between border-b border-white/[0.06] px-4">
                        <div className="flex flex-col gap-0.5">
                            <span className="text-[8px] font-semibold tracking-[0.1em] text-white/80">Dashboard</span>
                            <span className="text-[6px] font-medium uppercase tracking-[0.14em] text-white/30">
                                Secretaría de Administración
                            </span>
                        </div>
                        <div className="flex items-center gap-2">
                            {/* Notificación */}
                            <div className={`relative text-white/30 transition-colors duration-500 ${tick % 2 === 0 ? 'text-brand-gold/70' : 'text-white/30'}`}>
                                {Icon.bell}
                                <span className="absolute -right-0.5 -top-0.5 h-1.5 w-1.5 rounded-full bg-brand-gold" />
                            </div>
                            {/* Búsqueda falsa */}
                            <div className="flex h-5 w-16 items-center gap-1 rounded-md border border-white/[0.08] bg-white/[0.04] px-1.5">
                                <span className="text-[6px] text-white/20 tracking-wide">Buscar…</span>
                            </div>
                        </div>
                    </header>

                    {/* ─── CONTENT ─────────────────────── */}
                    <main className="flex flex-1 flex-col gap-3 overflow-hidden p-4">
                        {/* KPI row */}
                        <div className="grid grid-cols-3 gap-2">
                            {[
                                { label: 'Empleados', value: '1,248', pct: 82 },
                                { label: 'Productos', value: '3,410', pct: 65 },
                                { label: 'Partidas', value: '24', pct: 90 },
                            ].map(({ label, value, pct }, i) => (
                                <div
                                    key={label}
                                    className="flex flex-col gap-1.5 rounded-lg border border-white/[0.06] bg-white/[0.03] p-2.5"
                                >
                                    <span className="text-[6px] font-medium uppercase tracking-[0.12em] text-white/30">{label}</span>
                                    <span className="text-[11px] font-semibold text-white/80">{value}</span>
                                    <Bar w={pct} delay={i * 120} />
                                </div>
                            ))}
                        </div>

                        {/* Tabla simulada */}
                        <div className="flex flex-1 flex-col overflow-hidden rounded-lg border border-white/[0.06] bg-white/[0.02]">
                            {/* Cabecera tabla */}
                            <div className="flex items-center justify-between border-b border-white/[0.06] px-3 py-2">
                                <span className="text-[7px] font-semibold tracking-[0.1em] text-white/50">Vestuario reciente</span>
                                <div className="h-1.5 w-8 rounded-full bg-brand-gold/30" />
                            </div>
                            {/* Filas */}
                            <div className="flex flex-col divide-y divide-white/[0.04]">
                                {['JLMR850312', 'ASGV920105', 'MRCT780923', 'PLGM001204'].map((rfc, idx) => (
                                    <div key={rfc} className="flex items-center gap-2 px-3 py-1.5">
                                        <div className="h-4 w-4 shrink-0 rounded-full bg-white/[0.06] flex items-center justify-center">
                                            <span className="text-[5px] font-bold text-white/40">
                                                {rfc.slice(0, 2)}
                                            </span>
                                        </div>
                                        <div className="flex flex-1 flex-col gap-0.5">
                                            <div className="h-1.5 rounded-full bg-white/20" style={{ width: `${55 + idx * 8}%` }} />
                                            <div className="h-1 rounded-full bg-white/[0.08]" style={{ width: `${35 + idx * 5}%` }} />
                                        </div>
                                        <div className={`h-1.5 w-1.5 rounded-full ${idx === 1 ? 'bg-brand-gold/70' : 'bg-white/[0.12]'}`} />
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Barra de estado / Chart simulado */}
                        <div className="flex h-14 shrink-0 items-end gap-0.5 rounded-lg border border-white/[0.06] bg-white/[0.02] px-3 pb-2 pt-2">
                            <div className="flex-1 text-[6px] text-white/25 mb-1 self-start tracking-wide">Asignaciones / mes</div>
                            {[40, 65, 45, 80, 55, 90, 70, 60, 85, 50, 75, 95].map((h, i) => (
                                <div
                                    key={i}
                                    className="flex-1 rounded-sm bg-brand-gold/30 transition-all duration-500 hover:bg-brand-gold/60"
                                    style={{ height: `${h * 0.38}px` }}
                                />
                            ))}
                        </div>
                    </main>
                </div>
            </div>

            {/* Overlay gradiente para integrar con el formulario */}
            <div className="pointer-events-none absolute inset-0 bg-[linear-gradient(to_right,transparent_85%,rgba(10,10,11,0.8)_100%)]" />
            {/* Label "Preview" */}
            <div className="absolute bottom-3 right-4 flex items-center gap-1 opacity-50">
                <div className="h-1 w-1 rounded-full bg-brand-gold animate-pulse" />
                <span className="text-[6px] font-semibold uppercase tracking-[0.18em] text-white/40">Vista previa</span>
            </div>
        </div>
    );
}

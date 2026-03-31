<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Líneas presupuestales (provisional)</title>
    <style>
        :root { --bg: #f8fafc; --card: #fff; --border: #e2e8f0; --text: #0f172a; --muted: #64748b; --accent2: #0f766e; }
        @media (prefers-color-scheme: dark) {
            :root { --bg: #0f172a; --card: #1e293b; --border: #334155; --text: #f1f5f9; --muted: #94a3b8; --accent2: #2dd4bf; }
        }
        * { box-sizing: border-box; }
        body { font-family: ui-sans-serif, system-ui, sans-serif; margin: 0; padding: 1rem 1.25rem 2rem; background: var(--bg); color: var(--text); line-height: 1.45; font-size: 14px; }
        h1 { font-size: 1.25rem; font-weight: 700; margin: 0 0 0.25rem; }
        .sub { color: var(--muted); font-size: 0.8rem; margin-bottom: 1rem; }
        .banner { background: #fef3c7; color: #92400e; padding: 0.65rem 0.85rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.8rem; border: 1px solid #fcd34d; }
        @media (prefers-color-scheme: dark) {
            .banner { background: #422006; color: #fde68a; border-color: #78350f; }
        }
        .ok { background: #d1fae5; color: #065f46; padding: 0.5rem 0.75rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.85rem; }
        @media (prefers-color-scheme: dark) {
            .ok { background: #064e3b; color: #a7f3d0; }
        }
        .err { background: #fee2e2; color: #991b1b; padding: 0.5rem 0.75rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.85rem; }
        .row {
            display: grid;
            grid-template-columns: 88px minmax(120px, 2fr) 88px minmax(90px, 1fr) minmax(90px, 1fr) 100px 96px;
            gap: 0.5rem;
            align-items: start;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 0.65rem 0.75rem;
            margin-bottom: 0.5rem;
            box-shadow: 0 1px 2px rgba(0,0,0,.04);
        }
        @media (max-width: 900px) {
            .row { grid-template-columns: 1fr; }
        }
        .hdr { font-size: 0.6rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--muted); margin-bottom: 0.2rem; }
        input[type="text"], input[type="number"], textarea {
            width: 100%; padding: 0.35rem 0.45rem; border: 1px solid var(--border); border-radius: 6px;
            background: var(--card); color: var(--text); font-size: 0.8rem;
        }
        textarea { min-height: 2.75rem; resize: vertical; }
        button[type="submit"] {
            background: var(--accent2); color: #fff; border: none; padding: 0.45rem 0.6rem; border-radius: 6px;
            font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; cursor: pointer; width: 100%;
        }
        button[type="submit"]:hover { filter: brightness(1.08); }
        .empty { padding: 2rem; text-align: center; color: var(--muted); background: var(--card); border-radius: 10px; border: 1px dashed var(--border); }
        .legend { display: flex; gap: 0.5rem; flex-wrap: wrap; font-size: 0.65rem; color: var(--muted); margin-bottom: 0.75rem; }
    </style>
</head>
<body>
    <h1>Líneas presupuestales</h1>
    <p class="sub">Vista <strong>provisional</strong> · sin acceso · ejercicio <strong>{{ $anio }}</strong> · base: <code>{{ \Illuminate\Support\Facades\DB::connection('copiasivso')->getDatabaseName() }}</code></p>
    <div class="banner">
        Quitar o proteger esta ruta en producción. Cualquiera con la URL puede editar datos.
    </div>

    @if (session('ok'))
        <div class="ok">{{ session('ok') }}</div>
    @endif
    @if ($errors->any())
        <div class="err">
            @foreach ($errors->all() as $e)
                <div>{{ $e }}</div>
            @endforeach
        </div>
    @endif

    @if ($rows->isEmpty())
        <div class="empty">No hay registros en <code>partidas_por_ejercicio</code> para el ejercicio {{ $anio }}.</div>
    @else
        <div class="legend">
            <span><strong>No. partida</strong> = catálogo partidas</span>
            <span><strong>Snap</strong> = no_partida_snapshot</span>
            <span><strong>Claves</strong> = partidas_por_ejercicio</span>
        </div>
        @foreach ($rows as $r)
            <form method="post" action="{{ route('provisional.lineas-presupuestales.update', $r->ppe_id) }}" class="row">
                @csrf
                <div>
                    <div class="hdr">No. partida</div>
                    <input type="number" name="no_partida" value="{{ old('no_partida', $r->no_partida) }}" min="1" required>
                </div>
                <div>
                    <div class="hdr">Descripción</div>
                    <textarea name="descripcion" rows="2" placeholder="Descripción de la partida…">{{ old('descripcion', $r->partida_descripcion) }}</textarea>
                </div>
                <div>
                    <div class="hdr">Snap. no.</div>
                    <input type="number" name="no_partida_snapshot" value="{{ old('no_partida_snapshot', $r->no_partida_snapshot) }}" min="0">
                </div>
                <div>
                    <div class="hdr">Clave como se usó</div>
                    <input type="text" name="clave_como_se_uso" value="{{ old('clave_como_se_uso', $r->clave_como_se_uso) }}" maxlength="512">
                </div>
                <div>
                    <div class="hdr">Clave ejercicio</div>
                    <input type="text" name="clave_para_ejercicio" value="{{ old('clave_para_ejercicio', $r->clave_para_ejercicio) }}" maxlength="512">
                </div>
                <div>
                    <div class="hdr">Clave presup.</div>
                    <input type="number" name="clave_presupuestal" value="{{ old('clave_presupuestal', $r->clave_presupuestal) }}" min="0">
                </div>
                <div>
                    <div class="hdr">&nbsp;</div>
                    <button type="submit">Guardar</button>
                </div>
            </form>
        @endforeach
    @endif
</body>
</html>

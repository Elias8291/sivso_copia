<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Productos</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 1rem; }
        table { border-collapse: collapse; width: 100%; max-width: 1200px; margin-top: 1rem; }
        th, td { border: 1px solid #ccc; padding: 0.35rem 0.5rem; text-align: left; font-size: 0.9rem; }
        th { background: #f0f0f0; }
        a { color: #0645ad; }
        .muted { color: #555; font-size: 0.85rem; }
    </style>
</head>
<body>
    @if($delegadoId)
        <p><a href="{{ route('reportes.delegados.show', $delegadoId) }}">← Volver al delegado</a></p>
    @else
        <p><a href="{{ url('/dashboard') }}">← Inicio</a></p>
    @endif

    <h1>Productos / vestuario</h1>
    <p>
        <strong>{{ $empleado->nombre }} {{ $empleado->apellido_paterno }} {{ $empleado->apellido_materno }}</strong>
        · NUE: {{ $empleado->nue ?? '—' }} · UR: {{ $empleado->ur ?? '—' }}
        <br>
        <span class="muted">Dependencia: {{ $empleado->dependencia_nombre ?? '—' }} · Delegación: {{ $empleado->delegacion_clave ?? '—' }}</span>
    </p>

    <p class="muted">
        Ejercicio por defecto: <strong>{{ $ejercicio }}</strong>.
        @if($soloEjercicio)
            Solo solicitudes con año <strong>{{ $anioFiltro }}</strong>.
            <a href="{{ route('reportes.empleados.productos', array_filter(['empleado' => $empleado->id, 'todos' => 1, 'delegado' => $delegadoId])) }}">Ver todos los ejercicios</a>
        @else
            Todos los ejercicios.
            <a href="{{ route('reportes.empleados.productos', array_filter(['empleado' => $empleado->id, 'anio' => $ejercicio, 'delegado' => $delegadoId])) }}">Filtrar año {{ $ejercicio }}</a>
        @endif
    </p>

    <table>
        <thead>
            <tr>
                <th>Año</th>
                <th>Partida</th>
                <th>Esp.</th>
                <th>Descripción</th>
                <th>Talla</th>
                <th>Cant.</th>
                <th>P. unit.</th>
                <th>Importe</th>
                <th>IVA</th>
                <th>Total</th>
                <th>Clave partida</th>
                <th>Clave ejercicio</th>
                <th>Línea presupuestal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($lineas as $row)
                <tr>
                    <td>{{ $row->anio }}</td>
                    <td>{{ $row->no_partida ?? $row->no_partida_snapshot ?? '—' }}</td>
                    <td>{{ $row->partida_especifica_codigo ?? '—' }}</td>
                    <td>{{ $row->producto_descripcion }}</td>
                    <td>{{ $row->talla ?? '—' }}</td>
                    <td>{{ $row->cantidad }}</td>
                    <td>{{ $row->precio_unitario }}</td>
                    <td>{{ $row->importe ?? '—' }}</td>
                    <td>{{ $row->iva ?? '—' }}</td>
                    <td>{{ $row->importe_total ?? '—' }}</td>
                    <td class="muted">{{ $row->clave_partida_snapshot ?? '—' }}</td>
                    <td class="muted">{{ $row->partida_especifica_clave ?? $row->clave_para_ejercicio_snapshot ?? '—' }}</td>
                    <td class="muted">{{ \Illuminate\Support\Str::limit($row->partida_especifica_linea ?? '', 80) ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="13">No hay solicitudes @if($soloEjercicio) en {{ $anioFiltro }} @endif.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

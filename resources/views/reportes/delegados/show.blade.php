<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Delegado: {{ $delegado->nombre_completo }}</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 1rem; }
        table { border-collapse: collapse; width: 100%; max-width: 1100px; margin-top: 1rem; }
        th, td { border: 1px solid #ccc; padding: 0.35rem 0.5rem; text-align: left; }
        th { background: #f0f0f0; }
        a { color: #0645ad; }
    </style>
</head>
<body>
    <p><a href="{{ route('reportes.delegados.index') }}">← Lista de delegados</a></p>
    <h1>{{ $delegado->nombre_completo }}</h1>
    <p>Ejercicio de referencia: <strong>{{ $ejercicio }}</strong></p>
    <p>
        <strong>Delegaciones asignadas al delegado:</strong>
        @forelse ($delegaciones as $del)
            <span>{{ $del->clave }}@if($del->nombre) ({{ $del->nombre }})@endif</span>@if(!$loop->last), @endif
        @empty
            (ninguna)
        @endforelse
    </p>
    <h2>Empleados con esa delegación</h2>
    <p>Personas cuyo <code>empleados.delegacion_id</code> coincide con alguna delegación de este delegado.</p>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>NUE</th>
                <th>Nombre</th>
                <th>Apellidos</th>
                <th>UR</th>
                <th>Dependencia</th>
                <th>Delegación (empleado)</th>
                <th>Productos</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($empleados as $e)
                <tr>
                    <td>{{ $e->id }}</td>
                    <td>{{ $e->nue ?? '—' }}</td>
                    <td>{{ $e->nombre }}</td>
                    <td>{{ trim(($e->apellido_paterno ?? '').' '.($e->apellido_materno ?? '')) }}</td>
                    <td>{{ $e->ur ?? '—' }}</td>
                    <td>{{ $e->dependencia_nombre ?? '—' }}</td>
                    <td>{{ $e->delegacion_clave ?? '—' }}</td>
                    <td>
                        <a href="{{ route('reportes.empleados.productos', ['empleado' => $e->id, 'delegado' => $delegado->id]) }}">Ver productos</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8">No hay empleados enlazados a esas delegaciones.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

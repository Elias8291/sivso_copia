<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Delegados — {{ $database_name }}</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 1rem; }
        table { border-collapse: collapse; width: 100%; max-width: 960px; }
        th, td { border: 1px solid #ccc; padding: 0.35rem 0.5rem; text-align: left; }
        th { background: #f0f0f0; }
        a { color: #0645ad; }
    </style>
</head>
<body>
    <p><a href="{{ url('/dashboard') }}">← Volver</a></p>
    <h1>Delegados</h1>
    <p>Ejercicio de referencia: <strong>{{ $ejercicio }}</strong> · Base: <code>{{ $database_name }}</code></p>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Delegación(es)</th>
                <th>Empleados</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($delegados as $d)
                <tr>
                    <td>{{ $d->id }}</td>
                    <td>{{ $d->nombre_completo }}</td>
                    <td>{{ implode(', ', $d->delegaciones_claves ?? []) }}</td>
                    <td>{{ $d->empleados_count }}</td>
                    <td><a href="{{ route('reportes.delegados.show', $d->id) }}">Ver empleados</a></td>
                </tr>
            @empty
                <tr><td colspan="5">No hay delegados.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

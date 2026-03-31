<?php

namespace App\Http\Controllers;

use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProductosController extends Controller
{
    private function cx(): Connection
    {
        return DB::connection('copiasivso');
    }

    public function index(Request $request): Response
    {
        $ejercicio = (int) config('sivso.ejercicio_actual', 2025);
        $anio = (int) $request->query('anio', $ejercicio);

        if ($anio !== $ejercicio && $anio !== $ejercicio - 1) {
            $anio = $ejercicio;
        }

        if ($request->query->has('partida_id') && $request->query('partida_id') === '') {
            return redirect()->route('productos.index', array_filter([
                'anio' => $request->query('anio'),
                'buscar' => $request->filled('buscar') ? $request->query('buscar') : null,
            ], fn ($v) => $v !== null && $v !== ''));
        }

        $rawPartida = $request->query('partida_id');
        $partidaFiltro = ($rawPartida !== null && $rawPartida !== '' && (int) $rawPartida > 0)
            ? (int) $rawPartida
            : null;

        $q = $this->cx()->table('productos as p')
            ->join('producto_precios as pp', function ($j) use ($anio) {
                $j->on('pp.producto_id', '=', 'p.id')->where('pp.anio', '=', $anio);
            })
            ->join('partidas as pa', 'pa.id', '=', 'p.partida_id')
            ->leftJoin('partidas_por_ejercicio as ppe', function ($j) use ($anio) {
                $j->on('ppe.partida_id', '=', 'pa.id')->where('ppe.anio', '=', $anio);
            })
            ->leftJoin('tipos_partida_especifica as tpe', 'tpe.id', '=', 'p.tipo_partida_especifica_id')
            ->orderBy('pa.no_partida')
            ->orderBy('tpe.codigo')
            ->orderBy('p.descripcion')
            ->select([
                'p.id',
                'p.descripcion',
                'p.marca',
                'p.unidad_medida',
                'p.codigo',
                'p.medida',
                'p.partida_id',
                'p.tipo_partida_especifica_id',
                'pp.precio_unitario',
                'pp.proveedor',
                'pa.no_partida',
                'pa.descripcion as partida_descripcion',
                'ppe.clave_como_se_uso as clave_partida',
                'tpe.codigo as tipo_codigo',
                'tpe.nombre as tipo_nombre',
            ]);

        if ($partidaFiltro) {
            $q->where('p.partida_id', $partidaFiltro);
        }

        if ($request->filled('buscar')) {
            $term = '%'.$request->string('buscar')->trim().'%';
            $q->where(function ($w) use ($term) {
                $w->where('p.descripcion', 'like', $term)
                    ->orWhere('p.marca', 'like', $term)
                    ->orWhere('p.codigo', 'like', $term)
                    ->orWhere('pa.no_partida', 'like', $term)
                    ->orWhere('pa.descripcion', 'like', $term)
                    ->orWhere('tpe.nombre', 'like', $term);
            });
        }

        $paginator = $q->paginate(40);
        $paginator->appends(array_filter([
            'anio' => $anio,
            'buscar' => $request->filled('buscar') ? $request->query('buscar') : null,
            'partida_id' => $partidaFiltro,
        ], fn ($v) => $v !== null && $v !== ''));

        $yaCopiadosIds = collect();
        if ($anio === $ejercicio - 1) {
            $ids = $paginator->getCollection()->pluck('id');
            $yaCopiadosIds = $ids->isEmpty()
                ? collect()
                : $this->cx()->table('producto_precios')
                    ->whereIn('producto_id', $ids)
                    ->where('anio', $ejercicio)
                    ->pluck('producto_id');
        }

        $paginator->setCollection(
            $paginator->getCollection()->map(fn ($row) => [
                'id' => $row->id,
                'descripcion' => $row->descripcion,
                'marca' => $row->marca,
                'unidad_medida' => $row->unidad_medida,
                'codigo' => $row->codigo,
                'medida' => $row->medida,
                'precio_unitario' => $row->precio_unitario,
                'proveedor' => $row->proveedor,
                'partida_id' => $row->partida_id,
                'no_partida' => $row->no_partida,
                'partida_descripcion' => $row->partida_descripcion,
                'clave_partida' => $row->clave_partida,
                'tipo_partida_especifica_id' => $row->tipo_partida_especifica_id,
                'tipo_codigo' => $row->tipo_codigo,
                'tipo_nombre' => $row->tipo_nombre,
                'ya_en_ejercicio_actual' => $yaCopiadosIds->contains($row->id),
            ])
        );

        $partidasRaw = $this->cx()->table('partidas as pa')
            ->leftJoin('partidas_por_ejercicio as ppe', function ($j) use ($anio) {
                $j->on('ppe.partida_id', '=', 'pa.id')->where('ppe.anio', '=', $anio);
            })
            ->orderBy('pa.no_partida')
            ->select([
                'pa.id',
                'pa.no_partida',
                'pa.descripcion',
                'ppe.clave_como_se_uso as clave_partida',
            ])
            ->get();

        $lineasPorPartida = [];
        if ($this->cx()->getSchemaBuilder()->hasTable('partidas_especificas')) {
            $lineasPorPartida = $this->cx()->table('partidas_especificas')
                ->where('anio', $anio)
                ->selectRaw('partida_id, COUNT(*) as c')
                ->groupBy('partida_id')
                ->pluck('c', 'partida_id')
                ->all();
        }

        $partidas = $partidasRaw->map(function ($p) use ($lineasPorPartida) {
            return [
                'id' => (int) $p->id,
                'no_partida' => (int) $p->no_partida,
                'descripcion' => $p->descripcion,
                'clave_partida' => $p->clave_partida,
                'lineas_presupuestales' => (int) ($lineasPorPartida[$p->id] ?? 0),
            ];
        })->values();

        $tipos = $this->cx()->table('tipos_partida_especifica')
            ->orderBy('codigo')
            ->select(['id', 'codigo', 'nombre'])
            ->get();

        return Inertia::render('Productos/Index', [
            'productos' => $paginator,
            'partidas' => $partidas,
            'tipos' => $tipos,
            'ejercicio' => $ejercicio,
            'anio' => $anio,
            'filters' => [
                'buscar' => $request->string('buscar')->toString(),
                'partida_id' => $partidaFiltro,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $ejercicio = (int) config('sivso.ejercicio_actual', 2025);

        $validated = $request->validate([
            'descripcion' => 'required|string|max:255',
            'partida_id' => 'required|integer',
            'tipo_partida_especifica_id' => 'required|integer',
            'marca' => 'nullable|string|max:120',
            'unidad_medida' => 'nullable|string|max:30',
            'codigo' => 'nullable|string|max:60',
            'medida' => 'nullable|string|max:10',
            'precio_unitario' => 'required|numeric|min:0',
            'proveedor' => 'nullable|string|max:120',
        ]);

        $productoId = $this->cx()->table('productos')->insertGetId([
            'partida_id' => $validated['partida_id'],
            'tipo_partida_especifica_id' => $validated['tipo_partida_especifica_id'],
            'descripcion' => $validated['descripcion'],
            'marca' => $validated['marca'] ?? null,
            'unidad_medida' => $validated['unidad_medida'] ?? null,
            'codigo' => $validated['codigo'] ?? null,
            'medida' => $validated['medida'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->cx()->table('producto_precios')->insert([
            'producto_id' => $productoId,
            'anio' => $ejercicio,
            'precio_unitario' => $validated['precio_unitario'],
            'proveedor' => $validated['proveedor'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back();
    }

    public function activar(Request $request, int $producto)
    {
        $ejercicio = (int) config('sivso.ejercicio_actual', 2025);

        $validated = $request->validate([
            'precio_unitario' => 'required|numeric|min:0',
            'proveedor' => 'nullable|string|max:120',
        ]);

        $existe = $this->cx()->table('producto_precios')
            ->where('producto_id', $producto)
            ->where('anio', $ejercicio)
            ->exists();

        if ($existe) {
            return back()->withErrors(['precio_unitario' => 'Este producto ya está registrado para el ejercicio actual.']);
        }

        $this->cx()->table('producto_precios')->insert([
            'producto_id' => $producto,
            'anio' => $ejercicio,
            'precio_unitario' => $validated['precio_unitario'],
            'proveedor' => $validated['proveedor'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back();
    }
}

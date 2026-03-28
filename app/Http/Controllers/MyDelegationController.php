<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MyDelegationController extends Controller
{
    public function index(): Response
    {
        // Artículos disponibles (con descripción)
        $allItems = [
            ['id' => 1, 'name' => 'Camisa Institucional',   'type' => 'Superior', 'price' => 380.00,  'description' => 'Camisa manga larga, color blanco con logo bordado. Uso para actividades oficiales y representación institucional.', 'sizes' => ['CH', 'M', 'G', 'XG', 'XXG']],
            ['id' => 2, 'name' => 'Pantalón de Vestir',     'type' => 'Inferior', 'price' => 520.00,  'description' => 'Pantalón de corte recto, color azul marino. Confeccionado en gabardina 100% poliéster resistente.', 'sizes' => ['28', '30', '32', '34', '36', '38', '40', '42']],
            ['id' => 3, 'name' => 'Zapatos de Seguridad',   'type' => 'Calzado',  'price' => 1250.00, 'description' => 'Bota de seguridad con puntera de acero y suela antiderrapante. Norma NOM-113-STPS-2009.', 'sizes' => ['24', '25', '25.5', '26', '26.5', '27', '28', '29', '30']],
            ['id' => 4, 'name' => 'Chamarra de Invierno',   'type' => 'Exterior', 'price' => 980.00,  'description' => 'Chamarra acolchada con forro polar, color gris institucional. Resistente al agua y viento.', 'sizes' => ['CH', 'M', 'G', 'XG', 'XXG']],
            ['id' => 5, 'name' => 'Playera Tipo Polo',      'type' => 'Superior', 'price' => 210.00,  'description' => 'Playera polo piqué, color beige. Para uso en actividades de campo o trabajo operativo.', 'sizes' => ['CH', 'M', 'G', 'XG', 'XXG']],
            ['id' => 6, 'name' => 'Botas de Campo',         'type' => 'Calzado',  'price' => 1450.00, 'description' => 'Bota de campo estilo vaquero con suela de hule. Para trabajos en zonas rurales o terreno irregular.', 'sizes' => ['24', '25', '26', '27', '28', '29', '30']],
        ];

        // Función auxiliar para obtener artículos por IDs
        $getItems = fn(array $ids) => array_values(array_filter($allItems, fn($i) => in_array($i['id'], $ids)));

        // Cada empleado tiene su propio set de artículos
        $employees = [
            [
                'id'           => 1,
                'name'         => 'Ana Martínez Silva',
                'rfc'          => 'MASA850101XYZ',
                'position'     => 'Auxiliar Administrativo',
                'wardrobeItems'=> $getItems([1, 2, 3, 4]),
                'selections'   => [1 => 'M', 2 => '32', 3 => '25', 4 => 'M'],
            ],
            [
                'id'           => 2,
                'name'         => 'Carlos López Hernández',
                'rfc'          => 'LOHC920315ABC',
                'position'     => 'Chofer',
                'wardrobeItems'=> $getItems([1, 2, 3, 6]),
                'selections'   => [1 => '', 2 => '', 3 => '', 6 => ''],
            ],
            [
                'id'           => 3,
                'name'         => 'María Gómez Pérez',
                'rfc'          => 'GOPM880722DEF',
                'position'     => 'Secretaria',
                'wardrobeItems'=> $getItems([1, 5]),
                'selections'   => [1 => 'CH', 5 => ''],
            ],
            [
                'id'           => 4,
                'name'         => 'Roberto Sánchez Ruiz',
                'rfc'          => 'SARR791130GHI',
                'position'     => 'Intendente',
                'wardrobeItems'=> $getItems([5, 2, 3, 4, 6]),
                'selections'   => [5 => 'G', 2 => '36', 3 => '28', 4 => 'G', 6 => '27'],
            ],
            [
                'id'           => 5,
                'name'         => 'Laura Torres Mendoza',
                'rfc'          => 'TOML950418JKL',
                'position'     => 'Analista',
                'wardrobeItems'=> $getItems([1, 2, 4]),
                'selections'   => [1 => '', 2 => '', 4 => ''],
            ],
        ];

        // Estado por empleado según sus propias prendas (cada vestuario es distinto)
        foreach ($employees as &$emp) {
            $itemIds = array_column($emp['wardrobeItems'], 'id');
            $filled = 0;
            foreach ($itemIds as $id) {
                if (! empty($emp['selections'][$id])) {
                    $filled++;
                }
            }
            $total = count($itemIds);

            if ($total === 0) {
                $emp['status'] = 'Pendiente';
            } elseif ($filled === $total) {
                $emp['status'] = 'Completado';
            } elseif ($filled > 0) {
                $emp['status'] = 'En progreso';
            } else {
                $emp['status'] = 'Pendiente';
            }
        }

        return Inertia::render('MyDelegation/Index', [
            'employees'       => $employees,
            'delegation_name' => 'Delegación Centro',
        ]);
    }

    public function show($id): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('my-delegation.index');
    }
}

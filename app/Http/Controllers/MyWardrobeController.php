<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MyWardrobeController extends Controller
{
    public function index(): Response
    {
        // Datos estáticos de prueba para el diseño
        $employee = [
            'name' => auth()->user()->name,
            'department' => 'Dirección General de Administración',
            'position' => 'Analista Administrativo',
            'status' => 'Pendiente de confirmación', // Pendiente, Confirmado
            'deadline' => '15 de Abril, 2026',
        ];

        $wardrobeItems = [
            [
                'id' => 1,
                'name' => 'Camisa Institucional Manga Larga',
                'type' => 'Prenda Superior',
                'description' => 'Camisa de algodón con logo bordado.',
                'sizes' => ['CH', 'M', 'G', 'XG', 'XXG'],
                'current_size' => 'M',
            ],
            [
                'id' => 2,
                'name' => 'Pantalón de Vestir',
                'type' => 'Prenda Inferior',
                'description' => 'Pantalón formal corte recto.',
                'sizes' => ['28', '30', '32', '34', '36', '38', '40', '42'],
                'current_size' => '32',
            ],
            [
                'id' => 3,
                'name' => 'Zapatos de Seguridad',
                'type' => 'Calzado',
                'description' => 'Calzado ergonómico con casquillo.',
                'sizes' => ['24', '25', '25.5', '26', '26.5', '27', '28', '29', '30'],
                'current_size' => '27',
            ],
            [
                'id' => 4,
                'name' => 'Chamarra de Invierno',
                'type' => 'Exterior',
                'description' => 'Chamarra impermeable con forro polar.',
                'sizes' => ['CH', 'M', 'G', 'XG', 'XXG'],
                'current_size' => '', // Simulando que falta por seleccionar
            ],
        ];

        return Inertia::render('MyWardrobe/Index', [
            'employee' => $employee,
            'wardrobeItems' => $wardrobeItems,
        ]);
    }
}

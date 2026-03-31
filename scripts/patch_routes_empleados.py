# -*- coding: utf-8 -*-
p = r"c:\Users\Elias\Documents\sistema_sivso_2026\routes\web.php"
with open(p, encoding="utf-8") as f:
    s = f.read()
if "EmpleadosReporteController" not in s:
    s = s.replace(
        "use App\\Http\\Controllers\\DelegadosReporteController;",
        "use App\\Http\\Controllers\\DelegadosReporteController;\nuse App\\Http\\Controllers\\EmpleadosReporteController;",
    )
    old = (
        "    Route::get('/reportes/delegados/{id}', [DelegadosReporteController::class, 'show'])->name('reportes.delegados.show');\n\n"
        "    Route::get('/periodos'"
    )
    new = (
        "    Route::get('/reportes/delegados/{id}', [DelegadosReporteController::class, 'show'])->name('reportes.delegados.show');\n"
        "    Route::get('/reportes/empleados/{empleado}/productos', [EmpleadosReporteController::class, 'productos'])->name('reportes.empleados.productos');\n\n"
        "    Route::get('/periodos'"
    )
    if old not in s:
        raise SystemExit("route block not found")
    s = s.replace(old, new, 1)
with open(p, "w", encoding="utf-8", newline="\n") as f:
    f.write(s)
print("ok")

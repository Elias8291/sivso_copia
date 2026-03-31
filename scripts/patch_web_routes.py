p = r"c:\Users\Elias\Documents\sistema_sivso_2026\routes\web.php"
with open(p, encoding="utf-8") as f:
    s = f.read()
if "DelegadosReporteController" not in s:
    s = s.replace(
        "use App\\Http\\Controllers\\MyDelegationController;\nuse App\\Http\\Controllers\\PeriodosController;",
        "use App\\Http\\Controllers\\MyDelegationController;\nuse App\\Http\\Controllers\\DelegadosReporteController;\nuse App\\Http\\Controllers\\PeriodosController;",
    )
    old = (
        "    Route::get('/mi-delegacion/{id}', [MyDelegationController::class, 'show'])->name('my-delegation.show');\n\n"
        "    Route::get('/periodos'"
    )
    new = (
        "    Route::get('/mi-delegacion/{id}', [MyDelegationController::class, 'show'])->name('my-delegation.show');\n\n"
        "    Route::get('/reportes/delegados', [DelegadosReporteController::class, 'index'])->name('reportes.delegados.index');\n"
        "    Route::get('/reportes/delegados/{id}', [DelegadosReporteController::class, 'show'])->name('reportes.delegados.show');\n\n"
        "    Route::get('/periodos'"
    )
    if old not in s:
        raise SystemExit("route block not found")
    s = s.replace(old, new, 1)
with open(p, "w", encoding="utf-8", newline="\n") as f:
    f.write(s)
print("ok")

import pathlib
base = pathlib.Path(r"c:/Users/Elias/Documents/sistema_sivso_2026")
for rel in [
    "app/Http/Controllers/DelegadosReporteController.php",
    "app/Console/Commands/MigrateCopiasivsoFromLegacyCommand.php",
    "config/sivso.php",
]:
    p = base / rel
    raw = p.read_bytes()
    if raw.startswith(b"\xef\xbb\xbf"):
        raw = raw[3:]
    text = raw.decode("utf-8")
    if "MigrateCopiasivso" in rel:
        text = text.replace("AÃ±o", "Año")
    p.write_text(text, encoding="utf-8", newline="\n")
print("ok")

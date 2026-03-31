path = r"c:\Users\Elias\Documents\sistema_sivso_2026\app\Console\Commands\MigrateCopiasivsoFromLegacyCommand.php"
with open(path, "r", encoding="utf-8") as f:
    s = f.read()
if not s.rstrip().endswith("}"):
    s = s.rstrip() + "\n}\n"
# fix broken concatenation: partida." newline -> partida."\n".
import re
s = re.sub(
    r'\$p->partida\."\s*\n\s*"\s*\.strtolower',
    r'$p->partida."\n".strtolower',
    s,
)
s = re.sub(
    r'\$c->no_partida\."\s*\n\s*"\s*\.strtolower',
    r'$c->no_partida."\n".strtolower',
    s,
)
with open(path, "w", encoding="utf-8", newline="\n") as f:
    f.write(s)
print("fixed")

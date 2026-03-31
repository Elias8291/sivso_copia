p = r"c:\\Users\\Elias\\Documents\\sistema_sivso_2026\\config\\database.php"
with open(p, "r", encoding="utf-8") as f:
    s = f.read()
if "copiasivso" in s:
    print("skip")
    raise SystemExit(0)
needle = """            'engine' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table"""
insert = """            'engine' => null,
        ],

        'copiasivso' => [
            'driver' => 'mysql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table"""
if needle not in s:
    raise SystemExit("needle missing")
with open(p, "w", encoding="utf-8", newline="\n") as f:
    f.write(s.replace(needle, insert, 1))
print("ok")

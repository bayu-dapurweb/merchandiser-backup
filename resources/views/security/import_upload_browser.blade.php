<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Import Upload Security — Browser Test</title>
    <style>
        :root {
            --bg: #0f1419;
            --panel: #1a2332;
            --text: #e7ecf3;
            --muted: #9aa8bc;
            --accent: #3d8bfd;
            --danger: #f07178;
            --ok: #7fd992;
            --warn: #e6c07b;
            --border: #2a3648;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", system-ui, sans-serif;
            background: linear-gradient(160deg, #0f1419 0%, #162033 100%);
            color: var(--text);
            line-height: 1.45;
            min-height: 100vh;
        }
        .wrap { max-width: 980px; margin: 0 auto; padding: 28px 18px 60px; }
        h1 { font-size: 1.55rem; margin: 0 0 8px; font-weight: 650; }
        h2 { font-size: 1.05rem; margin: 0 0 12px; color: var(--warn); }
        p, li { color: var(--muted); }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .badge.on { background: rgba(127,217,146,.15); color: var(--ok); }
        .badge.off { background: rgba(240,113,120,.15); color: var(--danger); }
        .panel {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 18px;
            margin: 16px 0;
        }
        .case {
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 14px;
            margin: 12px 0;
            background: rgba(0,0,0,.18);
        }
        .case h3 { margin: 0 0 8px; font-size: 1rem; }
        .expect { font-size: 0.88rem; margin: 4px 0; }
        .expect .before { color: var(--danger); }
        .expect .after { color: var(--ok); }
        .row { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-top: 10px; }
        a.btn, button.btn {
            display: inline-block;
            border: 0;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            color: #fff;
            background: var(--accent);
        }
        button.btn-danger { background: #c44b55; }
        button.btn-ok { background: #2f9e5d; }
        a.btn-secondary { background: #3a465a; }
        code, .mono {
            font-family: ui-monospace, Consolas, monospace;
            font-size: 0.85rem;
            color: #cde;
        }
        table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
        th, td { text-align: left; padding: 6px 8px; border-bottom: 1px solid var(--border); }
        th { color: var(--muted); font-weight: 600; }
        .flash {
            padding: 10px 12px;
            border-radius: 8px;
            background: rgba(61,139,253,.15);
            border: 1px solid rgba(61,139,253,.35);
            margin-bottom: 12px;
        }
        .flash-success {
            background: rgba(127,217,146,.12);
            border-color: rgba(127,217,146,.4);
            color: var(--ok);
        }
        .flash-warning, .flash-danger {
            background: rgba(240,113,120,.12);
            border-color: rgba(240,113,120,.4);
            color: var(--danger);
        }
        .flash-info {
            background: rgba(61,139,253,.15);
            border-color: rgba(61,139,253,.35);
        }
        ol { padding-left: 1.2rem; }
        .endpoint { word-break: break-all; }
    </style>
</head>
<body>
<div class="wrap">
    <h1>Import upload security — browser test</h1>
    <p>
        Hardening now:
        @if($hardening)
            <span class="badge on">ON (AFTER / fixed)</span>
        @else
            <span class="badge off">OFF (BEFORE / legacy vulnerable)</span>
        @endif
        &nbsp;· Module: <span class="mono">{{ $module }}</span>
    </p>

    @if(!empty($flashMessage))
        <div class="flash flash-{{ $flashType }}">
            <strong>{{ $flashMessage }}</strong>
            @if(!empty($flashDetail))
                <div style="margin-top:4px;font-size:0.9rem;">{{ $flashDetail }}</div>
            @endif
        </div>
    @elseif(session('message'))
        <div class="flash">{{ session('message') }}</div>
    @endif

    <div class="panel">
        <h2>How to compare BEFORE vs AFTER</h2>
        <ol>
            <li>Stay logged in as admin in this browser.</li>
            <li>
                <strong>BEFORE:</strong> set <code>CB_IMPORT_UPLOAD_HARDENING_ENABLED=false</code> in <code>.env</code>,
                run <code>php artisan config:clear</code>, reload this page (badge should be OFF).
            </li>
            <li>Upload <strong>shell.csv</strong> below → should land under <code>storage/app/uploads/</code> (you stay on this page).</li>
            <li>
                <strong>AFTER:</strong> set <code>CB_IMPORT_UPLOAD_HARDENING_ENABLED=true</code>,
                <code>php artisan config:clear</code>, reload (badge ON).
            </li>
            <li>Upload the same <strong>shell.csv</strong> → rejected flash; check storage panel below.</li>
            <li>Upload <strong>valid.csv</strong> with hardening ON → accepted into <code>storage/app/imports/</code>.</li>
        </ol>
        <p class="endpoint">Test upload action (redirects back here): <code>{{ $uploadUrl }}</code></p>
        <p class="endpoint">Real CB endpoint (for reference): <code>{{ $realEndpoint }}</code></p>
        <p>
            Change module via query:
            <code>{{ $pageUrl }}?module=users</code>
        </p>
    </div>

    <div class="panel">
        <h2>Test cases — download then upload</h2>
        <p>Each form uses the same validation/storage rules as <code>postDoUploadImportData()</code>, then redirects back here with the result.</p>

        @foreach($fixtures as $key => $fixture)
            <div class="case">
                <h3>{{ $fixture['label'] }}</h3>
                <div class="expect"><span class="before">BEFORE:</span> {{ $fixture['expect_legacy'] }}</div>
                <div class="expect"><span class="after">AFTER:</span> {{ $fixture['expect_hardened'] }}</div>
                <div class="row">
                    <a class="btn btn-secondary" href="{{ url($adminPath.'/security-import-upload-test/download/'.$key) }}">Download {{ $fixture['filename'] }}</a>
                    <form method="post" action="{{ $uploadUrl }}" enctype="multipart/form-data" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                        <input type="hidden" name="_token" value="{{ $csrf }}">
                        <input type="hidden" name="module" value="{{ $module }}">
                        <input type="file" name="userfile" accept=".csv,.xls,.xlsx,text/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                        <button class="btn {{ strpos($key, 'shell') !== false || $key === 'fake.xlsx' ? 'btn-danger' : 'btn-ok' }}" type="submit">
                            Upload &amp; return here
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

    <div class="panel">
        <h2>Storage snapshot (newest first)</h2>
        <p>
            Snapshot refreshes automatically after each upload.
            <a class="btn btn-secondary" href="{{ url($adminPath.'/security-import-upload-test/refresh').'?module='.$module }}">Refresh snapshot</a>
        </p>

        @foreach($storageSnapshot as $bucket => $files)
            <h3 style="margin-top:16px;font-size:0.95rem;">storage/app/{{ $bucket }}/</h3>
            @if(empty($files))
                <p><em>No files found.</em></p>
            @else
                <table>
                    <thead>
                    <tr>
                        <th>Path</th>
                        <th>Modified</th>
                        <th>Size</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($files as $f)
                        <tr>
                            <td class="mono">{{ $f['path'] }}</td>
                            <td>{{ $f['mtime'] }}</td>
                            <td>{{ $f['size'] }} B</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        @endforeach
    </div>
</div>
</body>
</html>

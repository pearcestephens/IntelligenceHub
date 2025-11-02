<?php
declare(strict_types=1);

/**
 * MySQL schema exporter (no-blank-screen edition)
 * - Loud error handling (fatals & exceptions included)
 * - JSON by default; plain-text chunk mode for ChatGPT copy/paste
 * - Filters: ?only=tables|schema|columns|keys|relations|data|create|all
 *            &q=keyword  &t=tbl1,tbl2  &rows=1  &maxlen=96  &create=1
 *            &mode=chunks (or &chunks=1)  &debug=1
 *
 * Legend (I):
 *  DB: db name
 *  T : tables (object keyed by table name)
 *  C : columns [n,ty,N,Df,Ex,K]
 *  P : primary key cols
 *  K : indexes  [n,u,c[]]
 *  F : fkeys    [n,c[],rt,rc[],u,d]
 *  D : sample row(s) aligned to C
 *  Ct: SHOW CREATE TABLE (when requested)
 */

ini_set('display_errors', '1');
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_OFF);
if (function_exists('mb_internal_encoding')) mb_internal_encoding('UTF-8');

// ----------- Output style & toggles -----------
$modeChunks = (isset($_GET['mode']) && strtolower($_GET['mode']) === 'chunks') || isset($_GET['chunks']);
$debug      = isset($_GET['debug']);
$chunkSize  = 110 * 1024; // ~110 KB per chunk (safe for ChatGPT messages)

// Plaintext in chunk mode, JSON otherwise
if ($modeChunks) {
    if (!headers_sent()) {
        header_remove('X-Powered-By');
        header('Content-Type: text/plain; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
    }
} else {
    if (!headers_sent()) {
        header_remove('X-Powered-By');
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
    }
}

// ----------- Fatal / exception handlers -----------
// --- REPLACE your $emit function with this (only the body changed slightly) ---
$emit = function(array $payload) use ($modeChunks, $chunkSize): void {
    $json = json_encode($payload, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);
    if (!is_string($json)) {
        $json = json_encode(['err'=>'encode_failed','raw'=>$payload], JSON_UNESCAPED_SLASHES|JSON_INVALID_UTF8_SUBSTITUTE);
    }
    if ($modeChunks) {
        $total = strlen($json);
        $i = 0; $part = 1;
        echo "=== CHUNKS: {$total} bytes ===\n";
        while ($i < $total) {
            $slice = substr($json, $i, $chunkSize);
            echo "\n--- CHUNK {$part} START ---\n";
            echo $slice, "\n";
            echo "--- CHUNK {$part} END ---\n";
            $i += $chunkSize; $part++;
        }
        echo "\n=== END ===\n";
    } else {
        echo $json;
    }
};


set_exception_handler(function(Throwable $e) use ($emit) {
    $emit([
        'err' => 'exception',
        'msg' => $e->getMessage(),
        'file'=> $e->getFile(),
        'line'=> $e->getLine(),
        'trace'=> $e->getTraceAsString(),
    ]);
    exit;
});

register_shutdown_function(function() use ($emit) {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR], true)) {
        $emit([
            'err'  => 'fatal',
            'type' => $e['type'],
            'msg'  => $e['message'],
            'file' => $e['file'],
            'line' => $e['line'],
        ]);
    }
});

// ----------- Params -----------
$only    = strtolower($_GET['only'] ?? '');      // tables|schema|columns|keys|relations|data|create|all
$q       = trim((string)($_GET['q'] ?? ''));
$tParam  = trim((string)($_GET['t'] ?? ''));
$rows    = max(1, (int)($_GET['rows'] ?? 1));
$maxLen  = max(8, (int)($_GET['maxlen'] ?? 96));
$wantCt  = ($only === 'create') || isset($_GET['create']);

$wantCols= ($only === '' || $only === 'all' || $only === 'schema' || $only === 'columns');
$wantKeys= ($only === '' || $only === 'all' || $only === 'schema' || $only === 'keys');
$wantFKs = ($only === '' || $only === 'all' || $only === 'schema' || $only === 'relations');
$wantData= ($only === '' || $only === 'all' || $only === 'data');
$justTables = ($only === 'tables');

// ----------- Credentials (prefilled; env/defines override) -----------
$host = getenv('DB_HOST') ?: (defined('DB_HOST') ? DB_HOST : '127.0.0.1');
$user = getenv('DB_USER') ?: (defined('DB_USERNAME') ? DB_USERNAME : (defined('DB_USER') ? DB_USER : 'hdgwrzntwa'));
$pass = getenv('DB_PASS') ?: (defined('DB_PASSWORD') ? DB_PASSWORD : (defined('DB_PASS') ? DB_PASS : 'bFUdRjh4Jx'));
$db   = getenv('DB_NAME') ?: (defined('DB_DATABASE') ? DB_DATABASE : (defined('DB_NAME') ? DB_NAME : 'hdgwrzntwa'));

// ----------- Connect -----------
$started = microtime(true);
$con = @mysqli_connect($host, $user, $pass, $db);
if (!$con) {
    $emit(['err'=>'db_connect','msg'=>mysqli_connect_error(),'h'=>$host,'u'=>$user,'db'=>$db]);
    exit;
}
mysqli_set_charset($con, 'utf8mb4');

// ----------- Helpers -----------
$esc = static function(string $s) use ($con): string {
    return "'" . mysqli_real_escape_string($con, $s) . "'";
};
$implodeEsc = static function(array $items) use ($esc): string {
    return implode(',', array_map($esc, $items));
};
// --- REPLACE your existing $truncate with this ---
$truncate = static function($v) use ($maxLen) {
    // null stays null
    if ($v === null) return null;

    // booleans -> 1/0
    if (is_bool($v)) return $v ? 1 : 0;

    // integers / floats -> numeric (preserve type)
    if (is_int($v) || is_float($v)) return $v + 0;

    // resources -> tag
    if (is_resource($v)) return '(resource)';

    // arrays/objects -> minified JSON
    if (is_array($v) || is_object($v)) {
        $json = json_encode($v, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);
        if ($json === false) $json = '(unencodable)';
        // ensure string length safe
        if (mb_strlen($json, '8bit') > $maxLen) return mb_substr($json, 0, $maxLen, '8bit');
        return $json;
    }

    // everything else -> string (including binary)
    $s = (string)$v;

    // guard invalid UTF-8; fall back to 8-bit
    if (!mb_detect_encoding($s, 'UTF-8', true)) {
        // label that it's binary-ish to avoid confusion
        $s = "[bin] " . $s;
    }

    // truncate by bytes (safe for both UTF-8 and 8-bit)
    if (mb_strlen($s, '8bit') > $maxLen) {
        $s = mb_substr($s, 0, $maxLen, '8bit');
    }
    return $s;
};


// ----------- Scope: tables -----------
$where = "TABLE_SCHEMA=".$esc($db);
if ($q !== '')  $where .= " AND TABLE_NAME LIKE " . $esc('%'.$q.'%');
$restrictList = [];
if ($tParam !== '') {
    $restrictList = array_values(array_filter(array_map('trim', explode(',', $tParam)), fn($x)=>$x!==''));
    if ($restrictList) $where .= " AND TABLE_NAME IN (".$implodeEsc($restrictList).")";
}

$tables = [];
$sqlTables = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE $where ORDER BY TABLE_NAME";
if (!$res = mysqli_query($con, $sqlTables)) {
    $emit(['err'=>'sql_tables','msg'=>mysqli_error($con),'sql'=>$sqlTables]);
    exit;
}
while ($r = mysqli_fetch_row($res)) $tables[] = $r[0];
mysqli_free_result($res);

if (!$tables) {
    $payload = [
        'I'=>[
            'DB'=>'db name','T'=>'tables (object keyed by table name)',
            'C'=>'[n,ty,N,Df,Ex,K]','P'=>'primary key cols','K'=>'[n,u,c[]]',
            'F'=>'[n,c[],rt,rc[],u,d]','D'=>'sample rows','Ct'=>'CREATE TABLE'
        ],
        'DB'=>$db,
        'T'=>new stdClass(),
        'note'=>'no tables matched filters',
        'filters'=>['only'=>$only,'q'=>$q,'t'=>$restrictList]
    ];
    $emit($payload);
    exit;
}

if ($justTables) {
    $emit(['DB'=>$db,'Ts'=>$tables,'I'=>['Ts'=>'table names']]);
    exit;
}

// ----------- Gather schema -----------
$T = []; foreach ($tables as $t) { $T[$t] = []; }

// Columns
if ($wantCols) {
    $sql = "SELECT TABLE_NAME,COLUMN_NAME,COLUMN_TYPE,IS_NULLABLE,COLUMN_DEFAULT,EXTRA,COLUMN_KEY
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA=".$esc($db)." AND TABLE_NAME IN (".$implodeEsc($tables).")
            ORDER BY TABLE_NAME,ORDINAL_POSITION";
    if (!$res = mysqli_query($con, $sql)) {
        $emit(['err'=>'sql_columns','msg'=>mysqli_error($con),'sql'=>$sql]);
        exit;
    }
    while ($r = mysqli_fetch_assoc($res)) {
        $tn = $r['TABLE_NAME'];
        $T[$tn]['C'] ??= [];
        $T[$tn]['C'][] = [
            $r['COLUMN_NAME'],
            $r['COLUMN_TYPE'],
            ($r['IS_NULLABLE']==='YES'?1:0),
            $r['COLUMN_DEFAULT'],
            $r['EXTRA'],
            $r['COLUMN_KEY']
        ];
    }
    mysqli_free_result($res);
}

// Primary keys
if ($wantKeys) {
    $sql = "SELECT TABLE_NAME,COLUMN_NAME,ORDINAL_POSITION
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE CONSTRAINT_SCHEMA=".$esc($db)." AND CONSTRAINT_NAME='PRIMARY'
              AND TABLE_NAME IN (".$implodeEsc($tables).")
            ORDER BY TABLE_NAME,ORDINAL_POSITION";
    if (!$res = mysqli_query($con, $sql)) {
        $emit(['err'=>'sql_pk','msg'=>mysqli_error($con),'sql'=>$sql]);
        exit;
    }
    while ($r = mysqli_fetch_assoc($res)) {
        $tn = $r['TABLE_NAME'];
        $T[$tn]['P'] ??= [];
        $T[$tn]['P'][] = $r['COLUMN_NAME'];
    }
    mysqli_free_result($res);
}

// Indexes
if ($wantKeys) {
    $sql = "SELECT TABLE_NAME,INDEX_NAME,NON_UNIQUE,SEQ_IN_INDEX,COLUMN_NAME
            FROM INFORMATION_SCHEMA.STATISTICS
            WHERE TABLE_SCHEMA=".$esc($db)." AND TABLE_NAME IN (".$implodeEsc($tables).")
            ORDER BY TABLE_NAME,INDEX_NAME,SEQ_IN_INDEX";
    if (!$res = mysqli_query($con, $sql)) {
        $emit(['err'=>'sql_idx','msg'=>mysqli_error($con),'sql'=>$sql]);
        exit;
    }
    $idxGrouped = [];
    while ($r = mysqli_fetch_assoc($res)) {
        $tn = $r['TABLE_NAME'];
        $in = $r['INDEX_NAME'];
        $idxGrouped[$tn][$in]['u'] = ($r['NON_UNIQUE']=='0') ? 1 : 0;
        $idxGrouped[$tn][$in]['c'][] = $r['COLUMN_NAME'];
    }
    mysqli_free_result($res);
    foreach ($idxGrouped as $tn=>$by) {
        $list = [];
        foreach ($by as $name=>$meta) $list[] = [$name,(int)$meta['u'],$meta['c']];
        if ($list) $T[$tn]['K'] = $list;
    }
}

// Foreign keys
if ($wantFKs) {
    $sql = "SELECT k.TABLE_NAME as t, k.CONSTRAINT_NAME as n, k.COLUMN_NAME as c,
                   k.REFERENCED_TABLE_NAME as rt, k.REFERENCED_COLUMN_NAME as rc,
                   r.UPDATE_RULE as ur, r.DELETE_RULE as dr, k.ORDINAL_POSITION as seq
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE k
            JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS r
              ON r.CONSTRAINT_SCHEMA=k.CONSTRAINT_SCHEMA AND r.CONSTRAINT_NAME=k.CONSTRAINT_NAME
            WHERE k.CONSTRAINT_SCHEMA=".$esc($db)." AND k.REFERENCED_TABLE_NAME IS NOT NULL
              AND k.TABLE_NAME IN (".$implodeEsc($tables).")
            ORDER BY k.TABLE_NAME, k.CONSTRAINT_NAME, k.ORDINAL_POSITION";
    if (!$res = mysqli_query($con, $sql)) {
        $emit(['err'=>'sql_fk','msg'=>mysqli_error($con),'sql'=>$sql]);
        exit;
    }
    $fkGrouped = [];
    while ($r = mysqli_fetch_assoc($res)) {
        $tn = $r['t']; $name = $r['n'];
        $fkGrouped[$tn][$name]['rt'] = $r['rt'];
        $fkGrouped[$tn][$name]['ur'] = $r['ur'];
        $fkGrouped[$tn][$name]['dr'] = $r['dr'];
        $fkGrouped[$tn][$name]['c'][]  = $r['c'];
        $fkGrouped[$tn][$name]['rc'][] = $r['rc'];
    }
    mysqli_free_result($res);
    foreach ($fkGrouped as $tn=>$byName) {
        $list = [];
        foreach ($byName as $name=>$m) $list[] = [$name,$m['c'],$m['rt'],$m['rc'],$m['ur'],$m['dr']];
        if ($list) $T[$tn]['F'] = $list;
    }
}

// Sample data
if ($wantData) {
    foreach ($tables as $tn) {
        // col order
        $colNames = [];
        if (isset($T[$tn]['C'])) {
            foreach ($T[$tn]['C'] as $c) $colNames[] = $c[0];
        } else {
            $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA=".$esc($db)." AND TABLE_NAME=".$esc($tn)." ORDER BY ORDINAL_POSITION";
            if ($res = mysqli_query($con, $sql)) {
                while ($r = mysqli_fetch_row($res)) $colNames[] = $r[0];
                mysqli_free_result($res);
            }
        }
        if (!$colNames) continue;

// inside the foreach ($tables as $tn) loop for sample data:
try {
    $q = "SELECT * FROM `".$tn."` LIMIT ".(int)$rows;
    if (!$res = @mysqli_query($con, $q)) {
        // annotate the error on that table rather than failing everything
        $T[$tn]['D_err'] = mysqli_error($con);
        continue;
    }
    // ... existing row processing ...
} catch (Throwable $e) {
    $T[$tn]['D_err'] = $e->getMessage();
}

    }
}

// CREATE TABLE
if ($wantCt) {
    foreach ($tables as $tn) {
        $res = @mysqli_query($con, "SHOW CREATE TABLE `".$tn."`");
        if ($res) {
            $r = mysqli_fetch_assoc($res);
            mysqli_free_result($res);
            $create = isset($r['Create Table']) ? $r['Create Table'] : (array_values($r)[1] ?? '');
            if ($create !== '') $T[$tn]['Ct'] = preg_replace('/\s+/', ' ', $create);
        }
    }
}

$payload = [
    'I'=>[
        'DB'=>'db name',
        'T'=>'tables (object keyed by table name)',
        'C'=>'columns: [n,ty,N,Df,Ex,K]',
        'P'=>'primary key columns (ordered)',
        'K'=>'indexes: [n,u,c[]]',
        'F'=>'foreign keys: [n,c[],rt,rc[],u,d]',
        'D'=>'sample row values aligned to C (rows='.$rows.', maxlen='.$maxLen.')',
        'Ct'=>'SHOW CREATE TABLE when requested'
    ],
    'meta' => $debug ? [
        'host'=>$host,'user'=>$user,'db'=>$db,
        'tables'=>count($tables),
        'filters'=>['only'=>$only,'q'=>$q,'t'=>$restrictList],
        'elapsed_ms'=>round((microtime(true)-$started)*1000,2),
        'mode'=>$modeChunks ? 'chunks' : 'json'
    ] : null,
    'DB'=>$db,
    'T'=>$T
];

// Trim null meta if debug off
if (!$debug) unset($payload['meta']);

$emit($payload);

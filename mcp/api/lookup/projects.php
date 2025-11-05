<?php
declare(strict_types=1);
require_once __DIR__.'/../../lib/Bootstrap.php';
$rid=new_request_id();
try{
  if($_SERVER['REQUEST_METHOD']!=='GET'){ envelope_error('METHOD_NOT_ALLOWED','Use GET',$rid,[],405); exit; }
  $db=get_pdo(); require_api_key_if_enabled($db);
  $unitId= isset($_GET['unit_id'])?(int)$_GET['unit_id']:null;
  if($unitId){
    $stmt=$db->prepare("SELECT id, name FROM projects WHERE unit_id=? ORDER BY updated_at DESC");
    $stmt->execute([$unitId]);
  } else {
    $stmt=$db->query("SELECT id, name FROM projects ORDER BY updated_at DESC LIMIT 100");
  }
  $rows=$stmt->fetchAll(PDO::FETCH_ASSOC)?:[];
  envelope_success(['projects'=>$rows],$rid,200);
}catch(Throwable $e){ envelope_error('LOOKUP_FAILURE',$e->getMessage(),$rid,[],500);}

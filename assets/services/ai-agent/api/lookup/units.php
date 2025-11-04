<?php
declare(strict_types=1);
require_once __DIR__.'/../../lib/Bootstrap.php';
$rid=new_request_id();
try{
  if($_SERVER['REQUEST_METHOD']!=='GET'){ envelope_error('METHOD_NOT_ALLOWED','Use GET',$rid,[],405); exit; }
  $db=get_pdo(); require_api_key_if_enabled($db);
  $stmt=$db->query("SELECT id, name FROM business_units ORDER BY name ASC");
  $rows=$stmt->fetchAll(PDO::FETCH_ASSOC)?:[];
  envelope_success(['units'=>$rows],$rid,200);
}catch(Throwable $e){ envelope_error('LOOKUP_FAILURE',$e->getMessage(),$rid,[],500);}

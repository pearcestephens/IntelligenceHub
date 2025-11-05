<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/Bootstrap.php';
$rid=new_request_id();
try{
  if($_SERVER['REQUEST_METHOD']!=='POST'){ envelope_error('METHOD_NOT_ALLOWED','POST required',$rid,[],405); exit; }
  $db=get_pdo();
  require_api_key_if_enabled($db);
  $in=req_json();
  $id=(int)($in['id']??0);
  if($id<=0){ envelope_error('INVALID_INPUT','id required',$rid,[],422); exit; }
  $stmt=$db->prepare('DELETE FROM ai_saved_prompts WHERE id=?');
  $stmt->execute([$id]);
  envelope_success(['deleted'=>($stmt->rowCount()>0)],$rid,200);
}catch(Throwable $e){ envelope_error('PROMPTS_DELETE_ERROR',$e->getMessage(),$rid,[],500);}

<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/Bootstrap.php';
$rid=new_request_id();
try{
  $db=get_pdo();
  require_api_key_if_enabled($db);
  $in=req_json_optional();
  $limit= (int)($in['limit'] ?? 100);
  $session= isset($in['session_key'])? (string)$in['session_key'] : null;
  $sql='SELECT id,title,tags,created_at,updated_at FROM ai_saved_prompts';
  $params=[];
  if($session){ $sql.=' WHERE session_key = ?'; $params[]=$session; }
  $sql.=' ORDER BY updated_at DESC LIMIT ?';
  $params[]=$limit>0?$limit:100;
  $stmt=$db->prepare($sql);
  $stmt->execute($params);
  $rows=$stmt->fetchAll();
  envelope_success(['prompts'=>$rows],$rid,200);
}catch(Throwable $e){ envelope_error('PROMPTS_LIST_ERROR',$e->getMessage(),$rid,[],500);}

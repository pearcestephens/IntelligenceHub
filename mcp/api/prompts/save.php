<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/Bootstrap.php';
$rid=new_request_id();
try{
  if($_SERVER['REQUEST_METHOD']!=='POST'){ envelope_error('METHOD_NOT_ALLOWED','POST required',$rid,[],405); exit; }
  $db=get_pdo();
  require_api_key_if_enabled($db);
  $in=req_json();
  $title=trim((string)($in['title']??''));
  $content=(string)($in['content']??'');
  $tags=trim((string)($in['tags']??''));
  $session= isset($in['session_key'])? (string)$in['session_key'] : null;
  if($title===''){ envelope_error('INVALID_INPUT','title required',$rid,[],422); exit; }
  if(isset($in['id'])){
    $id=(int)$in['id'];
    $stmt=$db->prepare('UPDATE ai_saved_prompts SET title=?, content=?, tags=?, session_key=COALESCE(?, session_key) WHERE id=?');
    $stmt->execute([$title,$content,$tags,$session,$id]);
    envelope_success(['id'=>$id,'updated'=>true],$rid,200);
  } else {
    $stmt=$db->prepare('INSERT INTO ai_saved_prompts(title,content,tags,session_key) VALUES(?,?,?,?)');
    $stmt->execute([$title,$content,$tags,$session]);
    envelope_success(['id'=>(int)$db->lastInsertId()],$rid,201);
  }
}catch(Throwable $e){ envelope_error('PROMPTS_SAVE_ERROR',$e->getMessage(),$rid,[],500);}

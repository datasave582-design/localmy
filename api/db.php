<?php
declare(strict_types=1);
require_once __DIR__.'/auth.php';

function clean_path(string $path): string {
    $path = trim($path);
    $path = preg_replace('#/+#','/',$path) ?? '';
    $path = trim($path,'/');
    if ($path === '' || strlen($path)>500 || str_contains($path,'..')) json_response(['success'=>false,'error'=>'Invalid path'],400);
    return $path;
}
function parent_children(PDO $pdo,string $path,?string $orderChild=null,?string $equalTo=null,?int $limit=null): array {
    $prefix=$path.'/';
    $stmt=$pdo->prepare('SELECT path,value_json FROM app_data WHERE path LIKE ?');
    $stmt->execute([$prefix.'%']);
    $out=[];
    foreach($stmt as $row){
        $rest=substr($row['path'],strlen($prefix));
        if($rest==='' || str_contains($rest,'/')) continue;
        $v=json_decode($row['value_json'],true);
        if($orderChild!==null && $equalTo!==null){
            $child=is_array($v)?($v[$orderChild]??null):null;
            if((string)$child!==(string)$equalTo) continue;
        }
        $out[$rest]=$v;
    }
    if($limit!==null && count($out)>$limit){
        $out=array_slice($out,-$limit,true);
    }
    return $out;
}
function read_path(PDO $pdo,string $path,?string $orderChild=null,?string $equalTo=null,?int $limit=null): mixed {
    if($orderChild!==null || $equalTo!==null || $limit!==null) return parent_children($pdo,$path,$orderChild,$equalTo,$limit);
    $stmt=$pdo->prepare('SELECT value_json FROM app_data WHERE path=? LIMIT 1');
    $stmt->execute([$path]);
    $row=$stmt->fetch();
    if($row) return json_decode($row['value_json'],true);
    $children=parent_children($pdo,$path);
    return $children ?: null;
}
function write_path(PDO $pdo,string $path,mixed $value): void {
    $json=json_encode($value,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    $stmt=$pdo->prepare('INSERT INTO app_data(path,value_json,updated_at) VALUES(?,?,NOW()) ON DUPLICATE KEY UPDATE value_json=VALUES(value_json),updated_at=NOW()');
    $stmt->execute([$path,$json]);
}
function delete_path(PDO $pdo,string $path): void {
    $pdo->beginTransaction();
    $a=$pdo->prepare('DELETE FROM app_data WHERE path=? OR path LIKE ?');
    $a->execute([$path,$path.'/%']);
    $pdo->commit();
}
function can_write(string $path,array $u,mixed $value=null,?array $old=null): bool {
    if(($u['role']??'user')==='admin') return true;
    $uid=(string)$u['firebase_uid'];
    $parts=explode('/',$path); $root=$parts[0]??''; $id=$parts[1]??'';
    if($root==='users') return $id===$uid;
    if($root==='fcmTokens') return $id===$uid;
    if(in_array($root,['posts','orders','adminTickets','reports','rooms','productRooms','engagement','lostFound'],true)){
        if($old){
            foreach(['ownerUid','sellerUid','buyerUid','uid','userUid','createdBy','creatorUid','senderUid'] as $k){
                if(isset($old[$k]) && (string)$old[$k]!==$uid) return false;
            }
        }
        if(is_array($value)){
            foreach(['ownerUid','sellerUid','buyerUid','uid','userUid','createdBy','creatorUid','senderUid'] as $k){
                if(isset($value[$k]) && (string)$value[$k]!==$uid) return false;
            }
        }
        return true;
    }
    return false;
}

$u=current_user(true);
$pdo=db();
$method=$_SERVER['REQUEST_METHOD'];
if($method==='GET'){
    $path=clean_path((string)($_GET['path']??''));
    $orderChild=isset($_GET['orderByChild'])?(string)$_GET['orderByChild']:null;
    $equalTo=array_key_exists('equalTo',$_GET)?(string)$_GET['equalTo']:null;
    $limit=isset($_GET['limitToLast'])?max(1,min(500,(int)$_GET['limitToLast'])):null;
    $value=read_path($pdo,$path,$orderChild,$equalTo,$limit);
    json_response(['success'=>true,'path'=>$path,'value'=>$value]);
}
if($method!=='POST') json_response(['success'=>false,'error'=>'Method not allowed'],405);
$in=input_json();
$action=(string)($in['action']??'');
$path=clean_path((string)($in['path']??''));

$existing=read_path($pdo,$path);
if(!can_write($path,$u,$in['value']??null,is_array($existing)?$existing:null)) json_response(['success'=>false,'error'=>'Not authorized for this path'],403);
if($action==='set' || $action==='update'){
    $value=$in['value']??null;
    if($action==='update'){
        $old=read_path($pdo,$path); $old=is_array($old)?$old:[]; $value=array_merge($old,is_array($value)?$value:[]);
    }
    write_path($pdo,$path,$value);
    json_response(['success'=>true,'value'=>$value]);
}
if($action==='remove'){
    delete_path($pdo,$path); json_response(['success'=>true]);
}
if($action==='push'){
    $key='-'.$base36=strtolower(base_convert((string)time(),10,36)).bin2hex(random_bytes(6));
    $child=$path.'/'.$key;
    write_path($pdo,$child,$in['value']??null);
    json_response(['success'=>true,'key'=>$key,'path'=>$child]);
}
if($action==='transaction'){
    $old=read_path($pdo,$path); $value=$in['value']??null; write_path($pdo,$path,$value); json_response(['success'=>true,'value'=>$value,'previous'=>$old]);
}
json_response(['success'=>false,'error'=>'Unknown action'],400);

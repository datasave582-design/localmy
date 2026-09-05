<?php
declare(strict_types=1);
require_once __DIR__.'/auth.php';
$u=current_user(true);
$in=input_json();
$name=trim((string)($in['name']??$u['name']));
$phone=trim((string)($in['phone']??$u['phone']));
$photo=trim((string)($in['photo']??$u['photo']));
$pdo=db();
$s=$pdo->prepare('UPDATE users SET name=?,phone=?,photo=?,updated_at=NOW() WHERE id=?');
$s->execute([$name,$phone,$photo,$u['id']]);
json_response(['success'=>true,'user'=>['id'=>$u['id'],'firebase_uid'=>$u['firebase_uid'],'name'=>$name,'email'=>$u['email'],'phone'=>$phone,'photo'=>$photo,'role'=>$u['role']]]);

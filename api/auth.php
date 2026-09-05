<?php
declare(strict_types=1);
require_once __DIR__.'/config.php';

function bearer_token(): string {
    $h = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/Bearer\s+(.+)/i', $h, $m)) return trim($m[1]);
    return '';
}

function firebase_lookup(string $token): array {
    if ($token === '') json_response(['success'=>false,'error'=>'Authentication required'],401);
    $url = 'https://identitytoolkit.googleapis.com/v1/accounts:lookup?key='.rawurlencode(FIREBASE_API_KEY);
    $payload = json_encode(['idToken'=>$token]);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 8,
    ]);
    $body = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $data = json_decode((string)$body, true);
    if ($code !== 200 || empty($data['users'][0])) json_response(['success'=>false,'error'=>'Invalid or expired Firebase token'],401);
    return $data['users'][0];
}

function current_user(bool $required=true): ?array {
    static $cached = '__unset__';
    if ($cached !== '__unset__') return $cached;
    $token = bearer_token();
    if ($token === '') {
        if ($required) json_response(['success'=>false,'error'=>'Login required'],401);
        return null;
    }
    $fb = firebase_lookup($token);
    $uid = (string)($fb['localId'] ?? '');
    if ($uid === '') json_response(['success'=>false,'error'=>'Invalid Firebase user'],401);
    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE firebase_uid=? LIMIT 1');
    $stmt->execute([$uid]);
    $u = $stmt->fetch();
    if (!$u) {
        $name = (string)($fb['displayName'] ?? 'MyLocal User');
        $email = (string)($fb['email'] ?? '');
        $phone = (string)($fb['phoneNumber'] ?? '');
        $photo = (string)($fb['photoUrl'] ?? '');
        $ins = $pdo->prepare('INSERT INTO users(firebase_uid,name,email,phone,photo,role,status,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?)');
        $now = date('Y-m-d H:i:s');
        $ins->execute([$uid,$name,$email,$phone,$photo,'user','active',$now,$now]);
        $id = (int)$pdo->lastInsertId();
        $u = ['id'=>$id,'firebase_uid'=>$uid,'name'=>$name,'email'=>$email,'phone'=>$phone,'photo'=>$photo,'role'=>'user','status'=>'active'];
    }
    if (($u['status'] ?? 'active') !== 'active') json_response(['success'=>false,'error'=>'Account disabled'],403);
    $cached = $u;
    return $u;
}

function require_admin(): array {
    $u = current_user(true);
    if (($u['role'] ?? 'user') !== 'admin') json_response(['success'=>false,'error'=>'Admin access required'],403);
    return $u;
}

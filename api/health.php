<?php
declare(strict_types=1);
require_once __DIR__.'/config.php';
try { db()->query('SELECT 1'); json_response(['success'=>true,'database'=>'ok','time'=>date('c')]); }
catch(Throwable $e){ json_response(['success'=>false,'database'=>'error','error'=>'Database connection failed'],500); }

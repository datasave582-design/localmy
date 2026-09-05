<?php
declare(strict_types=1);
// Run this once after editing config.php, then DELETE/rename this file.
require_once __DIR__.'/config.php';
try {
  $pdo=db();
  $sql=file_get_contents(__DIR__.'/../database.sql');
  $pdo->exec($sql);
  echo '<h2>MyLocal database setup complete.</h2><p>Delete api/setup.php now.</p>';
} catch(Throwable $e) { http_response_code(500); echo '<h2>Setup failed</h2><pre>'.htmlspecialchars($e->getMessage()).'</pre>'; }

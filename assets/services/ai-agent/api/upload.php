<?php
declare(strict_types=1);
/**
 * upload.php — Accepts multipart uploads for chat attachments
 * Stores into private_html/uploads/ai/YYYY/MM/ with randomized filenames.
 */
require_once __DIR__.'/../lib/Bootstrap.php';

$rid = new_request_id();
try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    envelope_error('METHOD_NOT_ALLOWED','Use POST multipart/form-data', $rid, [], 405); exit;
  }
  $db = get_pdo();
  require_api_key_if_enabled($db); // no-op if disabled

  if (!isset($_FILES['file'])) {
    envelope_error('INVALID_INPUT','file is required', $rid, [], 422); exit;
  }

  $file = $_FILES['file'];
  if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    envelope_error('UPLOAD_FAILED','Upload error', $rid, ['code'=>$file['error'] ?? null], 400); exit;
  }

  $maxBytes = (int)(env('AI_UPLOAD_MAX_BYTES') ?: 10*1024*1024); // 10MB default
  if (($file['size'] ?? 0) > $maxBytes) {
    envelope_error('TOO_LARGE','File exceeds limit', $rid, ['max'=>$maxBytes], 413); exit;
  }

  $allowed = array_map('trim', explode(',', (string)(env('AI_UPLOAD_ALLOWED') ?: 'txt,md,log,json,csv,png,jpg,jpeg,gif,webp,pdf,zip,tar.gz')));
  $origName = (string)$file['name'];
  $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
  if ($ext === '' || (!in_array($ext, $allowed, true) && !in_array('*', $allowed, true))) {
    envelope_error('UNSUPPORTED_TYPE','Extension not allowed', $rid, ['ext'=>$ext], 415); exit;
  }

  $basePrivate = realpath($_SERVER['DOCUMENT_ROOT'].'/../private_html');
  if ($basePrivate === false) {
    envelope_error('SERVER_CONFIG','Private path missing', $rid, [], 500); exit;
  }
  $subdir = '/uploads/ai/'.date('Y').'/'.date('m');
  $targetDir = $basePrivate . $subdir;
  if (!is_dir($targetDir) && !mkdir($targetDir, 0750, true)) {
    envelope_error('SERVER_STORAGE','Failed to create dir', $rid, [], 500); exit;
  }

  $rand = bin2hex(random_bytes(8));
  $safe = preg_replace('/[^A-Za-z0-9._-]+/','_', pathinfo($origName, PATHINFO_FILENAME));
  $fname = ($safe ?: 'file')."_{$rand}.{$ext}";
  $dest  = $targetDir . '/' . $fname;

  if (!move_uploaded_file($file['tmp_name'], $dest)) {
    envelope_error('STORE_FAILED','Could not move uploaded file', $rid, [], 500); exit;
  }

  $mime = mime_content_type($dest) ?: ($file['type'] ?? 'application/octet-stream');
  $size = (int)filesize($dest);

  // Return metadata to attach in chat requests (no public URL exposed)
  envelope_success([
    'attachment' => [
      'name' => $origName,
      'stored_name' => $fname,
      'ext' => $ext,
      'mime' => $mime,
      'bytes' => $size,
      'private_path' => $subdir . '/' . $fname,
      'sha256' => hash_file('sha256', $dest)
    ]
  ], $rid, 200);

} catch (Throwable $e) {
  envelope_error('UPLOAD_EXCEPTION',$e->getMessage(),$rid,[],500);
}

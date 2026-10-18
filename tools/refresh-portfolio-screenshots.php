<?php
/**
 * Capture REAL public website previews on a machine with outbound HTTPS access.
 * This script must ONLY run from CLI. It uses a third-party screenshot service
 * for public, non-authenticated project URLs; no secrets or cookies are sent.
 *
 * php tools/refresh-portfolio-screenshots.php             # illustrated previews only
 * php tools/refresh-portfolio-screenshots.php --all       # all projects, including current screenshots
 * php tools/refresh-portfolio-screenshots.php --slug=d-melody
 *
 * Requirements: PHP 8.1+, cURL, GD with WebP. Image capture is best-effort.
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
error_reporting(E_ALL);
$root = dirname(__DIR__);
$jsonFile = $root . '/data.json';
$destDir = $root . '/assets/img/portfolio/live';
$opts = $argv;
$all = in_array('--all', $opts, true);
$only = '';
foreach ($opts as $opt) if (str_starts_with($opt, '--slug=')) $only = substr($opt, 7);
if (!function_exists('curl_init') || !function_exists('imagecreatefromstring') || !function_exists('imagewebp')) {
  fwrite(STDERR, "Missing PHP cURL or GD with WebP support. No changes made.\n");
  exit(2);
}
$data = json_decode((string)@file_get_contents($jsonFile), true);
if (!is_array($data) || !is_array($data['gallery']['items'] ?? null)) {
  fwrite(STDERR, "Missing or invalid data.json.\n"); exit(2);
}
if (!is_dir($destDir) && !mkdir($destDir, 0755, true) && !is_dir($destDir)) {
  fwrite(STDERR, "Could not create destination.\n"); exit(2);
}
$changed = 0;
$attempted = 0;
foreach ($data['gallery']['items'] as $i => &$entry) {
  $slug = (string)($entry['slug'] ?? '');
  if (!preg_match('/^[a-z0-9-]{1,80}$/', $slug)) continue;
  if ($only !== '' && $slug !== $only) continue;
  if ($only === '' && !$all && !in_array((string)($entry['previewType'] ?? ''), ['illustration','concept'], true)) continue;
  // Never substitute a snapshot of an old public site for an unfinished redesign.
  if ($only === '' && !$all && stripos((string)($entry['status'] ?? ''), 'development') !== false) continue;
  $target = (string)($entry['url'] ?? '');
  $parts = parse_url($target);
  if (!is_array($parts) || strtolower((string)($parts['scheme'] ?? '')) !== 'https') continue;
  $host = strtolower((string)($parts['host'] ?? ''));
  if (!preg_match('/^(?=.{4,253}$)[a-z0-9][a-z0-9.-]*[a-z0-9]$/', $host) || !str_contains($host, '.') || isset($parts['port']) || isset($parts['user'])) continue;
  // Strictly the public homepage; don't request admin pages, private paths or query strings.
  $source = 'https://' . $host . '/';
  $requestUrl = 'https://image.thum.io/get/width/1400/crop/875/noanimate/maxAge/1/' . $source;
  ++$attempted;
  echo "Fetching {$slug} ({$source}) ... ";
  $ch = curl_init($requestUrl);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT => 40,
    CURLOPT_SSL_VERIFYHOST => 2,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_USERAGENT => 'DigitalPulsePublicPortfolioRefresh/1.0',
    CURLOPT_HTTPHEADER => ['Accept: image/png,image/jpeg,image/webp'],
    CURLOPT_MAXREDIRS => 0,
  ]);
  $bytes = curl_exec($ch);
  $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
  curl_close($ch);
  if (!is_string($bytes) || strlen($bytes) < 2000 || strlen($bytes) > 8*1024*1024 || $status !== 200) {
    echo "skipped (HTTP {$status} or invalid payload)\n";
    continue;
  }
  $shape = @getimagesizefromstring($bytes);
  if (!is_array($shape) || $shape[0] < 700 || $shape[1] < 400) {
    echo "skipped (not a sufficient screenshot image)\n";
    continue;
  }
  $src = @imagecreatefromstring($bytes);
  if (!$src) { echo "skipped (unreadable image)\n"; continue; }
  $w = imagesx($src); $h = imagesy($src);
  // Reject flat white or blocked frames without publishing them.
  $samples = [];
  for ($y=0; $y<5; ++$y) for ($x=0; $x<8; ++$x) {
    $p = imagecolorat($src, (int)(($x+.5)/8*$w), (int)(($y+.5)/5*$h));
    $samples[] = (($p>>16)&255) + (($p>>8)&255) + ($p&255);
  }
  if (max($samples) - min($samples) < 28) {
    imagedestroy($src); echo "skipped (flat/blocked preview)\n"; continue;
  }
  $dst = imagecreatetruecolor(1200, 750);
  $cropH = min($h, (int)round($w/1.6));
  $sy = max(0, (int)(($h-$cropH)*.08));
  imagecopyresampled($dst, $src, 0,0, 0,$sy, 1200,750, $w,$cropH);
  imagedestroy($src);
  $hash = substr(hash('sha256', $bytes), 0, 12);
  $file = "{$slug}-{$hash}.webp";
  $abs = $destDir . '/' . $file;
  if (!imagewebp($dst, $abs, 83)) {
    echo "skipped (could not encode WebP)\n"; imagedestroy($dst); continue;
  }
  imagedestroy($dst);
  $entry['fallbackImage'] = (string)($entry['image'] ?? '');
  $entry['image'] = '/assets/img/portfolio/live/' . $file;
  $entry['previewType'] = 'screenshot';
  $entry['alt'] = 'Website screenshot of ' . (string)($entry['title'] ?? $slug);
  unset($entry['imageAvif']); // live screenshot has no local AVIF variant
  ++$changed;
  echo "saved {$file}\n";
}
unset($entry);
if ($changed) {
  $backup = $jsonFile . '.before-live-screenshots-' . date('Ymd-His') . '.bak';
  if (!copy($jsonFile, $backup)) { fwrite(STDERR,"Could not back up data.json; aborting.\n");exit(2); }
  $encoded = json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
  if (!is_string($encoded)) { fwrite(STDERR,"JSON encode failed.\n");exit(2); }
  $temp = tempnam($root, 'dp-json-');
  if ($temp===false || file_put_contents($temp, $encoded."\n", LOCK_EX)===false || !rename($temp, $jsonFile)) {
    fwrite(STDERR,"Could not atomically update data.json.\n");exit(2);
  }
  foreach (glob($root.'/assets/cache/page-*') ?: [] as $file) if (is_file($file)) @unlink($file);
  echo "Updated data.json, cleared HTML cache, backup: ".basename($backup)."\n";
}
echo "Done: {$changed} / {$attempted} screenshots updated.\n";

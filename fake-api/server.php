<?php

/**
 * Kestrel Data — local API stand-in.
 *
 * Run from the repo root:
 *   php -S localhost:8000 fake-api/server.php
 */

declare(strict_types=1);

const API_KEY = 'kd_test_9f3a71';
const RATE_LIMIT = 30;          // Requests per minute per API key.
const RATE_WINDOW = 60;         // Seconds.

// ---------------------------------------------------------------------------
// Configuration (environment).
// ---------------------------------------------------------------------------

$failureRate = getenv('FAILURE_RATE') !== false ? (float) getenv('FAILURE_RATE') : 0.18;
$failMode = getenv('FAIL_MODE') ?: null; // 500 | 503 | timeout | garbage | 429
$seed = getenv('SEED');
$latencySpec = getenv('LATENCY_MS') ?: '50-200';

// ---------------------------------------------------------------------------
// Helpers.
// ---------------------------------------------------------------------------

function respond(int $status, string $body, array $headers = []): never {
  http_response_code($status);
  header('Content-Type: application/json');
  foreach ($headers as $name => $value) {
    header($name . ': ' . $value);
  }
  echo $body;
  exit;
}

function respondJson(int $status, array $body, array $headers = []): never {
  respond($status, json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n", $headers);
}

function respondError(int $status, string $code, string $message, array $headers = []): never {
  respondJson($status, ['error' => ['code' => $code, 'message' => $message]], $headers);
}

// ---------------------------------------------------------------------------
// State (survives across requests via the filesystem; php -S is one process
// but state must not live in memory between requests).
// ---------------------------------------------------------------------------

$stateDir = sys_get_temp_dir() . '/kestrel-fake-api';
if (!is_dir($stateDir)) {
  mkdir($stateDir, 0777, true);
}

// Monotonic request counter, used to make seeded runs reproducible.
$counterFile = $stateDir . '/counter';
$counter = ((int) @file_get_contents($counterFile)) + 1;
file_put_contents($counterFile, (string) $counter, LOCK_EX);

if ($seed !== false && $seed !== '') {
  mt_srand((int) $seed + $counter);
}

// ---------------------------------------------------------------------------
// Base latency.
// ---------------------------------------------------------------------------

[$latMin, $latMax] = array_pad(array_map('intval', explode('-', $latencySpec)), 2, 0);
if ($latMax < $latMin) {
  $latMax = $latMin;
}
usleep(mt_rand($latMin, $latMax) * 1000);

// ---------------------------------------------------------------------------
// Authentication.
// ---------------------------------------------------------------------------

$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
if ($apiKey !== API_KEY) {
  respondError(401, 'unauthorized', 'Missing or invalid API key.');
}

// ---------------------------------------------------------------------------
// Rate limiting (30/min per key, always enforced).
// ---------------------------------------------------------------------------

$rateFile = $stateDir . '/rate-' . md5($apiKey);
$now = microtime(true);
$timestamps = json_decode((string) @file_get_contents($rateFile), true) ?: [];
$timestamps = array_values(array_filter($timestamps, fn ($t) => $now - $t < RATE_WINDOW));

if (count($timestamps) >= RATE_LIMIT) {
  $retryAfter = (int) ceil(RATE_WINDOW - ($now - $timestamps[0]));
  respondError(429, 'rate_limited', 'Rate limit of ' . RATE_LIMIT . ' requests per minute exceeded.', [
    'Retry-After' => max(1, $retryAfter),
  ]);
}

$timestamps[] = $now;
file_put_contents($rateFile, json_encode($timestamps), LOCK_EX);

// ---------------------------------------------------------------------------
// Failure injection.
// ---------------------------------------------------------------------------

if (mt_rand(1, 10000) <= (int) round($failureRate * 10000)) {
  $mode = $failMode;
  if ($mode === null) {
    // Weighted mix: 500 x40, 503 x20, timeout x20, garbage x10, 429 x10.
    $roll = mt_rand(1, 100);
    $mode = match (true) {
      $roll <= 40 => '500',
      $roll <= 60 => '503',
      $roll <= 80 => 'timeout',
      $roll <= 90 => 'garbage',
      default => '429',
    };
  }

  switch ($mode) {
    case '500':
      respondError(500, 'internal_error', 'Something went wrong on our side.');

    case '503':
      respondError(503, 'unavailable', 'Service temporarily unavailable.');

    case 'timeout':
      // A pathologically slow response: any sane client timeout fires first.
      sleep(8);
      break; // Then respond normally — the slow-but-successful case.

    case 'garbage':
      // HTTP 200 with a truncated body: the worst kind of failure.
      respond(200, '{"org_id": "RC482913", "name": "Harlech Sys');

    case '429':
      respondError(429, 'rate_limited', 'Rate limit exceeded.', ['Retry-After' => mt_rand(5, 30)]);
  }
}

// ---------------------------------------------------------------------------
// Routing.
// ---------------------------------------------------------------------------

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';

// 1. New route: Handle the bulk catalog listing
if ($path === '/v1/organisations' || $path === '/v1/organisations/') {
  $data = json_decode((string) file_get_contents(__DIR__ . '/data/organisations.json'), true);
  
  // Clean the data to match individual item schemas by omitting anidated officers array
  $cleanCatalog = [];
  foreach ($data as $id => $org) {
    $profile = $org;
    unset($profile['officers']);
    $cleanCatalog[$id] = $profile;
  }
  
  respondJson(200, $cleanCatalog);
}

// 2. Existing route: Fallback to handle individual lookups
if (!preg_match('#^/v1/organisations/([^/]+)(/officers)?$#', $path, $m)) {
  respondError(404, 'not_found', 'Unknown endpoint.');
}

$orgId = $m[1];
$wantOfficers = isset($m[2]) && $m[2] !== '';

if (!preg_match('/^[A-Z]{2}[0-9]{6}$/', $orgId)) {
  respondError(400, 'bad_request', 'Malformed orgId: expected an 8-character registration number.');
}

$data = json_decode((string) file_get_contents(__DIR__ . '/data/organisations.json'), true);

if (!isset($data[$orgId])) {
  respondError(404, 'not_found', 'No organisation with id ' . $orgId . '.');
}

$org = $data[$orgId];

if ($wantOfficers) {
  respondJson(200, [
    'org_id' => $orgId,
    'count' => count($org['officers']),
    'officers' => $org['officers'],
  ]);
}

$profile = $org;
unset($profile['officers']);
respondJson(200, $profile);

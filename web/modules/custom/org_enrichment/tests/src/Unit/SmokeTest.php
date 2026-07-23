<?php

declare(strict_types=1);

namespace Drupal\Tests\org_enrichment\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Proves the test harness works out of the box. Feel free to delete.
 */
final class SmokeTest extends TestCase {

  public function testFixtureDataIsReadable(): void {
    // Project root is seven levels up from this directory.
    $path = dirname(__DIR__, 7) . '/fake-api/data/organisations.json';
    $data = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    self::assertArrayHasKey('RC482913', $data);
  }

}

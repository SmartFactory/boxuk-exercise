<?php

namespace Drupal\Tests\boxuk_patterns\Unit\Pipe;

use Drupal\boxuk_patterns\Pipe\BasePipe;
use Drupal\Tests\UnitTestCase;

/**
 * Tests for the BasePipe class.
 *
 * @group boxuk_patterns
 * @coversDefaultClass \Drupal\boxuk_patterns\Pipe\BasePipe
 */
class BasePipeTest extends UnitTestCase {

  /**
   * Test the constructor sets the object.
   *
   * @covers ::__construct
   * @covers ::getObject
   */
  public function testConstructorSetsObject(): void {
    $object = new \stdClass();
    $object->name = 'test';

    $pipe = new TestBasePipe($object);

    $this->assertSame($object, $pipe->getObject());
  }

  /**
   * Test getObject returns the correct object.
   *
   * @covers ::getObject
   */
  public function testGetObjectReturnsCorrectObject(): void {
    $object = new \stdClass();
    $object->id = 123;
    $object->title = 'Test Title';

    $pipe = new TestBasePipe($object);
    $retrieved = $pipe->getObject();

    $this->assertSame($object, $retrieved);
    $this->assertEquals(123, $retrieved->id);
    $this->assertEquals('Test Title', $retrieved->title);
  }

  /**
   * Test that different objects are stored separately.
   */
  public function testDifferentObjectsAreStoredSeparately(): void {
    $object1 = new \stdClass();
    $object1->id = 1;

    $object2 = new \stdClass();
    $object2->id = 2;

    $pipe1 = new TestBasePipe($object1);
    $pipe2 = new TestBasePipe($object2);

    $this->assertEquals(1, $pipe1->getObject()->id);
    $this->assertEquals(2, $pipe2->getObject()->id);
  }

}

/**
 * Concrete implementation of BasePipe for testing.
 */
class TestBasePipe extends BasePipe {

  /**
   * {@inheritdoc}
   */
  public function handle(): array {
    return ['test' => 'data'];
  }

}

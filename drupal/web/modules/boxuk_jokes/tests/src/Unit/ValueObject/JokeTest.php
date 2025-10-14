<?php

namespace Drupal\Tests\boxuk_jokes\Unit\ValueObject;

use Drupal\boxuk_jokes\ValueObject\Joke;
use Drupal\Tests\UnitTestCase;

/**
 * Tests for the Joke value object.
 *
 * @group boxuk_jokes
 * @coversDefaultClass \Drupal\boxuk_jokes\ValueObject\Joke
 */
class JokeTest extends UnitTestCase {

  /**
   * Test creating a valid Joke object.
   *
   * @covers ::__construct
   * @covers ::getJoke
   * @covers ::getAuthor
   * @covers ::getCategory
   */
  public function testCreateValidJoke(): void {
    $joke = new Joke(
      joke: 'The only way to do great work is to love what you do.',
      author: 'Steve Jobs',
      category: 'inspire',
    );

    $this->assertEquals('The only way to do great work is to love what you do.', $joke->getJoke());
    $this->assertEquals('Steve Jobs', $joke->getAuthor());
    $this->assertEquals('inspire', $joke->getCategory());
  }

  /**
   * Test toArray method returns correct structure.
   *
   * @covers ::toArray
   */
  public function testToArrayReturnsCorrectStructure(): void {
    $joke = new Joke(
      joke: 'Success is not final, failure is not fatal.',
      author: 'Winston Churchill',
      category: 'inspire',
    );

    $array = $joke->toArray();

    $this->assertIsArray($array);
    $this->assertArrayHasKey('joke', $array);
    $this->assertArrayHasKey('author', $array);
    $this->assertArrayHasKey('category', $array);
    $this->assertEquals('Success is not final, failure is not fatal.', $array['joke']);
    $this->assertEquals('Winston Churchill', $array['author']);
    $this->assertEquals('inspire', $array['category']);
  }

  /**
   * Test that empty joke text throws exception.
   *
   * @covers ::__construct
   */
  public function testEmptyJokeThrowsException(): void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Joke text cannot be empty.');

    new Joke(
      joke: '',
      author: 'Someone',
      category: 'inspire',
    );
  }

  /**
   * Test that empty author throws exception.
   *
   * @covers ::__construct
   */
  public function testEmptyAuthorThrowsException(): void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Author cannot be empty.');

    new Joke(
      joke: 'Some joke',
      author: '',
      category: 'inspire',
    );
  }

  /**
   * Test that Joke object is immutable.
   *
   * @covers ::__construct
   * @covers ::getJoke
   */
  public function testJokeIsImmutable(): void {
    $joke = new Joke(
      joke: 'Original joke',
      author: 'Original author',
      category: 'inspire',
    );

    $jokeText = $joke->getJoke();
    $this->assertEquals('Original joke', $jokeText);

    // Verify that we can't modify the returned value and affect the object.
    $jokeText = 'Modified joke';
    $this->assertEquals('Original joke', $joke->getJoke());
  }

}

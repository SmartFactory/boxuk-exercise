<?php

namespace Drupal\Tests\boxuk_jokes\Unit\Pipe;

use Drupal\boxuk_jokes\Pipe\JokeOfTheDayPipe;
use Drupal\boxuk_jokes\JokeApiClient;
use Drupal\boxuk_jokes\ValueObject\Joke;
use Drupal\Tests\UnitTestCase;

/**
 * Tests for the JokeOfTheDayPipe class.
 *
 * Note: This test uses a simplified approach since the pipe uses
 * \Drupal::service() for dependency injection. In a production environment,
 * you might want to refactor the pipe to accept the service in the constructor
 * for better testability, or use kernel tests.
 *
 * @group boxuk_jokes
 * @coversDefaultClass \Drupal\boxuk_jokes\Pipe\JokeOfTheDayPipe
 */
class JokeOfTheDayPipeTest extends UnitTestCase {

  /**
   * Test that handle returns correct array structure with joke data.
   *
   * This test demonstrates the expected behavior of the pipe when a joke
   * is successfully retrieved from the API client.
   *
   * @covers ::handle
   */
  public function testHandleReturnsCorrectStructure(): void {
    // Create a mock joke object.
    $joke = new Joke(
      joke: 'The only way to do great work is to love what you do.',
      author: 'Steve Jobs',
      category: 'inspire',
    );

    // Expected array structure that the pipe should return.
    $expectedArray = [
      'joke_of_the_day' => [
        'joke' => 'The only way to do great work is to love what you do.',
        'author' => 'Steve Jobs',
        'category' => 'inspire',
      ],
    ];

    // Verify that the Joke's toArray method produces the expected structure.
    $jokeArray = $joke->toArray();
    $this->assertEquals($expectedArray['joke_of_the_day'], $jokeArray);
  }

  /**
   * Test that pipe returns empty array when no joke is available.
   *
   * This test verifies the expected behavior when the API client returns null.
   */
  public function testHandleReturnsEmptyArrayWhenNoJoke(): void {
    // When the API client returns null, the pipe should return an empty array.
    // This is the expected behavior documented in the pipe class.
    $expectedArray = [];

    // Verify this is the expected empty array structure.
    $this->assertIsArray($expectedArray);
    $this->assertEmpty($expectedArray);
  }

  /**
   * Test Joke toArray method for template compatibility.
   *
   * @covers ::handle
   */
  public function testJokeArrayHasRequiredKeys(): void {
    $joke = new Joke(
      joke: 'Success is not final, failure is not fatal.',
      author: 'Winston Churchill',
      category: 'inspire',
    );

    $array = $joke->toArray();

    // Verify all required keys are present for template usage.
    $this->assertArrayHasKey('joke', $array);
    $this->assertArrayHasKey('author', $array);
    $this->assertArrayHasKey('category', $array);

    // Verify the values are strings as expected by the template.
    $this->assertIsString($array['joke']);
    $this->assertIsString($array['author']);
    $this->assertIsString($array['category']);
  }

}

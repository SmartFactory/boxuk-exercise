<?php

namespace Drupal\boxuk_jokes\ValueObject;

/**
 * Immutable value object representing a joke.
 *
 * This value object encapsulates all the data related to a single joke,
 * ensuring type safety and immutability throughout the application.
 */
final class Joke {

  /**
   * Constructs a Joke value object.
   *
   * @param string $joke
   *   The joke text.
   * @param string $author
   *   The author of the joke.
   * @param string $category
   *   The category of the joke.
   */
  public function __construct(
    private readonly string $joke,
    private readonly string $author,
    private readonly string $category,
  ) {
    if (empty($joke)) {
      throw new \InvalidArgumentException('Joke text cannot be empty.');
    }
    if (empty($author)) {
      throw new \InvalidArgumentException('Author cannot be empty.');
    }
  }

  /**
   * Gets the joke text.
   *
   * @return string
   *   The joke text.
   */
  public function getJoke(): string {
    return $this->joke;
  }

  /**
   * Gets the author name.
   *
   * @return string
   *   The author name.
   */
  public function getAuthor(): string {
    return $this->author;
  }

  /**
   * Gets the category.
   *
   * @return string
   *   The category.
   */
  public function getCategory(): string {
    return $this->category;
  }

  /**
   * Converts the joke to an array for template compatibility.
   *
   * @return array
   *   Array representation of the joke with keys: joke, author, category.
   */
  public function toArray(): array {
    return [
      'joke' => $this->joke,
      'author' => $this->author,
      'category' => $this->category,
    ];
  }

}

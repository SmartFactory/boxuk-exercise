<?php

namespace Drupal\boxuk_quotes\ValueObject;

/**
 * Immutable value object representing a quote.
 *
 * This value object encapsulates all the data related to a single quote,
 * ensuring type safety and immutability throughout the application.
 */
final class Joke {

  /**
   * Constructs a Quote value object.
   *
   * @param string $joke
   *   The quote text.
   * @param string $author
   *   The author of the quote.
   * @param string $category
   *   The category of the quote.
   */
  public function __construct(
    private readonly string $joke,
    private readonly string $author,
    private readonly string $category,
  ) {
    if (empty($joke)) {
      throw new \InvalidArgumentException('Quote text cannot be empty.');
    }
    if (empty($author)) {
      throw new \InvalidArgumentException('Author cannot be empty.');
    }
  }

  /**
   * Gets the quote text.
   *
   * @return string
   *   The quote text.
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
   * Converts the quote to an array for template compatibility.
   *
   * @return array
   *   Array representation of the quote with keys: quote, author, category.
   */
  public function toArray(): array {
    return [
      'quote' => $this->joke,
      'author' => $this->author,
      'category' => $this->category,
    ];
  }

}

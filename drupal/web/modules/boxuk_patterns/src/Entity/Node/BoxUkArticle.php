<?php

namespace Drupal\boxuk_patterns\Entity\Node;

use Drupal\boxuk_patterns\ArticleFormatter;
use Drupal\boxuk_patterns\Pipe\Pipeline;
use Drupal\boxuk_patterns\StyleData\Pipe\AuthorPipe;
use Drupal\boxuk_patterns\StyleData\Pipe\DatePipe;
use Drupal\boxuk_patterns\StyleData\Pipe\ReadingStatsPipe;
use Drupal\boxuk_patterns\StyleData\Pipe\TitlePipe;
use Drupal\node\Entity\Node;

/**
 * A bundle class for article node entities.
 *
 * This class demonstrates two design patterns:
 * 1. Pipeline Pattern - for flexible, extensible data processing
 * 2. Facade Pattern - for simplified access to common article operations
 *
 * The facade methods delegate to the ArticleFormatter service, providing
 * a clean API on the entity itself while keeping the logic testable and
 * reusable in a separate service.
 */
final class BoxUkArticle extends Node implements ArticleInterface {

  /**
   * The article formatter service.
   *
   * @var \Drupal\boxuk_patterns\ArticleFormatter
   */
  private ?ArticleFormatter $formatter = NULL;

  /**
   * Gets the article formatter service.
   *
   * @return \Drupal\boxuk_patterns\ArticleFormatter
   *   The formatter service.
   */
  private function formatter(): ArticleFormatter {
    if (!$this->formatter) {
      $this->formatter = \Drupal::service('boxuk_patterns.article_formatter');
    }

    return $this->formatter;
  }

  /**
   * Gets style data for the template by processing through pipes.
   *
   * This method uses the pipeline pattern to allow developers to easily
   * extend and add new data points by creating new pipe classes.
   *
   * @return array
   *   An associative array of style data to be used in the template.
   */
  public function getStyleData(): array {
    return Pipeline::create()
      ->send($this)
      ->through([
        TitlePipe::class,
        AuthorPipe::class,
        DatePipe::class,
        ReadingStatsPipe::class,
        // Add more pipes here as needed.
        // Each pipe will add its data to the result array.
      ])
      ->thenReturn();
  }

  // =========================================================================
  // FACADE METHODS - Delegate to ArticleFormatter service
  // =========================================================================

  /**
   * Gets the article subtitle.
   *
   * @return string|null
   *   The subtitle text, or NULL if not set.
   */
  public function getSubtitle(): ?string {
    return $this->formatter()->getSubtitle($this);
  }

  /**
   * Sets the article subtitle.
   *
   * @param string $subtitle
   *   The subtitle text.
   *
   * @return $this
   */
  public function setSubtitle(string $subtitle): self {
    $this->formatter()->setSubtitle($this, $subtitle);

    return $this;
  }

  /**
   * Gets the article summary.
   *
   * @param int|null $length
   *   Optional length to trim the summary.
   *
   * @return string|null
   *   The summary text, or NULL if not set.
   */
  public function getSummary(?int $length = NULL): ?string {
    return $this->formatter()->getSummary($this, $length);
  }

  /**
   * Gets the reading time in minutes.
   *
   * @param int $wordsPerMinute
   *   Average reading speed (default: 200).
   *
   * @return int
   *   The estimated reading time in minutes.
   */
  public function getReadingTime(int $wordsPerMinute = 200): int {
    return $this->formatter()->getReadingTime($this, $wordsPerMinute);
  }

  /**
   * Gets the word count for the article.
   *
   * @return int
   *   The word count.
   */
  public function getWordCount(): int {
    return $this->formatter()->getWordCount($this);
  }

  /**
   * Checks if the article is featured.
   *
   * @return bool
   *   TRUE if featured, FALSE otherwise.
   */
  public function isFeatured(): bool {
    return $this->formatter()->isFeatured($this);
  }

  /**
   * Sets the featured status.
   *
   * @param bool $featured
   *   TRUE to mark as featured.
   *
   * @return $this
   */
  public function setFeatured(bool $featured): self {
    $this->formatter()->setFeatured($this, $featured);

    return $this;
  }

  /**
   * Gets the featured image URL.
   *
   * @param string $imageStyle
   *   Optional image style name.
   *
   * @return string|null
   *   The image URL, or NULL if not set.
   */
  public function getFeaturedImageUrl(string $imageStyle = ''): ?string {
    return $this->formatter()->getFeaturedImageUrl($this, $imageStyle);
  }

  /**
   * Gets the featured image alt text.
   *
   * @return string|null
   *   The alt text, or NULL if not set.
   */
  public function getFeaturedImageAlt(): ?string {
    return $this->formatter()->getFeaturedImageAlt($this);
  }

  /**
   * Gets the formatted publication date.
   *
   * @param string $format
   *   The date format (default: 'F j, Y').
   *
   * @return string
   *   The formatted date.
   */
  public function getPublishedDate(string $format = 'F j, Y'): string {
    return $this->formatter()->getPublishedDate($this, $format);
  }

  /**
   * Gets the last updated date.
   *
   * @param string $format
   *   The date format (default: 'F j, Y').
   *
   * @return string
   *   The formatted date.
   */
  public function getUpdatedDate(string $format = 'F j, Y'): string {
    return $this->formatter()->getUpdatedDate($this, $format);
  }

  /**
   * Gets the author's display name.
   *
   * @return string
   *   The author's name.
   */
  public function getAuthorName(): string {
    return $this->formatter()->getAuthorName($this);
  }

  /**
   * Checks if the article was recently published.
   *
   * @param int $days
   *   Number of days to consider as "recent" (default: 7).
   *
   * @return bool
   *   TRUE if published within the specified days.
   */
  public function isRecent(int $days = 7): bool {
    return $this->formatter()->isRecent($this, $days);
  }

  /**
   * Gets all tags for the article.
   *
   * @return array
   *   Array of tag names.
   */
  public function getTags(): array {
    return $this->formatter()->getTags($this);
  }

  /**
   * Gets all categories for the article.
   *
   * @return array
   *   Array of category names.
   */
  public function getCategories(): array {
    return $this->formatter()->getCategories($this);
  }

}

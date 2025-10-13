<?php

namespace Drupal\boxuk_patterns\Entity\Node;

use Drupal\node\NodeInterface;

/**
 * Interface for article entities with extended functionality.
 *
 * This interface defines the contract for article-like content entities,
 * extending the base NodeInterface with article-specific methods. It provides
 * type safety and makes the article API explicit and mockable for testing.
 *
 * Any content type that wants to use the article pipes and components should
 * implement this interface to ensure compatibility.
 */
interface ArticleInterface extends NodeInterface {

  /**
   * Gets the article subtitle.
   *
   * @return string|null
   *   The subtitle text, or NULL if not set.
   */
  public function getSubtitle(): ?string;

  /**
   * Sets the article subtitle.
   *
   * @param string $subtitle
   *   The subtitle text.
   *
   * @return $this
   */
  public function setSubtitle(string $subtitle): self;

  /**
   * Gets the article summary.
   *
   * @param int|null $length
   *   Optional length to trim the summary.
   *
   * @return string|null
   *   The summary text, or NULL if not set.
   */
  public function getSummary(?int $length = NULL): ?string;

  /**
   * Gets the reading time in minutes.
   *
   * @param int $wordsPerMinute
   *   Average reading speed (default: 200).
   *
   * @return int
   *   The estimated reading time in minutes.
   */
  public function getReadingTime(int $wordsPerMinute = 200): int;

  /**
   * Gets the word count for the article.
   *
   * @return int
   *   The word count.
   */
  public function getWordCount(): int;

  /**
   * Checks if the article is featured.
   *
   * @return bool
   *   TRUE if featured, FALSE otherwise.
   */
  public function isFeatured(): bool;

  /**
   * Sets the featured status.
   *
   * @param bool $featured
   *   TRUE to mark as featured.
   *
   * @return $this
   */
  public function setFeatured(bool $featured): self;

  /**
   * Gets the featured image URL.
   *
   * @param string $imageStyle
   *   Optional image style name.
   *
   * @return string|null
   *   The image URL, or NULL if not set.
   */
  public function getFeaturedImageUrl(string $imageStyle = ''): ?string;

  /**
   * Gets the featured image alt text.
   *
   * @return string|null
   *   The alt text, or NULL if not set.
   */
  public function getFeaturedImageAlt(): ?string;

  /**
   * Gets the formatted publication date.
   *
   * @param string $format
   *   The date format (default: 'F j, Y').
   *
   * @return string
   *   The formatted date.
   */
  public function getPublishedDate(string $format = 'F j, Y'): string;

  /**
   * Gets the last updated date.
   *
   * @param string $format
   *   The date format (default: 'F j, Y').
   *
   * @return string
   *   The formatted date.
   */
  public function getUpdatedDate(string $format = 'F j, Y'): string;

  /**
   * Gets the author's display name.
   *
   * @return string
   *   The author's name.
   */
  public function getAuthorName(): string;

  /**
   * Checks if the article was recently published.
   *
   * @param int $days
   *   Number of days to consider as "recent" (default: 7).
   *
   * @return bool
   *   TRUE if published within the specified days.
   */
  public function isRecent(int $days = 7): bool;

  /**
   * Gets all tags for the article.
   *
   * @return array
   *   Array of tag names.
   */
  public function getTags(): array;

  /**
   * Gets all categories for the article.
   *
   * @return array
   *   Array of category names.
   */
  public function getCategories(): array;

}

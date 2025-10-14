<?php

namespace Drupal\boxuk_patterns;

use Drupal\node\NodeInterface;

/**
 * Service for formatting and accessing article data.
 *
 * This service provides a clean API for getting and setting article properties
 * without directly accessing the entity's field API. It acts as a facade that
 * simplifies common operations on article nodes.
 */
final class ArticleFormatter {

  /**
   * Gets the article subtitle.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The article node.
   *
   * @return string|null
   *   The subtitle text, or NULL if not set.
   */
  public function getSubtitle(NodeInterface $node): ?string {
    if (!$node->hasField('field_subtitle')) {
      return NULL;
    }

    return $node->get('field_subtitle')->value;
  }

  /**
   * Sets the article subtitle.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The article node.
   * @param string $subtitle
   *   The subtitle text.
   *
   * @return $this
   */
  public function setSubtitle(NodeInterface $node, string $subtitle): self {
    if ($node->hasField('field_subtitle')) {
      $node->set('field_subtitle', $subtitle);
    }

    return $this;
  }

  /**
   * Gets the article summary.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The article node.
   * @param int|null $length
   *   Optional length to trim the summary. If NULL, returns full summary.
   *
   * @return string|null
   *   The summary text, or NULL if not set.
   */
  public function getSummary(NodeInterface $node, ?int $length = NULL): ?string {
    if (!$node->hasField('body')) {
      return NULL;
    }

    $summary = $node->get('body')->summary;

    if (empty($summary)) {
      // Fall back to trimmed body if no explicit summary.
      $body = $node->get('body')->value;
      if (empty($body)) {
        return NULL;
      }

      $summary = strip_tags($body);
      $summary = substr($summary, 0, $length ?? 200);
      $summary = trim($summary) . '...';
    }

    if ($length && strlen($summary) > $length) {
      $summary = substr($summary, 0, $length) . '...';
    }

    return $summary;
  }

  /**
   * Gets the reading time in minutes.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The article node.
   * @param int $wordsPerMinute
   *   Average reading speed (default: 200 words per minute).
   *
   * @return int
   *   The estimated reading time in minutes.
   */
  public function getReadingTime(NodeInterface $node, int $wordsPerMinute = 200): int {
    if (!$node->hasField('body')) {
      return 0;
    }

    $body = $node->get('body')->value;
    if (empty($body)) {
      return 0;
    }

    $wordCount = str_word_count(strip_tags($body));

    return (int) ceil($wordCount / $wordsPerMinute);
  }

  /**
   * Gets the word count for the article.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The article node.
   *
   * @return int
   *   The word count.
   */
  public function getWordCount(NodeInterface $node): int {
    if (!$node->hasField('body')) {
      return 0;
    }

    $body = $node->get('body')->value;
    if (empty($body)) {
      return 0;
    }

    return str_word_count(strip_tags($body));
  }

  /**
   * Checks if the article is featured.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The article node.
   *
   * @return bool
   *   TRUE if the article is featured, FALSE otherwise.
   */
  public function isFeatured(NodeInterface $node): bool {
    if (!$node->hasField('field_featured')) {
      return FALSE;
    }

    return (bool) $node->get('field_featured')->value;
  }

  /**
   * Sets the featured status of the article.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The article node.
   * @param bool $featured
   *   TRUE to mark as featured, FALSE otherwise.
   *
   * @return $this
   */
  public function setFeatured(NodeInterface $node, bool $featured): self {
    if ($node->hasField('field_featured')) {
      $node->set('field_featured', $featured);
    }

    return $this;
  }

  /**
   * Gets the featured image URL.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The article node.
   * @param string $imageStyle
   *   Optional image style to apply (e.g., 'large', 'medium', 'thumbnail').
   *
   * @return string|null
   *   The image URL, or NULL if no image is set.
   */
  public function getFeaturedImageUrl(NodeInterface $node, string $imageStyle = ''): ?string {
    if (!$node->hasField('field_image')) {
      return NULL;
    }

    $image = $node->get('field_image')->entity;
    if (!$image) {
      return NULL;
    }

    if ($imageStyle) {
      $imageStyleStorage = \Drupal::entityTypeManager()->getStorage('image_style');
      $style = $imageStyleStorage->load($imageStyle);
      if ($style) {
        return $style->buildUrl($image->getFileUri());
      }
    }

    return $image->createFileUrl();
  }

  /**
   * Gets the featured image alt text.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The article node.
   *
   * @return string|null
   *   The alt text, or NULL if not set.
   */
  public function getFeaturedImageAlt(NodeInterface $node): ?string {
    if (!$node->hasField('field_image')) {
      return NULL;
    }

    return $node->get('field_image')->alt;
  }

  /**
   * Gets the article's formatted publication date.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The article node.
   * @param string $format
   *   The date format (default: 'F j, Y').
   *
   * @return string
   *   The formatted date.
   */
  public function getPublishedDate(NodeInterface $node, string $format = 'F j, Y'): string {
    $timestamp = $node->getCreatedTime();

    return date($format, $timestamp);
  }

  /**
   * Gets the article's last updated date.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The article node.
   * @param string $format
   *   The date format (default: 'F j, Y').
   *
   * @return string
   *   The formatted date.
   */
  public function getUpdatedDate(NodeInterface $node, string $format = 'F j, Y'): string {
    $timestamp = $node->getChangedTime();

    return date($format, $timestamp);
  }

  /**
   * Gets the author's display name.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The article node.
   *
   * @return string
   *   The author's name.
   */
  public function getAuthorName(NodeInterface $node): string {
    $author = $node->getOwner();

    return $author ? $author->getDisplayName() : 'Anonymous';
  }

  /**
   * Checks if the article was recently published.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The article node.
   * @param int $days
   *   Number of days to consider as "recent" (default: 7).
   *
   * @return bool
   *   TRUE if the article was published within the specified days.
   */
  public function isRecent(NodeInterface $node, int $days = 7): bool {
    $created = $node->getCreatedTime();
    $daysSinceCreated = (time() - $created) / 86400;

    return $daysSinceCreated <= $days;
  }

  /**
   * Gets all tags for the article.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The article node.
   *
   * @return array
   *   Array of tag names.
   */
  public function getTags(NodeInterface $node): array {
    if (!$node->hasField('field_tags')) {
      return [];
    }

    $tags = [];
    foreach ($node->get('field_tags') as $item) {
      $term = $item->entity;
      if ($term) {
        $tags[] = $term->getName();
      }
    }

    return $tags;
  }

  /**
   * Gets all category names for the article.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The article node.
   *
   * @return array
   *   Array of category names.
   */
  public function getCategories(NodeInterface $node): array {
    if (!$node->hasField('field_categories')) {
      return [];
    }

    $categories = [];
    foreach ($node->get('field_categories') as $item) {
      $term = $item->entity;
      if ($term) {
        $categories[] = $term->getName();
      }
    }

    return $categories;
  }

}

<?php

namespace Drupal\boxuk_patterns\StyleData\Pipe;

use Drupal\boxuk_patterns\Entity\Node\ArticleInterface;
use Drupal\boxuk_patterns\Pipe\BasePipe;

/**
 * Pipe to extract reading statistics for the article.
 *
 * This pipe extracts word count and reading time information from the article
 * and formats it for display in templates. The data can be used with the
 * reading-stats.html.twig component for consistent display across the site.
 *
 * Example usage in template:
 * @code
 * {% include '@boxuk_patterns/reading-stats.html.twig' with {
 *   'word_count': style_data.reading_stats.word_count,
 *   'reading_time': style_data.reading_stats.reading_time
 * } %}
 * @endcode
 */
class ReadingStatsPipe extends BasePipe {

  /**
   * {@inheritdoc}
   */
  public function handle(): array {
    $object = $this->getObject();

    // Ensure we have an article entity before accessing its properties.
    if (!$object instanceof ArticleInterface) {
      return [];
    }

    // Get word count and reading time from the node's facade methods.
    $wordCount = $object->getWordCount();
    $readingTime = $object->getReadingTime();

    // Return empty array if there's no content to read.
    if ($wordCount === 0) {
      return [];
    }

    return [
      'reading_stats' => [
        'word_count' => $wordCount,
        'word_count_formatted' => number_format($wordCount) . ' ' . $this->pluralize('word', $wordCount),
        'reading_time' => $readingTime,
        'reading_time_formatted' => $readingTime . ' min read',
        'reading_speed' => 200, // Words per minute (for reference).
      ],
    ];
  }

  /**
   * Helper method to pluralize words.
   *
   * @param string $word
   *   The word to pluralize.
   * @param int $count
   *   The count to determine singular or plural.
   *
   * @return string
   *   The word in singular or plural form.
   */
  private function pluralize(string $word, int $count): string {
    return $count === 1 ? $word : $word . 's';
  }

}

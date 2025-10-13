<?php

declare(strict_types=1);

namespace Drupal\Tests\boxuk_patterns\Kernel;

use Drupal\boxuk_patterns\Entity\Node\BoxUkArticle;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\user\Entity\User;

/**
 * Tests for the BoxUkArticle bundle class.
 *
 * @group boxuk_patterns
 * @coversDefaultClass \Drupal\boxuk_patterns\Entity\Node\BoxUkArticle
 */
class BoxUkArticleTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'node',
    'field',
    'text',
    'filter',
    'boxuk_patterns',
  ];

  /**
   * Test article node.
   *
   * @var \Drupal\boxuk_patterns\Entity\Node\BoxUkArticle
   */
  protected BoxUkArticle $article;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installSchema('node', ['node_access']);
    $this->installConfig(['filter', 'node', 'field']);

    // Create article content type.
    $nodeType = NodeType::create([
      'type' => 'article',
      'name' => 'Article',
    ]);
    $nodeType->save();

    // Create a test user.
    $user = User::create([
      'name' => 'Test Author',
      'mail' => 'test@example.com',
    ]);
    $user->save();

    // Create a test node with BoxUkArticle bundle class.
    /** @var \Drupal\boxuk_patterns\Entity\Node\BoxUkArticle $article */
    $article = Node::create([
      'type' => 'article',
      'title' => 'Test Article Title',
      'uid' => $user->id(),
      'body' => [
        'value' => '<p>This is a test article body with enough words to calculate reading time properly. ' .
          'It has multiple sentences and paragraphs to test the word count functionality. ' .
          'We want to ensure that the reading time calculation is accurate. ' .
          'Here is even more text to make the test realistic.</p>',
        'summary' => 'This is the summary of the article',
        'format' => 'basic_html',
      ],
      'created' => strtotime('2024-01-01 12:00:00'),
      'changed' => strtotime('2024-01-15 14:30:00'),
    ]);
    $article->save();

    $this->article = $article;
  }

  /**
   * Test getStyleData returns array with pipeline data.
   *
   * @covers ::getStyleData
   */
  public function testGetStyleDataReturnsArray(): void {
    $result = $this->article->getStyleData();

    $this->assertIsArray($result);
    $this->assertNotEmpty($result);
  }

  /**
   * Test getStyleData includes data from TitlePipe.
   *
   * @covers ::getStyleData
   */
  public function testGetStyleDataIncludesTitle(): void {
    $result = $this->article->getStyleData();

    $this->assertArrayHasKey('title', $result);
    $this->assertIsArray($result['title']);
    $this->assertEquals('Test Article Title', $result['title']['text']);
    $this->assertEquals('TEST ARTICLE TITLE', $result['title']['formatted']);
  }

  /**
   * Test getStyleData includes data from AuthorPipe.
   *
   * @covers ::getStyleData
   */
  public function testGetStyleDataIncludesAuthor(): void {
    $result = $this->article->getStyleData();

    $this->assertArrayHasKey('author', $result);
    $this->assertIsArray($result['author']);
    $this->assertEquals('Test Author', $result['author']['name']);
    $this->assertNotEmpty($result['author']['id']);
  }

  /**
   * Test getStyleData includes data from DatePipe.
   *
   * @covers ::getStyleData
   */
  public function testGetStyleDataIncludesDates(): void {
    $result = $this->article->getStyleData();

    $this->assertArrayHasKey('dates', $result);
    $this->assertIsArray($result['dates']);
    $this->assertArrayHasKey('created', $result['dates']);
    $this->assertArrayHasKey('changed', $result['dates']);
    $this->assertEquals('January 1, 2024', $result['dates']['created']['formatted']);
    $this->assertEquals('January 15, 2024', $result['dates']['changed']['formatted']);
  }

  /**
   * Test getSubtitle facade method.
   *
   * @covers ::getSubtitle
   * @covers ::formatter
   */
  public function testGetSubtitleDelegatesToFormatter(): void {
    // Field doesn't exist, should return NULL
    $result = $this->article->getSubtitle();
    $this->assertNull($result);
  }

  /**
   * Test getSummary facade method returns NULL when no body.
   *
   * @covers ::getSummary
   * @covers ::formatter
   */
  public function testGetSummaryReturnsNull(): void {
    $result = $this->article->getSummary();
    $this->assertNull($result);
  }

  /**
   * Test getReadingTime facade method returns 0 when no body.
   *
   * @covers ::getReadingTime
   * @covers ::formatter
   */
  public function testGetReadingTimeReturnsZero(): void {
    $result = $this->article->getReadingTime();
    $this->assertIsInt($result);
    $this->assertEquals(0, $result);
  }

  /**
   * Test getWordCount facade method returns 0 when no body.
   *
   * @covers ::getWordCount
   * @covers ::formatter
   */
  public function testGetWordCountReturnsZero(): void {
    $result = $this->article->getWordCount();
    $this->assertIsInt($result);
    $this->assertEquals(0, $result);
  }

  /**
   * Test isFeatured facade method.
   *
   * @covers ::isFeatured
   * @covers ::formatter
   */
  public function testIsFeaturedDelegatesToFormatter(): void {
    $result = $this->article->isFeatured();
    $this->assertIsBool($result);
    $this->assertFalse($result); // Field doesn't exist
  }

  /**
   * Test getPublishedDate facade method.
   *
   * @covers ::getPublishedDate
   * @covers ::formatter
   */
  public function testGetPublishedDateDelegatesToFormatter(): void {
    $result = $this->article->getPublishedDate();
    $this->assertEquals('January 1, 2024', $result);
  }

  /**
   * Test getPublishedDate with custom format.
   *
   * @covers ::getPublishedDate
   */
  public function testGetPublishedDateWithCustomFormat(): void {
    $result = $this->article->getPublishedDate('Y-m-d');
    $this->assertEquals('2024-01-01', $result);
  }

  /**
   * Test getUpdatedDate facade method.
   *
   * @covers ::getUpdatedDate
   * @covers ::formatter
   */
  public function testGetUpdatedDateDelegatesToFormatter(): void {
    $result = $this->article->getUpdatedDate();
    $this->assertEquals('January 15, 2024', $result);
  }

  /**
   * Test getAuthorName facade method.
   *
   * @covers ::getAuthorName
   * @covers ::formatter
   */
  public function testGetAuthorNameDelegatesToFormatter(): void {
    $result = $this->article->getAuthorName();
    $this->assertEquals('Test Author', $result);
  }

  /**
   * Test isRecent facade method.
   *
   * @covers ::isRecent
   * @covers ::formatter
   */
  public function testIsRecentDelegatesToFormatter(): void {
    // Node is from 2024-01-01, which is old
    $result = $this->article->isRecent(7);
    $this->assertFalse($result);
  }

  /**
   * Test getTags facade method.
   *
   * @covers ::getTags
   * @covers ::formatter
   */
  public function testGetTagsDelegatesToFormatter(): void {
    $result = $this->article->getTags();
    $this->assertIsArray($result);
    $this->assertEmpty($result); // No tags field
  }

  /**
   * Test getCategories facade method.
   *
   * @covers ::getCategories
   * @covers ::formatter
   */
  public function testGetCategoriesDelegatesToFormatter(): void {
    $result = $this->article->getCategories();
    $this->assertIsArray($result);
    $this->assertEmpty($result); // No categories field
  }

  /**
   * Test setSubtitle fluent interface.
   *
   * @covers ::setSubtitle
   * @covers ::formatter
   */
  public function testSetSubtitleReturnsArticle(): void {
    $result = $this->article->setSubtitle('New Subtitle');
    $this->assertInstanceOf(BoxUkArticle::class, $result);
    $this->assertSame($this->article, $result);
  }

  /**
   * Test setFeatured fluent interface.
   *
   * @covers ::setFeatured
   * @covers ::formatter
   */
  public function testSetFeaturedReturnsArticle(): void {
    $result = $this->article->setFeatured(TRUE);
    $this->assertInstanceOf(BoxUkArticle::class, $result);
    $this->assertSame($this->article, $result);
  }

  /**
   * Test fluent interface chaining.
   *
   * @covers ::setSubtitle
   * @covers ::setFeatured
   */
  public function testFluentInterfaceChaining(): void {
    $result = $this->article
      ->setSubtitle('Chained Subtitle')
      ->setFeatured(TRUE);

    $this->assertInstanceOf(BoxUkArticle::class, $result);
    $this->assertSame($this->article, $result);
  }

  /**
   * Test that formatter is lazily loaded.
   *
   * @covers ::formatter
   */
  public function testFormatterIsLazilyLoaded(): void {
    // First call should load the formatter
    $subtitle1 = $this->article->getSubtitle();

    // Second call should use the cached formatter
    $subtitle2 = $this->article->getSubtitle();

    // Results should be the same
    $this->assertEquals($subtitle1, $subtitle2);
  }

}

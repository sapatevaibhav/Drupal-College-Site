<?php

namespace Drupal\vaibhav_articles\Batch;

use Drupal\node\Entity\Node;

/**
 * Batch process for articles.
 */
class ArticleBatchProcess {

  /**
   * Process callback.
   */
  public static function processCsv($uri, &$context) {
    if (empty($context['sandbox'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current_line'] = 0;
      $context['results'] = [];
      $context['sandbox']['max'] = count(file($uri));
    }

    $handle = fopen($uri, 'r');
    // Skip header.
    fgetcsv($handle);

    while ($row = fgetcsv($handle)) {
      $context['sandbox']['current_line']++;

      $node = Node::create([
        'type' => 'article',
        'title' => $row[0],
        'body' => [
          'value' => $row[1],
          'format' => 'basic_html',
        ],
      ]);

      $node->save();

      $context['results'][] = $node->id();
      $context['sandbox']['progress']++;
      $context['message'] = t('Processing: @title', ['@title' => $row[0]]);
    }

    fclose($handle);
    $context['finished'] = 1;
  }

  /**
   * Finish callback.
   */
  public static function finishBatch($success, $results, $operations) {
    if ($success) {
      \Drupal::messenger()->addMessage(t('Created @count articles.', ['@count' => count($results)]));
    }
  }

}

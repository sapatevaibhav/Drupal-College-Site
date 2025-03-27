<?php

namespace Drupal\vaibhav_batch_api_teacher\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

/**
 * Controller for batch actions.
 */
class BatchActionsController extends ControllerBase {

  /**
   * Returns the batch actions.
   */
  public function batchActions(): array {
    return [
      '#type' => 'container',
      '#attributes' => ['class' => ['batch-actions-container', 'my-4', 'flex', 'gap-4']],
      'teacher_button' => [
        '#type' => 'link',
        '#title' => $this->t('Generate Teachers'),
        '#url' => Url::fromUserInput('/teacher-import'),
        '#attributes' => [
          'class' => ['button', 'bg-blue-600', 'p-2', 'rounded', 'font-bold', 'hover:bg-blue-700', 'text-white'],
        ],
      ],
      'article_button' => [
        '#type' => 'link',
        '#title' => $this->t('Generate Articles'),
        '#url' => Url::fromUserInput('/article-import'),
        '#attributes' => [
          'class' => ['button', 'p-2', 'rounded', 'bg-green-600', 'font-bold', 'hover:bg-green-700', 'text-white'],
        ],
      ],
    ];
  }

}

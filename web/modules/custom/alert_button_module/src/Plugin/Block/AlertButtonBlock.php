<?php

namespace Drupal\alert_button_module\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides an 'Alert Button' Block.
 *
 * @Block(
 *   id = "alert_button_block",
 *   admin_label = @Translation("Alert Button Block"),
 * )
 */
class AlertButtonBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#type' => 'markup',
      '#markup' => '<button id="alert-button">Click Me</button>',
      '#allowed_tags' => ['button'],
      '#attached' => [
        'library' => [
          'alert_button_module/alert_button',
        ],
      ],
    ];
  }
}

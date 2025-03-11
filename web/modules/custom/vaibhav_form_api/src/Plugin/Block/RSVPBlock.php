<?php

namespace Drupal\vaibhav_form_api\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Provides a 'RSVP' Block.
 *
 * @Block(
 *  id = "rsvp_block",
 *  admin_label = @Translation("The RSVP Block")
 * )
 */
class RSVPBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return \Drupal::formBuilder()->getForm('Drupal\vaibhav_form_api\Form\RSVPForm');
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

}

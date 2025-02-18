<?php

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_form_system_theme_settings_alter().
 */
function simplecollege_form_system_theme_settings_alter(&$form, FormStateInterface $form_state) {
  // Create a section for Simple College theme settings.
  $form['simplecollege_settings'] = [
    '#type' => 'details',
    '#title' => t('Simple College Theme Settings'),
    '#open' => TRUE,
  ];

  // Checkbox to enable custom logo.
  $form['simplecollege_settings']['custom_logo'] = [
    '#type' => 'checkbox',
    '#title' => t('Enable custom logo'),
    '#default_value' => theme_get_setting('custom_logo'),
    '#description' => t('Check this box to enable the custom logo feature.'),
  ];

  // Add a text field for a custom footer message.
  $form['simplecollege_settings']['footer_text'] = [
    '#type' => 'textfield',
    '#title' => t('Footer Text'),
    '#default_value' => theme_get_setting('footer_text'),
    '#description' => t('Enter the custom text for the footer.'),
  ];

  // Add submit handler.
  $form['#submit'][] = 'simplecollege_theme_settings_submit';
}

/**
 * Submit handler for theme settings form.
 */
function simplecollege_theme_settings_submit($form, FormStateInterface $form_state) {
  \Drupal::configFactory()->getEditable('simplecollege.settings')
    ->set('custom_logo', $form_state->getValue('custom_logo'))
    ->set('footer_text', $form_state->getValue('footer_text'))
    ->save();
}

<?php

namespace Drupal\vaibhav_form_api\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * A form to collect email addresses for RSVPs.
 */
class RSVPForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'vaibhav_form_api_email_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $node = \Drupal::routeMatch()->getParameter('node');

    // If node is loaded get its id.
    if (!(is_null($node))) {
      $nid = $node->id();
    }
    else {
      // If node is not loaded set the id to 0.
      $nid = 0;
    }

    $form['email'] = [
      '#title' => $this->t('Email Address'),
      '#type' => 'textfield',
      '#size' => 25,
      '#description' => $this->t("We'll send updates to the email address you provide."),
      '#required' => TRUE,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('RSVP'),
    ];
    $form['#nid'] = [
      '#type' => 'hidden',
      '#value' => $nid,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $value = $form_state->getValue('email');
    if (!(\Drupal::service('email.validator')->isValid($value))) {
      $form_state->setErrorByName('email',
      $this->t('The email address %mail is not valid.', ['%mail' => $value]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // $email = $form_state->getValue('email');
    // $this->messenger()->addMessage($this->t('The email address %mail has been
    // added to the list of RSVPs.', ['%mail' => $email]));
    try {
      // Begin phase 1 :  Initiate variables to save the form data.
      // Get current user id.
      $uid = \Drupal::currentUser()->id();

      // How to get the current user object.
      // $full_user = $this->userStorage->load($this->currentUser()->id());
      // Obtain the values as entered in the form.
      $email = $form_state->getValue('email');
      $nid = $form['#nid']['#value'];

      $current_time = \Drupal::time()->getRequestTime();

      // Begin phase 2: Save the values to the database.
      $query = \Drupal::database()->insert('rsvplist');
      $query->fields([
        'uid',
        'nid',
        'mail',
        'created',
      ]);

      // Set the values to be inserted for the fields we have selected.
      $query->values([
        $uid,
        $nid,
        $email,
        $current_time,
      ]);

      // Execute the query.
      $query->execute();

      // Begin phase 3: Display a message to the user.
      $this->messenger()->addMessage($this->t('The email address %mail has been added to the list of RSVPs.', ['%mail' => $email]));
    }
    catch (\Exception $e) {
      $this->messenger()->addMessage($this->t('There was a problem submitting your RSVP due to a database error.'));
    }
  }

}

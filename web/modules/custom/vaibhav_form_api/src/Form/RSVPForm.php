<?php

namespace Drupal\vaibhav_form_api\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Component\Datetime\Time;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Component\Utility\EmailValidatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A form to collect email addresses for RSVPs.
 */
class RSVPForm extends FormBase {

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The email validator.
   *
   * @var \Drupal\Component\Utility\EmailValidatorInterface
   */
  protected $emailValidator;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The time service.
   *
   * @var \Drupal\Core\Datetime\TimeInterface
   */
  protected $time;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs an RSVPForm object.
   */
  public function __construct(RouteMatchInterface $route_match, EmailValidatorInterface $email_validator, Connection $database, Time $time, AccountProxyInterface $current_user) {
    $this->routeMatch = $route_match;
    $this->emailValidator = $email_validator;
    $this->database = $database;
    $this->time = $time;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_route_match'),
      $container->get('email.validator'),
      $container->get('database'),
      $container->get('datetime.time'),
      $container->get('current_user')
    );
  }

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
    $node = $this->routeMatch->getParameter('node');
    $nid = $node ? $node->id() : 0;

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
    if (!$this->emailValidator->isValid($value)) {
      $form_state->setErrorByName('email', $this->t('The email address %mail is not valid.', ['%mail' => $value]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    try {
      $uid = $this->currentUser->id();
      $email = $form_state->getValue('email');
      $nid = $form['#nid']['#value'];
      $current_time = $this->time->getRequestTime();

      $this->database->insert('rsvplist')
        ->fields(['uid', 'nid', 'mail', 'created'])
        ->values([$uid, $nid, $email, $current_time])
        ->execute();

      $this->messenger()->addMessage($this->t('The email address %mail has been added to the list of RSVPs.', ['%mail' => $email]));
    }
    catch (\Exception $e) {
      $this->messenger()->addMessage($this->t('There was a problem submitting your RSVP due to a database error.'));
    }
  }

}

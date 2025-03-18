<?php

namespace Drupal\vaibhav_form_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for generating RSVP reports.
 */
class ReportController extends ControllerBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a ReportController object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database service.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger service.
   */
  public function __construct(Connection $database, LoggerInterface $logger) {
    $this->database = $database;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('logger.factory')->get('vaibhav_form_api')
    );
  }

  /**
   * Gets and returns all the RSVP entries with user and event details.
   *
   * Returns an associative array from the database containing
   * username, node title, and email ID for each RSVP entry.
   *
   * @return array|null
   *   Array of RSVP entries or NULL if there was an error.
   */
  public function load() {
    try {
      $select_query = $this->database->select('rsvplist', 'r');
      // Join the users table to get the username.
      $select_query->join('users_field_data', 'u', 'r.uid = u.uid');
      // Join the node table to get the event title.
      $select_query->join('node_field_data', 'n', 'r.nid = n.nid');
      // Select the fields to be returned.
      $select_query->addField('u', 'name', 'username');
      $select_query->addField('n', 'title');
      $select_query->addField('r', 'mail');

      // To fetch the results in an associative array.
      $entries = $select_query->execute()->fetchAll(\PDO::FETCH_ASSOC);

      // Return the array of results.
      return $entries;
    }
    catch (\Exception $e) {
      $this->logger->error('Error loading RSVP report: @message', ['@message' => $e->getMessage()]);
      return NULL;
    }
  }

  /**
   * Generates and displays the RSVP report.
   *
   * @return array
   *   A render array containing the report message and table.
   */
  public function report() {
    // To define the content of the page.
    $content = [];

    // To define the message of the page.
    $content['message'] = [
      '#markup' => $this->t('Below is the list of all the RSVPs including username, event title and email ID and the name of the event.'),
    ];

    // To define the header of the table.
    $header = [
      $this->t('Username'),
      $this->t('Event'),
      $this->t('Email'),
    ];

    // To load the data from the database.
    $table_rows = $this->load();

    // As we have associative array, we need to convert it to a table.
    $content['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $table_rows,
      '#empty' => $this->t('No RSVPs found'),
    ];

    // To tell drupal not to cache the page.
    $content['#cache']['max-age'] = 0;

    return $content;
  }

}

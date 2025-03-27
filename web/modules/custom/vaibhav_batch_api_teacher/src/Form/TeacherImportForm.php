<?php

namespace Drupal\vaibhav_batch_api_teacher\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Batch\BatchBuilder;
use Drupal\node\Entity\Node;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;

/**
 * Form to upload a CSV file for batch processing.
 */
class TeacherImportForm extends FormBase implements ContainerInjectionInterface {

  /**
   * The file system service.
   */
  protected FileSystemInterface $fileSystem;

  /**
   * The entity type manager.
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The logger service.
   */
  protected LoggerChannelFactoryInterface $logger;

  /**
   * Constructs a new TeacherImportForm.
   */
  public function __construct(
    FileSystemInterface $fileSystem,
    EntityTypeManagerInterface $entityTypeManager,
    MessengerInterface $messenger,
    LoggerChannelFactoryInterface $logger,
  ) {
    $this->fileSystem = $fileSystem;
    $this->entityTypeManager = $entityTypeManager;
    $this->messenger = $messenger;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('file_system'),
      $container->get('entity_type.manager'),
      $container->get('messenger'),
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'teacher_import_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['csv_file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Upload CSV File'),
      '#upload_validators' => [
        'FileExtension' => ['extensions' => 'csv'],
      ],
      '#upload_location' => 'public://teacher_imports/',
      '#required' => TRUE,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $fid = reset($form_state->getValue('csv_file'));
    $file = $this->entityTypeManager->getStorage('file')->load($fid);
    $file->setPermanent();
    $file->save();

    $batch_builder = (new BatchBuilder())
      ->setTitle($this->t('Importing teachers'))
      ->setInitMessage($this->t('Starting teacher import...'))
      ->setProgressMessage($this->t('Processing...'))
      ->setErrorMessage($this->t('An error occurred'))
      ->setFinishCallback([$this, 'finishBatch']);

    $csv_path = $file->getFileUri();
    $data = $this->parseCsv($csv_path);

    foreach ($data as $row) {
      if (count($row) < 6) {
        $this->logger->get('vaibhav_batch_api_teacher')->error('Skipping malformed row: @row', ['@row' => json_encode($row)]);
        continue;
      }
      $batch_builder->addOperation([$this, 'processRow'], [$row]);
    }

    batch_set($batch_builder->toArray());
  }

  /**
   * Parses CSV file into an array.
   */
  private function parseCsv(string $file_path): array {
    $rows = [];
    if (($handle = fopen($this->fileSystem->realpath($file_path), 'r')) !== FALSE) {
      // Read and ignore the header row.
      fgetcsv($handle, 1000, ",");
      while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $data = array_map(function ($value) {
          return $value ?? '';
        }, $data);
        $rows[] = array_map('trim', $data);
      }
      fclose($handle);
    }
    return $rows;
  }

  /**
   * Processes a single row and creates or updates a teacher node.
   */
  public function processRow(array $row, array &$context): void {
    $title = trim($row[0]);
    $biography = trim($row[1]);
    $department = trim($row[2]);
    $social_links = explode(';', trim($row[3]));
    $specialization = substr(trim($row[4]), 0, 20);
    $delete = strtolower(trim($row[5])) === 'true';

    // Check if a teacher node with the same title already exists.
    $existing_nodes = $this->entityTypeManager->getStorage('node')->loadByProperties([
      'title' => $title,
      'type' => 'teacher',
    ]);
    if ($existing_nodes) {
      $existing_node = reset($existing_nodes);
      if ($delete) {
        // Delete the existing node.
        $existing_node->delete();
      }
      else {
        // Skip creating a new node.
        $this->logger->get('vaibhav_batch_api_teacher')->notice('Skipping existing teacher: @title', ['@title' => $title]);
        return;
      }
    }

    $department_term = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties(['name' => $department]);
    $department_id = reset($department_term) ? reset($department_term)->id() : NULL;

    $this->logger->get('vaibhav_batch_api_teacher')->notice('Processing: Title=@title, Spec=@spec, Delete=@delete', [
      '@title' => $title,
      '@spec' => $specialization,
      '@delete' => $delete ? 'true' : 'false',
    ]);

    $node = Node::create([
      'type' => 'teacher',
      'title' => $title,
      'field_biography' => $biography,
      'field_department' => ['target_id' => $department_id],
      'field_social_media_links' => array_map(fn($url) => ['uri' => $url], $social_links),
      'field_subject_specialization' => $specialization,
    ]);

    $node->save();
    $context['results'][] = $title;
  }

  /**
   * Finish callback for the batch operation.
   */
  public function finishBatch(bool $success, array $results, array $operations): void {
    if ($success) {
      $count = count($results);
      if ($count > 0) {
        $this->messenger->addMessage($this->t('Created @count teacher nodes.', [
          '@count' => $count,
        ]));
      }
      else {
        $this->messenger->addMessage($this->t('No new teacher nodes were created.'));
      }
    }
    else {
      $this->messenger->addError($this->t('An error occurred while processing the file.'));
    }
  }

}

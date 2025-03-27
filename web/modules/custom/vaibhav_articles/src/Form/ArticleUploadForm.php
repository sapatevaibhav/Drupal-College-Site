<?php

namespace Drupal\vaibhav_articles\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\FileStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form to upload a CSV file of articles.
 */
class ArticleUploadForm extends FormBase {

  /**
   * The file storage service.
   *
   * @var \Drupal\file\FileStorageInterface
   */
  protected $fileStorage;

  /**
   * Constructs a new ArticleUploadForm.
   *
   * @param \Drupal\file\FileStorageInterface $file_storage
   *   The file storage service.
   */
  public function __construct(FileStorageInterface $file_storage) {
    $this->fileStorage = $file_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('file')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'vaibhav_articles_upload_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['csv_file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Upload CSV File to generate articles'),
      '#upload_validators' => [
        'FileExtension' => ['extensions' => 'csv'],
      ],
      '#upload_location' => 'public://article_csvs/',
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Process CSV'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $file = $this->fileStorage->load($form_state->getValue('csv_file')[0]);
    $file->setPermanent();
    $file->save();

    batch_set([
      'title' => $this->t('Processing Articles'),
      'operations' => [
        [['\Drupal\vaibhav_articles\Batch\ArticleBatchProcess', 'processCsv'], [$file->getFileUri()]],
      ],
      'finished' => ['\Drupal\vaibhav_articles\Batch\ArticleBatchProcess', 'finishBatch'],
    ]);
  }

}

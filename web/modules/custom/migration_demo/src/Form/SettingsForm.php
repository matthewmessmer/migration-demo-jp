<?php

namespace Drupal\migration_demo\Form;

use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;

/**
 * Configure Migration Demo settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'migration_demo_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['migration_demo.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('migration_demo.settings');

    $form['limit'] = [
      '#type' => 'number',
      '#title' => t('Limit'),
      '#default_value' => $config->get('limit') ?? $config->get('limit') || 151,
      '#description' => t('Controls limit parameter of API.'),
    ];

    $form['offset'] = [
      '#type' => 'number',
      '#title' => t('Offset'),
      '#default_value' => $config->get('offset') ?? $config->get('offset') || 0,
      '#description' => t('Controls offset parameter of API.'),
    ];

    $form['cache'] = [
      '#type' => 'textfield',
      '#title' => t('Cache Time'),
      '#default_value' => $config->get('cache') ?? $config->get('cache') || '+1 Day',
      '#description' => t('Controls time of caching api responses.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->cleanValues();
    foreach ($form_state->getValues() as $key => $value) {
      $this->config('migration_demo.settings')
        ->set($key, $value)
        ->save();
    }

    parent::submitForm($form, $form_state);
  }

}

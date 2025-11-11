<?php

namespace Drupal\migration_demo\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

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

    // @TODO SET SKIPPED TYPES
    $form['promoted_parks'] = [
      '#type' => 'entity_autocomplete',
      '#title' => t('Promoted Parks.'),
      '#description' => t('Can enter multiple parks separated by commas'),
      '#tags' => TRUE,
      '#target_type' => 'node',
      '#bundles' => ['park'],
    ];

    // Need to set this way to avoid error with entity_autocomplete field.
    // InvalidArgumentException: The #default_value property has to be an entity object or an array of entity objects.
    if (!empty($this->config('migration_demo.settings')->get('promoted_parks'))) {
      $form['promoted_parks']['#default_value'] = Node::loadMultiple($this->config('migration_demo.settings')->get('promoted_parks'));
    }

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

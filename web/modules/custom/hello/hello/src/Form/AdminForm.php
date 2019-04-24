<?php

namespace Drupal\hello\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements a hello admin form.
 */
class AdminForm extends ConfigFormBase {

  /**
   * {@inheritdoc}.
   */
  public function getFormID() {
    return 'hello_admin_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['hello.settings'];
  }

  /**
   * {@inheritdoc}.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $purge_days_number = $this->config('hello.settings')->get('purge_days_number');

    $form['purge_days_number'] = [
      '#type' => 'select',
      '#title' => $this->t('How long to keep user activity statistics'),
      '#options' => [
        '0' => $this->t('Never purge'),
        '1' => $this->t('One day'),
        '2' => $this->t('Two days'),
        '7' => $this->t('One week'),
        '14' => $this->t('Two weeks'),
        '30' => $this->t('One month'),
      ],
      '#default_value' => $purge_days_number,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('hello.settings')
      ->set('purge_days_number', $form_state->getValue('purge_days_number'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}

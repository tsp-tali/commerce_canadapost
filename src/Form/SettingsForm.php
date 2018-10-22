<?php

namespace Drupal\commerce_canadapost\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Canada Post settings form.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'commerce_canadapost_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['commerce_canadapost.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('commerce_canadapost.settings');

    $form['api'] = [
      '#type' => 'details',
      '#title' => $this->t('API authentication'),
      '#open' => TRUE,
    ];

    $form['api']['customer_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Customer number'),
      '#default_value' => $config->get('api.customer_number'),
      '#required' => TRUE,
    ];
    $form['api']['username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username'),
      '#default_value' => $config->get('api.username'),
      '#required' => TRUE,
    ];
    $form['api']['password'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Password'),
      '#default_value' => $config->get('api.password'),
      '#required' => TRUE,
    ];
    $form['api']['contract_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Contract ID'),
      '#default_value' => $config->get('api.contract_id'),
    ];
    $form['api']['mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Mode'),
      '#default_value' => $config->get('api.mode'),
      '#options' => [
        'test' => $this->t('Test'),
        'live' => $this->t('Live'),
      ],
      '#required' => TRUE,
    ];
    $form['api']['log'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Log the following messages for debugging'),
      '#options' => [
        'request' => $this->t('API request messages'),
        'response' => $this->t('API response messages'),
      ],
      '#default_value' => $config->get('api.log'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this
      ->config('commerce_canadapost.settings')
      ->set('api.customer_number', $form_state->getValue('customer_number'))
      ->set('api.username', $form_state->getValue('username'))
      ->set('api.password', $form_state->getValue('password'))
      ->set('api.contract_id', $form_state->getValue('contract_id'))
      ->set('api.mode', $form_state->getValue('mode'))
      ->set('api.log', $form_state->getValue('log'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}

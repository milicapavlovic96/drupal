<?php

namespace Drupal\products_module\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class CustomForm extends ConfigFormBase {
  public function getFormId() {
    return 'custom_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('products_module.settings');

    $form['productsPerPage'] = [
      '#type' => 'number',
      '#title' => $this->t('Products per page'),
      '#default_value' => $config->get('productsPerPage'),
    ];
    $form['fbLink'] = [
      '#type' => 'textfield',
      '#title' => $this->t('FB Link'),
      '#default_value' => $config->get('fbLink'),
    ];

    $form['#theme'] = ['products_theme'];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('products_module.settings');
    $config->set('productsPerPage', $form_state->getValue('productsPerPage'));
    $config->set('fbLink', $form_state->getValue('fbLink'));
    $config->save();
    parent::submitForm($form, $form_state);
  }

  protected function getEditableConfigNames() {
    return ['products_module.settings'];
  }
}
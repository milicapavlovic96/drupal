<?php

namespace Drupal\custom_module\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class customForm extends ConfigFormBase {

    public function getFormId() {
        return 'custom_form';
      }
    
      public function buildForm(array $form, FormStateInterface $form_state) {
        $config = $this->config('custom_module.settings');
    
        $form['default_count'] = [
          '#type' => 'number',
          '#title' => $this->t('Default count'),
          '#default_value' => $config->get('default_count'),
        ];
        return parent::buildForm($form, $form_state);
      }
    
      public function submitForm(array &$form, FormStateInterface $form_state) {
        $config = $this->config('custom_module.settings');
        $config->set('default_count', $form_state->getValue('default_count'));
        $config->save();
        parent::submitForm($form, $form_state);
      }
    
      protected function getEditableConfigNames() {
        return ['custom_module.settings'];
      }
}
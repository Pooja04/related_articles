<?php

/**
 * @file
 * Contains \Drupal\related_articles\Form\SettingsForm.
 */

namespace Drupal\related_articles\Form;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Cache\Cache;

class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'related_articles_settings_form';
  }

  protected function getEditableConfigNames() {
    return ['related_articles.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('related_articles.settings');
    $form['total_articles'] = array(
      '#title' => 'Total number of articles in a block',
      '#type' => 'textfield',
      '#default_value' => $config->get('total_articles'),
    );
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    Cache::invalidateTags(['node_list:article']);
    $values = $form_state->getValues();
    $this->config('related_articles.settings')
      ->set('total_articles', $values['total_articles'])
      ->save();
    return parent::SubmitForm($form, $form_state);
  }

}

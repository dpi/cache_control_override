<?php

namespace Drupal\cache_control_override\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form to configure module settings.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'cache_control_override_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['cache_control_override.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('cache_control_override.settings');

    $form['use_cacheability_metadata'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Use cacheability metadata to override Cache Control max-age value'),
      '#default_value' => $config->get('use_cacheability_metadata'),
      '#description' => $this->t('When enabled, cacheability metadata attached to the response object will be used to identify cache control max-age value.') . '<br/>' .
        '<strong>' . $this->t('Warning: This includes cacheability metadata bubbled to the response level from various blocks rendered on the page, so be careful as this setting can make your site uncacheable for reverse-proxies if you have at least one non-cacheable block displayed on all pages') . '</strong>',
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('cache_control_override.settings')
      ->set('use_cacheability_metadata', $form_state->getValue('use_cacheability_metadata'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}

<?php
/**
 * @file
 * Contains \Drupal\birthday_remind\Form\BsettingsForm.
 */

namespace Drupal\birthday_remind\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


/**
 * Contribute form.
 */
class BsettingsForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {  	
  	return 'birthday_remind_bsettings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
  	// get configuration values
  	$config = \Drupal::config('birthday.settings');
  	$email_send = $config->get('email_send');
  	$flag_email_send = $config->get('flag_email_send');
  	
  	$form['email_send'] = array(
  			'#type' => 'textfield',
  			'#title' => t('Want to Send Email to birthday People. 1 - to send mail, 0 - to not to send mail.'),
  			'#default_value' => $email_send,
  			'#size' => 5,
  			'#maxlength' => 1,
  			'#required' => TRUE,
  	);
  	$form['flag_email_send'] = array(
  			'#type' => 'textfield',
  			'#disabled' => true,
  			'#default_value' => 0,
  			'#size' => 5,
  			'#maxlength' => 1,
  			'#title' => t('Mail Sent / Not.'),
  	);
  	
  	$form['submit'] = array(
  			'#type' => 'submit',
  			'#value' => t('Submit'),
  	);
  	
  	return $form;
  
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  	
  	if ($form_state->getValue('email_send') != '0' && $form_state->getValue('email_send') != '1') {
  		$form_state->setErrorByName('email_send', $this->t("The field should have value 1 (yes)/ 2 (no)."));
  	}
  	
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  	
  	$config = \Drupal::service('config.factory')->getEditable('birthday.settings');
  	
  	// Set and save new config values.
  	$config->set('email_send', $form_state->getValue('email_send'))->save();
  	$config->set('flag_email_send', 0)->save();
  }
}

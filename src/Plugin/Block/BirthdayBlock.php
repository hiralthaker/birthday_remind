<?php

namespace Drupal\birthday_remind\Plugin\Block;

use Drupal\Core\Block\BlockBase;

// for using form 
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;

/**
 * Provides a 'Birthday' Block
 *
 * @Block(
 *   id = "birthday_block",
 *   admin_label = @Translation("Birthday block"),
 * )
 */
class BirthdayBlock extends BlockBase implements BlockPluginInterface{
  /**
   * {@inheritdoc}
   */
  public function build() {
  	//db connection
  	$this->connection = \Drupal::database();
  	$data = $this->getPersons();
  	
  	$i = 0;
  	$arrVal = array();
  	$strBlock = "";
  	foreach($data as $dt){
  		// load node
  		$entity = node_load($dt->nid);
  			
  		// get b'day value
  		$bday = $dt->field_mybday_value;
  		// explode value and get date and month only
  		$arrDate = explode('T',$bday);
  		$arrDateval = explode('-',$arrDate[0]);
  		$bDate = $arrDateval[1] . '-' . $arrDateval[2];
  			
  		// if b'day values = today's date
  		if($bDate == date('m-d')){
  			$name = $dt->title;
  	
  			if(!empty($dt->field_mytextb_value)){
  				$line = $dt->field_mytextb_value;
  			}else{
  				$line = $this->t("No words from person.");
  			}
  	
  			if(!empty($dt->field_bdayemail_value)){
  				$email = $dt->field_bdayemail_value;
  			}else{
  				$email = "";
  			}
  				
  			$arrVal[$i]['name'] = $name;
  			$arrVal[$i]['line'] = $line;
  			$arrVal[$i]['email'] = $email;
  			
  			$strBlock .= '<p>"'.$line.'"&nbsp;&nbsp;--' . $name . '</p>--------------------------------------------------';
  			$i++;
  		}
  	}
  	
  	// send mail to bday person
  	$config = \Drupal::config('birthday.settings');
  	$email_send = $config->get('email_send');
  	$flag_email_send = $config->get('flag_email_send');
  	\Drupal::logger('birthday_remind')->error("flag email sned " . $flag_email_send);
  	// if $email_send = 1 and there are person to send mail and mail not sent in a day then only send mail to all person
  	if($email_send == 1 && count($arrVal) >= 1 && $flag_email_send != 1){
  			
  		foreach($arrVal as $val){
  			// if email field have value then only send mail
  			if(!empty($val['email'])){
  				// send mail
  				$mailManager = \Drupal::service('plugin.manager.mail');
  	
  				$module = 'birthday_remind';
  				$key = 'birthday_remind';
  				$to = $val['email'];
  				$params['message'] = $this->t("Hello @title, <br><br> Wish you many many happy returns of the day! Have a great year ahead!", array('@title' => $val['name']));
  				$params['title'] = $val['name'];
  				$language =  \Drupal::languageManager()->getCurrentLanguage()->getId(); // for getting language code
  				$langcode = $language;
  				$send = true;
  				
  				//echo "Lag code : " . $langcode . " Message : " . $params['message'];exit;
  	
  				$result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
  				if ($result['result'] !== true) {
  					$message = $this->t('There was a problem sending your email notification to @email.', array('@email' => $to));
  					drupal_set_message($message, 'error');
  					\Drupal::logger('birthday_remind')->error($message);
  				}
  			}
  		}
  		// set falg_mail_send value from 0 to 1
  		$config = \Drupal::service('config.factory')->getEditable('birthday.settings');
  		$config->set('flag_email_send', 1)->save();
  		$flag_email_send = $config->get('flag_email_send');
  		\Drupal::logger('birthday_remind')->error("flag email send after saving to 1: " . $flag_email_send);
  	}
  	
  	if(strlen($strBlock) == 0){
  		$strBlock .= $this->t("No one's birthday today.");
  	}
  	
  	return array(
    		'#markup' => $strBlock
    );
    
  }
  
  /**
   * Function for getting node data from database
   * @return unknown
   */
  public function getPersons() {
  	
  	$langcode =  \Drupal::languageManager()->getCurrentLanguage()->getId(); // for getting language code
  	
  	// db_select()
  	$query = \Drupal::database()->select('node_field_data', 'n');
  	$query->join('node__field_mybday', 'nv', 'nv.entity_id = n.nid');
  	$query->leftJoin('node__field_mytextb', 'nt', 'nt.entity_id = n.nid');
  	$query->leftJoin('node__field_bdayemail', 'be', 'be.entity_id = n.nid');
  	$query->fields('n')
  	->fields('nv')
  	->fields('be')
  	->fields('nt')
  	->condition('n.langcode', $langcode)
  	->condition('n.status', '1');
  		
  	$result = $query->execute()->fetchAllAssoc('nid');
  		
  	return $result;
  }
  
} // class ends here
?>
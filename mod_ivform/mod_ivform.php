<?php
/**
* IVENC Form module Entry Point
*
* @package    Joomla.Ivenc
* @subpackage Modules
* @link http://ivenc.com
* @license        GNU/GPL
*/
 
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
 
// Include the syndicate functions only once
require_once( dirname(__FILE__).DS.'helper.php' );

require_once( dirname(__FILE__).DS.'lib'.DS.'IVForm.php' );

// берем параметры из файла конфигурации
$emailTo 		= $params->get('emailTo');
$emailBcc 		= $params->get('emailBcc','');
$mailSubject	= $params->get('mailSubject','Заявка с сайта');

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
 
$formText = ModIvFormHelper::getIvForm( $params );

//Готовим форму
$ivform = new IVForm();
$ivform -> arrEmailTo = array( $emailTo );
if ($emailBcc !== '') {
    $ivform -> arrEmailBcc = array( $emailBcc );
}
$ivform -> subject   =  $mailSubject;
$ivform -> fromName  = 	$params->get('fromName'); //Кириллица криво работает (в одном почтовике нормально, в другом нет)
$ivform -> fromEmail = 	$params->get('fromEmail');

require JModuleHelper::getLayoutPath('mod_ivform', $params->get('layout', 'default'));
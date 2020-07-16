<?php

require_once 'sendconfirmation.civix.php';
use CRM_Sendconfirmation_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function sendconfirmation_civicrm_config(&$config) {
  _sendconfirmation_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function sendconfirmation_civicrm_xmlMenu(&$files) {
  _sendconfirmation_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function sendconfirmation_civicrm_install() {
  _sendconfirmation_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function sendconfirmation_civicrm_postInstall() {
  _sendconfirmation_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function sendconfirmation_civicrm_uninstall() {
  _sendconfirmation_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function sendconfirmation_civicrm_enable() {
  _sendconfirmation_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function sendconfirmation_civicrm_disable() {
  _sendconfirmation_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function sendconfirmation_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _sendconfirmation_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function sendconfirmation_civicrm_managed(&$entities) {
  _sendconfirmation_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function sendconfirmation_civicrm_caseTypes(&$caseTypes) {
  _sendconfirmation_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function sendconfirmation_civicrm_angularModules(&$angularModules) {
  _sendconfirmation_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function sendconfirmation_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _sendconfirmation_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function sendconfirmation_civicrm_entityTypes(&$entityTypes) {
  _sendconfirmation_civix_civicrm_entityTypes($entityTypes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function sendconfirmation_civicrm_preProcess($formName, &$form) {

} // */

function sendconfirmation_civicrm_buildForm($formName, &$form) {
  if (in_array($formName, ['CRM_Event_Form_Participant', 'CRM_Contribute_Form_AdditionalPayment']) && !empty($form->_id))  {
    $form->addElement('checkbox',
      'send_online_receipt',
      ts('Send Confirmation using online template?'), NULL
    );
    $templatePath = realpath(dirname(__FILE__)."/templates");
    // dynamically insert a template block in the page
    CRM_Core_Region::instance('page-body')->add(array(
      'template' => "{$templatePath}/sendconfirmation.tpl"
    ));
  }
}

function sendconfirmation_civicrm_postProcess($formName, &$form) {
  if (in_array($formName, ['CRM_Event_Form_Participant', 'CRM_Contribute_Form_AdditionalPayment']) && !empty($form->_submitValues['send_online_receipt']) && !empty($form->_id)) {
    $relatedContributionID = $form->_id;
    if ($formName == 'CRM_Event_Form_Participant') {
      $relatedContributions = civicrm_api3('ParticipantPayment', 'get', [
        'sequential' => 1,
        'return' => ["contribution_id"],
        'participant_id' => $form->_id,
      ]);
      if (!empty($relatedContributions['values'])) {
        if (!empty($relatedContributions['id'])) {
          $relatedContributionID = $relatedContributions['id'];
        }
        if (!empty($form->_contactId)) {
          foreach ($relatedContributions['values'] as $val) {
            //check if this contribution is related to the same contact.
            $result = civicrm_api3('Contribution', 'get', [
              'sequential' => 1,
              'id' => $val['contribution_id'],
              'contact_id' => $form->_contactId,
            ]);
            if (!empty($result['id'])) {
              $relatedContributionID = $result['id'];
            }
          }
        }
      }
    }
    if (!empty($relatedContributionID)) {
      civicrm_api3('Contribution', 'sendconfirmation', [
        'id' => $relatedContributionID,
      ]);
    }
    else {
      throw new CRM_Core_Exception("Receipt not sent as no valid contribution id was found related to this participant.");
    }
  }
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function sendconfirmation_civicrm_navigationMenu(&$menu) {
  _sendconfirmation_civix_insert_navigation_menu($menu, 'Mailings', array(
    'label' => E::ts('New subliminal message'),
    'name' => 'mailing_subliminal_message',
    'url' => 'civicrm/mailing/subliminal',
    'permission' => 'access CiviMail',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _sendconfirmation_civix_navigationMenu($menu);
} // */

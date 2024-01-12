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
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function sendconfirmation_civicrm_install() {
  _sendconfirmation_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function sendconfirmation_civicrm_enable() {
  _sendconfirmation_civix_civicrm_enable();
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *

 // */

function sendconfirmation_civicrm_buildForm($formName, &$form) {
  if (in_array($formName, ['CRM_Event_Form_Participant', 'CRM_Contribute_Form_AdditionalPayment', 'CRM_Contribute_Form_Contribution'])
    && !empty($form->_id)
    && in_array($form->_action, [CRM_Core_Action::ADD, CRM_Core_Action::UPDATE])) {
    if ($form->elementExists('is_email_receipt')) {
      $element = $form->getElement('is_email_receipt');
      if ($formName == 'CRM_Contribute_Form_AdditionalPayment') {
        $element->_label = 'Send Receipt for this payment';
      }
      else {
        $element->_label = 'Send Receipt (offline template)?';
      }
    }
    $form->addElement('checkbox',
      'send_online_receipt',
      ts('Send full contribution receipt (online template)'), NULL
    );
    $templatePath = realpath(dirname(__FILE__)."/templates");
    // dynamically insert a template block in the page
    CRM_Core_Region::instance('page-body')->add(array(
      'template' => "{$templatePath}/sendconfirmation.tpl"
    ));
  }
}

function sendconfirmation_civicrm_postProcess($formName, &$form) {
  if (in_array($formName, ['CRM_Event_Form_Participant', 'CRM_Contribute_Form_AdditionalPayment', 'CRM_Contribute_Form_Contribution']) && !empty($form->_submitValues['send_online_receipt']) && !empty($form->_id)) {
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

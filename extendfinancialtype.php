<?php

require_once 'extendfinancialtype.civix.php';

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function extendfinancialtype_civicrm_config(&$config) {
  _extendfinancialtype_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function extendfinancialtype_civicrm_xmlMenu(&$files) {
  _extendfinancialtype_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function extendfinancialtype_civicrm_install() {
  _extendfinancialtype_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function extendfinancialtype_civicrm_uninstall() {
  _extendfinancialtype_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function extendfinancialtype_civicrm_enable() {
  _extendfinancialtype_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function extendfinancialtype_civicrm_disable() {
  _extendfinancialtype_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function extendfinancialtype_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _extendfinancialtype_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function extendfinancialtype_civicrm_managed(&$entities) {
  _extendfinancialtype_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function extendfinancialtype_civicrm_caseTypes(&$caseTypes) {
  _extendfinancialtype_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function extendfinancialtype_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _extendfinancialtype_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implementation of hook_civicrm_buildForm
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_buildForm
 */
function extendfinancialtype_civicrm_buildForm($formName, &$form) {
  if (!in_array($form->_action, [CRM_Core_Action::ADD, CRM_Core_Action::UPDATE])) {
    return;
  }
  if ($formName == "CRM_Price_Form_Field") {
    $codes = CRM_Core_OptionGroup::values('chapter_codes');
    for ($i = 1; $i <= 15; $i++) {
      $form->add('select', 'option_chapter_code[' . $i . ']',
        ts('Chapter Code'),
        $codes
      );
    }
  }
  if (array_key_exists('financial_type_id', $form->_elementIndex)
      || ($formName == "CRM_Event_Form_Participant" && ($form->_action & CRM_Core_Action::ADD))) {
    if (($form->_action & CRM_Core_Action::UPDATE) && $formName == "CRM_Member_Form_Membership") {
      return;
    }
    $codes = CRM_Core_OptionGroup::values('chapter_codes');
    $form->add('select', 'chapter_code',
      ts('Chapter Code'),
      $codes
    );
    CRM_Core_Region::instance('page-body')->add(array(
      'template' => 'CRM/EFT/AddChapterCode.tpl',
    ));
  }

  if ($form->_action & CRM_Core_Action::UPDATE) {
    // Setting defaults.
    $defaults = [];
    switch ($formName) {
    case "CRM_Contribute_Form_ContributionPage_Settings":
      $defaults = CRM_EFT_BAO_EFT::getChapterFund($form->getVar('_id'), "civicrm_contribution_page");
      break;

    case "CRM_Contribute_Form_Contribution":
      $defaults = CRM_EFT_BAO_EFT::getChapterFund($form->_id, "civicrm_contribution");
      break;

    case "CRM_Event_Form_ManageEvent_Fee":
      $defaults = CRM_EFT_BAO_EFT::getChapterFund($form->_id, "civicrm_event");
      break;

    case "CRM_Price_Form_Set":
      $defaults = CRM_EFT_BAO_EFT::getChapterFund($form->getVar('_sid'), "civicrm_price_set");
      break;

    case "CRM_Price_Form_Field":
      $defaults = CRM_EFT_BAO_EFT::getChapterFund($form->getVar('_fid'), "civicrm_price_field");
      break;

    case "CRM_Price_Form_Option":
      $defaults = CRM_EFT_BAO_EFT::getChapterFund($form->getVar('_oid'), "civicrm_price_field_value");
      break;

    case "CRM_Member_Form_MembershipType":
      $defaults = CRM_EFT_BAO_EFT::getChapterFund($form->_id, "civicrm_membership_type");
      break;

    default:
      break;
    }
    $form->setDefaults($defaults);
  }
}

function extendfinancialtype_civicrm_postSave_civicrm_price_set($dao) {
  if ($dao->id) {
    CRM_Core_Smarty::singleton()->assign('eft_price_set_id', $dao->id);
  }
}

function extendfinancialtype_civicrm_postSave_civicrm_price_field($dao) {
  if ($dao->id) {
    CRM_Core_Smarty::singleton()->assign('eft_price_field_id', $dao->id);
  }
}

function extendfinancialtype_civicrm_postSave_civicrm_price_field_value($dao) {
  if ($dao->id) {
    CRM_Core_Smarty::singleton()->assign('eft_price_field_value_id', $dao->id);
  }
}

function extendfinancialtype_civicrm_postSave_civicrm_membership_type($dao) {
  if ($dao->id) {
    CRM_Core_Smarty::singleton()->assign('eft_membership_type_id', $dao->id);
  }
}

/**
 * Implementation of hook_civicrm_postProcess
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postProcess
 */
function extendfinancialtype_civicrm_postProcess($formName, &$form) {
  if ($chapter = CRM_Utils_Array::value('chapter_code', $form->_submitValues)) {
    switch ($formName) {
    case "CRM_Price_Form_Set":
      $sid = CRM_Core_Smarty::singleton()->get_template_vars('eft_price_set_id');
      CRM_EFT_BAO_EFT::addChapterFund($chapter, $sid, "civicrm_price_set");
      break;

    case "CRM_Price_Form_Field":
      if ($form->_submitValues['html_type'] == "Text") {
        $fid = CRM_Core_Smarty::singleton()->get_template_vars('eft_price_field_id');
        CRM_EFT_BAO_EFT::addChapterFund($chapter, $fid, "civicrm_price_field");
      }
      else {
        // We need to save the chapter and fund for each price field value rather than price field.
        $fid = CRM_Core_Smarty::singleton()->get_template_vars('eft_price_field_id');
        CRM_EFT_BAO_EFT::addChapterFund($form->_submitValues, $fid, "civicrm_price_field_value" , TRUE);
      }
      break;

    case "CRM_Price_Form_Option":
      $oid = CRM_Core_Smarty::singleton()->get_template_vars('eft_price_field_value_id');
      CRM_EFT_BAO_EFT::addChapterFund($chapter, $oid, "civicrm_price_field_value", FALSE);
      break;

    case "CRM_Contribute_Form_ContributionPage_Settings":
      CRM_EFT_BAO_EFT::addChapterFund($chapter, $form->getVar('_id'), "civicrm_contribution_page");
      break;

    case "CRM_Event_Form_ManageEvent_Fee":
      CRM_EFT_BAO_EFT::addChapterFund($chapter, $form->_id, "civicrm_event");
      break;

    case "CRM_Member_Form_MembershipType":
      $mid = CRM_Core_Smarty::singleton()->get_template_vars('eft_membership_type_id');
      CRM_EFT_BAO_EFT::addChapterFund($chapter, $mid, "civicrm_membership_type");
      break;

    case "CRM_Contribute_Form_Contribution":
      $isPriceSet = FALSE;
      if (CRM_Utils_Array::value('price_set_id', $form->_submitValues)) {
        $isPriceSet = TRUE;
      }
      CRM_EFT_BAO_EFT::addChapterFund($chapter, $form->_id, "civicrm_line_item", $isPriceSet);
      break;

    case "CRM_Event_Form_Participant":
      // Add chapter code for main contribution.
      $contributionId = CRM_Core_DAO::singleValueQuery("SELECT contribution_id FROM civicrm_participant_payment WHERE participant_id = {$form->_id}");
      if ($contributionId) {
        CRM_EFT_BAO_EFT::addChapterFund($chapter, $contributionId, "civicrm_line_item");
      }
      break;

    case "CRM_Member_Form_Membership":
      // Add chapter code for main contribution.
      $contributionId = CRM_Core_DAO::singleValueQuery("SELECT contribution_id FROM civicrm_membership_payment WHERE membership_id = {$form->_id}");
      if ($contributionId) {
        CRM_EFT_BAO_EFT::addChapterFund($chapter, $contributionId, "civicrm_line_item");
      }
      break;

    default:
      break;
    }
  }

  // Front End Forms.
  if ($formName == "CRM_Contribute_Form_Contribution_Confirm") {
    CRM_EFT_BAO_EFT::addChapterFund($form->_params['contributionPageID'], $form->_contributionID, "civicrm_contribution_page_online");
  }
}

<?php
define('CHAPTER_CODE', 'custom_758');
define('FUND_CODE', 'custom_759');

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
  if ($formName == 'CRM_Contribute_Form_ContributionView') {
    $contributionId = $form->get('id');
    // SELECT chapter code and FA code as fund ID.
    $fa = CRM_Contribute_PseudoConstant::getRelationalFinancialAccount(CRM_Core_Smarty::singleton()->get_template_vars('financial_type_id'), 'Income Account is');
    if ($fa) {
      $acCode = CRM_Core_DAO::singleValueQuery("SELECT accounting_code FROM civicrm_financial_account WHERE id = {$fa}");
      $codes = CRM_Core_DAO::executeQuery("SELECT chapter_code, fund_code FROM civicrm_chapter_entity WHERE entity_table = 'civicrm_contribution' AND entity_id = {$contributionId}")->fetchAll()[0];
    }
    if ($acCode && !empty($codes)) {
      $chapter = $codes['chapter_code'];
      $fundCodes = CRM_Core_OptionGroup::values('fund_codes');
      $fund = $fundCodes[$codes['fund_code']];
      $string = '&nbsp;&nbsp;&nbsp;&nbsp;';
      $string .= "&nbsp;&nbsp;&nbsp;<span class=\"label\"><strong>Fund ID</strong></span>:&nbsp;$acCode-$chapter";
      if ($fund) {
        $string .= "&nbsp;&nbsp;&nbsp;<span class=\"label\"><strong>Fund</strong></span>:&nbsp;$fund";
      }
      CRM_Core_Resources::singleton()->addScript(
       "CRM.$(function($) {
           $.each($('.crm-contribution-view-form-block table > tbody > tr:nth-child(2)'), function() {
           if ($('td', this).length == 2) {
             $('td:nth-child(2)', this).append('$string');
           }
         });
       });"
      );
    }
  }
  if ($formName == "CRM_Event_Form_ManageEvent_Registration") {
    $cid = CRM_Core_Session::singleton()->get('userID');
    if ($cid) {
      $details = CRM_Core_DAO::executeQuery("SELECT display_name, email FROM civicrm_contact c INNER JOIN civicrm_email e ON e.contact_id = c.id WHERE c.id = {$cid} AND e.is_primary = 1")->fetchAll()[0];
      if (!empty($details)) {
        $form->setDefaults(['confirm_from_name' => $details['display_name'], 'confirm_from_email' => $details['email']]);
      }
    }
  }
  if (!in_array($form->_action, [CRM_Core_Action::ADD, CRM_Core_Action::UPDATE])) {
    return;
  }
  if ($formName == "CRM_Price_Form_Field") {

    // Add chapter codes.
    $chapterCodes = CRM_EFT_BAO_EFT::getCodes('chapter_codes');
    for ($i = 1; $i <= 15; $i++) {
      $form->add('select', 'option_chapter_code[' . $i . ']',
        ts('Chapter Code'),
        $chapterCodes
      );
    }

    // Add fund codes.
    $fundCodes = CRM_EFT_BAO_EFT::getCodes('fund_codes');
    for ($i = 1; $i <= 15; $i++) {
      $form->add('select', 'option_fund_code[' . $i . ']',
        ts('Fund Code'),
        $fundCodes
      );
    }
    
  }
  if (array_key_exists('financial_type_id', $form->_elementIndex)
      || ($formName == "CRM_Event_Form_Participant" && ($form->_action & CRM_Core_Action::ADD))) {
    if (($form->_action & CRM_Core_Action::UPDATE) && ($formName == "CRM_Member_Form_Membership" || $formName == "CRM_Contribute_Form_Contribution")) {
      return;
    }
    // Add chapter codes.
    $chapterCodes = CRM_EFT_BAO_EFT::getCodes('chapter_codes');
    $form->add('select', 'chapter_code',
      ts('Chapter Code'),
      $chapterCodes
    );
    // Add fund codes.
    $fundCodes = CRM_Core_OptionGroup::values('fund_codes');
    $form->add('select', 'fund_code',
      ts('Fund Code'),
      $fundCodes
    );
    CRM_Core_Region::instance('page-body')->add(array(
      'template' => 'CRM/EFT/AddChapterFundCode.tpl',
    ));
  }

  if (array_key_exists('payment_instrument_id', $form->_elementIndex) || ($formName == "CRM_Event_Form_Participant" && ($form->_action & CRM_Core_Action::ADD))
    && (in_array($form->_action, [CRM_Core_Action::ADD, CRM_Core_Action::UPDATE]))) {
    
    if (in_array($formName, ["CRM_Financial_Form_FinancialBatch", "CRM_Financial_Form_Search"])) {
      return;
    }
    // Add chapter codes.
    $chapterCodes = CRM_EFT_BAO_EFT::getCodes('chapter_codes');
    $form->add('select', 'chapter_code_trxn',
      ts('Chapter Code'),
      $chapterCodes
    );
    // Add fund codes.
    $fundCodes = CRM_EFT_BAO_EFT::getCodes('fund_codes');
    $form->add('select', 'fund_code_trxn',
      ts('Fund Code'),
      $fundCodes
    );
    CRM_Core_Region::instance('page-body')->add(array(
      'template' => 'CRM/EFT/AddChapterFundCodeTrxn.tpl',
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

function extendfinancialtype_civicrm_pre($op, $objectName, $objectId, &$objectRef) {
  if ($op == "delete") {
    CRM_EFT_BAO_EFT::deleteChapterFundEntity($objectId, $objectName);
  }
}

function extendfinancialtype_civicrm_postSave_civicrm_contribution_soft($dao) {
  // Save appropriate gift types for In Honour Of and In Memory Of.
  if ($dao->id) {
    $softCreditTypes = CRM_Core_OptionGroup::values('soft_credit_type');
    if (in_array($softCreditTypes[$dao->soft_credit_type_id], ["In Honor of", "In Memory of"])) {
      // If financial type ID of contribution is general donation, change financial account id.
      $ft = CRM_Core_DAO::singleValueQuery("SELECT f.name
        FROM civicrm_contribution c
        INNER JOIN civicrm_financial_type f ON f.id = c.financial_type_id
        WHERE c.id = {$dao->contribution_id}");
      if ($ft == "General Donation") {
        // We change the financial account in this case to Memorial Donation.
        $fa = CRM_Core_DAO::singleValueQuery("SELECT id FROM civicrm_financial_account WHERE accounting_code = 4012");
        $fi = CRM_Contribute_BAO_Contribution::getLastFinancialItemIds($dao->contribution_id)[0][1];
        if ($fi) {
          civicrm_api3('FinancialItem', 'create', ['id' => $fi, 'financial_account_id' => $fa]);
        }
      }
    }
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
  if (CRM_Utils_Array::value('chapter_code', $form->_submitValues) || CRM_Utils_Array::value('fund_code', $form->_submitValues)
    || CRM_Utils_Array::value('fund_code_trxn', $form->_submitValues) || CRM_Utils_Array::value('chapter_code_trxn', $form->_submitValues)) {
    switch ($formName) {
    case "CRM_Price_Form_Set":
      $sid = CRM_Core_Smarty::singleton()->get_template_vars('eft_price_set_id');
      CRM_EFT_BAO_EFT::addChapterFund($form->_submitValues['chapter_code'], $form->_submitValues['fund_code'], $sid, "civicrm_price_set");
      break;

    case "CRM_Price_Form_Field":
      if ($form->_submitValues['html_type'] == "Text") {
        $fid = CRM_Core_Smarty::singleton()->get_template_vars('eft_price_field_id');
        CRM_EFT_BAO_EFT::addChapterFund($form->_submitValues['chapter_code'], $form->_submitValues['fund_code'], $fid, "civicrm_price_field");
      }
      else {
        // We need to save the chapter and fund for each price field value rather than price field.
        $fid = CRM_Core_Smarty::singleton()->get_template_vars('eft_price_field_id');
        CRM_EFT_BAO_EFT::addChapterFund($form->_submitValues, NULL, $fid, "civicrm_price_field_value" , TRUE);
      }
      break;

    case "CRM_Price_Form_Option":
      $oid = CRM_Core_Smarty::singleton()->get_template_vars('eft_price_field_value_id');
      CRM_EFT_BAO_EFT::addChapterFund($form->_submitValues['chapter_code'], $form->_submitValues['fund_code'], $oid, "civicrm_price_field_value", FALSE);
      break;

    case "CRM_Contribute_Form_ContributionPage_Settings":
      CRM_EFT_BAO_EFT::addChapterFund($form->_submitValues['chapter_code'], $form->_submitValues['fund_code'], $form->getVar('_id'), "civicrm_contribution_page");
      break;

    case "CRM_Event_Form_ManageEvent_Fee":
      CRM_EFT_BAO_EFT::addChapterFund($form->_submitValues['chapter_code'], $form->_submitValues['fund_code'], $form->_id, "civicrm_event");
      break;

    case "CRM_Member_Form_MembershipType":
      $mid = CRM_Core_Smarty::singleton()->get_template_vars('eft_membership_type_id');
      CRM_EFT_BAO_EFT::addChapterFund($form->_submitValues['chapter_code'], $form->_submitValues['fund_code'], $mid, "civicrm_membership_type");
      break;

    case "CRM_Contribute_Form_Contribution":
      $isPriceSet = FALSE;
      if (CRM_Utils_Array::value('chapter_code_trxn', $form->_submitValues) || CRM_Utils_Array::value('fund_code_trxn', $form->_submitValues)) {
        $isPriceSet = TRUE;
      }
      $fts = CRM_EFT_BAO_EFT::addChapterFund($form->_submitValues['chapter_code'], $form->_submitValues['fund_code'], $form->_id, "civicrm_line_item", $isPriceSet);
      CRM_EFT_BAO_EFT::addTrxnChapterFund($fts, $form->_submitValues);
      break;

    case "CRM_Event_Form_Participant":
      // Add chapter code for main contribution.
      $contributionId = CRM_Core_DAO::singleValueQuery("SELECT contribution_id FROM civicrm_participant_payment WHERE participant_id = {$form->_id}");
      $isPriceSet = FALSE;
      if (CRM_Utils_Array::value('chapter_code_trxn', $form->_submitValues) || CRM_Utils_Array::value('fund_code_trxn', $form->_submitValues)) {
        $isPriceSet = TRUE;
      }
      if ($contributionId) {
        $fts = CRM_EFT_BAO_EFT::addChapterFund($form->_submitValues['chapter_code'], $form->_submitValues['fund_code'], $contributionId, "civicrm_line_item", $isPriceSet);
        CRM_EFT_BAO_EFT::addTrxnChapterFund($fts, $form->_submitValues);
      }
      break;

    case "CRM_Member_Form_Membership":
      // Add chapter code for main contribution.
      $contributionId = CRM_Core_DAO::singleValueQuery("SELECT contribution_id FROM civicrm_membership_payment WHERE membership_id = {$form->_id}");
      $isPriceSet = FALSE;
      if (CRM_Utils_Array::value('chapter_code_trxn', $form->_submitValues) || CRM_Utils_Array::value('fund_code_trxn', $form->_submitValues)) {
        $isPriceSet = TRUE;
      }
      if ($contributionId) {
        $fts = CRM_EFT_BAO_EFT::addChapterFund($form->_submitValues['chapter_code'], $form->_submitValues['fund_code'], $contributionId, "civicrm_line_item", $isPriceSet);
        CRM_EFT_BAO_EFT::addTrxnChapterFund($fts, $form->_submitValues);
      }
      break;

    default:
      break;
    }
  }

  // Handle updates to Contributions.
  if ($form->_action & CRM_Core_Action::UPDATE) {
    switch ($formName) {
    case "CRM_Contribute_Form_Contribution":
      // Get last inserted financial trxn if updated.
      $ft = CRM_Core_DAO::executeQuery("SELECT ft.id
        FROM civicrm_contribution c
        INNER JOIN civicrm_entity_financial_trxn eft ON eft.entity_id = c.id AND eft.entity_table = 'civicrm_contribution'
        INNER JOIN civicrm_financial_trxn ft ON ft.id = eft.financial_trxn_id
        LEFT JOIN civicrm_chapter_entity ce ON ce.entity_id = ft.id AND ce.entity_table = 'civicrm_financial_trxn'
        WHERE c.id = {$form->_id} AND ce.entity_id IS NULL")->fetchAll()[0];
      if (!empty($ft)) {
        $lastFt = CRM_Core_DAO::executeQuery("SELECT ce.chapter_code, ce.fund_code 
          FROM civicrm_contribution c
          INNER JOIN civicrm_entity_financial_trxn eft ON eft.entity_id = c.id AND eft.entity_table = 'civicrm_contribution'
          INNER JOIN civicrm_financial_trxn ft ON ft.id = eft.financial_trxn_id
          INNER JOIN civicrm_chapter_entity ce ON ce.entity_id = ft.id AND ce.entity_table = 'civicrm_financial_trxn'
          WHERE c.id = {$form->_id} ORDER BY ft.id DESC LIMIT 1")->fetchAll()[0];
        if (!empty($lastFt)) {
          $params = [
            "entity_id" => $ft['id'],
            "entity_table" => "civicrm_financial_trxn",
            "chapter" => $lastFt['chapter_code'],
            "fund" => $lastFt['fund_code'],
          ];
          CRM_EFT_BAO_EFT::saveChapterFund($params);
        }
      }
      $fi = CRM_Contribute_BAO_Contribution::getLastFinancialItemIds($form->_id)[0][1];
      if ($fi) {
        $entry = CRM_Core_DAO::singleValueQuery("SELECT id FROM civicrm_chapter_entity WHERE entity_id = {$fi} AND entity_table = 'civicrm_financial_item'");
        if (!$entry) {
          $lastFi = CRM_Core_DAO::executeQuery("SELECT ce.chapter_code, ce.fund_code
            FROM civicrm_financial_item fi
            INNER JOIN civicrm_line_item li ON li.id = fi.entity_id and fi.entity_table = 'civicrm_line_item'
            INNER JOIN civicrm_chapter_entity ce ON ce.entity_id = fi.id AND ce.entity_table = 'civicrm_financial_item'
            WHERE li.contribution_id = {$form->_id} ORDER BY fi.id DESC LIMIT 1")->fetchAll()[0];
          $params = [
            "entity_id" => $fi,
            "entity_table" => "civicrm_financial_item",
            "chapter" => $lastFi['chapter_code'],
            "fund" => $lastFi['fund_code'],
          ];
          CRM_EFT_BAO_EFT::saveChapterFund($params);
        }
      }
      break;
    default:
      break;
    }
  }

  // Front End Forms.
  if ($formName == "CRM_Contribute_Form_Contribution_Confirm") {
    $fts = CRM_EFT_BAO_EFT::addChapterFund($form->_params['contributionPageID'], NULL, $form->_contributionID, "civicrm_contribution_page_online");
    $params = [
      'chapter_code_trxn' => CRM_Utils_Array::value(CHAPTER_CODE, $form->_submitValues),
      'fund_code_trxn' => CRM_Utils_Array::value(FUND_CODE, $form->_submitValues),
    ];
    CRM_EFT_BAO_EFT::addTrxnChapterFund($fts, $params);
  }
}

function extendfinancialtype_civicrm_alterReportVar($varType, &$var, &$object) {
  if ('CRM_Report_Form_Contribute_Bookkeeping' == get_class($object)) {
    if ($varType == 'columns') {
      $var['civicrm_chapter_entity']['fields']['chapter_code_from'] = array(
        'name' => 'chapter_code_from',
        'title' => ts('Chapter Code - Credit'),
        'dbAlias' => 'CONCAT(ce_from.chapter_code, " ", covc_from.label)',
      );
      $var['civicrm_chapter_entity']['fields']['chapter_code_to'] = array(
        'name' => 'chapter_code_to',
        'title' => ts('Chapter Code - Debit'),
        'dbAlias' => 'CONCAT(ce_to.chapter_code, " ", covc_to.label)',
      );
      $var['civicrm_chapter_entity']['fields']['fund_code_from'] = array(
        'name' => 'fund_code_from',
        'title' => ts('Fund Code - Credit'),
        'dbAlias' => 'covf_from.label',
      );
      $var['civicrm_chapter_entity']['fields']['fund_code_to'] = array(
        'name' => 'fund_code_to',
        'title' => ts('Fund Code - Debit'),
        'dbAlias' => 'covf_to.label',
      );
    }
    if ($varType == 'sql') {
      $from = $var->getVar('_from');
      $from .= "
      LEFT JOIN civicrm_line_item li ON li.contribution_id = contribution_civireport.id
      LEFT JOIN civicrm_chapter_entity ce_from ON ce_from.entity_id = li.id AND ce_from.entity_table = 'civicrm_line_item'
      LEFT JOIN civicrm_chapter_entity ce_to ON ce_to.entity_id = financial_trxn_civireport.id AND ce_to.entity_table = 'civicrm_financial_trxn'
      LEFT JOIN civicrm_option_group cogf ON cogf.name = 'fund_codes'
      LEFT JOIN civicrm_option_group cogc ON cogc.name = 'chapter_codes'
      LEFT JOIN civicrm_option_value covf_from ON (covf_from.value = ce_from.fund_code AND covf_from.option_group_id = cogf.id)
      LEFT JOIN civicrm_option_value covf_to ON (covf_to.value = ce_to.fund_code AND covf_to.option_group_id = cogf.id)
      LEFT JOIN civicrm_option_value covc_from ON (covc_from.value = ce_from.chapter_code AND covc_from.option_group_id = cogc.id)
      LEFT JOIN civicrm_option_value covc_to ON (covc_to.value = ce_to.chapter_code AND covc_to.option_group_id = cogc.id)";
      $var->setVar('_from', $from);
    }
  }
}
<?php
define('CHAPTERFUND', 'Chapter_Funds__');
define('MEMCHAPTERFUND', 'Chapter_Funds_Memberships__');
define('DONATION_PAGE', 1);
define('RAISE_THE_FLAG', 23);
define('MEMBERSHIPFIELD', 23);

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
  // Raise the flag events.
  if ($formName == "CRM_Event_Form_Registration_Register" && $form->_values['event']['event_type_id'] == RAISE_THE_FLAG) {
    $chapters = CRM_Core_OptionGroup::values('chapter_codes');
    asort($chapters);

    $form->add('select', 'chapter_code',
      ts('Chapter/Fund'), $chapters, TRUE, array('class' => 'crm-select2 ')
    );
    CRM_Core_Region::instance('page-body')->add(array(
      'template' => 'CRM/RaiseTheFlag.tpl',
    ));
    $form->setDefaults(['chapter_code' => 1000]);
  }
  if ($formName == 'CRM_Contribute_Form_ContributionView') {
    CRM_Core_Resources::singleton()->addScript(
       "CRM.$(function($) {
           $('td#". CHAPTERFUND ."').hide();
       });"
    );
    // Assign chapter and fund.
    $payments = $form->get_template_vars('payments');
    if (!empty($payments)) {
      _extendfinancialtype_alterpayments($payments);
      $form->assign('payments', $payments);
    }
    $lineitem = $form->get_template_vars('lineItem');
    if (!empty($lineitem)) {
      _extendfinancialtype_alterlineitems($lineitem);
      $form->assign('lineItem', $lineitem);
      if (!empty($lineitem)) {
        $form->assign('isChapterFund', TRUE);
      }
    }

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
  if ($formName == 'CRM_Contribute_Form_Contribution') {
    CRM_Core_Resources::singleton()->addScript(
      "CRM.$(function($) {
        $('div.custom-group-Chapter_Funds').hide();
        $( document ).ajaxComplete(function( event, xhr, settings ) {
          $('div.custom-group-Chapter_Funds').hide();
        });
      });"
    );
  }
  if ($formName == 'CRM_Member_Form_Membership') {
    CRM_Core_Resources::singleton()->addScript(
      "CRM.$(function($) {
        $('div.custom-group-Chapter_Funds_Memberships').hide();
        $( document ).ajaxComplete(function( event, xhr, settings ) {
          $('div.custom-group-Chapter_Funds_Memberships').hide();
        });
      });"
    );
  }
  if ($formName == 'CRM_Member_Form_MembershipView') {
    $memberId = $form->get('id');
    CRM_Core_Resources::singleton()->addScript(
      "CRM.$(function($) {
        $('td#". MEMCHAPTERFUND ."').hide();
      });"
    );
    $codes = CRM_Core_DAO::executeQuery("SELECT chapter_code, fund_code FROM civicrm_chapter_entity WHERE entity_table = 'civicrm_membership' AND entity_id = {$memberId}")->fetchAll();
    if (!empty($codes)) {
      $chapterCodes = CRM_EFT_BAO_EFT::getCodes('chapter_codes');
      $chapter = $chapterCodes[$codes[0]['chapter_code']];
      $fundCodes = CRM_Core_OptionGroup::values('fund_codes');
      $fund = $fundCodes[$codes[0]['fund_code']];
      $string = '&nbsp;&nbsp;&nbsp;&nbsp;';
      $string .= "&nbsp;&nbsp;&nbsp;<span class=\"label\"><strong>Chapter</strong></span>:&nbsp;$chapter";
      if ($fund) {
        $string .= "&nbsp;&nbsp;&nbsp;<span class=\"label\"><strong>Fund</strong></span>:&nbsp;$fund";
      }
      CRM_Core_Resources::singleton()->addScript(
       "CRM.$(function($) {
           $.each($('.crm-membership-view-form-block table > tbody > tr:nth-child(2)'), function() {
           if ($('td', this).length == 2) {
             $('td:nth-child(2)', this).append('$string');
           }
         });
       });"
      );
    }
  }
  if ($formName == 'CRM_Event_Form_ParticipantView') {
    $participantId = $form->get('id');
    $codes = CRM_Core_DAO::executeQuery("SELECT chapter_code, fund_code FROM civicrm_chapter_entity WHERE entity_table = 'civicrm_participant' AND entity_id = {$participantId}")->fetchAll();
    if (!empty($codes)) {
      $chapterCodes = CRM_EFT_BAO_EFT::getCodes('chapter_codes');
      $chapter = $chapterCodes[$codes[0]['chapter_code']];
      $fundCodes = CRM_Core_OptionGroup::values('fund_codes');
      $fund = $fundCodes[$codes[0]['fund_code']];
      $string = '&nbsp;&nbsp;&nbsp;&nbsp;';
      $string .= "&nbsp;&nbsp;&nbsp;<span class=\"label\"><strong>Chapter</strong></span>:&nbsp;$chapter";
      if ($fund) {
        $string .= "&nbsp;&nbsp;&nbsp;<span class=\"label\"><strong>Fund</strong></span>:&nbsp;$fund";
      }
      CRM_Core_Resources::singleton()->addScript(
       "CRM.$(function($) {
           $.each($('.crm-event-participant-view-form-block table > tbody > tr:nth-child(2)'), function() {
           if ($('td', this).length == 2) {
             $('td:nth-child(2)', this).append('$string');
           }
         });
       });"
      );
    }
  }
  if ($formName == "CRM_Event_Form_ManageEvent_Registration" && ($form->_action & CRM_Core_Action::ADD)) {
    $cid = CRM_Core_Session::singleton()->get('userID');
    if ($cid) {
      $details = CRM_Core_DAO::executeQuery("SELECT display_name, email FROM civicrm_contact c INNER JOIN civicrm_email e ON e.contact_id = c.id WHERE c.id = {$cid} AND e.is_primary = 1")->fetchAll()[0];
      if (!empty($details)) {
        $form->setDefaults(['confirm_from_name' => $details['display_name'], 'confirm_from_email' => $details['email']]);
      }
    }
  }
  if (!in_array($form->_action, [CRM_Core_Action::ADD, CRM_Core_Action::UPDATE, CRM_Core_Action::RENEW])) {
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
    $fundCodes = CRM_Core_OptionGroup::values('fund_codes');
    for ($i = 1; $i <= 15; $i++) {
      $form->add('select', 'option_fund_code[' . $i . ']',
        ts('Fund Code'),
        $fundCodes
      );
    }
  }
  if ($formName == "CRM_Contribute_Form_Contribution" && ($form->_action & CRM_Core_Action::UPDATE)) {
    $payments = $form->get_template_vars('payments');
    if (!empty($payments)) {
      _extendfinancialtype_alterpayments($payments);
      $form->assign('payments', $payments);
    }
    $lineitem = $form->get_template_vars('lineItem');
    if (!empty($lineitem)) {
      _extendfinancialtype_alterlineitems($lineitem);
      $form->assign('lineItem', $lineitem);
      if (!empty($lineitem)) {
        $form->assign('isChapterFund', TRUE);
      }
    }
  }
  if ($formName == "CRM_Contribute_Form_Contribution_Main") {
    if (!empty($form->_membershipBlock)) {
      // This is a membership page, so we add the chapter dropdown by default.
      $chapters = CRM_Core_OptionGroup::values('chapter_codes');
      $validChapters = [
        "Provincial Office",
        "Central West (Peel, Wellington, Waterloo, Halton, Hamilton)",
        "Chatham",
        "Durham",
        "Grey/Bruce",
        "Huron Perth",
        "Kingston",
        "London",
        "Metro Toronto",
        "Niagara Region",
        "North East",
        "Ottawa",
        "Peterborough",
        "Sault St. Marie",
        "Simcoe",
        "Sudbury & District",
        "Thunder Bay & District",
        "Upper Canada",
        "Windsor/Essex",
        "York Region",
      ];
      asort($chapters);
      $chapters = array_intersect($chapters, $validChapters);
      $chapters = [1000 => "Provincial Office"] + $chapters;
      $form->add('select', 'chapter_code',
        ts('Chapter'), $chapters, FALSE, array('class' => 'crm-select2 ')
      );
      CRM_Core_Region::instance('page-body')->add(array(
        'template' => 'CRM/EFT/AddChapterMem.tpl',
      ));
    }
  }
  if (($form->_action & CRM_Core_Action::UPDATE) && ($formName == "CRM_Member_Form_Membership")) {
    $chapterCodes = CRM_Core_OptionGroup::values('chapter_codes');
    $fundCodes = CRM_Core_OptionGroup::values('fund_codes');
    asort($chapterCodes);
    asort($fundCodes);
    $chapterCodes[1000] = $fundCodes[1000] = "Member-at-Large";
    $defaults = ['chapter_code' => 1000, 'fund_code' => 1000];
    if (!empty($form->_id)) {
      $defaults = CRM_EFT_BAO_EFT::getChapterFund($form->_id, "civicrm_membership");
      if (!empty($defaults)) {
        $defaults = ['chapter_code' => $defaults['chapter_code'], 'fund_code' => $defaults['fund_code']];
      }
    }
    $form->setDefaults($defaults);
    $form->add('select', 'chapter_code',
      ts('Chapter Code'),
      $chapterCodes
    );
    $form->add('select', 'fund_code',
      ts('Fund Code'),
      $fundCodes
    );
    CRM_Core_Region::instance('page-body')->add(array(
      'template' => 'CRM/EFT/AddChapterMem.tpl',
    ));
    $form->assign('isBackOffice', TRUE);
  }
  if (array_key_exists('financial_type_id', $form->_elementIndex)
      || (in_array($formName, ["CRM_Event_Form_Participant", "CRM_Contribute_Form_AdditionalPayment"]) && ($form->_action & CRM_Core_Action::ADD))) {
    if (($form->_action & CRM_Core_Action::UPDATE) && ($formName == "CRM_Member_Form_Membership" || $formName == "CRM_Contribute_Form_Contribution")) {
      return;
    }
    // Add chapter codes.
    $chapterCodes = CRM_EFT_BAO_EFT::getCodes('chapter_codes');
    // Add fund codes.
    $fundCodes = CRM_Core_OptionGroup::values('fund_codes');
    if ($formName == "CRM_Member_Form_Membership") {
      $chapterCodes = CRM_Core_OptionGroup::values('chapter_codes');
      asort($chapterCodes);
      asort($fundCodes);
      $chapterCodes[1000] = $fundCodes[1000] = "Member-at-Large";
      $form->setDefaults(['chapter_code' => 1000, 'fund_code' => 1000]);
    }
    $form->add('select', 'chapter_code',
      ts('Chapter Code'),
      $chapterCodes
    );
    $form->add('select', 'fund_code',
      ts('Fund Code'),
      $fundCodes
    );
    if ($formName == "CRM_Contribute_Form_AdditionalPayment") {
      $form->assign('isPayment', TRUE);
    }
    CRM_Core_Region::instance('page-body')->add(array(
      'template' => 'CRM/EFT/AddChapterFundCode.tpl',
    ));
  }

  if ($formName == "CRM_Admin_Form_PaymentProcessor" && in_array($form->_action, [CRM_Core_Action::ADD, CRM_Core_Action::UPDATE])) {
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
    $form->assign('isPaymentProcessor', TRUE);
    CRM_Core_Region::instance('page-body')->add(array(
      'template' => 'CRM/EFT/AddChapterFundCode.tpl',
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
      // Assign chapter and fund.
      $payments = $form->get_template_vars('payments');
      _extendfinancialtype_alterpayments($payments);
      $form->assign('payments', $payments);
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

    case "CRM_Admin_Form_PaymentProcessor":
      $defaults = CRM_EFT_BAO_EFT::getChapterFund($form->getVar('_id'), "civicrm_payment_processor");
      break;

    case "CRM_Financial_Form_PaymentEdit":
      $defaults = CRM_EFT_BAO_EFT::getChapterFund($form->getVar('_id'), "civicrm_financial_trxn");
      if (!empty($defaults)) {
        $defaults = ['chapter_code_trxn' => $defaults['chapter_code'], 'fund_code_trxn' => $defaults['fund_code']];
      }
      break;

    default:
      break;
    }
    $form->setDefaults($defaults);
  }

  if (array_key_exists('payment_instrument_id', $form->_elementIndex) || (in_array($formName, ["CRM_Contribute_Form_Contribution", "CRM_Event_Form_Participant"]) && ($form->_action & CRM_Core_Action::ADD))
    && (in_array($form->_action, [CRM_Core_Action::ADD, CRM_Core_Action::UPDATE]))) {
    
    if (in_array($formName, ["CRM_Financial_Form_FinancialBatch", "CRM_Financial_Form_Search", "CRM_Admin_Form_PaymentProcessor"])) {
      return;
    }
    if ($formName == "CRM_Contribute_Form_Contribution" && !empty($form->_mode)) {
      $form->assign('isPayment', TRUE);
    }
    // Add chapter codes.
    $chapterCodes = CRM_EFT_BAO_EFT::getCodes('chapter_codes');
    // Add fund codes.
    $fundCodes = CRM_Core_OptionGroup::values('fund_codes');
    if ($formName == "CRM_Member_Form_Membership") {
      $chapterCodes = CRM_Core_OptionGroup::values('chapter_codes');
      asort($chapterCodes);
      asort($fundCodes);
      $chapterCodes[1000] = $fundCodes[1000] = "Member-at-Large";
      $form->setDefaults(['chapter_code_trxn' => 1000, 'fund_code_trxn' => 1000]);
    }
    $form->add('select', 'chapter_code_trxn',
      ts('Chapter Code'),
      $chapterCodes
    );
    $form->add('select', 'fund_code_trxn',
      ts('Fund Code'),
      $fundCodes
    );
    CRM_Core_Region::instance('page-body')->add(array(
      'template' => 'CRM/EFT/AddChapterFundCodeTrxn.tpl',
    ));
  }
}

function _extendfinancialtype_alterpayments(&$payments) {
  $chapterCodes = CRM_EFT_BAO_EFT::getCodes('chapter_codes');
  $fundCodes = CRM_Core_OptionGroup::values('fund_codes');
  foreach ($payments as &$payment) {
    $chapterFund = CRM_EFT_BAO_EFT::getChapterFund($payment['id'], "civicrm_financial_trxn");
    if (!empty($chapterFund)) {
      $payment['chapter_code'] = $chapterCodes[$chapterFund['chapter_code']];
      $payment['fund_code'] = $fundCodes[$chapterFund['fund_code']];
    }
  }
  CRM_Core_Region::instance('page-body')->add(array(
    'template' => 'CRM/PaymentInfo.tpl',
  ));
}

function _extendfinancialtype_alterlineitems(&$lineitems) {
  $chapterCodes = CRM_EFT_BAO_EFT::getCodes('chapter_codes');
  $fundCodes = CRM_Core_OptionGroup::values('fund_codes');
  foreach ($lineitems as &$lineitem) {
    foreach ($lineitem as $id => &$item) {
      $chapterFund = CRM_EFT_BAO_EFT::getChapterFund($id, "civicrm_line_item");
      if (!empty($chapterFund)) {
        $item['chapter_code'] = $chapterCodes[$chapterFund['chapter_code']];
        $item['fund_code'] = $fundCodes[$chapterFund['fund_code']];
      }
    }
  }
}

function extendfinancialtype_civicrm_pre($op, $objectName, $objectId, &$objectRef) {
  if ($op == "delete") {
    CRM_EFT_BAO_EFT::deleteChapterFundEntity($objectId, $objectName);
  }
  if ($objectName == "Profile") {
    $giftType = CRM_Utils_Array::value('custom_13', $objectRef, NULL);
    if (!empty($giftType)) {
      CRM_Core_Session::singleton()->set('giftType', $giftType);
    }
  }
  if ($objectName == "Contribution" && $op == 'create' && CRM_Utils_Array::value('contribution_page_id', $objectRef)) {
    $giftType = CRM_Core_Session::singleton()->get('giftType');
    list($code, $ft) = addGiftFT($giftType);
    if (!empty($ft)) {
      $objectRef['financial_type_id'] = $ft;
    }
  }
  if ($objectName == "FinancialItem" && $op == 'create') {
    $giftType = CRM_Core_Session::singleton()->get('giftType');
    list($code, $ft) = addGiftFT($giftType);
    if (!empty($code)) {
      $fa = CRM_Core_DAO::singleValueQuery("SELECT id FROM civicrm_financial_account WHERE accounting_code = {$code}");
      if (!empty($fa)) {
        $objectRef['financial_account_id'] = $fa;
      }
    }
  }
  if ($objectName == "LineItem" && $op == 'create') {
    $giftType = CRM_Core_Session::singleton()->get('giftType');
    list($code, $ft) = addGiftFT($giftType);
    if (!empty($ft)) {
      $objectRef['financial_type_id'] = $ft;
    }
  }
}

function addGiftFT($giftType) {
  if (!empty($giftType)) {
    switch ($giftType) {
      case "General Donation":
        $ft = 4010;
      break;
      case "Adult Support Program":
        $ft = 4016;
      break;
      case "Autism Awareness Day":
        $ft = 4314;
      break;
      case "Building Brighter Futures Fund":
        $ft = 4036;
      break;
      case "Eleanor Ritchie Scholarship":
        $ft = 4420;
      break;
      case "Jeanette Holden Scholarship":
        $ft = 4425;
      break;
      case "Hollyllyn Towie Scholarship":
        $ft = 4428;
      break;
      case "Research":
        $ft = 4015;
      break;
      default:
        break;
    }
    if (!empty($ft)) {
      $financialType = CRM_Core_DAO::singleValueQuery("SELECT id FROM civicrm_financial_type WHERE name LIKE '{$ft}%'");
      if (!empty($financialType)) {
        return [$ft, $financialType];
      }
    }
  }
  return FALSE;
}

function extendfinancialtype_civicrm_postSave_civicrm_contribution_soft($dao) {
  // Save appropriate gift types for In Honour Of and In Memory Of.
  if ($dao->id) {
    $softCreditTypes = CRM_Core_OptionGroup::values('soft_credit_type');
    if (in_array($softCreditTypes[$dao->soft_credit_type_id], ["In Honor of", "In Memory of"])) {
      $ft = CRM_Core_DAO::singleValueQuery("SELECT f.name
        FROM civicrm_contribution c
        INNER JOIN civicrm_financial_type f ON f.id = c.financial_type_id
        WHERE c.id = {$dao->contribution_id}");
      // We change the financial account in this case to Memorial Donation.
      $fa = CRM_Core_DAO::singleValueQuery("SELECT id FROM civicrm_financial_account WHERE accounting_code = 4012");
      $fi = CRM_Contribute_BAO_Contribution::getLastFinancialItemIds($dao->contribution_id)[0];
      $fi = reset($fi);
      if ($fi) {
        civicrm_api3('FinancialItem', 'create', ['id' => $fi, 'financial_account_id' => $fa]);
      }
      civicrm_api3('Contribution', 'create', ['id' => $dao->contribution_id, 'financial_type_id' => "4012 Memorial Donations"]);
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

function extendfinancialtype_civicrm_postSave_civicrm_payment_processor($dao) {
  if ($dao->id) {
    $pid = CRM_Core_Smarty::singleton()->get_template_vars('eft_payment_processor');
    if ($pid) {
      $pids = [$pid, $dao->id];
    }
    else {
      CRM_Core_Smarty::singleton()->assign('eft_payment_processor', $dao->id);
    }
    if (!empty($pids)) {
      CRM_Core_Smarty::singleton()->assign('eft_payment_processors', $pids);
    }
  }
}

/**
 * Implementation of hook_civicrm_post
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_post
 */
function extendfinancialtype_civicrm_post($op, $objectName, $objectId, &$objectRef) {
  if ($objectName == "Contribution" && $op == 'create') {
    if (!empty($objectRef->contribution_recur_id)) {
      $chapterFund = CRM_EFT_BAO_EFT::getChapterFund($objectRef->contribution_recur_id, "civicrm_contribution_recur");
      if (!empty($chapterFund)) {
        $fts = CRM_EFT_BAO_EFT::addChapterFund($chapterFund['chapter_code'], $chapterFund['fund_code'], $objectId, "civicrm_line_item", TRUE);
        foreach ($fts as $ft) {
          if (!empty($ft)) {
            $lastFt = CRM_Core_DAO::executeQuery("SELECT ce.chapter_code, ce.fund_code 
              FROM civicrm_contribution c
              INNER JOIN civicrm_entity_financial_trxn eft ON eft.entity_id = c.id AND eft.entity_table = 'civicrm_contribution'
              INNER JOIN civicrm_financial_trxn ft ON ft.id = eft.financial_trxn_id
              INNER JOIN civicrm_chapter_entity ce ON ce.entity_id = ft.id AND ce.entity_table = 'civicrm_financial_trxn'
              WHERE c.id = {$objectId} ORDER BY ft.id DESC LIMIT 1")->fetchAll()[0];
            $params = [
              "entity_id" => $ft,
              "entity_table" => "civicrm_financial_trxn",
            ];
            if (!empty($lastFt)) {
              $params['chapter'] = $lastFt['chapter_code'];
              $params['fund'] = $lastFt['fund_code'];
            }
            else {
              $params['chapter'] = 1000;
              $params['fund'] = 1000;
            }
            CRM_EFT_BAO_EFT::saveChapterFund($params);
          }
        }
      }
    }
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
      if ($form->_action & CRM_Core_Action::ADD) {
        $sid = CRM_Core_Smarty::singleton()->get_template_vars('eft_price_set_id');
      }
      elseif ($form->_action & CRM_Core_Action::UPDATE) {
        $sid = $form->getVar('_sid');
      }
      if (!$sid) {
        return;
      }
      CRM_EFT_BAO_EFT::addChapterFund($form->_submitValues['chapter_code'], $form->_submitValues['fund_code'], $sid, "civicrm_price_set");
      break;

    case "CRM_Price_Form_Field":
      if ($form->_action & CRM_Core_Action::ADD) {
        $fid = CRM_Core_Smarty::singleton()->get_template_vars('eft_price_field_id');
      }
      elseif ($form->_action & CRM_Core_Action::UPDATE) {
        $fid = $form->getVar('_fid');
      }
      if (!$fid) {
        return;
      }
      if ($form->_submitValues['html_type'] == "Text") {
        CRM_EFT_BAO_EFT::addChapterFund($form->_submitValues['chapter_code'], $form->_submitValues['fund_code'], $fid, "civicrm_price_field");
      }
      else {
        // We need to save the chapter and fund for each price field value rather than price field.
        CRM_EFT_BAO_EFT::addChapterFund($form->_submitValues, NULL, $fid, "civicrm_price_field_value" , TRUE);
      }
      break;

    case "CRM_Price_Form_Option":
      if ($form->_action & CRM_Core_Action::ADD) {
        $oid = CRM_Core_Smarty::singleton()->get_template_vars('eft_price_field_value_id');
      }
      elseif ($form->_action & CRM_Core_Action::UPDATE) {
        $oid = $form->getVar('_oid');
      }
      if (!$oid) {
        return;
      }
      CRM_EFT_BAO_EFT::addChapterFund($form->_submitValues['chapter_code'], $form->_submitValues['fund_code'], $oid, "civicrm_price_field_value", FALSE);
      break;

    case "CRM_Contribute_Form_ContributionPage_Settings":
      CRM_EFT_BAO_EFT::addChapterFund($form->_submitValues['chapter_code'], $form->_submitValues['fund_code'], $form->getVar('_id'), "civicrm_contribution_page");
      break;

    case "CRM_Event_Form_ManageEvent_Fee":
      CRM_EFT_BAO_EFT::addChapterFund($form->_submitValues['chapter_code'], $form->_submitValues['fund_code'], $form->_id, "civicrm_event");
      break;

    case "CRM_Member_Form_MembershipType":
      if ($form->_action & CRM_Core_Action::ADD) {
        $mid = CRM_Core_Smarty::singleton()->get_template_vars('eft_membership_type_id');
      }
      elseif ($form->_action & CRM_Core_Action::UPDATE) {
        $mid = $form->getVar('_id');
      }
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
    case "CRM_Member_Form_MembershipRenewal":
      // Add chapter, fund code for main contribution.
      $contributionId = CRM_Core_DAO::singleValueQuery("SELECT contribution_id FROM civicrm_membership_payment WHERE membership_id = {$form->_id} ORDER BY id DESC LIMIT 1");
      $isPriceSet = FALSE;
      if (CRM_Utils_Array::value('chapter_code_trxn', $form->_submitValues) || CRM_Utils_Array::value('fund_code_trxn', $form->_submitValues)) {
        $isPriceSet = TRUE;
      }
      if ($contributionId) {
        $fts = CRM_EFT_BAO_EFT::addChapterFund($form->_submitValues['chapter_code'], $form->_submitValues['fund_code'], $contributionId, "civicrm_line_item", $isPriceSet);
        CRM_EFT_BAO_EFT::addTrxnChapterFund($fts, $form->_submitValues);
      }
      // We save the chapter and fund for membership as well.
      $memChapParams = [
        'entity_id' => $form->_id,
        'entity_table' => "civicrm_membership",
        'chapter' => $form->_submitValues['chapter_code'],
        'fund' => $form->_submitValues['fund_code'],
      ];
      CRM_EFT_BAO_EFT::saveChapterFund($memChapParams);
      break;

    case "CRM_Admin_Form_PaymentProcessor":
      $pids = CRM_Core_Smarty::singleton()->get_template_vars('eft_payment_processors');
      foreach ($pids as $pid) {
        CRM_EFT_BAO_EFT::addChapterFund($form->_submitValues['chapter_code'], $form->_submitValues['fund_code'], $pid, "civicrm_payment_processor");
      }
      break;

    case "CRM_Contribute_Form_AdditionalPayment":
      // Get last inserted financial trxn if updated.
      $ft = CRM_EFT_BAO_EFT::getLastTrxnId($form->_id);
      if (!empty($ft)) {
        $params = [
          "entity_id" => $ft,
          "entity_table" => "civicrm_financial_trxn",
          "chapter" => $form->_submitValues['chapter_code_trxn'],
          "fund" => $form->_submitValues['fund_code_trxn'],
        ];
        CRM_EFT_BAO_EFT::saveChapterFund($params);
      }
      $fi = CRM_Contribute_BAO_Contribution::getLastFinancialItemIds($form->_id)[0];
      $fi = reset($fi);
      if (!empty($fi)) {
        $entry = CRM_Core_DAO::singleValueQuery("SELECT id FROM civicrm_chapter_entity WHERE entity_id = {$fi} AND entity_table = 'civicrm_financial_item'");
        if (!$entry) {
          $params = [
            "entity_id" => $fi[0],
            "entity_table" => "civicrm_financial_item",
            "chapter" => $form->_submitValues['chapter_code_trxn'],
            "fund" => $form->_submitValues['fund_code_trxn'],
          ];
          CRM_EFT_BAO_EFT::saveChapterFund($params);
        }
      }
      break;

    case "CRM_Financial_Form_PaymentEdit":
      CRM_EFT_BAO_EFT::addChapterFund($form->_submitValues['chapter_code_trxn'], $form->_submitValues['fund_code_trxn'], $form->getVar('_id'), "civicrm_financial_trxn");
      // Check if payments have same chapter and fund code too.
      $contribChapterParams = [
        'chapter' => $form->_submitValues['chapter_code_trxn'],
        'fund' => $form->_submitValues['fund_code_trxn'],
        'entity_table' => 'civicrm_financial_trxn',
        'entity_id' => $form->getVar('_id'),
      ];
      CRM_EFT_BAO_EFT::checkAndUpdateRelatedContribution($contribChapterParams);
      break;

    default:
      break;
    }
  }

  if ($formName == "CRM_Member_Form_Membership" && ($form->_action & CRM_Core_Action::ADD)) {
    // Add chapter, fund code for membership.
    $memType = $form->getVar('_memType');
    $chapterFund = CRM_Core_DAO::executeQuery("SELECT chapter_code, fund_code FROM civicrm_chapter_entity WHERE entity_id = {$memType} AND entity_table = 'civicrm_membership_type'")->fetchAll();
    if (!empty($chapterFund)) {
      CRM_EFT_BAO_EFT::addChapterFund($chapterFund[0]['chapter_code'], $chapterFund[0]['fund_code'], $form->_id, "civicrm_membership");
    }
    else {
      CRM_EFT_BAO_EFT::addChapterFund($form->_submitValues['chapter_code'], $form->_submitValues['fund_code'], $form->_id, "civicrm_membership");
    }
  }

  if ($formName == "CRM_Event_Form_Participant" && ($form->_action & CRM_Core_Action::ADD)) {
    // Add chapter, fund code for participant.
    $eventId = $form->getVar('_eventId');
    $chapterFund = CRM_Core_DAO::executeQuery("SELECT chapter_code, fund_code FROM civicrm_chapter_entity WHERE entity_id = {$eventId} AND entity_table = 'civicrm_event'")->fetchAll();
    if (!empty($chapterFund)) {
      CRM_EFT_BAO_EFT::addChapterFund($chapterFund[0]['chapter_code'], $chapterFund[0]['fund_code'], $form->_id, "civicrm_participant");
    }
    else {
      CRM_EFT_BAO_EFT::addChapterFund($form->_submitValues['chapter_code'], $form->_submitValues['fund_code'], $form->_id, "civicrm_participant");
    }
  }

  // Handle updates to Contributions.
  if ($form->_action & CRM_Core_Action::UPDATE) {
    switch ($formName) {
    case "CRM_Contribute_Form_Contribution":
      // Get last inserted financial trxn if updated.
      $ft = CRM_EFT_BAO_EFT::getLastTrxnId($form->_id);
      if (!empty($ft)) {
        $lastFt = CRM_Core_DAO::executeQuery("SELECT ce.chapter_code, ce.fund_code 
          FROM civicrm_contribution c
          INNER JOIN civicrm_entity_financial_trxn eft ON eft.entity_id = c.id AND eft.entity_table = 'civicrm_contribution'
          INNER JOIN civicrm_financial_trxn ft ON ft.id = eft.financial_trxn_id
          INNER JOIN civicrm_chapter_entity ce ON ce.entity_id = ft.id AND ce.entity_table = 'civicrm_financial_trxn'
          WHERE c.id = {$form->_id} ORDER BY ft.id DESC LIMIT 1")->fetchAll()[0];
        $params = [
          "entity_id" => $ft,
          "entity_table" => "civicrm_financial_trxn",
        ];
        if (!empty($lastFt)) {
          $params['chapter'] = $lastFt['chapter_code'];
          $params['fund'] = $lastFt['fund_code'];
        }
        else {
          $params['chapter'] = 1000;
          $params['fund'] = 1000;
        }
        CRM_EFT_BAO_EFT::saveChapterFund($params);
      }
      $fi = CRM_Contribute_BAO_Contribution::getLastFinancialItemIds($form->_id)[0];
      $fi = reset($fi);
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
    $manualChapter = [];
    $memberItems = [
      'isMembership' => CRM_Utils_Array::value('isMembership', $form->_values, NULL),
      'memType' => $form->get('membershipTypeID'),
    ];

    if ($form->_id == DONATION_PAGE) {
      if ($submitChapter = CRM_Utils_Array::value('chapter_code', $form->_params, NULL)) {
        $fts = CRM_EFT_BAO_EFT::addChapterFund($submitChapter, $submitChapter, $form->_contributionID, "civicrm_line_item", TRUE);
        // Add chapter and fund for recurring contributions.
        if (CRM_Utils_Array::value('is_recur', $form->_values)) {
          CRM_EFT_BAO_EFT::addChapterFund($submitChapter, $submitChapter, $form->_params['contributionRecurID'], "civicrm_contribution_recur", TRUE);
        }
        $chapterFund = [
          'chapter_code' => $submitChapter,
          'fund_code' => $submitChapter,
        ];
      }
    }
    else {
      if (!empty($memberItems['isMembership'])) {
        $form->_params['contributionPageID'] = $form->_membershipBlock['entity_id'];
      }
      if (!empty($form->_params['chapter_code']) && !empty($memberItems['isMembership'])) {
        $originalFund = CRM_EFT_BAO_EFT::getChapterFund($memberItems['memType'], "civicrm_membership_type");
        $chapterFund = [
          'chapter_code' => $form->_params['chapter_code'],
          'fund_code' => $originalFund['fund_code'],
        ];
        $fts = CRM_EFT_BAO_EFT::addChapterFund($form->_params['chapter_code'], $memberItems, $form->_contributionID, "civicrm_contribution_page_online", TRUE);
        // Add chapter and fund for recurring contributions.
        if (CRM_Utils_Array::value('is_recur', $form->_values)) {
          CRM_EFT_BAO_EFT::addChapterFund($form->_params['chapter_code'], $originalFund['fund_code'], $form->_params['contributionRecurID'], "civicrm_contribution_recur", TRUE);
        }
        // We save the chapter and fund for membership as well.
        $memChapParams = [
          'entity_id' => $form->_params['membershipID'],
          'entity_table' => "civicrm_membership",
          'chapter' => $form->_params['chapter_code'],
          'fund' => $originalFund['fund_code'],
        ];
        CRM_EFT_BAO_EFT::saveChapterFund($memChapParams);
      }
      else {
        $fts = CRM_EFT_BAO_EFT::addChapterFund($form->_params['contributionPageID'], $memberItems, $form->_contributionID, "civicrm_contribution_page_online");
        // Add chapter and fund for recurring contributions.
        if (CRM_Utils_Array::value('is_recur', $form->_values)) {
          CRM_EFT_BAO_EFT::addChapterFund($form->_params['contributionPageID'], $memberItems, $form->_params['contributionRecurID'], "civicrm_contribution_recur");
        }
        // Get chapter and fund for payment processor id.
        $paymentProcessorId = CRM_Utils_Array::value('payment_processor_id', $form->_params);
        if ($paymentProcessorId) {
          $chapterFund = CRM_EFT_BAO_EFT::getChapterFund($paymentProcessorId, "civicrm_payment_processor");
        }
      }
    }
    $ftParams = [
      'chapter_code_trxn' => CRM_Utils_Array::value('chapter_code', $chapterFund, NULL),
      'fund_code_trxn' => CRM_Utils_Array::value('fund_code', $chapterFund, NULL),
    ];
    if (!empty($ftParams['chapter_code_trxn']) || !empty($ftParams['fund_code_trxn'])) {
      CRM_EFT_BAO_EFT::addTrxnChapterFund($fts, $ftParams);
    }
  }
  if ($formName == "CRM_Event_Form_Registration_Confirm") {
    $participants = $form->getVar('_participantIDS');
    // Get chapter and fund for payment processor id.
    $paymentProcessorId = CRM_Utils_Array::value('payment_processor_id', $form->getVar('_params'));
    if ($paymentProcessorId) {
      $chapterFund = CRM_EFT_BAO_EFT::getChapterFund($paymentProcessorId, "civicrm_payment_processor");
    }
    $ftParams = [
      'chapter_code_trxn' => CRM_Utils_Array::value('chapter_code', $chapterFund),
      'fund_code_trxn' => CRM_Utils_Array::value('fund_code', $chapterFund),
    ];
    foreach ($participants as $pid) {
      if ($form->_values['event']['event_type_id'] == RAISE_THE_FLAG) {
        $contributionId = CRM_Core_DAO::singleValueQuery("SELECT contribution_id FROM civicrm_participant_payment WHERE participant_id = {$pid}");
        $chapter = CRM_Utils_Array::value('chapter_code', $form->_params['params'][$pid]);
        $fts = CRM_EFT_BAO_EFT::addChapterFund($chapter, $chapter, $contributionId, "civicrm_line_item", TRUE);
      }
      else {
        $fts = CRM_EFT_BAO_EFT::addChapterFund($form->_eventId, NULL, $pid, "civicrm_event_page_online");
      }
      if (!empty($ftParams['chapter_code_trxn']) || !empty($ftParams['fund_code_trxn'])) {
        CRM_EFT_BAO_EFT::addTrxnChapterFund($fts, $ftParams);
      }
    }
  }
}

function extendfinancialtype_civicrm_alterReportVar($varType, &$var, &$object) {
  if ('CRM_Report_Form_Contribute_Detail' == get_class($object)) {
    if ($varType == 'columns') {
      $var['civicrm_contribution']['fields']['fund_id'] = array(
        'name' => 'fund_id',
        'title' => ts('Fund ID'),
        'dbAlias' => "CONCAT(cfa.accounting_code, '-', cefa.chapter_code)",
      );
      $var['civicrm_financial_trxn']['fields']['pan_truncation'] = array(
        'name' => 'pan_truncation',
        'title' => ts('Last 4 digits of the card'),
      );
      $var['civicrm_contribution']['fields']['invoice_id'] = array(
        'name' => 'invoice_id',
        'title' => ts('Invoice Number'),
        'dbAlias' => "SUBSTRING(contribution_civireport.invoice_id, -10)",
      );
    }
    if ($varType == 'sql') {
      $object->_originVar = NULL;
      $params = $object->getVar('_params');
      $aliases = $object->getVar('_aliases');
      $fromClause = "
        LEFT JOIN civicrm_entity_financial_account efa ON efa.entity_id = contribution_civireport.financial_type_id
          AND efa.entity_table = 'civicrm_financial_type' AND efa.account_relationship = 1
        LEFT JOIN civicrm_financial_account cfa ON cfa.id = efa.financial_account_id
        LEFT JOIN civicrm_chapter_entity cefa ON cefa.entity_id = contribution_civireport.id AND cefa.entity_table = 'civicrm_contribution'
        LEFT JOIN civicrm_entity_financial_trxn eftpt ON eftpt.entity_id = contribution_civireport.id AND eftpt.entity_table = 'civicrm_contribution'
        LEFT JOIN civicrm_financial_trxn ftpt ON ftpt.id = eftpt.financial_trxn_id
      ";
      $from = $object->getVar('_from') . $fromClause;
      $aclFrom = $object->getVar('_aclFrom') . $fromClause;
      $object->_originVar = $from;
      $object->setVar('_from', $from);
      $object->setVar('_aclFrom', $aclFrom);
    }
    elseif ($varType == 'rows') {
      if (!empty($object->_originVar)) {
        $object->setVar('_from', $object->_originVar);
      }
    }
  }
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
        'dbAlias' => 'CONCAT(ce_from.fund_code, " ", covf_from.label)',
      );
      $var['civicrm_chapter_entity']['fields']['fund_code_to'] = array(
        'name' => 'fund_code_to',
        'title' => ts('Fund Code - Debit'),
        'dbAlias' => 'CONCAT(ce_to.fund_code, " ", covf_to.label)',
      );
      $var['civicrm_chapter_entity']['fields']['fund_id'] = array(
        'name' => 'fund_id',
        'title' => ts('Fund ID'),
        'default' => TRUE,
        'dbAlias' => 'CASE
          WHEN financial_trxn_civireport.from_financial_account_id IS NOT NULL
          THEN  CONCAT(financial_account_civireport_credit_1.accounting_code, "-", ce_to.chapter_code)
          ELSE  CONCAT(financial_account_civireport_credit_2.accounting_code, "-", ce_to.chapter_code)
          END',
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
  if ('CRM_Report_Form_Member_Detail' == get_class($object)) {
    if ($varType == 'columns') {
      $var['civicrm_chapter_entity']['fields']['membership_chapter'] = array(
        'name' => 'membership_chapter',
        'title' => ts('Membership for which chapter'),
        'dbAlias' => 'covc.label',
      );
    }
    if ($varType == 'sql') {
      $from = $var->getVar('_from');
      $from .= "
      LEFT JOIN civicrm_chapter_entity ce ON ce.entity_id = membership_civireport.id AND ce.entity_table = 'civicrm_membership'
      LEFT JOIN civicrm_option_group cogc ON cogc.name = 'chapter_codes'
      LEFT JOIN civicrm_option_value covc ON (covc.value = ce.chapter_code AND covc.option_group_id = cogc.id)";
      $var->setVar('_from', $from);
    }
  }
}

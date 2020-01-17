<?php

use CRM_EFT_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;

/**
 * FIXME - Add test description.
 *
 * Tips:
 *  - With HookInterface, you may implement CiviCRM hooks directly in the test class.
 *    Simply create corresponding functions (e.g. "hook_civicrm_post(...)" or similar).
 *  - With TransactionalInterface, any data changes made by setUp() or test****() functions will
 *    rollback automatically -- as long as you don't manipulate schema or truncate tables.
 *    If this test needs to manipulate schema or truncate tables, then either:
 *       a. Do all that using setupHeadless() and Civi\Test.
 *       b. Disable TransactionalInterface, and handle all setup/teardown yourself.
 *
 * @group headless
 */
class CRM_ExtendedFinancialType_Form_ContributionTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface {

  use Civi\Test\Api3TestTrait;
  use Civi\Test\ContactTestTrait;

  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://docs.civicrm.org/dev/en/latest/testing/phpunit/#civitest
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function setUp() {
    $this->_individualId = $this->individualCreate();
    $instruments = $this->callAPISuccess('contribution', 'getoptions', ['field' => 'payment_instrument_id']);
    $this->paymentInstruments = $instruments['values'];
    parent::setUp();
  }

  public function tearDown() {
    $this->callAPISuccess('Contact', 'delete', ['id' => $this->_individualId, 'skip_undelete' => TRUE]);
    parent::tearDown();
  }

  /**
   * Example: Test that a version is returned.
   */
  public function testChangeContributionFinancialType() {
    $form = new CRM_Contribute_Form_Contribution();

    $form->_submitValues = [
      'total_amount' => '6100.10',
      'financial_type_id' => 1,
      'contact_id' => $this->_individualId,
      'payment_instrument_id' => array_search('Check', $this->paymentInstruments),
      'contribution_status_id' => 1,
      'price_set_id' => 0,
      'chapter_code' => 1000,
      'fund_code' => 1000,
    ];
    $form->_action = CRM_Core_Action::ADD;
    $formResult = $form->testSubmit($form->_submitValues, CRM_Core_Action::ADD);
    $form->_id = $formResult->id;
    CRM_Utils_Hook::postProcess('CRM_Contribute_Form_Contribution', $form);
    $contribution = $this->callAPISuccessGetSingle('Contribution', ['contact_id' => $this->_individualId]);
    $form->_submitValues = [
      'total_amount' => '6100.10',
      'net_amount' => '6100.10',
      'financial_type_id' => 2,
      'contact_id' => $this->_individualId,
      'payment_instrument_id' => array_search('Check', $this->paymentInstruments),
      'contribution_status_id' => 1,
      'price_set_id' => 0,
      'id' => $contribution['id'],
    ];
    $form->_action = CRM_Core_Action::UPDATE;
    $formResult = $form->testSubmit($form->_submitValues, CRM_Core_Action::UPDATE);
    CRM_Utils_Hook::postProcess('CRM_Contribute_Form_Contribution', $form);
    $contribution = $this->callAPISuccessGetSingle('Contribution', ['contact_id' => $this->_individualId]);
    $lineItem = $this->callAPISuccessGetSingle('lineItem', ['contribution_id' => $contribution['id']]);
    $this->assertEquals(6100.10, $contribution['total_amount'], 2);
    $financialTransactions = $this->callAPISuccess('FinancialTrxn', 'get', ['sequential' => TRUE]);
    $financialTrxnIds = CRM_Utils_Array::collect('id', $financialTransactions['values']);
    foreach ($financialTrxnIds as $financialTrxnId) {
      $chapterEntityDeets = CRM_Core_DAO::executeQuery("SELECT chapter_code, fund_code FROM civicrm_chapter_entity WHERE entity_table = 'civicrm_financial_trxn' AND entity_id = %1", [1 => [$financialTrxnId, 'Positive']])->fetchAll()[0];
      $this->assertEquals(1000, $chapterEntityDeets['chapter_code']);
      $this->assertEquals(1000, $chapterEntityDeets['fund_code']);
    }
    $financialItems = $this->callAPISuccess('FinancialItem', 'get', ['sequential' => TRUE]);
    $financialItemIds = CRM_Utils_Array::collect('id', $financialItems['values']);
    foreach ($financialItemIds as $financialItemId) {
      $chapterEntityDeets = CRM_Core_DAO::executeQuery("SELECT chapter_code, fund_code FROM civicrm_chapter_entity WHERE entity_table = 'civicrm_financial_item' AND entity_id = %1", [1 => [$financialItemId, 'Positive']])->fetchAll()[0];
      $this->assertEquals(1000, $chapterEntityDeets['chapter_code']);
      $this->assertEquals(1000, $chapterEntityDeets['fund_code']);
    }
    $this->callAPISuccess('LineItem', 'delete', ['id' => $lineItem['id']]);
    $this->callAPISuccess('Contribution', 'delete', ['id' => $contribution['id']]);
    CRM_Core_DAO::executeQuery("DELETE FROM civicrm_financial_trxn WHERE id IN (%1)", [1 => [implode(',', $financialTrxnIds), 'CommaSeparatedIntegers']]);
    CRM_Core_DAO::executeQuery("DELETE FROM civicrm_financial_item WHERE id IN (%1)", [1 => [implode(',', $financialItemIds), 'CommaSeparatedIntegers']]);
    CRM_Core_DAO::executeQuery("DELETE FROM civicrm_chapter_entity WHERE entity_table = 'civicrm_financial_trxn' AND entity_id IN (%1)", [1 => [implode(',', $financialTrxnIds), 'CommaSeparatedIntegers']]);
    CRM_Core_DAO::executeQuery("DELETE FROM civicrm_chapter_entity WHERE entity_table = 'civicrm_financial_item' AND entity_id IN (%1)", [1 => [implode(',', $financialItemIds), 'CommaSeparatedIntegers']]);
    CRM_Core_DAO::executeQuery("DELETE FROM civicrm_chapter_entity WHERE entity_table = 'civicrm_contribution' AND entity_id = %1", [1 => [$contribution['id'], 'Positive']]);
    CRM_Core_DAO::executeQuery("DELETE FROM civicrm_chapter_entity WHERE entity_table = 'civicrm_line_item' AND entity_id = %1", [1 => [$lineItem['id'], 'Positive']]);
  }

}

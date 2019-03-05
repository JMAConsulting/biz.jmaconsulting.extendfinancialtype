<?php
/*
 +--------------------------------------------------------------------+
 | Extended Financial Type Extension                                  |
 +--------------------------------------------------------------------+
 | Copyright (C) JMA Consulting                                       |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
 */

/**
 *
 * @package CRM
 * @copyright JMA Consulting (c)
 * $Id$
 *
 */
class CRM_EFT_BAO_EFT extends CRM_EFT_DAO_EFT {

  public static function getCodes($ogName) {
    $codes = CRM_Core_DAO::executeQuery("SELECT v.value as code, CONCAT(v.value, ' ', v.label) as label FROM civicrm_option_value v INNER JOIN civicrm_option_group g ON g.id = v.option_group_id WHERE g.name = '{$ogName}'")->fetchAll();
    foreach ($codes as $code) {
      $info[$code['code']] = $code['label'];
    }
    return $info;
  }

  public static function addChapterFund($chapter, $fund, $entityId, $entityTable, $isPriceSet = FALSE) {
    if ($entityTable == "civicrm_line_item") {
      $lineItems = civicrm_api3('LineItem', 'get', [
        'contribution_id' => $entityId,
      ])['values'];
      foreach ($lineItems as $lineItem) {
        // Retrieve chapters and funds.
        $lineItemChapterFund = CRM_Core_DAO::executeQuery("SELECT
          chapter_code, fund_code
          FROM civicrm_chapter_entity
          WHERE entity_table = 'civicrm_price_field_value'
          AND entity_id = {$lineItem['price_field_value_id']}")->fetchAll();
        $params = [
          'entity_id' => $lineItem['id'],
          'entity_table' => $entityTable,
        ];
        if (!empty($lineItemChapterFund)) {
          // We set the chapter code and fund code for each individual price field option if found.
          $params['chapter'] = $lineItemChapterFund[0]['chapter_code'];
          $params['fund'] = $lineItemChapterFund[0]['fund_code'];
        }
        else {
          // We set the chapter and fund code for the whole contribution.
          $params['chapter'] = $chapter;
          $params['fund'] = $fund;
        }
        self::saveChapterFund($params);

        // We also set the chapter and code for the corresponding financial item for this line item.
        $financialItem = civicrm_api3('FinancialItem', 'getsingle', [
          'return' => ["id"],
          'entity_id' => $lineItem['id'],
          'entity_table' => $entityTable,
        ])['id'];
        $itemParams = [
          'entity_id' => $financialItem,
          'entity_table' => "civicrm_financial_item",
          'chapter' => $params['chapter'],
          'fund' => $params['fund'],
        ];
        self::saveChapterFund($itemParams);

        if ($isPriceSet) {
          // And for the corresponding financial trxn for this contribution.
          $financialTrxn = civicrm_api3('EntityFinancialTrxn', 'getvalue', [
            'return' => "financial_trxn_id",
            'entity_id' => $financialItem,
            'entity_table' => "civicrm_financial_item",
          ]);
          $fts[] = $financialTrxn;
        }
      }
      if ($chapter && $fund) {
        // Add chapter code for contribution as well.
        $params = [
          'entity_id' => $entityId,
          'entity_table' => "civicrm_contribution",
          'chapter' => $chapter,
          'fund' => $fund,
        ];
        self::saveChapterFund($params);
      }
      
      if ($fts) {
        return $fts;
      }
    }
    if ($entityTable == "civicrm_contribution_page_online") {
      $lineItems = civicrm_api3('LineItem', 'get', [
        'contribution_id' => $entityId,
      ])['values'];
      foreach ($lineItems as $lineItem) {
        // Retrieve chapters and funds.
        $lineItemChapterFund = CRM_Core_DAO::executeQuery("SELECT
          chapter_code, fund_code
          FROM civicrm_chapter_entity
          WHERE entity_table = 'civicrm_price_field_value'
          AND entity_id = {$lineItem['price_field_value_id']}")->fetchAll();
        $lineItemChapterFund = CRM_Utils_Array::value(0, $lineItemChapterFund);
        $params = [
          'entity_id' => $lineItem['id'],
          'entity_table' => "civicrm_line_item",
        ];
        if (!empty($lineItemChapterFund)) {
          $params['chapter'] = $lineItemChapterFund['chapter_code'];
          $params['fund'] = $lineItemChapterFund['fund_code'];
        }
        elseif (!$fund['isMembership']) {
          $chapterFund = self::getChapterFund($chapter, "civicrm_contribution_page");
          $params['chapter'] = $chapterFund['chapter_code'];
          $params['fund'] = $chapterFund['fund_code'];
        }
        elseif ($fund['isMembership']) {
          $chapterFund = self::getChapterFund($fund['memType'], "civicrm_membership_type");
          $params['chapter'] = $chapterFund['chapter_code'];
          $params['fund'] = $chapterFund['fund_code'];
        }
        self::saveChapterFund($params);

        // We also set the chapter and code for the corresponding financial item for this line item.
        $financialItem = civicrm_api3('FinancialItem', 'getsingle', [
          'return' => ["id"],
          'entity_id' => $lineItem['id'],
          'entity_table' => "civicrm_line_item",
        ])['id'];
        $itemParams = [
          'entity_id' => $financialItem,
          'entity_table' => "civicrm_financial_item",
          'chapter' => $params['chapter'],
          'fund' => $params['fund'],
        ];
        self::saveChapterFund($itemParams);
        // And for the corresponding financial trxn for this contribution.
        $financialTrxn = civicrm_api3('EntityFinancialTrxn', 'getvalue', [
          'return' => "financial_trxn_id",
          'entity_id' => $financialItem,
          'entity_table' => "civicrm_financial_item",
        ]);
        $fts[] = $financialTrxn;
      }
      // Add chapter code for contribution as well.
      $chapterFund = self::getChapterFund($chapter, "civicrm_contribution_page");
      $params = [
        'entity_id' => $entityId,
        'entity_table' => "civicrm_contribution",
        'chapter' => $chapterFund['chapter_code'],
        'fund' => $chapterFund['fund_code'],
      ];
      self::saveChapterFund($params);

      if ($fts) {
        return $fts;
      }
    }
    if ($entityTable == "civicrm_event_page_online") {
      $contributionId = CRM_Core_DAO::singleValueQuery("SELECT contribution_id FROM civicrm_participant_payment WHERE participant_id = {$entityId}");
      if (!$contributionId) {
        return;
      }
      $lineItems = civicrm_api3('LineItem', 'get', [
        'contribution_id' => $contributionId,
      ])['values'];
      foreach ($lineItems as $lineItem) {
        // Retrieve chapters and funds.
        $lineItemChapterFund = CRM_Core_DAO::executeQuery("SELECT
          chapter_code, fund_code
          FROM civicrm_chapter_entity
          WHERE entity_table = 'civicrm_price_field_value'
          AND entity_id = {$lineItem['price_field_value_id']}")->fetchAll();
        $lineItemChapterFund = CRM_Utils_Array::value(0, $lineItemChapterFund);
        $params = [
          'entity_id' => $lineItem['id'],
          'entity_table' => "civicrm_line_item",
        ];
        if (!empty($lineItemChapterFund)) {
          $params['chapter'] = $lineItemChapterFund['chapter_code'];
          $params['fund'] = $lineItemChapterFund['fund_code'];
        }
        else {
          $chapterFund = self::getChapterFund($chapter, "civicrm_event");
          $params['chapter'] = $chapterFund['chapter_code'];
          $params['fund'] = $chapterFund['fund_code'];
        }
        self::saveChapterFund($params);

        // We also set the chapter and code for the corresponding financial item for this line item.
        $financialItem = civicrm_api3('FinancialItem', 'getsingle', [
          'return' => ["id"],
          'entity_id' => $lineItem['id'],
          'entity_table' => "civicrm_line_item",
        ])['id'];
        $itemParams = [
          'entity_id' => $financialItem,
          'entity_table' => "civicrm_financial_item",
          'chapter' => $params['chapter'],
          'fund' => $params['fund'],
        ];
        self::saveChapterFund($itemParams);
        // And for the corresponding financial trxn for this contribution.
        $financialTrxn = civicrm_api3('EntityFinancialTrxn', 'getvalue', [
          'return' => "financial_trxn_id",
          'entity_id' => $financialItem,
          'entity_table' => "civicrm_financial_item",
        ]);
        $fts[] = $financialTrxn;
      }
      // Add chapter code for contribution as well.
      $chapterFund = self::getChapterFund($chapter, "civicrm_event");
      $params = [
        'entity_id' => $contributionId,
        'entity_table' => "civicrm_contribution",
        'chapter' => $chapterFund['chapter_code'],
        'fund' => $chapterFund['fund_code'],
      ];
      self::saveChapterFund($params);
      // Add chapter code for participant too.
      $params = [
        'entity_id' => $entityId,
        'entity_table' => "civicrm_participant",
        'chapter' => $chapterFund['chapter_code'],
        'fund' => $chapterFund['fund_code'],
      ];
      self::saveChapterFund($params);

      if ($fts) {
        return $fts;
      }
    }
    if (in_array($entityTable, [
      "civicrm_price_set",
      "civicrm_event",
      "civicrm_membership_type",
      "civicrm_membership",
      "civicrm_participant",
      "civicrm_contribution_page",
      "civicrm_price_field_value",
    ])) {
      $params = [
        'entity_id' => $entityId,
        'entity_table' => $entityTable,
        'chapter' => $chapter,
        'fund' => $fund,
      ];
      self::saveChapterFund($params);
    }
    if ($entityTable == "civicrm_price_field") {
      // We save the same for price field and price field value.
      $params = [
        'entity_id' => $entityId,
        'entity_table' => $entityTable,
        'chapter' => $chapter,
        'fund' => $fund,
      ];
      self::saveChapterFund($params);

      // Price Field Value
      $params = [
        'entity_id' => CRM_Core_DAO::singleValueQuery("SELECT id FROM civicrm_price_field_value WHERE price_field_id = {$entityId}"),
        'entity_table' => "civicrm_price_field_value",
        'chapter' => $chapter,
        'fund' => $fund,
      ];
      self::saveChapterFund($params);
    }
    if ($entityTable == "civicrm_price_field_value") {
      if ($isPriceSet == TRUE) {
        $priceFieldValues = CRM_Core_DAO::executeQuery("SELECT id, label FROM civicrm_price_field_value WHERE price_field_id = {$entityId}")->fetchAll();
        $priceLabels = $chapter['option_label'];
        $chapters = $chapter['option_chapter_code'];
        $funds = $chapter['option_fund_code'];
        foreach ($priceFieldValues as $key => $priceFieldValue) {
          $chapterKey = array_search($priceFieldValue['label'], $priceLabels);
          $params = [
            'entity_id' => $priceFieldValue['id'],
            'entity_table' => "civicrm_price_field_value",
            'chapter' => $chapters[$chapterKey],
            'fund' => $funds[$chapterKey],
          ];
        }
      }
      else {
        $params = [
          'entity_id' => $entityId,
          'entity_table' => $entityTable,
          'chapter' => $chapter,
          'fund' => $fund,
        ];
        self::saveChapterFund($params);
      }
    }
  }

  public static function addTrxnChapterFund($fts, $params) {
    if ($fts) { 
      foreach ($fts as $ft) {
        $params = [
          "entity_id" => $ft,
          "entity_table" => "civicrm_financial_trxn",
          "chapter" => CRM_Utils_Array::value('chapter_code_trxn', $params),
          "fund" => CRM_Utils_Array::value('fund_code_trxn', $params),
        ];
        self::saveChapterFund($params);
      }
    }
  }

  public static function getChapterFund($entityId, $entityTable) {
    $chapterFundCode = CRM_Core_DAO::executeQuery("SELECT chapter_code, fund_code FROM civicrm_chapter_entity WHERE entity_id = {$entityId} AND entity_table = '{$entityTable}'")->fetchAll()[0];
    return ['chapter_code' => $chapterFundCode['chapter_code'], 'fund_code' => $chapterFundCode['fund_code']];
  }

  public static function saveChapterFund($params) {
    $eft = new CRM_EFT_BAO_EFT();
    $eft->entity_id = $params['entity_id'];
    $eft->entity_table = $params['entity_table'];
    $eft->find(TRUE);
    $eft->chapter_code = $params['chapter'];
    $eft->fund_code = $params['fund'];
    $eft->save();
  }

  public static function deleteChapterFundEntity($id, $entity) {
    switch ($entity) {
    case "Contribution":
      $lineItems = civicrm_api3('LineItem', 'get', [
        'contribution_id' => $id,
      ])['values'];
      if (!empty($lineItems)) {
        foreach ($lineItems as $lid => $lineItem) {
          self::deleteEntity($lid, 'civicrm_line_item');
          $financialItem = civicrm_api3('FinancialItem', 'getsingle', [
            'return' => ["id"],
            'entity_id' => $lineItem['id'],
            'entity_table' => 'civicrm_line_item',
          ])['id'];
          self::deleteEntity($financialItem, 'civicrm_financial_item');
          $financialTrxn = civicrm_api3('EntityFinancialTrxn', 'getvalue', [
            'return' => "financial_trxn_id",
            'entity_id' => $financialItem,
            'entity_table' => "civicrm_financial_item",
          ]);
          self::deleteEntity($financialTrxn, 'civicrm_financial_trxn');
        }
      }
      self::deleteEntity($id, 'civicrm_contribution');
      break;
    case "Event":
      self::deleteEntity($id, 'civicrm_event');
      break;
    default:
      break;
    }
  }

  public static function deleteEntity($id, $entity) {
    $eft = new CRM_EFT_BAO_EFT();
    $eft->entity_id = $id;
    $eft->entity_table = $entity;
    $eft->find(TRUE);
    $eft->delete();
    $eft->free();
  }
}

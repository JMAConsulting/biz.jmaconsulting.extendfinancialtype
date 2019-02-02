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

  public function addChapterFund($chapter, $fund, $entityId, $entityTable, $isPriceSet = FALSE) {
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
          AND entity_id = {$lineItem['price_field_value_id']}")->fetchAll()[0];
        $params = [
          'entity_id' => $lineItem['id'],
          'entity_table' => $entityTable,
        ];
        if (!empty($lineItemChapterFund)) {
          // We set the chapter code and fund code for each individual price field option if found.
          $params['chapter'] = $lineItemChapterFund['chapter_code'];
          $params['fund'] = $lineItemChapterFund['fund_code'];
        }
        else {
          // We set the chapter and fund code for the whole contribution.
          $params['chapter'] = $chapter;
          $params['fund'] = $fund;
        }
        self::saveChapterFund($params);
      }
      if (!$isPriceSet && $chapter) {
        // Add chapter code for contribution as well.
        $params = [
          'entity_id' => $entityId,
          'entity_id' => "civicrm_contribution",
          'chapter' => $chapter,
          'fund' => $fund,
        ];
        self::saveChapterFund($params);
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
          AND entity_id = {$lineItem['price_field_value_id']}")->fetchAll()[0];
        $params = [
          'entity_id' => $lineItem['id'],
          'entity_table' => "civicrm_line_item",
        ];
        if (!empty($lineItemChapterFund)) {
          $params['chapter'] = $lineItemChapterFund['chapter_code'];
          $params['fund'] = $lineItemChapterFund['fund_code'];
        }
        else {
          $chapterFund = self::getChapterFund($chapter, "civicrm_contribution_page")['chapter_code'];
          $params['chapter'] = $chapterFund['chapter_code'];
          $params['fund'] = $chapterFund['fund_code'];
        }
        self::saveChapterFund($params);
      }
      // Add chapter code for contribution as well.
      $chapterFund = self::getChapterFund($chapter, "civicrm_contribution_page")['chapter_code'];
      $params = [
        'entity_id' => $entityId,
        'entity_id' => "civicrm_contribution",
        'chapter' => $chapterFund['chapter_code'],
        'fund' => $chapterFund['fund_code'],
      ];
      self::saveChapterFund($params);
    }
    if (in_array($entityTable, [
      "civicrm_price_set",
      "civicrm_event",
      "civicrm_membership_type",
      "civicrm_contribution_page",
      "civicrm_price_field_value",
    ])) {
      $params = [
        'entity_id' => $entityId,
        'entity_id' => $entityTable,
        'chapter' => $chapter,
        'fund' => $fund,
      ];
      self::saveChapterFund($params);
    }
    if ($entityTable == "civicrm_price_field") {
      // We save the same for price field and price field value.
      $params = [
        'entity_id' => $entityId,
        'entity_id' => $entityTable,
        'chapter' => $chapter,
        'fund' => $fund,
      ];
      self::saveChapterFund($params);

      // Price Field Value
      $params = [
        'entity_id' => CRM_Core_DAO::singleValueQuery("SELECT id FROM civicrm_price_field_value WHERE price_field_id = {$entityId}"),
        'entity_id' => "civicrm_price_field_value",
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
            'entity_id' => "civicrm_price_field_value",
            'chapter' => $chapters[$chapterKey],
            'fund' => $funds[$chapterKey],
          ];
        }
      }
      else {
        $params = [
          'entity_id' => $entityId,
          'entity_id' => $entityTable,
          'chapter' => $chapter,
          'fund' => $fund,
        ];
        self::saveChapterFund($params);
      }
    }
  }

  public function getChapterFund($entityId, $entityTable) {
    $chapterFundCode = CRM_Core_DAO::executeQuery("SELECT chapter_code, fund_code FROM civicrm_chapter_entity WHERE entity_id = {$entityId} AND entity_table = '{$entityTable}'")->fetchAll()[0];
    return ['chapter_code' => $chapterFundCode['chapter_code'], 'fund_code' => $chapterFundCode['fund_code']];
  }

  public function saveChapterFund($params) {
    $eft = new CRM_EFT_BAO_EFT();
    $eft->entity_id = $params['entity_id'];
    $eft->entity_table = $params['entity_table'];
    $eft->find(TRUE);
    $eft->chapter_code = $params['chapter'];
    $eft->fund_code = $params['fund'];
    $eft->save();
  }
}
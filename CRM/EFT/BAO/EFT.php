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

  public function addChapterFund($chapter, $entityId, $entityTable, $isPriceSet = FALSE) {
    if ($entityTable == "civicrm_line_item") {
      $lineItems = civicrm_api3('LineItem', 'get', [
        'contribution_id' => $entityId,
      ])['values'];
      foreach ($lineItems as $lineItem) {
        // Retrieve chapters and funds.
        $lineItemChapter = CRM_Core_DAO::singleValueQuery("SELECT
          chapter_code
          FROM civicrm_chapter_entity
          WHERE entity_table = 'civicrm_price_field_value'
          AND entity_id = {$lineItem['price_field_value_id']}");
        if ($lineItemChapter) {
          $eft = new CRM_EFT_BAO_EFT();
          $eft->entity_id = $lineItem['id'];
          $eft->entity_table = $entityTable;
          $eft->find(TRUE);
          $eft->chapter_code = $lineItemChapter;
          $eft->save();
        }
      }
      if (!$isPriceSet && $chapter) {
        // Add chapter code for contribution as well.
        $eft = new CRM_EFT_BAO_EFT();
        $eft->entity_id = $entityId;
        $eft->entity_table = "civicrm_contribution";
        $eft->find(TRUE);
        $eft->chapter_code = $chapter;
        $eft->save();
      }
    }
    if ($entityTable == "civicrm_contribution_page_online") {
      $lineItems = civicrm_api3('LineItem', 'get', [
        'contribution_id' => $entityId,
      ])['values'];
      foreach ($lineItems as $lineItem) {
        // Retrieve chapters and funds.
        $lineItemChapter = CRM_Core_DAO::singleValueQuery("SELECT
          chapter_code
          FROM civicrm_chapter_entity
          WHERE entity_table = 'civicrm_price_field_value'
          AND entity_id = {$lineItem['price_field_value_id']}");
        if ($lineItemChapter) {
          $eft = new CRM_EFT_BAO_EFT();
          $eft->entity_id = $lineItem['id'];
          $eft->entity_table = "civicrm_line_item";
          $eft->find(TRUE);
          $eft->chapter_code = $lineItemChapter;
          $eft->save();
        }
      }
      // Add chapter code for contribution as well.
      $eft = new CRM_EFT_BAO_EFT();
      $eft->entity_id = $entityId;
      $eft->entity_table = "civicrm_contribution";
      $eft->find(TRUE);
      $eft->chapter_code = self::getChapterFund($chapter, "civicrm_contribution_page")['chapter_code'];
      $eft->save();
    }
    if (in_array($entityTable, [
      "civicrm_price_set",
      "civicrm_event",
      "civicrm_membership_type",
      "civicrm_contribution_page",
      "civicrm_price_field_value",
    ])) {
      $eft = new CRM_EFT_BAO_EFT();
      $eft->entity_id = $entityId;
      $eft->entity_table = $entityTable;
      $eft->find(TRUE);
      $eft->chapter_code = $chapter;
      $eft->save();
    }
    if ($entityTable == "civicrm_price_field") {
      // We save the same for price field and price field value.
      $eft = new CRM_EFT_BAO_EFT();
      $eft->entity_id = $entityId;
      $eft->entity_table = $entityTable;
      $eft->find(TRUE);
      $eft->chapter_code = $chapter;
      $eft->save();

      // Price Field Value
      $eft = new CRM_EFT_BAO_EFT();
      $eft->entity_id = CRM_Core_DAO::singleValueQuery("SELECT id FROM civicrm_price_field_value WHERE price_field_id = {$entityId}");
      $eft->entity_table = "civicrm_price_field_value";
      $eft->find(TRUE);
      $eft->chapter_code = $chapter;
      $eft->save();
    }
    if ($entityTable == "civicrm_price_field_value") {
      if ($isPriceSet == TRUE) {
        $priceFieldValues = CRM_Core_DAO::executeQuery("SELECT id, label FROM civicrm_price_field_value WHERE price_field_id = {$entityId}")->fetchAll();
        $priceLabels = $chapter['option_label'];
        $chapters = $chapter['option_chapter_code'];
        foreach ($priceFieldValues as $key => $priceFieldValue) {
          $chapterKey = array_search($priceFieldValue['label'], $priceLabels);
          $eft = new CRM_EFT_BAO_EFT();
          $eft->entity_id = $priceFieldValue['id'];
          $eft->entity_table = "civicrm_price_field_value";
          $eft->find(TRUE);
          $eft->chapter_code = $chapters[$chapterKey];
          $eft->save();
        }
      }
      else {
        $eft = new CRM_EFT_BAO_EFT();
        $eft->entity_id = $entityId;
        $eft->entity_table = $entityTable;
        $eft->find(TRUE);
        $eft->chapter_code = $chapter;
        $eft->save();
      }
    }
  }

  public function getChapterFund($entityId, $entityTable) {
    $chapterCode = CRM_Core_DAO::singleValueQuery("SELECT chapter_code FROM civicrm_chapter_entity WHERE entity_id = {$entityId} AND entity_table = '{$entityTable}'");
    return ['chapter_code' => $chapterCode];
  }
}
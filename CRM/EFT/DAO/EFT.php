<?php
/**
 *
 * @package CRM
 * @copyright JMAConsulting (c)
 *
 * DO NOT EDIT.  Generated by CRM_Core_CodeGen
 */
require_once 'CRM/Core/DAO.php';
require_once 'CRM/Utils/Type.php';
class CRM_EFT_DAO_EFT extends CRM_Core_DAO
{
  /**
   * static instance to hold the table name
   *
   * @var string
   * @static
   */
  static $_tableName = 'civicrm_chapter_entity';
  /**
   * static instance to hold the field values
   *
   * @var array
   * @static
   */
  static $_fields = null;
  /**
   * static instance to hold the keys used in $_fields for each field.
   *
   * @var array
   * @static
   */
  static $_fieldKeys = null;
  /**
   * static instance to hold the FK relationships
   *
   * @var string
   * @static
   */
  static $_links = null;
  /**
   *
   * @var int unsigned
   */
  public $id;
  /**
   * Entity ID.
   *
   * @var int unsigned
   */
  public $entity_id;
  /**
   * Entity Table.
   *
   * @var string
   */
  public $entity_table;
  /**
   * Chapter Code.
   *
   * @var int
   */
  public $chapter_code;
  /**
   * Fund Code.
   *
   * @var int
   */
  public $fund_code;

  /**
   * class constructor
   *
   * @access public
   * @return civicrm_mailing
   */
  function __construct()
  {
    $this->__table = 'civicrm_chapter_entity';
    parent::__construct();
  }
  /**
   * return foreign keys and entity references
   *
   * @static
   * @access public
   * @return array of CRM_Core_Reference_Interface
   */
  static function getReferenceColumns()
  {
    if (!self::$_links) {
      self::$_links = static ::createReferenceColumns(__CLASS__);
    }
    return self::$_links;
  }
  /**
   * returns all the column names of this table
   *
   * @access public
   * @return array
   */
  static function &fields()
  {
    if (!(self::$_fields)) {
      self::$_fields = array(
        'id' => array(
          'name' => 'id',
          'type' => CRM_Utils_Type::T_INT,
          'required' => TRUE,
          'title' => ts('ID'),
        ),
        'entity_id' => array(
          'name' => 'entity_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Entity ID'),
        ),
        'entity_table' => array(
          'name' => 'entity_table',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('Entity Table'),
        ),
        'chapter_code' => array(
          'name' => 'chapter_code',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Chapter Code'),
        ),
        'fund_code' => array(
          'name' => 'fund_code',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Fund Code'),
        ),
      );
    }
    return self::$_fields;
  }
  /**
   * Returns an array containing, for each field, the arary key used for that
   * field in self::$_fields.
   *
   * @access public
   * @return array
   */
  static function &fieldKeys()
  {
    if (!(self::$_fieldKeys)) {
      self::$_fieldKeys = array(
        'id' => 'id',
        'entity_id' => 'entity_id',
        'entity_table' => 'entity_table',
        'chapter_code' => 'chapter_code',
        'fund_code' => 'fund_code',
      );
    }
    return self::$_fieldKeys;
  }
  /**
   * returns the names of this table
   *
   * @access public
   * @static
   * @return string
   */
  static function getTableName()
  {
    return self::$_tableName;
  }
}

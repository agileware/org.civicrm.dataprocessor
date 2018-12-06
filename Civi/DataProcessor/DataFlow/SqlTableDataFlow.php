<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\DataFlow;

use Civi\DataProcessor\DataSpecification\DataSpecification;
use Civi\DataProcessor\DataSpecification\FieldSpecification;

class SqlTableDataFlow extends SqlDataFlow {

  /**
   * @var string
   *   The name of the database table
   */
  protected $table;

  /**
   * @var string
   *   The alias of the database table
   */
  protected $table_alias;


  public function __construct($table, $table_alias) {
    parent::__construct();
    $this->table = $table;
    $this->table_alias = $table_alias;
  }

  public function getName() {
    return $this->table_alias;
  }

  /**
   * Returns the From Statement.
   *
   * @return string
   */
  public function getFromStatement() {
    return "FROM `{$this->table}` `{$this->table_alias}`";
  }

  /**
   * Returns an array with the fields for in the select statement in the sql query.
   *
   * @return string[]
   * @throws \Civi\DataProcessor\DataSpecification\FieldExistsException
   */
  public function getFieldsForSelectStatement() {
    $fields = array();
    foreach($this->getDataSpecification()->getFields() as $field) {
      $fields[] = "`{$this->table_alias}`.`{$field->name}` AS `{$field->alias}`";
    }
    return $fields;
  }

  /**
   * Returns an array with the fields for in the group by statement in the sql query.
   *
   * @return string[]
   */
  public function getFieldsForGroupByStatement() {
    $fields = array();
    foreach($this->aggregateFields as $field) {
      $fields[] = "`{$this->table_alias}`.`{$field->name}`";
    }
    return $fields;
  }

  /**
   * @return string
   */
  public function getTable() {
    return $this->table;
  }

  /**
   * @return string
   */
  public function getTableAlias() {
    return $this->table_alias;
  }

}
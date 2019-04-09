<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\Source\Contact;

use Civi\DataProcessor\DataSpecification\DataSpecification;
use Civi\DataProcessor\Source\AbstractCivicrmEntitySource;

use CRM_Dataprocessor_ExtensionUtil as E;

class ContactSource extends AbstractCivicrmEntitySource {

  /**
   * Returns the entity name
   *
   * @return String
   */
  protected function getEntity() {
    return 'Contact';
  }

  /**
   * Returns the table name of this entity
   *
   * @return String
   */
  protected function getTable() {
    return 'civicrm_contact';
  }

  /**
   * @return \Civi\DataProcessor\DataSpecification\DataSpecification
   * @throws \Exception
   */
  public function getAvailableFilterFields() {
    if (!$this->availableFilterFields) {
      $this->availableFilterFields = new DataSpecification();
      $this->loadFields($this->availableFilterFields);
      $this->loadCustomGroupsAndFields($this->availableFilterFields, true);
    }
    return $this->availableFilterFields;
  }

  /**
   * Returns the default configuration for this data source
   *
   * @return array
   */
  public function getDefaultConfiguration() {
    return array(
      'filter' => array(
        'is_deleted' => array (
          'op' => '=',
          'value' => '0',
        )
      )
    );
  }

}
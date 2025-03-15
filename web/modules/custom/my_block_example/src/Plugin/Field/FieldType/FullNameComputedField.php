<?php
namespace Drupal\my_block_example\Plugin\Field\FieldType;
use Drupal\Core\Field\FieldItemList;
use Drupal\Core\TypedData\ComputedItemListTrait;
/**
 * Full name computed field class.
 */
class FullNameComputedField extends FieldItemList {
  use ComputedItemListTrait;
  /**
   * Computes the values for an item list.
   */
  protected function computeValue() {
    $fullName = '';
    $entity = $this->getEntity();
    $item =  $this->createItem(0,$entity->get('title')->value);
    $this->list[0] = $item;

  }
}

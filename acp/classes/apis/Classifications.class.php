<?php
/**
 * Classifications
 * Admin API
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */
class Classifications extends API
{
  /**
   * Add a classification.
   * @param string $name The name of the classification.
   * @return boolean|integer
   */
  public function add($name)
  {
    // Remove tags
    $name = strip_tags($name);
    $viewFile = strip_tags($viewFile);
  
    $this->db->insert('bf_classifications', array(
                       'name' => $name
                     ))->execute();
                     
    return $this->db->insertId;
  }

  /**
   * Remove a classification.
   * @param integer $classificationID The ID of the classification to remove.
   * @return boolean
   */
  public function remove($classificationID)
  {
    // Remove the classification
    $this->db->delete('bf_classifications')
             ->where("`id` = '{1}'", $classificationID)
             ->limit(1)
             ->execute();
             
    // Remove data from attributes
    $this->db->delete('bf_item_attribute_applications')
             ->where('`classification_attribute_id` IN (SELECT `id` FROM ' .
                     '`bf_classification_attributes` WHERE `classification_id` = \'{1}\')',
                     $classificationID)
             ->execute(); 
             
    // Remove default attributes
    $this->db->delete('bf_classification_attributes')
             ->where('`classification_id` = \'{1}\'', $classificationID)
             ->execute();
             
    // Update items to default classification
    $this->db->update('bf_items', array(
                       'classification_id' => '-1' 
                     ))
             ->where('`classification_id` = \'{1}\'', $classificationID)
             ->execute();
               
    return true;
  }
  
  /**
   * Remove an attribute with the specified ID and all it's data
   * @param integer $attributeID The ID of the classification attribute to remove.
   * @return boolean
   */
  public function removeAttribute($attributeID)
  {
    // Remove data from attribute
    $this->db->delete('bf_item_attribute_applications')
             ->where('`classification_attribute_id` = \'{1}\'', $attributeID)
             ->execute(); 
             
    // Remove attribute
    $this->db->delete('bf_classification_attributes')
             ->where('`id` = \'{1}\'', $attributeID)
             ->limit(1)
             ->execute(); 
    
    return true;
  }
  
  /**
   * Add an attribute to a specified existing classification.
   * @param string $attributeName The name of the new attribute.
   * @param integer $classificationID The ID of the classification to modify.
   * @return boolean
   */
  public function addAttribute($attributeName, $classificationID)
  {
    // Add a new attribute to a classification
    $this->db->insert('bf_classification_attributes', array(
                 'name' => $attributeName,
                 'classification_id' => $classificationID
               ))
             ->execute(); 
             
    return true;
  }
}
?>
<?php
/**
 * Default Classification Attributes Loader
 * AJAX Responder
 *
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */
 
// Set context
define('BF_CONTEXT_ADMIN', true);

// Relative path for this - no BF_ROOT yet.
require_once('../admin_startup.php');
require_once(BF_ROOT . 'tools.php');

// New BFClass & Admin class
$BF = new BFClass(true);
$BF->admin = new Admin(& $BF);

if(!$BF->admin->isAdmin)
{
  exit();
}

// Get the classification ID
$classificationID = $BF->inInteger('id');

// Parent mode?
$parentMode = ($BF->in('parent') == '1');

// Select default attributes from DB
$BF->db->select('*', 'bf_classification_attributes')
           ->where('classification_id = \'{1}\'', $classificationID)
           ->order('name', 'asc')
           ->execute();

// Build a collection of attributes for outputting
$attributeCollection = array();
           
while($attribute = $BF->db->next())
{
  $attributeCollection[$attribute->id] = array();
  $attributeCollection[$attribute->id]['id'] = $attribute->id;
  $attributeCollection[$attribute->id]['name'] = $attribute->name;       
  $attributeCollection[$attribute->id]['value'] = '';                    
}

// Possible to obtain values for these?
if($BF->in('item_id') && count($attributeCollection) > 0)
{
  // Get values
  $itemID = $BF->inInteger('item_id');
  
  // Compound the IDs of attributes as a CSV
  $attributeCSV = Tools::CSV(array_keys($attributeCollection));
  
  // Search for values
  $BF->db->select('*', ($parentMode ? 'bf_parent_item_attribute_applications' :
                  'bf_item_attribute_applications'))
         ->where('`classification_attribute_id` IN ({1}) AND `' . 
                 ($parentMode ? 'parent_item_id' : 'item_id') . '` = \'{2}\'',
                 $attributeCSV, $itemID)
         ->limit(count($attributeCollection))
         ->execute();
         
  // Assign values
  while($valueRow = $BF->db->next())
  {
    $attributeCollection[$valueRow->classification_attribute_id]['value'] = 
      $valueRow->value;
  }
}

// Write JSON
print json_encode($attributeCollection);

?>
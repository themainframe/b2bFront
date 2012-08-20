<?php
/**
 * Editable Field Modifier
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

// Get the Field, Table, RowID and new value.
$field = $BF->in('field');
$table = $BF->in('table');
$rowID = $BF->inInteger('rowid');
$value = $BF->in('value');

// Cache level move?
$cache = $BF->in('cache');
$BF->log('Cache: ' . $cache);

if($cache && $cache != 'undefined')
{
  // Advance
  $BF->cache->advanceLevel($cache);
}

// Creating classification attribute assignments?
if($table == 'bf_item_attribute_applications')
{
  // Get Item ID and Classification Attribute ID
  $itemID = $BF->inInteger('itemID');
  $classificationAttributeID = $BF->inInteger('classificationID');

  // Check if the value is set
  $check = $BF->db->query();
  $check->select('*', 'bf_item_attribute_applications')
        ->where('`item_id` = \'{1}\' AND `classification_attribute_id` = \'{2}\'',
          $itemID, $classificationAttributeID)
        ->limit(1)
        ->execute();
        
  $BF->log('Check: ' . $check->count);

  // Allow creation if no row is found
  if($check->count == 0)
  {
    // Create a row
    $newApplication = $BF->db->query();
    $newApplication->insert('bf_item_attribute_applications', array(
                       'value' => $value,
                       'item_id' => $itemID,
                       'classification_attribute_id' => $classificationAttributeID
                     ))
                   ->execute();
  
  }
  else
  {
    // Update it
    $update = $BF->db->query();
    $update->update('bf_item_attribute_applications', array(
               'value' => $value
             ))
           ->where('`item_id` = \'{1}\' AND `classification_attribute_id` = \'{2}\'',
             $itemID, $classificationAttributeID)
           ->limit(1)
           ->execute();
           
    $BF->log('Affected with Update: ' . $itemID . ' and ' . $classificationAttributeID);
  }
  
  // New value    
  print $value;
  
  // Finished
  $BF->shutdown();
  exit();  
}
else
{
  // Update the field in the appropriate row
  $BF->db->update($table, array(
                       $field => $value
                     ))
             ->where('id = \'{1}\'', $rowID)
             ->limit(1)
             ->execute();
        
  // Get the value back to obtain any transforms
  $transform = $BF->db->query();
  $transform->select($field, $table)
            ->where('id = \'{1}\'', $rowID)
            ->limit(1)
            ->execute();
}
           
if($transform->count == 1)
{
  print $transform->next()->{$field};
}

// Remove the row from the cache
$BF->cache->removeRow($table, $rowID);

$BF->shutdown();

?>
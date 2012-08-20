<?php
/**
 * Parent Item Details Loader
 *
 * Given a parent item ID and variation option values deturmines if
 * the proposed item exists, provides refreshed variation options as
 * a JSON response.
 *
 * AJAX Responder
 *
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */

// Set context
define('BF_CONTEXT_INDEX', true);

// Relative path for this - no BF_ROOT yet.
require_once('../startup.php');
require_once(BF_ROOT . 'tools.php');

// New BFClass & Admin class
$BF = new BFClass(true);

// Verify that I am logged in
if(!$BF->security->loggedIn())
{
  // Not authenticated
  $BF->shutdown();
}

// Get the ID of the parent item
$ID = $BF->inInteger('id');

// Get the chosen variation ID
$keyVariationKey = $BF->inInteger('variationKey');

// Get the ID of the chosen variation value
$keyVariationValue = $BF->in('variationValue');

// Set content type of output
header('Content-Type: text/plain');
       
// Find variation options
$variationOptions = $BF->db->query();
$variationOptions->select('*', 'bf_parent_item_variations')
                 ->where('`parent_item_id` = \'{1}\'', $ID)
                 ->execute();
  
// Copy variation options in memory
$variations = array();
while($variationOption = $variationOptions->next())
{
  // Ignore current variationKey
  if($variationOption->id == $keyVariationKey)
  {
    continue;
  }
      
  $variations[$variationOption->id]['name'] = $variationOption->name;
  $variations[$variationOption->id]['id'] = $variationOption->id;
  $variations[$variationOption->id]['values'] = array();
}
  
// Find all child items
$childItems = $BF->db->query();
$childItems->select('*', 'bf_items')
           ->where('`parent_item_id` = \'{1}\'', $ID)
           ->execute();
  
$items = array();               
while($childItem = $childItems->next())
{
  $variationValues = $BF->db->query();
  $variationValues->select('*', 'bf_parent_item_variation_data')
                  ->where('`item_id` = \'{1}\'', $childItem->id)
                  ->order('parent_item_variation_id', 'ASC')
                  ->execute();
                  
  while($variationValue = $variationValues->next())
  {
    $items[$childItem->id][$variationValue->parent_item_variation_id] = 
      array('key' => $variationValue->id, 'value' => $variationValue->value);
  }
}
                               
// Build possible values matrix
$options = array();

foreach($items as $id => $item)
{
  // If this item is appropriate with the current selection
  if($item[$keyVariationKey]['value'] == $keyVariationValue)
  {
    // For each of it's variation option values
    foreach($item as $variationKey => $variationValue)
    {
      // Except the governing one (the current selection)
      if($variationKey == $keyVariationKey)
      {
        continue;
      }
      
      // Set the value in the hash
      $variations[$variationKey]['values'][$variationValue['key']] = $variationValue['value'];
    }
  }
}

// Output
print json_encode($variations);

// Exit
$BF->shutdown();

?>
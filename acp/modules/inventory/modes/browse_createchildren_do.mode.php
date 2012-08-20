<?php
/**
 * Module: Inventory
 * Mode: Do Create Multiple Child Items
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */

// Context check
if(!defined("BF_CONTEXT_ADMIN") || !defined("BF_CONTEXT_MODULE"))
{
  exit();
}


// Preload the parent item we are using as the template
$parentItemID = $BF->inInteger('f_parent_item_id');
$BF->db->select('*', 'bf_parent_items')
       ->where('`id` = \'{1}\'', $parentItemID)
       ->limit(1)
       ->execute();
       
// Valid?
if($BF->db->count != 1)
{
  // Return to browse page
  $BF->go('./acp/?act=inventory&mode=parents');
  exit();
}

// Buffer
$parentItem = $BF->db->next();

// Load the parent item variations
$BF->db->select('*', 'bf_parent_item_variations')
       ->where('`parent_item_id` = \'{1}\'', $parentItemID)
       ->execute();
      
// Buffer
$variations = array();
while($variation = $BF->db->next())
{
  $variations[$variation->id] = (array)$variation;
}

// Count how many to create 
$requestedCount = $BF->inInteger('f_row_count');
$createCount = 0;

// For each, load data and add item
for($itemID = 1; $itemID <= $requestedCount; $itemID ++)
{
  // Build the validation array
  $validation = array(
    
      'sku' => array(
      
                 'validations' => array(
                                   'unique' => array('bf_items', 'sku'),
                                   'done' => array()
                                  ),
                                  
                 'value' => $BF->in('sku_' . $itemID),
                 
                 'name' => 'SKU'
                     
               ),

    'name' => array(
    
               'validations' => array(
                                 'max' => array(90),
                                 'done' => array(),
                                 'min' => array(5)
                                ),
                                
               'value' => $BF->in('name_' . $itemID),
               
               'name' => 'Name'
                   
              ),
              
    'trade_price'  => array(
    
               'validations' => array(
                                 'numeric' => array(),
                                 'pos' => array(),
                                 'done' => array()
                                ),
                                
               'value' => $BF->in('trade_price_' . $itemID),
               
               'name' => 'Trade Price'
                   
              ),
              
    'pro_net_price'  => array(
    
               'validations' => array(
                                 'numeric' => array(),
                                 'pos' => array(),
                                 'done' => array()
                                ),
                                
               'value' => $BF->in('pro_net_price_' . $itemID),
               
               'name' => 'Pro Net Price'
                   
              ),
          
    'rrp_price'  => array(
    
               'validations' => array(
                                 'numeric' => array(),
                                 'pos' => array(),
                                 'done' => array()
                                ),
                                
               'value' => $BF->in('rrp_price_' . $itemID),
               
               'name' => 'RRP / MSRP'
                   
              )
  ); 
    
  // Check each field
  foreach($validation as $fieldName => $fieldData)
  {
    // Create a validator
    $validator = new FormValue($fieldData['value'], $fieldData['name'], & $BF);
  
    // Check
    if($validator->batch($fieldData['validations'])->failed())
    {
      // Failed - Skip Item
      continue 2;
    }
  }
  
  // Perform insertion
  $result = $BF->admin->api('Items')
                          ->add(
                                 $BF->in('sku_' . $itemID), 
                                 stripslashes($BF->in('name_' . $itemID)),
                                 Tools::price($BF->in('trade_price_' . $itemID)),
                                 Tools::price($BF->in('pro_net_price_' . $itemID)),
                                 $parentItem->pro_net_qty,
                                 $parentItem->wholesale_price,
                                 Tools::price($BF->in('rrp_price_' . $itemID)),
                                 0,
                                 0,
                                 0,
                                 $parentItem->cost_price,
                                 '',
                                 $parentItem->description,
                                 $parentItem->classification_id,
                                 $parentItem->category_id,
                                 $parentItem->subcategory_id,
                                 $parentItem->brand_id,
                                 '',
                                 $parentItemID
                               );
  
  // Skip if result failed:
  if($result)
  {
    // Success
    $createCount ++;
  }
  else
  {
    continue;
  }
  
  // Create variation applications for this item
  foreach($variations as $id => $variation)
  {
    $BF->db->insert('bf_parent_item_variation_data', array(
                     'item_id' => $result,
                     'parent_item_variation_id' => $id,
                     'value' => $BF->in('var' . $id . '_' . $itemID),
                     
                     // Also cross-link with parent item
                     'parent_item_id' => $parentItemID
                   ))
           ->execute();
  }
}


if($result !== false)
{
  $BF->admin->notifyMe('OK', $createCount . ' item' . ($createCount == 1 ? ' was' : 's were') . ' created.');
  header('Location: ./?act=inventory&mode=browse');
}

?>
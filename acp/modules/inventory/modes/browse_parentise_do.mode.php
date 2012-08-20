<?php
/**
 * Module: Inventory
 * Mode: Browse Parentise - Do create parent item
 *       Redirect to create children interface after.
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

// Get the ID
$ID = $BF->inInteger('f_id');

// Get the row information
$BF->db->select('*', 'bf_items')
           ->where('id = \'{1}\'', $ID)
           ->limit(1)
           ->execute();
           
// Check the ID was valid
if($BF->db->count < 1)
{
  // Return the user to the selection interface
  $BF->admin->notifyMe('Error', 'The item no longer exists.', 'cross-circle.png');
  header('Location: ./?act=inventory&mode=browse');
  exit();
}

// Retrieve the row
$row = $BF->db->next();

// Uppercase SKU only
$BF->setIn('f_sku', strtoupper($BF->in('f_sku')));

// Build the validation array
$validation = array(
  
    'sku'  => array(
    
               'validations' => array(
                                 'done' => array(),
                                 'unique' => array('bf_parent_items', 'sku'),
                                 'max' => array(7),
                                 'min' => array(2)
                                ),
                                
               'value' => $BF->in('f_sku'),
               
               'name' => 'SKU'
                   
              ),
  
    'name' => array(
    
               'validations' => array(
                                 'done' => array()
                                ),
                                
               'value' => $BF->in('f_name'),
               
               'name' => 'Name'
                   
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
    // Failed - Pack up fields and redirect
    $BF->admin->packAndRedirect('./?act=inventory&mode=browse_parentise&id=' . $row->id,
                                    $fieldName, (string)$validator);
                                    
    exit();
  }
}

//
// Create parent item
//

$result = $BF->admin->api('Items')
                    ->addParent(
                                 $BF->in('f_sku') . '-PAR', 
                                 stripslashes($BF->in('f_name')),
                                 Tools::price($row->trade_price),
                                 Tools::price($row->pro_net_price),
                                 $row->pro_net_qty,
                                 Tools::price($row->wholesale_price),
                                 Tools::price($row->rrp_price),
                                 Tools::price($row->cost_price),
                                 stripslashes($BF->inUnfiltered('description')),
                                 $row->classification_id,
                                 $row->category_id,
                                 $row->subcategory_id,
                                 $row->brand_id
                               );

//
// Remove existing item
//

$BF->admin->api('Items')
          ->remove($row->id);

//
// Add variations
//

$variations = explode("\n", $BF->in('variations'));

foreach($variations as $variation)
{
  // Check emptyness
  if(trim($variation) == '')
  {
    continue;
  }

  // Insert:
  $BF->db->insert('bf_parent_item_variations',
                  array(
                    'name' => $variation,
                    'parent_item_id' => $result
                  )
                 )
         ->execute();
}

if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The parent item was created.');
  header('Location: ./?act=inventory&mode=browse_createchildren&id=' . $result);
}

?>
<?php
/**
 * Module: Inventory
 * Mode: Browse Modify Parent Item Do
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

// Uppercase SKUs only
$BF->setIn('f_sku', strtoupper($BF->in('f_sku')));

// Get the Old SKU for comparison with the new value to ensure
// duplicate SKU creation attempts do not take place
$oldSKU = strtoupper($BF->in('f_old_sku'));

// Create validations for SKU
$SKUvalidations = array(               
                         'min' => array(2)
                        );

// Check changes in SKU
if(strtoupper($BF->in('f_sku')) != $oldSKU)
{
  // Require a check for unique SKU
  $SKUvalidations['unique'] = array('bf_parent_items', 'sku');
}


$SKUvalidations['done'] = array();

// Build the validation array
$validation = array(
  
    'sku'  => array(
    
               'validations' => $SKUvalidations,
                                
               'value' => $BF->in('f_sku'),
               
               'name' => 'SKU'
                   
              ),
  
    'name' => array(
    
               'validations' => array(
                                 'max' => array(90),
                                 'min' => array(5),
                                 'done' => array()
                                ),
                                
               'value' => $BF->in('f_name'),
               
               'name' => 'Name'
                   
              ),
              
    'trade_price'  => array(
    
               'validations' => array(
                                 'numeric' => array(),
                                 'pos' => array(),
                                 'done' => array()
                                ),
                                
               'value' => $BF->in('f_trade_price'),
               
               'name' => 'Trade Price'
                   
              ),
              
    'pro_net_price'  => array(
    
               'validations' => array(
                                 'numeric' => array(),
                                 'pos' => array(),
                                 'done' => array()
                                ),
                                
               'value' => $BF->in('f_pro_net_price'),
               
               'name' => 'Pro Net Price'
                   
              ),
              
    'pro_net_qty'  => array(
    
               'validations' => array(
                                 'numeric' => array(),
                                 'pos' => array(),
                                 'done' => array()
                                ),
                                
               'value' => $BF->in('f_pro_net_qty'),
               
               'name' => 'Pro Net Quantity'
                   
              ),
              
    'wholesale_price'  => array(
    
               'validations' => array(
                                 'numeric' => array(),
                                 'pos' => array(),
                                 'done' => array()
                                ),
                                
               'value' => $BF->in('f_wholesale_price'),
               
               'name' => 'Wholesale Price'
                   
              ),

    'rrp_price'  => array(
    
               'validations' => array(
                                 'numeric' => array(),
                                 'pos' => array(),
                                 'done' => array()
                                ),
                                
               'value' => $BF->in('f_rrp_price'),
               
               'name' => 'RRP / MSRP'
                   
              ),
              
    'cost_price'  => array(
    
               'validations' => array(
                                 'numeric' => array(),
                                 'pos' => array()
                                ),
                                
               'value' => $BF->in('f_cost_price'),
               
               'name' => 'Cost Price'
                   
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
    // Unset the images first
    $BF->setIn('f_image_list', '');

  
    // Failed - Pack up fields and redirect
    $BF->admin->packAndRedirect('./?act=inventory&mode=browse_modify_parent&id=' .
                                $BF->inInteger('f_id'), $fieldName, (string)$validator);
           
    print($validator);
    exit();                               
  }
}

// Remove any drafts
$draftName = 'Draft of description for ' . $BF->in('f_sku');
$BF->db->delete('bf_admin_drafts')
           ->where('description = \'{1}\' AND admin_id = {2}', $draftName, $BF->admin->AID)
           ->limit(1)
           ->execute();

// Copy the target ID, integer-casted
$targetItemID = intval($BF->inInteger('f_id'));

//
// Because of the granularity of this modification process, and the need
// for customisation based on business model, this section will be
// performed stage-by-stage without a single, large API call as is usual.
//

// Non-Child-Optional values
$BF->db->update('bf_parent_items', array(
                 'sku' => strtoupper($BF->in('f_sku')),
                 'name' => stripslashes($BF->in('f_name')),
                 'description' => stripslashes($BF->inUnfiltered('description'))
               ))
       ->where('`id` = \'{1}\'', $targetItemID)
       ->limit(1)
       ->execute();
       
// Child-Optional values
$BF->db->update('bf_parent_items', array(
                 'classification_id' => $BF->inInteger('f_classification'),
                 'category_id' => $BF->inInteger('f_category'),
                 'subcategory_id' =>$BF->inInteger('f_subcategory'),
                 'brand_id' => $BF->inInteger('f_brand'),
                 'trade_price' => Tools::price($BF->in('f_trade_price')),
                 'pro_net_price' => Tools::price($BF->in('f_pro_net_price')),
                 'pro_net_qty' => $BF->inInteger('f_pro_net_qty'),
                 'wholesale_price' => Tools::price($BF->in('f_wholesale_price')),
                 'rrp_price' => Tools::price($BF->in('f_rrp_price')),
                 'cost_price' => Tools::price($BF->in('f_cost_price'))
               ))
       ->where('`id` = \'{1}\'', $targetItemID)
       ->limit(1)
       ->execute();

//  Child-Optional values of Child Items

$childOptions = array(
  'trade_price',
  'pro_net_price',
  'pro_net_qty',
  'wholesale_price',
  'rrp_price',
  'cost_price'
);

// Collect changes to child items
// These are changes that will be applied to ALL child items
$childChanges = array(
  'classification_id' => $BF->inInteger('f_classification'),
  'category_id' => $BF->inInteger('f_category'),
  'brand_id' => $BF->inInteger('f_brand'),
  'description' => stripslashes($BF->inUnfiltered('description'))
);

// Detect each
foreach($childOptions as $childOption)
{
  if($BF->in('f_' . $childOption . '_ud') == '1')
  {
    // Update in children too
    // Automatically return a price if required
    $childChanges[$childOption] = 
      (Tools::contains('price', $childOption) ? 
       Tools::price($BF->in('f_' . $childOption)) : 
       $BF->in('f_' . $childOption));
  }
}

// If changes are required, make them to all child items.
if(!empty($childChanges))
{
  $BF->db->update('bf_items', $childChanges)
         ->where('`parent_item_id` = \'{1}\'', $targetItemID)
         ->execute();
}

//
// Attributes
//

// Delete all current attribute data
$BF->db->delete('bf_parent_item_attribute_applications')
       ->where('`parent_item_id` = \'{1}\'', $targetItemID)
       ->execute();
       
// Get list of attributes
$attributeList = 
  Tools::removeEmptyEntries(Tools::unCSV($BF->in('f_attr_list')));

// For each value, create a new attribute data entry
foreach($attributeList as $attributeID)
{
  $BF->db->insert('bf_parent_item_attribute_applications', array(
                    'value' => $BF->in('f_attr_' . $attributeID),
                    'classification_attribute_id' => $attributeID,
                    'parent_item_id' => $targetItemID
                 ))
         ->execute();
}

// No validation for success
$result = true;

// Uncache the row
$BF->cache->removeRow('bf_parent_items', $BF->in('f_id'));

if($result !== false)
{
  $BF->admin->notifyMe('OK', 'Changes to ' . $BF->in('f_sku') . ' were saved.');
  header('Location: ./?act=inventory');
}

?>
<?php
/**
 * Module: Inventory
 * Mode: Browse Modify Do
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
  $SKUvalidations['unique'] = array('bf_items', 'sku');
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
              
    'stock_free'  => array(
    
               'validations' => array(
                                 'numeric' => array(),
                                 'done' => array()
                                ),
                                
               'value' => $BF->in('f_stock_free'),
               
               'name' => 'Free Stock'
                   
              ),

    'stock_held'  => array(
    
               'validations' => array(
                                 'numeric' => array(),
                                 'done' => array()
                                ),
                                
               'value' => $BF->in('f_stock_held'),
               
               'name' => 'Held Stock'
                   
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
    $BF->admin->packAndRedirect('./?act=inventory&mode=browse_modify',
                                    $fieldName, (string)$validator);
           
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

// Determine if this is a child item
$itemRow = $BF->db->getRow('bf_items', $targetItemID);
$isChild = ($itemRow->parent_item_id != -1);

// Get the parent row
if($isChild)
{
  $parentItemRow = 
    $BF->db->getRow('bf_parent_items', $itemRow->parent_item_id);
}

// Get stock due date
$stockDate = strtotime($BF->in('f_stock_date'));

// Past?
if($stockDate < time())
{
  $stockDate = ' ';
}

// Passed validation
$result = $BF->admin->api('Items')
                    ->modify(
                             $targetItemID,                                     // KEY : Item ID
                             $BF->in('f_sku'),                                  // New SKU
                             stripslashes($BF->in('f_name')),                   // New Name
                             Tools::price($BF->in('f_trade_price')),            // New Trade Price
                             Tools::price($BF->in('f_pro_net_price')),          // New Pro Net Price
                             $BF->in('f_pro_net_qty'),                          // New Pro Net QTY
                             Tools::price($BF->in('f_wholesale_price')),        // New WS Price
                             Tools::price($BF->in('f_rrp_price')),              // New RRP Price
                             $BF->in('f_stock_free'),                           // New Free Stock
                             $BF->in('f_stock_held'),                           // New Held Stock
                             $stockDate                        ,                // New Stock Due Date
                             Tools::price($BF->in('f_cost_price')),             // New Cost Price
                             $BF->in('f_barcode'),                              // New Barcode
                             stripslashes($BF->inUnfiltered('description')),    // New Description HTML
                             $BF->inInteger('f_classification'),                // New Classification ID
                             $BF->inInteger('f_category'),                      // New Category ID
                             $BF->inInteger('f_subcategory'),                   // New Subcategory ID
                             $BF->inInteger('f_brand'),                         // New Brand ID
                             str_replace("\n", ',', $BF->in('keywords'))        // New Keywords
                         );

// Remove all tags for this item
$BF->db->delete('bf_item_tag_applications')
       ->where('`item_id` = \'{1}\'', $BF->inInteger('f_id'))
       ->execute();

// Add tags
$itemTags = $BF->db->query();
$itemTags->select('*', 'bf_item_tags')
         ->execute();
         
while($itemTag = $itemTags->next())
{
  // Check each checkbox
  if($BF->in('f_tag_' . $itemTag->id) !== false)
  {
    // Apply tag if checked
    $BF->admin->api('ItemTags')
                  ->applyItemTag($targetItemID, $itemTag->id);
  }
}

//
// Images
//

// Collect the image IDs
$imageIDs = Tools::unCSV($BF->in('f_image_list'));
$imageIDs = Tools::removeEmptyEntries($imageIDs);

// Dissassociate all images from the item
$BF->admin->api('Items')
          ->clearImages($targetItemID);
          
// Associate images
$index = 1;

foreach($imageIDs as $imageID)
{
  $BF->admin->api('Items')
            ->attachImage($targetItemID, $imageID, $index);

  $index ++;
}

//
// Attributes
//

// Delete all current attribute data
$BF->db->delete('bf_item_attribute_applications')
       ->where('`item_id` = \'{1}\'', $targetItemID)
       ->execute();
       
// Get list of attributes
$attributeList = 
  Tools::removeEmptyEntries(Tools::unCSV($BF->in('f_attr_list')));

// For each value, create a new attribute data entry
foreach($attributeList as $attributeID)
{
  $BF->db->insert('bf_item_attribute_applications', array(
                    'value' => $BF->in('f_attr_' . $attributeID),
                    'classification_attribute_id' => $attributeID,
                    'item_id' => $targetItemID
                 ))
         ->execute();
}

//
// Variation Option Values
//

if($isChild)
{
  // Update variation option values
  $variationList = 
    Tools::removeEmptyEntries(Tools::unCSV($BF->in('f_variation_list')));
    
  // For each value, create a new attribute data entry
  foreach($variationList as $variationOptionValueID)
  {
    $BF->db->update('bf_parent_item_variation_data', array(
                      'value' => $BF->in('f_variation_' . $variationOptionValueID)
                   ))
           ->where('`id` = \'{1}\'', $variationOptionValueID)
           ->limit(1)
           ->execute();
  }
}

// Uncache the row
$BF->cache->removeRow('bf_items', $BF->in('f_id'));

if($result !== false)
{
  $BF->admin->notifyMe('OK', 'Changes to ' . $BF->in('f_sku') . ' were saved.');
  header('Location: ./?act=inventory');
}

?>
<?php
/**
 * Module: Inventory
 * Mode: Do Add Inventory Item
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

// Build the validation array
$validation = array(
  
    'sku'  => array(
    
               'validations' => array(
                                 'done' => array(),
                                 'unique' => array('bf_items', 'sku'),
                                 'min' => array(2),
                                 'doesNotContain' => array('-PAR')
                                ),
                                
               'value' => $BF->in('f_sku'),
               
               'name' => 'SKU'
                   
              ),
  
    'name' => array(
    
               'validations' => array(
                                 'max' => array(90),
                                 'done' => array(),
                                 'min' => array(5)
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
    $BF->admin->packAndRedirect('./?act=inventory&mode=add_standard',
                                    $fieldName, (string)$validator);
                                    
    exit();
  }
}

// Remove any drafts
$draftName = 'Draft of description for ' . $BF->in('f_sku');
$BF->db->delete('bf_admin_drafts')
           ->where('description = \'{1}\' AND admin_id = {2}',
                   $draftName, $BF->admin->AID)
           ->limit(1)
           ->execute();


// Get stock due date
$stockDate = strtotime($BF->in('f_stock_date'));

// Past?
if($stockDate < time())
{
  $stockDate = ' ';
}

// Passed validation
$result = $BF->admin->api('Items')
                    ->add(
                           $BF->in('f_sku'), 
                           stripslashes($BF->in('f_name')),
                           Tools::price($BF->in('f_trade_price')),
                           Tools::price($BF->in('f_pro_net_price')),
                           $BF->in('f_pro_net_qty'),
                           Tools::price($BF->in('f_wholesale_price')),
                           Tools::price($BF->in('f_rrp_price')),
                           $BF->in('f_stock_free'),
                           $BF->in('f_stock_held'),
                           $stockDate,
                           Tools::price($BF->in('f_cost_price')),
                           $BF->in('f_barcode'),
                           stripslashes($BF->inUnfiltered('description')),
                           $BF->inInteger('f_classification'),
                           $BF->inInteger('f_category'),
                           $BF->inInteger('f_subcategory'),
                           $BF->inInteger('f_brand'),
                           str_replace("\n", ',', $BF->in('keywords')),
                           $BF->inInteger('f_parent_id')
                         );

// Is this a child item?
if($BF->inInteger('f_parent_id') != -1)
{
  // Get variation data
  $variationList = 
    Tools::removeEmptyEntries(Tools::unCSV($BF->in('f_variation_list')));
  
  // Obtain each
  foreach($variationList as $variationID)
  {
    $variationValue = $BF->in('f_variation_' . $variationID);
    
    // Create a variation data record
    $BF->db->insert('bf_parent_item_variation_data', array(
                      'value' => $variationValue,
                      'item_id' => $result,
                      'parent_item_variation_id' => $variationID,
                      'parent_item_id' => $BF->inInteger('f_parent_id')
                   ))
           ->execute();
  }
}

// Add tags
$itemTags = $BF->db->query();
$itemTags->select('*', 'bf_item_tags')
         ->execute();
         
while($itemTag = $itemTags->next())
{
  if($BF->in('f_tag_' . $itemTag->id) !== false)
  {
    // Apply tag
    $BF->admin->api('ItemTags')
                  ->applyItemTag($result, $itemTag->id);
  }
}

//
// Images
//

// Collect the image IDs
$imageIDs = Tools::unCSV($BF->in('f_image_list'));
$imageIDs = Tools::removeEmptyEntries($imageIDs);
          
// Associate images
$index = 1;

foreach($imageIDs as $imageID)
{
  $BF->admin->api('Items')
            ->attachImage($result, $imageID, $index);

  $index ++;
}

//
// Attributes
//

// Get list of attributes
$attributeList = 
  Tools::removeEmptyEntries(Tools::unCSV($BF->in('f_attr_list')));

// For each value, create a new attribute data entry
foreach($attributeList as $attributeID)
{
  $BF->db->insert('bf_item_attribute_applications', array(
                    'value' => $BF->in('f_attr_' . $attributeID),
                    'classification_attribute_id' => $attributeID,
                    'item_id' => $result
                 ))
         ->execute();
}

if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The item ' . $BF->in('f_sku') . ' was created.');
  header('Location: ./?act=inventory');
}

?>
<?php
/**
 * Module: Inventory
 * Mode: Do Add Parent Inventory Item
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
                                 'unique' => array('bf_parent_items', 'sku'),
                                 'min' => array(2)
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
    $BF->admin->packAndRedirect('./?act=inventory&mode=add_parent',
                                    $fieldName, (string)$validator);
                                    
    exit();
  }
}

// Remove any drafts
$draftName = 'Draft of description for Parent ' . $BF->in('f_sku');
$BF->db->delete('bf_admin_drafts')
           ->where('description = \'{1}\' AND admin_id = {2}', $draftName, $BF->admin->AID)
           ->limit(1)
           ->execute();

//
// Passed validation
//
$result = $BF->admin->api('Items')
                        ->addParent(
                                     $BF->in('f_sku') . '-PAR', 
                                     stripslashes($BF->in('f_name')),
                                     Tools::price($BF->in('f_trade_price')),
                                     Tools::price($BF->in('f_pro_net_price')),
                                     $BF->in('f_pro_net_qty'),
                                     Tools::price($BF->in('f_wholesale_price')),
                                     Tools::price($BF->in('f_rrp_price')),
                                     Tools::price($BF->in('f_cost_price')),
                                     stripslashes($BF->inUnfiltered('description')),
                                     $BF->inInteger('f_classification'),
                                     $BF->inInteger('f_category'),
                                     $BF->inInteger('f_subcategory'),
                                     $BF->inInteger('f_brand')
                                   );

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

//
// Attributes
//

// Get list of attributes
$attributeList = 
  Tools::removeEmptyEntries(Tools::unCSV($BF->in('f_attr_list')));

// For each value, create a new attribute data entry
foreach($attributeList as $attributeID)
{
  $BF->db->insert('bf_parent_item_attribute_applications', array(
                    'value' => $BF->in('f_attr_' . $attributeID),
                    'classification_attribute_id' => $attributeID,
                    'parent_item_id' => $result
                 ))
         ->execute();
}


//
// All done.
//

if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The parent item ' . $BF->in('f_sku') . ' was created.');
  header('Location: ./?act=inventory');
}

?>
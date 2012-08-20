<?php
/**
 * Module: Statistics
 * Mode: Do Add Custom Statistic
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


// Build the validation array
$validation = array(
  
    'item_sku' => array(
    
               'validations' => array(
                                 'exists' => array('bf_items'),
                                 'done' => array()
                                ),
                                
               'value' => $BF->in('f_item_id'),
               
               'name' => 'Item SKU'
                   
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
    $BF->admin->packAndRedirect('./?act=statistics&mode=custom_add',
                                    $fieldName, (string)$validator);
                                    
    exit();
  }
}

// Get item
$item = $BF->db->getRow('bf_items', $BF->inInteger('f_item_id'));

// Description map
$aspectDescriptions = array(
  'item-views' => 'Views',
  'item-searched' => 'Search Clicks'
);

// Add custom statistic
$result = $BF->db->insert('bf_statistics', array(
                           'aftermarket' => 1,
                           'name' => 'com.b2bfront.stats.custom.' . $BF->inInteger('f_item_id') . 
                                     '-' . $BF->in('f_aspect'),
                           'description' => $item->sku . ' ' . 
                                            $aspectDescriptions[$BF->in('f_aspect')],
                           'value' => 0.00,
                           'domain_id' => 5    // "Custom" domain
                        ))
                 ->execute();

if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The custom statistic was created.');
  header('Location: ./?act=statistics&mode=custom');
}

?>
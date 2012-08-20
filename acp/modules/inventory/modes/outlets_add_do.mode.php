<?php
/**
 * Module: Inventory
 * Mode: Do Add Outlet
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
                   
              ),
              
    'dealer_name' => array(
    
               'validations' => array(
                                 'exists' => array('bf_users'),
                                 'done' => array()
                                ),
                                
               'value' => $BF->in('f_dealer_id'),
               
               'name' => 'Dealer'
                   
              ),
              
    'url' => array(
    
               'validations' => array(
                                 'done' => array()
                                ),
                                
               'value' => $BF->in('f_url'),
               
               'name' => 'Outlet URL'
                   
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
    $BF->admin->packAndRedirect('./?act=inventory&mode=outlets_add',
                                    $fieldName, (string)$validator);
                                    
    exit();
  }
}

// Clean price value
$price = Tools::cleanPrice($BF->in('f_actual_price_value'));
$nodeID = $BF->inInteger('f_actual_price_node');

if(!$price || !$nodeID)
{ 
  $BF->admin->packAndRedirect('./?act=inventory&mode=outlets_add',
                                  'url', 'Type a URL then choose a Outlet Price Value');
                                  
  exit();
}

// Passed validation
$result = $BF->admin->api('Outlets')
                        ->add($price, 
                              $BF->in('f_url'),
                              $nodeID,
                              '1',  // Start off OK
                              $BF->in('f_dealer_id'),
                              $BF->in('f_item_id'));

// Update all snapshots
$BF->admin->api('Outlets')
          ->updateAll();

if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The Outlet was created.');
  header('Location: ./?act=inventory&mode=outlets');
}

?>
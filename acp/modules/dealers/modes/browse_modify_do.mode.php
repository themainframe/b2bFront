<?php
/**
 * Module: Dealers
 * Mode: Do Modify
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

// Get ID
$dealerID = $BF->inInteger('f_id');

// Get the Old name for comparison with the new value to ensure
// duplicate name creation attempts do not take place
$oldName = strtoupper($BF->in('f_name'));

// Create validations for name
$nameValidations = array(               
                         'min' => array(2)
                        );

// Check changes in name
if(strtoupper($BF->in('f_name')) != $oldName)
{
  // Require a check for unique name
  $nameValidations['unique'] = array('bf_users', 'name');
}

// Build the validation array
$validation = array(
  
    'name' => array(
    
               'validations' => $nameValidations,
                                
               'value' => $BF->in('f_name'),
               
               'name' => 'Name'
                   
              ),

    'email' => array(
    
               'validations' => array(
                                 'email' => array(),
                                 'done' => array()
                                ),
                                
               'value' => $BF->in('f_email'),
               
               'name' => 'Email'
                   
              ),
              
    'description' => array(
    
               'validations' => array(
                                 'done' => array()
                                ),
                                
               'value' => $BF->in('f_description'),
               
               'name' => 'Description'
                   
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
    $BF->admin->packAndRedirect('./?act=dealers&mode=browse_modify&id=' . $dealerID,
                                    $fieldName, (string)$validator);
                                    
    exit();
  }
}

$result = $BF->admin->api('Dealers')
                        ->modify($dealerID,
                                 $BF->in('f_name'),
                                 $BF->in('f_password'),
                                 $BF->in('f_email'),
                                 $BF->in('f_description'),
                                 $BF->in('f_dealer_profile'),
                                 $BF->in('f_account_code'),
                                 $BF->in('f_address_building'),
                                 $BF->in('f_address_street'),
                                 $BF->in('f_address_city'),
                                 $BF->in('f_address_postcode'),
                                 $BF->in('f_phone_landline'),
                                 $BF->in('f_phone_mobile'),
                                 $BF->in('f_url'),
                                 $BF->in('f_slogan'),
                                 $BF->in('f_locale'),
                                 $BF->in('f_bulk_exclude'),
                                 $BF->in('f_dealer_band'),
                                 $BF->in('f_in_directory')
                                );


if($result !== false)
{
  $BF->admin->notifyMe('OK', 'Changes to the dealer \'' . $BF->in('f_name') . '\' were saved.');
  header('Location: ./?act=dealers&mode=browse');
}

?>
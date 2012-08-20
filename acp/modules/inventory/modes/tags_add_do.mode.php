<?php
/**
 * Module: Inventory
 * Mode: Do Add Item Tag
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
  
    'name' => array(
    
               'validations' => array(
                                 'unique' => array('bf_item_tags'),
                                 'done' => array()
                                ),
                                
               'value' => $BF->in('f_name'),
               
               'name' => 'Name'
                   
              ),
              
    'icon' => array(
    
               'validations' => array(
                                 'unique' => array('bf_item_tags', 'icon_path'),
                                 'done' => array()
                                ),
                                
               'value' => $BF->in('f_icon'),
               
               'name' => 'Icon'
                   
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
    $BF->admin->packAndRedirect('./?act=inventory&mode=tags_add',
                                    $fieldName, (string)$validator);
                                    
    exit();
  }
}

// Get formatting options
$formattingBold = $BF->in('f_list_bold');
$formattingItalic = $BF->in('f_list_italic');
$formattingSmallCaps = $BF->in('f_list_small_caps');
$formattingColour = $BF->in('f_list_colour');

// Add item tag
$result = $BF->admin->api('ItemTags')
                        ->add($BF->in('f_name'), 
                              $BF->in('f_icon'), 
                              $formattingBold, 
                              $formattingItalic, 
                              $formattingSmallCaps,
                              $formattingColour
                             );

if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The Item Tag ' . $BF->in('f_name') . ' was created.');
  header('Location: ./?act=inventory&mode=tags');
}

?>
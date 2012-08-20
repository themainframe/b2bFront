<?php
/**
 * Module: Inventory
 * Mode: Do Add Category/Subcategory
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

// Verify Permissions
if(!$BF->admin->can('categories'))
{
?>
    <h1>Permission Denied</h1>
    <br />
    <p>
      You do not have permission to use this section of the ACP.<br />
      Please ask your supervisor for more information.
    </p>
<?php

exit();

}

// Build the validation array
$validation = array(
  
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
    $BF->admin->packAndRedirect('./?act=inventory&mode=organise_add',
                                    $fieldName, (string)$validator);
                                    
    exit();
  }
}

// Is this a category or subcategory?
$isSubcategory = ($BF->in('f_parent') != '-1');

// Passed validation
if($isSubcategory)
{
  $result = $BF->admin->api('Categories')
                          ->addSubcategory($BF->in('f_name'),
                                           $BF->inInteger('f_parent'));
}
else
{
  $result = $BF->admin->api('Categories')
                          ->addCategory($BF->in('f_name'), $BF->in('f_parent_child_display_mode'));
}


if($result !== false)
{
  // Deturmine "sub" category status.
  $categoryNiceType = ($BF->in('f_parent') == '-1' ? 'category' : 'subcategory');

  $BF->admin->notifyMe('OK', 'The ' . $categoryNiceType . ' ' . $BF->in('f_name') . ' was created.');
  header('Location: ./?act=inventory&mode=organise');
}

?>
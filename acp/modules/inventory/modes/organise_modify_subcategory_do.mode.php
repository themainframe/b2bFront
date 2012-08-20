<?php
/**
 * Module: Inventory
 * Mode: Do Modify Subcategory
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

// Get the ID
$ID = $BF->inInteger('id');

// Get the row information
$BF->db->select('*', 'bf_subcategories')
           ->where('id = \'{1}\'', $ID)
           ->limit(1)
           ->execute();
           
// Check the ID was valid
if($BF->db->count < 1)
{
  // Return the user to the selection interface
  header('Location: ./?act=inventory&mode=organise');
  exit();
}

// Retrieve the row
$row = $BF->db->next();

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
    $BF->admin->packAndRedirect('./?act=inventory&mode=organise_modify_subcategory&id=' . $ID,
                                    $fieldName, (string)$validator);
                                    
    exit();
  }
}

$result = false;

$imageID = $BF->inInteger('f_image_id');
$result = $BF->admin->api('Categories')
                    ->modifySubcategory($BF->inInteger('id'),
                                        $BF->in('f_name'));


if($result !== false)
{
  $BF->admin->notifyMe('OK', 'Changes to the subcategory \'' . $BF->in('f_name') . '\' were saved.');
  header('Location: ./?act=inventory&mode=organise');
}

?>
<?php
/**
 * Module: Inventory
 * Mode: Do Remove Category/Subcategory
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

// Category or Subcategory?
$isSubcategory = ($BF->in('type') == 'subcategory');

// Obtain the category ID
$categoryID = $BF->in('id');
$name = '';
$result = false;

// Valid?
if($categoryID)
{
  // Get information
  if($isSubcategory)
  {
    $BF->db->select('*', 'bf_subcategories')
               ->where("`id` = '{1}'", $categoryID)
               ->limit(1)
               ->execute();
  }
  else
  {
    $BF->db->select('*', 'bf_categories')
               ->where("`id` = '{1}'", $categoryID)
               ->limit(1)
               ->execute();
  }
             
  // Get name
  $categoryRow = $BF->db->next();
  $name = $categoryRow->name;
  
  // Add restore point
  if($BF->config->get('com.b2bfront.restorepoints.auto', true))
  {
    $BF->admin->api('RestorePoints')
                  ->create('The ' . ($isSubcategory ? 'subcategory' : 'category') . 
                           ' \'' . $name . '\' was removed.');
  }
  
  if($isSubcategory)
  {
    // Remove subcategory
    $result = $BF->admin->api('Categories')
                            ->removeSubcategory($categoryID);
  }
  else
  {
    // Remove category
    $result = $BF->admin->api('Categories')
                            ->removeCategory($categoryID);
  }
  

}


if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The ' . ($isSubcategory ? 'subcategory' : 'category') . 
                           ' \'' . $name . '\' was removed.');
  header('Location: ./?act=inventory&mode=organise');
}

?>
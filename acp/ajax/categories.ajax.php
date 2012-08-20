<?php
/**
 * Categories
 * AJAX Responder
 * Sends AJAX replies to the jQueryFileTree objects.
 *
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */
 
// Set context
define('BF_CONTEXT_ADMIN', true);

// Relative path for this - no BF_ROOT yet.
require_once('../admin_startup.php');
require_once(BF_ROOT . 'tools.php');

// New BFClass & Admin class
$BF = new BFClass(true);
$BF->admin = new Admin(& $BF);

if(!$BF->admin->isAdmin)
{
  exit();
}

// This script can operate in two ways, either with a catid:
//   -> List the subcategories under that category
// Or without a catid:
//   -> List all categories

// Find the catid if supplied
$catid = $BF->in('dir');

// Get the tree_name variable too.
// This allows multiple jQueryFileTrees on one page
$treeName = $BF->in('tree_name');


// Is catid set?
if($catid)
{
  // Allow showing of subcategories?
  if($BF->in('restrict') != '1')
  {
    // Produce HTML
    print '<ul class="jqueryFileTree" style="display: none;">' . "\n";
  
    // List the contents of that category
    $subcategories = $BF->db->query();
    $subcategories->select('*', 'bf_subcategories')
                  ->where('category_id = {1}', $catid)
                  ->order('name', 'asc')
                  ->execute();
                  
    while($subcategory = $subcategories->next())
    {
      print '  <li class="directory collapsed subcategory">' . "\n";
      print '    <a cat="' . $catid . '" subcat="' . $subcategory->id . '" class="' . 
            $treeName . '_dir_link dir_link" href="#" rel="-1">';
      print htmlentities($subcategory->name) . '</a>' . "\n";
      print '</li>' . "\n\n";
    }
    
    print '</ul>';
    
  }
}
else
{

  // Produce HTML
  print '<ul class="jqueryFileTree" style="display: none;">' . "\n";
  
  // List all categories
  $categories = $BF->db->query();
  $categories->select('*', 'bf_categories')
             ->order('name', 'asc')
             ->execute();
             
  while($category = $categories->next())
  {
    print '  <li class="directory collapsed">' . "\n";
    print '    <a cat="' . $category->id . '" subcat="-1" class="' . $treeName . '_dir_link dir_link" href="#" rel="' . 
          ($category->id) . '">';
    print htmlentities($category->name) . '</a>' . "\n";
    print '</li>' . "\n\n";
  }
  
  print '</ul>';
}

$BF->shutdown();

?>
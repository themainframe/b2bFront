<?php
/**
 * Module: Inventory
 * Mode: Organisation
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */

// Context check
if(!defined('BF_CONTEXT_ADMIN') || !defined('BF_CONTEXT_MODULE'))
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

?>

<h1>Categories</h1>
<br />

<div class="panel">
  <div class="contents">
    
    <h3>About Categories</h3>
    
    <p>
      Categories are the main organisational structure of the inventory.<br />
      They may be nested up to two levels (<em>Eg.</em> Category -> Subcategory -> Item).
    </p>
    
    <br />
    
    <span class="button">
      <a href="./?act=inventory&mode=organise_add">
        <span class="img" style="background-image:url(/acp/static/icon/folder--plus.png)">&nbsp;</span>
        New Category/Subcategory...
      </a>
    </span>
 
    <br /><br />
  </div>
</div>

<br />

<?php

  // Find all categories
  $categories = $BF->db->query();
  $categories->select('*', 'bf_categories')
             ->order('name', 'asc')
             ->execute();
             
  // Find all subcategories
  $subcategories = $BF->db->query();
  $subcategories->select('*', 'bf_subcategories')
                ->order('name', 'asc')
                ->execute();
                
  // Define confirmation JS for Categories
  $categoryConfirmationJS = 'confirmation(\'Really remove this category?<br /><br /><br />Items inside it will become uncategorised.<br />' . 
                            'Subcategories inside it will be deleted.\', function() { window.location=\'' .
                            Tools::getModifiedURL(array('mode' => 'organise_remove_do')) . '&id={1}&type=category\'; })';

  // Define confirmation JS for Subcategories
  $subcategoryConfirmationJS = 'confirmation(\'Really remove this subcategory?\', function() { window.location=\'' .
                               Tools::getModifiedURL(array('mode' => 'organise_remove_do')) . '&id={1}&type=subcategory\'; })';

  // Buttons for Categories
  $categoryToolSet  = "\n";
  $categoryToolSet .= '<a class="tool" href="./?act=inventory&mode=organise_modify&id={1}" title="Modify">' . "\n";
  $categoryToolSet .= '  <img src="/acp/static/icon/zone--pencil.png" alt="Modify" />' . "\n";
  $categoryToolSet .= 'Modify</a>' . "\n";
  $categoryToolSet .= '<a onclick="' . $categoryConfirmationJS . '" class="tool" title="Remove">' . "\n";
  $categoryToolSet .= '  <img src="/acp/static/icon/cross-circle.png" alt="Remove" />' . "\n";
  $categoryToolSet .= 'Remove</a>' . "\n";
  
  // Buttons for Subcategories
  $subcategoryToolSet  = "\n";
  $subcategoryToolSet .= '<a class="tool" href="./?act=inventory&mode=organise_modify_subcategory&id={1}" title="Modify">' . 
                         "\n";
  $subcategoryToolSet .= '  <img src="/acp/static/icon/zone--pencil.png" alt="Modify" />' . "\n";
  $subcategoryToolSet .= 'Modify</a>' . "\n";
  $subcategoryToolSet .= '<a onclick="' . $subcategoryConfirmationJS . '" class="tool" title="Remove">' . "\n";
  $subcategoryToolSet .= '  <img src="/acp/static/icon/cross-circle.png" alt="Remove" />' . "\n";
  $subcategoryToolSet .= 'Remove</a>' . "\n";

?>

<table id="t1" class="data">
  <thead>
    <tr class="header">
      <td>
        Category Name
      </td>
      <td style="text-align: right; padding-right: 20px;">
      	Actions
      </td>
    </tr>
  </thead>
  <tbody>
<?php
  
  // Show all categories with their subcategories
  while($category = $categories->next())
  {

?>
    <tr class="category">
      <td class="name">
      
        <?php
          
          if(!$category->visible)
          {
            ?>
              <img src="/acp/static/icon/slash-small.png"
                title="Not Visible" style="position: relative; top: 3px" />
              <span class="grey">
            <?php
          }
          
        ?>
      
      <?php print $category->name; ?>
      
      
        <?php
          
          if(!$category->visible)
          {
            ?>
              </span>
            <?php
          }
          
        ?>
      
      
      </td>
      <td class="tools">
        

        
        <?php print Tools::replaceTokens($categoryToolSet, $category->id); ?>
      </td>
    </tr>
<?php
  
    // Any associated subcategories?
    while($subcategory = $subcategories->next())
    {
  
      if($subcategory->category_id != $category->id)
      {
        continue;
      }
  
?>  
    <tr class="subcategory">
      <td class="name"><?php print $subcategory->name; ?></td>
      <td class="tools">
        <?php print Tools::replaceTokens($subcategoryToolSet, $subcategory->id); ?>
      </td>
    </tr>
<?php

    }
    
    // Reset the subcategory result
    $subcategories->rewind();
    
  }

?>
  
    
  </tbody>
</table>
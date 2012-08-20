<?php
/**
 * Module: Inventory
 * Mode: Organisation - Category Groups
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

<h1>Category Groups</h1>
<br />

<div class="panel">
  <div class="contents">
    
    <h3>About Category Groups</h3>
    
    <p>
      Category Groups allow you to collect together related categories into a group.<br />
      Some skins may not display category groups in some or all views. 
    </p>
    
    <br />
    
    <span class="button">
      <a href="./?act=inventory&mode=organise_category_groups_add">
        <span class="img" style="background-image:url(/acp/static/icon/plus-button.png)">&nbsp;</span>
        New Category Group...
      </a>
    </span>
 
    <br /><br />
  </div>
</div>

<br />

<?php

  // Define a tool set HTML
  $confirmationJS = 'confirmation(\'Really remove this Category Group?<br />All contained categories will be ungrouped.\', function() { window.location=\'' .
                    Tools::getModifiedURL(array('mode' => 'organise_category_groups_remove_do')) . '&id={id}\'; })';
  
  // Buttons
  $toolSet  = "\n";
  $toolSet .= '<a onclick="' . $confirmationJS . '" class="tool" title="Remove">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/cross-circle.png" alt="Remove" />' . "\n";
  $toolSet .= 'Remove</a>' . "\n";
  

  // Find all Find all category groups
  $query = $BF->db->query();
  $query->select('*', 'bf_category_groups')
        ->order('name', 'asc')
        ->execute();
             
  // Create a data table view to show the groups
  $groups = new DataTable('cg1', $BF, $query);
  $groups->setOption('alternateRows');
  $groups->setOption('showTopPager');
  $groups->addColumns(array(
                        array(
                          'dataName' => 'name',
                          'niceName' => 'Category Group Name'
                        ),
                        array(
                          'dataName' => '',
                          'niceName' => 'Actions',
                          'options' => array('fixedOrder' => true),
                          'content' => $toolSet,
                          'css' => array(
                                     'width' => '70px'
                                   )
                        )
                      ));
  
  // Render & output content
  print $groups->render();
  
?>
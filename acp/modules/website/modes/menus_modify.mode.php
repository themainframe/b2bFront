<?php
/**
 * Module: Website
 * Mode: Menu Modify
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

// Get the ID
$ID = $BF->inInteger('id');

// Get the row information
$BF->db->select('*', 'bf_website_menus')
           ->where('id = \'{1}\'', $ID)
           ->limit(1)
           ->execute();
           
// Check the ID was valid
if($BF->db->count < 1)
{
  // Return the user to the selection interface
  header('Location: ./?act=website&mode=menus');
  exit();
}

// Retrieve the row
$row = $BF->db->next();

?>

<h1><?php print $row->description; ?></h1>
<br />

<div class="panel">
  <div class="contents">
    <h3>About Menu Modification</h3>
    <p>
      In this view you can add and remove items from the menu.<br />
      <a href="./?act=website&mode=menus" title="Menus">Click here</a> to return to Menus.
    </p>
  </div>
</div>

<br />

<?php

  // Create a new query to retreieve classifications
  $query = $BF->db->query();
  
  $query->select('`bf_website_menus`.*, COUNT(`bf_website_menu_items`.`id`) AS `items`', 
                'bf_website_menus')
        ->text('LEFT OUTER JOIN `bf_website_menu_items` ON `bf_website_menus`.`id` = ' .
               '`bf_website_menu_items`.`menu_id` ')
        ->group('`bf_website_menus`.`id`');
        
  // Define a tool set HTML
  $confirmationJS = 'confirmation(\'Really remove this Menu?\', function() { window.location=\'' .
                    Tools::getModifiedURL(array('mode' => 'menus_remove_do')) . '&id  ={id}\'; })';
  
  // Buttons
  $toolSet  = "\n";
  $toolSet .= '<a onclick="' . $confirmationJS . '" class="tool" title="Remove">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/cross-circle.png" alt="Remove" />' . "\n";
  $toolSet .= 'Remove</a>' . "\n";
  $toolSet .= '<a class="tool" title="Modify" href="./?' . 
              'act=website&mode=menus_modify&id={id}">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/zone--pencil.png" alt="Modify" />' . "\n";
  $toolSet .= 'Modify</a>' . "\n";
  
  // Create a data table view to show the classifications
  $menus = new DataTable('t1', $BF, $query);
  $menus->setOption('alternateRows');
  $menus->setOption('showTopPager');
  $menus->addColumns(array(
                      array(
                        'dataName' => 'description',
                        'niceName' => 'Menu Name'
                      ),
                      array(
                        'dataName' => 'name',
                        'niceName' => 'Private Name'
                      ),
                      array(
                        'dataName' => 'items',
                        'niceName' => 'Items', 
                        'content' => $toolSet,
                        'css' => array(
                                   'width' => '70px'
                                 )
                      ),
                      array(
                        'dataName' => '',
                        'niceName' => 'Actions',
                        'options' => array('fixedOrder' => true),
                        'content' => $toolSet,
                        'css' => array(
                                   'width' => '150px'
                                 )
                      )
                    ));
  
  // Render & output content
  print $menus->render();
?>
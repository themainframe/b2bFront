<?php
/**
 * Module: Inventory
 * Mode: Classifications
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

?>

<h1>Classifications</h1>
<br />

<div class="panel">
  <div class="contents">
    
    
    <h3>About Classifications</h3>
    
    <p>
      Classifications allow you to organise items in a more generic way than categories alone.<br />
      You should try to choose classifications that share attribute keys like sizes in a given dimension
      or some other property unique to a group of items.
    </p>
    
    <br />
    <span class="button">
      <a href="./?act=inventory&mode=classifications_add">
        <span class="img" style="background-image:url(/acp/static/icon/zone--plus.png)">&nbsp;</span>
        New Classification...
      </a>
    </span>
    
    <br /><br />
  </div>
</div>

<br />

<?php

  // Create a new query to retreieve classifications
  $query = $BF->db->query();
  
  $query->select('`bf_classifications`.*, COUNT(`bf_items`.`id`) AS `items`, ' . 
                 ' COUNT(`bf_classification_attributes`.`id`) AS `attributes`', 'bf_classifications')
        ->text('LEFT OUTER JOIN `bf_items` ON `bf_classifications`.`id` = `bf_items`.`classification_id` ')
        ->text('LEFT OUTER JOIN `bf_classification_attributes` ON `bf_classifications`.`id`' .
               ' = `bf_classification_attributes`.`classification_id`')
        ->group('`bf_classifications`.`id`');
        
  // Define a tool set HTML
  $confirmationJS = 'confirmation(\'Really remove this classification?\', function() { window.location=\'' .
                    Tools::getModifiedURL(array('mode' => 'classifications_remove_do')) . '&clid={id}\'; })';
  
  // Buttons
  $toolSet  = "\n";
  $toolSet .= '<a onclick="' . $confirmationJS . '" class="tool" title="Remove">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/cross-circle.png" alt="Remove" />' . "\n";
  $toolSet .= 'Remove</a>' . "\n";
  $toolSet .= '<a class="tool" title="Modify" href="./?act=inventory&mode=classifications_modify&id={id}">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/zone--pencil.png" alt="Modify" />' . "\n";
  $toolSet .= 'Modify</a>' . "\n";
  $toolSet .= '<a class="tool" title="Data" href="./?act=inventory&mode=classifications_data&id={id}">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/tags.png" alt="Data" />' . "\n";
  $toolSet .= 'Attributes</a>' . "\n";
  
  // Create a data table view to show the classifications
  $classifications = new DataTable('t1', $BF, $query);
  $classifications->setOption('alternateRows');
  $classifications->setOption('showTopPager');
  $classifications->addColumns(array(
                          array(
                            'dataName' => 'name',
                            'niceName' => 'Classication Name'
                          ),
                          array(
                            'dataName' => '',
                            'niceName' => 'Actions',
                            'options' => array('fixedOrder' => true),
                            'content' => $toolSet,
                            'css' => array(
                                       'width' => '216px'
                                     )
                          )
                        ));
  
  // Render & output content
  print $classifications->render();
?>
<?php
/**
 * Module: Inventory
 * Mode: Browse Labels
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

// Load label colours
$labelColourParser = new PropertyList();
$labelColours = $labelColourParser->parseFile(
  BF_ROOT . '/acp/definitions/inventory_label_colours.plist');
  
// Failure?
if(!$labelColours)
{
  $BF->log('Unable to load /acp/definitions/inventory_label_colours.plist');
}

$GLOBALS['label_colours'] = $labelColours;

?>

<h1>Labels</h1>
<br />

<div class="panel">
  <div class="contents">
    
    <h3>About Labels</h3>
    
    <p>
    
      Labels enable you to organise your inventory more effectively.<br /><br />
      
      They are invisible to everyone except staff and allow you to group items together more easily.<br />
      For example, you can use labels to perform actions on specific groupings of products.
    
    </p>
    
    <br />
    <span class="button">
      <a href="./?act=inventory&mode=browse_labels_add">
        <span class="img" style="background-image:url(/acp/static/icon/plus-button.png)">&nbsp;</span>
        New Label...
      </a>
    </span>
    
    <br /><br />
    
  </div>
</div>

<br />

<?php

  // Create a new query to retreieve labels
  $query = $BF->db->query();
  $query->select('`bf_item_labels`.*, COUNT(`bf_item_label_applications`.`id`) AS `items`', 'bf_item_labels')
        ->text('LEFT OUTER JOIN `bf_item_label_applications` ON `bf_item_labels`.`id` = `bf_item_label_applications`.`item_label_id` ')
        ->group('`bf_item_labels`.`id`');
        
  // Define a tool set HTML
  $confirmationJS = 'confirmation(\'Really remove this label?<br />' . 
                    'It will be stripped from all items.\', function() { window.location=\'' .
                    Tools::getModifiedURL(array('mode' => 'browse_labels_remove_do')) . '&id={id}\'; })';
  
  // Buttons
  $toolSet  = "\n";
  $toolSet .= '<a onclick="' . $confirmationJS . '" class="tool" title="Remove">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/cross-circle.png" alt="Remove" />' . "\n";
  $toolSet .= 'Remove</a>' . "\n";
  
  // Create a data table view to show the labels
  $labels = new DataTable('br1', $BF, $query);
  $labels->setOption('alternateRows');
  $labels->setOption('showTopPager');
  $labels->addColumns(array(
                        array(
                          'dataName' => 'name',
                          'niceName' => 'Label Name',
                          'options' => array(
                                         'callback' => function($row, $parent) {
                                            return '<span class="label_small" style="background-color: ' . 
                                                   $GLOBALS['label_colours'][$row->colour]['colour'] . 
                                                   '; position: relative; top: 2px;">' . $row->name . '</span>';
                                         }
                                       )
                        ),
                        array(
                          'dataName' => 'items',
                          'niceName' => 'Labelled Items',
                          'css' => array(
                                     'width' => '100px'
                                   )
                        ),
                        array(
                          'dataName' => '',
                          'niceName' => 'Actions',
                          'options' => array('fixedOrder' => false),
                          'css' => array(
                                     'width' => '65px',
                                     'text-align' => 'right',
                                     'padding-right' => '10px'
                                   ),
                          'content' => $toolSet
                        )
                      ));
  
  // Render & output content
  print $labels->render();
?>
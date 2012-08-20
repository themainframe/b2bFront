<?php
/**
 * Module: Inventory
 * Mode: Browse - Modify Filters
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

<h1>Modify Filters</h1>
<br />

<div class="panel">
  <div class="contents">
    
    <h3>About Filters</h3>
    
    <p>
      Filters are sets of conditions that can be applied to the inventory view.<br />
      They allow you to limit your inventory view to see only the items you need for a given activity.
      <br /><br />
      For example, you could create a filter to show all items with a trade price above 10.00 that have sold 20 or more units in the past week.
    </p>
    
    <br />
    <span class="button">
      <a href="./?act=inventory&mode=browse_modify_filters_add">
        <span class="img" style="background-image:url(/acp/static/icon/funnel--plus.png)">&nbsp;</span>
        New Filter...
      </a>
    </span>
    
    <br /><br />
  </div>
</div>

<br />

<?php

  // Create a new query to retreieve filters
  $query = $BF->db->query();
  $query->select('*', 'bf_admin_inventory_browse_filters');
        
  // Define a tool set HTML
  $confirmationJS = 'confirmation(\'Really remove this filter?\', function() { window.location=\'' .
                    Tools::getModifiedURL(array('mode' => 'browse_modify_filters_remove_do')) . '&id={id}\'; })';
  
  // Buttons
  $toolSet  = "\n";
  $toolSet .= '<a onclick="' . $confirmationJS . '" class="tool" title="Remove">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/cross-circle.png" alt="Remove" />' . "\n";
  $toolSet .= 'Remove</a>' . "\n";
  
  // Create a data table view to show the brands
  $brands = new DataTable('br1', $BF, $query);
  $brands->setOption('alternateRows');
  $brands->setOption('showTopPager');
  $brands->addColumns(array(
                        array(
                          'dataName' => 'name',
                          'niceName' => 'Filter Name'
                        ),
                        array(
                          'dataName' => '',
                          'niceName' => 'Actions',
                          'options' => array('fixedOrder' => false),
                          'css' => array(
                                     'width' => '70px',
                                     'text-align' => 'right',
                                     'padding-right' => '10px'
                                   ),
                          'content' => $toolSet
                        )
                      ));
  
  // Render & output content
  print $brands->render();
?>
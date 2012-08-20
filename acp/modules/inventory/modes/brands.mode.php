<?php
/**
 * Module: Inventory
 * Mode: Brands
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

<h1>Brands</h1>
<br />

<div class="panel">
  <div class="contents">
    
    
    <h3>About Brands</h3>
    
    <p>
      Brands allow you to identify the manufacturers of items.<br />
      You can add a logo to be displayed alongside the items assigned to the brand.
    </p>
    
    <br />
    <span class="button">
      <a href="./?act=inventory&mode=brands_add">
        <span class="img" style="background-image:url(/acp/static/icon/reg-trademark--plus.png)">&nbsp;</span>
        New Brand...
      </a>
    </span>
    
    <br /><br />
  </div>
</div>

<br />

<?php

  // Create a new query to retreieve brands
  $query = $BF->db->query();
  $query->select('*', 'bf_brands');
        
  // Define a tool set HTML
  $confirmationJS = 'confirmation(\'Really remove this brand?\', function() { window.location=\'' .
                    Tools::getModifiedURL(array('mode' => 'brands_remove_do')) . '&id={id}\'; })';
  
  // Buttons
  $toolSet  = "\n";
  $toolSet .= '<a onclick="' . $confirmationJS . '" class="tool" title="Remove">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/cross-circle.png" alt="Remove" />' . "\n";
  $toolSet .= 'Remove</a>' . "\n";
  $toolSet .= '<a class="tool" title="Modify" href="./?act=inventory&mode=brands_modify&id={id}">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/zone--pencil.png" alt="Modify" />' . "\n";
  $toolSet .= 'Modify</a>' . "\n";
  
  // Create a data table view to show the brands
  $brands = new DataTable('br1', $BF, $query);
  $brands->setOption('alternateRows');
  $brands->setOption('showTopPager');
  $brands->addColumns(array(
                        array(
                          'dataName' => 'name',
                          'niceName' => 'Brand Name'
                        ),
                        array(
                          'dataName' => '',
                          'niceName' => 'Actions',
                          'options' => array('fixedOrder' => false),
                          'css' => array(
                                     'width' => '130px',
                                     'text-align' => 'right',
                                     'padding-right' => '10px'
                                   ),
                          'content' => $toolSet
                        )
                      ));
  
  // Render & output content
  print $brands->render();
?>
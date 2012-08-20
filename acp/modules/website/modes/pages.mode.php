<?php
/**
 * Module: Website
 * Mode: Pages
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

<h1>Pages</h1>
<br />

<div class="panel">
  <div class="contents">
    <h3>About Pages</h3>
    <p>
      Pages are sections of the website that contain HTML text supplied by staff.<br />
      They can be used to provide additional information about your business to dealers.
    </p>
    
    <br />
    <span class="button">
      <a href="./?act=website&mode=pages_add">
        <span class="img" style="background-image:url(/acp/static/icon/plus-button.png)">&nbsp;</span>
        New Page...
      </a>
    </span>
    
    <br /><br />
    
  </div>
</div>

<br /> 

<?php

  // Create a new query to retreieve pages
  $query = $BF->db->query();
  $query->select('*', 'bf_pages');
        
  // Define a tool set HTML
  $confirmationJS = 'confirmation(\'Really remove this page?\', function() { window.location=\'' .
                    Tools::getModifiedURL(array('mode' => 'pages_remove_do')) . '&id={id}\'; })';
  
  // Buttons
  $toolSet  = "\n";
  $toolSet .= '<a onclick="' . $confirmationJS . '" class="tool" title="Remove">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/cross-circle.png" alt="Remove" />' . "\n";
  $toolSet .= 'Remove</a>' . "\n";
  $toolSet .= '<a class="tool" title="Modify" href="./?act=website&mode=pages_modify&id={id}">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/zone--pencil.png" alt="Modify" />' . "\n";
  $toolSet .= 'Modify</a>' . "\n";
  
  // Create a data table view to show the pages
  $pages = new DataTable('pg1', $BF, $query);
  $pages->setOption('alternateRows');
  $pages->setOption('showTopPager');
  $pages->addColumns(array(
                      array(
                        'dataName' => 'title',
                        'niceName' => 'Page Name'
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
  print $pages->render();
?>
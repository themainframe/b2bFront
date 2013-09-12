<?php
/**
 * Module: Website
 * Mode: Downloads
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

<h1>Downloads</h1>
<br />

<div class="panel">
  <div class="contents">
    <h3>About Downloads</h3>
    <p>
      You can use the Downloads system to make files available to dealers.<br />
      Simply upload any file and users with the Downloads permission will be able to save them to their computers.
    </p>
    
    <br />
    <span class="button">
      <a href="./?act=website&mode=downloads_add">
        <span class="img" style="background-image:url(/acp/static/icon/plus-button.png)">&nbsp;</span>
        New Download...
      </a>
    </span>
    
    <br /><br />
    
  </div>
</div>

<br /> 


<?php

  // Create a new query to retreieve downloads
  $query = $BF->db->query();
  $query->select('*', 'bf_downloads');
        
  // Define a tool set HTML
  $confirmationJS = 'confirmation(\'Really remove this download?<br />The file will be deleted from the server.' . 
                    '\', function() { window.location=\'' .
                    Tools::getModifiedURL(array('mode' => 'downloads_remove_do')) . '&id={id}\'; })';
  
  // Buttons
  $toolSet  = "\n";
  $toolSet .= '<a onclick="' . $confirmationJS . '" class="tool" title="Remove">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/cross-circle.png" alt="Remove" />' . "\n";
  $toolSet .= 'Remove</a>' . "\n";
  
  // Create a data table view to show the downloads
  $downloads = new DataTable('dl1', $BF, $query);
  $downloads->setOption('alternateRows');
  $downloads->setOption('showTopPager');
  $downloads->addColumns(array(
                      array(
                        'dataName' => 'name',
                        'niceName' => 'Download Title'
                      ),
                      array(
                        'dataName' => 'mime_type',
                        'niceName' => 'Type',
                        'css' => array(
                                   'width' => '160px'
                                 )
                      ),
                      
                      array(
                        'dataName' => 'is_on_avocet',
                        'niceName' => 'Avocet',
                        'options' => array(
                                       'editable_cb' => true,
                                       'editable' => true,
                                       'editableTable' => 'bf_downloads'
                                     ),
                        'css' => array('width' => '80px', 'text-align' => 'center')
                      ),
                      array(
                        'dataName' => 'is_on_viking',
                        'niceName' => 'Viking',
                        'options' => array(
                                       'editable_cb' => true,
                                       'editable' => true,
                                       'editableTable' => 'bf_downloads'
                                     ),
                        'css' => array('width' => '80px', 'text-align' => 'center')
                      ),
                      array(
                        'dataName' => 'is_on_coyote',
                        'niceName' => 'B2B',
                        'options' => array(
                                       'editable_cb' => true,
                                       'editable' => true,
                                       'editableTable' => 'bf_downloads'
                                     ),
                        'css' => array('width' => '80px', 'text-align' => 'center')
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
  print $downloads->render();
?>
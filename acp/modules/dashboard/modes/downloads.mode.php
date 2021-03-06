<?php
/**
 * Module: Dashboard
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

<h1>My Downloads</h1>
<br />

<div class="panel">
  <div class="contents" style="">
    <h3>About My Downloads</h3>
    <p>
      This view shows files that have been generated by b2bFront.<br /><br />
      If the file you are looking for is not visible, it is possible that it has not finished generating yet.<br />
      You will be notified when a file is added to this list.
    </p>
  </div>
</div>

<br />


<?php

  // Create a new query to retreieve downloads for me
  $query = $BF->db->query();
  $query->select('*', 'bf_admin_downloads')
        ->where('`admin_id` = \'{1}\'', $BF->admin->AID);
        
  // Define a tool set HTML
  $confirmationJS = 'confirmation(\'Really remove this download?\', function() { window.location=\'' .
                    Tools::getModifiedURL(array('mode' => 'downloads_remove_do')) . '&id={id}\'; })';
  
  // Buttons
  $toolSet  = "\n";
  $toolSet .= '<a onclick="' . $confirmationJS . '" class="tool" title="Remove">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/cross-circle.png" alt="Remove" />' . "\n";
  $toolSet .= 'Remove</a>' . "\n";
  $toolSet .= '<a class="tool" title="Download" href="' . $BF->config->get('com.b2bfront.site.url', true) . '/{path}">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/navigation-270-grn.png" alt="Download" />' . "\n";
  $toolSet .= 'Download</a>' . "\n";
  
  // Default ordering
  if($BF->in('do1_order_d') == '' && $BF->in('do1_order') == '')
  {
    $BF->setIn('do1_order', '2');
    $BF->setIn('do1_order_d', 'd');
  }
  
  // Create a data table view to show the downloads
  $downloads = new DataTable('do1', $BF, $query);
  $downloads->setOption('alternateRows');
  $downloads->setOption('showTopPager');
  $downloads->addColumns(array(
                        array(
                          'dataName' => 'name',
                          'niceName' => 'File Name'
                        ),
                        array(
                          'dataName' => 'path',
                          'niceName' => 'Size',
                          'options' => array(
                                         'formatAsFileSize' => true,
                                         'fixedOrder' => true
                                       ),
                          'css' => array(
                                     'width' => '100px'
                                   )
                        ),
                        array(
                          'dataName' => 'timestamp',
                          'niceName' => 'Generated',
                          'options' => array(
                                         'formatAsDate' => true
                                       ),
                          'css' => array(
                                     'width' => '150px'
                                   )
                        ),
                        array(
                          'dataName' => '',
                          'niceName' => 'Actions',
                          'options' => array('fixedOrder' => false),
                          'css' => array(
                                     'width' => '140px',
                                     'text-align' => 'right',
                                     'padding-right' => '10px'
                                   ),
                          'content' => $toolSet
                        )
                      ));
  
  // Render & output content
  print $downloads->render();
?>
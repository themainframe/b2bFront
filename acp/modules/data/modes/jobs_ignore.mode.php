<?php
/**
 * Module: Data
 * Mode: Data Jobs Ignore List
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

<h1>Data Jobs Ignore List</h1>
<br />

<div class="panel">
  <div class="contents">
    
    <h3>About Data Jobs Ignore List</h3>
    
    <p>
      This list contains items that b2bFront will not generate 
      <a href="./?act=data&mode=jobs" title="Data Jobs">Data Jobs</a> for.<br />
      You can remove items from the list by clicking the "Remove" button to the right.
    </p>
    
  </div>
</div>

<br />

<?php

  // Create a new query to retreieve jobs
  $query = $BF->db->query();
  $query->select('`bf_data_jobs_ignore`.*, `bf_items`.`sku` AS `sku`, `bf_items`.`name` AS `name`', 'bf_data_jobs_ignore, bf_items')
        ->where('bf_data_jobs_ignore.item_id = bf_items.id');

  // Buttons
  $toolSet  = "\n";
  $toolSet .= '<a class="tool" title="Ignore Item" href="./?act=data&mode=jobs_ignore_remove_do&id={item_id}">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/cross-circle.png" alt="Ignore Item" />' . "\n";
  $toolSet .= 'Remove</a>' . "\n";
  
  // Create a data table view to show the brands
  $dataJobs = new DataTable('sh1', $BF, $query);
  $dataJobs->setOption('alternateRows');
  $dataJobs->setOption('showTopPager');
  $dataJobs->addColumns(array(
                          array(
                            'dataName' => 'sku',
                            'niceName' => 'SKU',
                            'css' => array(
                              'width' => '70px'
                            )
                          ),
                          array(
                            'dataName' => 'name',
                            'niceName' => 'Name'
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
  print $dataJobs->render();
?>
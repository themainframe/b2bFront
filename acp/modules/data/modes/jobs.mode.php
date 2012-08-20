<?php
/**
 * Module: Data
 * Mode: Data Jobs
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

<h1>Data Jobs</h1>
<br />

<div class="panel">
  <div class="contents">
    
    <h3>About Data Jobs</h3>
    
    <p>
      Data Jobs are alerts of missing Inventory information.<br />
      These alerts can help you keep the Inventory up to date by showing you which products are missing data or images.<br /><br />
      
      b2bFront will analyse the Inventory every hour to search for missing data.
      <br /><br />
      
      If there are items you want to ignore you can add them to the
      <a href="./?act=data&mode=jobs_ignore" title="Data Jobs Ignore List">Data Jobs Ignore List</a> using the "Ignore" button.
    </p>
    
  </div>
</div>

<br />

<?php

  // Create a new query to retreieve jobs
  $query = $BF->db->query();
  $query->select('`bf_data_jobs`.*, `bf_items`.`sku` AS `sku`', 'bf_data_jobs, bf_items')
        ->where('bf_data_jobs.item_id = bf_items.id')
        ->group('`bf_data_jobs`.`item_id`');

  // Buttons
  $toolSet  = "\n";
  $toolSet .= '<a class="tool" title="Ignore Item" href="./?act=data&mode=jobs_ignore_do&id={item_id}">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/cross-circle.png" alt="Ignore Item" />' . "\n";
  $toolSet .= 'Ignore Item</a>' . "\n";
  $toolSet .= '<a class="tool" onclick="window.location=window.location;" target="_blank" title="Fix" ' . 
              'href="./?act=data&mode=jobs_fix_proxy&job_id={id}&item_id={item_id}">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/zone--pencil.png" alt="Fix" />' . "\n";
  $toolSet .= 'Fix</a>' . "\n";
  
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
                            'dataName' => 'description',
                            'niceName' => 'Description'
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
  print $dataJobs->render();
?>
<?php
/**
 * Module: System
 * Mode: Configuration
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

<h1>Configuration</h1>
<br />

<div class="panel">
  <div class="contents">
    <h3>About Configuration</h3>
    <p>
      This section allows you to modify the behaviour and appearance of the system.<br />
      You can change various options organised into the categories displayed below. Click on a category to edit settings
      associated with it.
    </p>
  </div>
</div>

<br />

<?php

  // Create a query
  $query = $BF->db->query();
  $query->select('*', 'bf_config_domains');

  // Create a data table view
  $configDomains = new DataTable('cd1', $BF, $query);
  $configDomains->setOption('alternateRows');
  $configDomains->setOption('showTopPager');
  $configDomains->setOption('defaultOrder', array('title', 'asc'));
  $configDomains->addColumns(array(
                          array(
                            'dataName' => 'icon_path',
                            'niceName' => '',
                            'css' => array(
                                            'width' => '16px'
                                          ),
                            'options' => array(
                                           'formatAsImage' => true,
                                           'fixedOrder' => true
                                         )
                          ),
                          array(
                            'dataName' => 'title',
                            'niceName' => 'Name',
                            'css' => array(
                                       'width' => '220px'
                                     ),
                            'options' => array(
                                           'formatAsLink' => true,
                                           'linkURL' => Tools::getModifiedURL(array(
                                                          'mode' => 'config_modify',
                                                          'domain' => '{id}'
                                                        ))
                                         )
                          ),
                          array(
                            'dataName' => 'description',
                            'niceName' => 'Description'
                          )
                        )
                       );
  
  // Render & output content
  print $configDomains->render();
  
?>
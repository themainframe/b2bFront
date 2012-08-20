<?php
/**
 * Module: System
 * Mode: Locales
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

<h1>Locales</h1>
<br />
<div class="panel">
  <div class="contents" style="">
    <h3>About Locales</h3>
    <p>
      Locales are groups of settings applied depending on a location chosen by a user.<br />
      Locales can include currency and language information so that the system can display information in the best way for every user.
      <br /><br />
      Click on an exchange rate value to modify it.
    </p>
  </div>
</div>

<br />

<?php

  // Create a query
  $query = $BF->db->query();
  $query->select('*', 'bf_locales');

  // Create a data table view
  $locales = new DataTable('lo1', $BF, $query);
  $locales->setOption('alternateRows');
  $locales->setOption('showTopPager');
  $locales->addColumns(array(
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
                            'dataName' => 'name',
                            'niceName' => 'Name'
                          ),
                          array(
                            'dataName' => 'language_code',
                            'niceName' => 'Language',
                            'css' => array(
                              'width' => '100px'
                            ),
                          ),
                          array(
                            'dataName' => 'currency_name',
                            'niceName' => 'Currency',
                            'css' => array(
                              'width' => '100px'
                            ),
                          ),
                          array(
                            'dataName' => 'currency_html_entity',
                            'niceName' => 'Symbol',
                            'css' => array(
                              'width' => '100px'
                            ),
                          ),
                          array(
                            'dataName' => 'currency_xr',
                            'niceName' => 'Exchange Rate',
                            'css' => array(
                              'width' => '130px'
                            ),
                            'options' => array(
                              'editable' => true,
                              'editableTable' => 'bf_locales'
                            )
                          )
                        )
                      );
  
  // Render & output content
  print $locales->render();
  
?>
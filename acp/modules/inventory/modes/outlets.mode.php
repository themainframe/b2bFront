<?php
/**
 * Module: Inventory
 * Mode: Outlets
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

?>

<h1>Outlets</h1>
<br />

<div class="panel">
  <div class="contents">
    
    <h3>About Outlets</h3>
    
    <p>
      Outlets are points of sale for the products in the Inventory.<br />
      You can track the sale prices of your products on your dealers' 
      websites and receive automatic notifications when they change.<br /><br />
      
      You can manually refresh outlets by clicking <a onclick="loadingScreen();"
        href="./?act=inventory&mode=outlets_refresh" title="Refresh">here</a>.
    </p>
    
    <br />
    
    <span class="button">
      <a href="./?act=inventory&mode=outlets_add">
        <span class="img" style="background-image:url(/acp/static/icon/store--plus.png)">&nbsp;</span>
        New Outlet...
      </a>
    </span>
 
    <br /><br />
  </div>
</div>

<br />

<?php

  // Create a new query to retreieve Outlets
  $query = $BF->db->query();
  $query->select('`bf_items`.`rrp_price` AS rrpprice, `bf_outlets`.`id`, `bf_outlets`.`price` - `bf_items`.`rrp_price` AS `rrp`, ' . 
                 '`bf_outlets`.`price`,`bf_outlets`.`url`,`bf_outlets`.`state_ok`, `bf_items`.`sku` ' . 
                 'as `item`, `bf_users`.`description` AS `user`, `bf_outlets`.`modification_timestamp`, ' . 
                 '(`bf_outlets`.`price`/`bf_items`.`rrp_price` * 100) AS rrp_percent', 
                 'bf_outlets, bf_items, bf_users')
        ->where('`bf_items`.`id` = `bf_outlets`.`item_id` AND `bf_users`.`id` = `bf_outlets`.`user_id`');
        
  // Define a tool set HTML
  $confirmationJS = 'confirmation(\'Really remove this outlet?\', function() { window.location=\'' .
                    Tools::getModifiedURL(array('mode' => 'outlets_remove_do')) . '&id={id}\'; })';
  
  // Buttons
  $toolSet  = "\n";
  $toolSet .= '<a onclick="' . $confirmationJS . '" class="tool" title="Remove">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/cross-circle.png" alt="Remove" />' . "\n";
  $toolSet .= 'Remove</a>' . "\n";
  $toolSet .= '<a class="tool" title="History" href="./?act=inventory&mode=outlets_history&id={id}">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/clock-history.png" alt="History" />' . "\n";
  $toolSet .= 'History</a>' . "\n";
  
  // Create a data table view to show the outlets
  $brands = new DataTable('ou1', $BF, $query);
  $brands->setOption('alternateRows');
  $brands->setOption('showTopPager');
  $brands->addColumns(array(
                        array(
                          'dataName' => 'state_ok',
                          'niceName' => '',
                          'options' => array(
                                         'formatAsToggleImage' => true,
                                         'toggleImageTrue' => '/acp/static/icon/tick-circle.png',
                                         'toggleImageFalse' => '/acp/static/icon/cross-circle.png',
                                         'toggleImageTrueTitle' => 'Receiving data for this Outlet',
                                         'toggleImageFalseTitle' => 'This Outlet is broken. Click Remove.',
                                         'fixedOrder' => true

                                       ),
                          'css' => array(
                                     'width' => '16px'
                                   )
                        ),
                        array(
                          'dataName' => 'item',
                          'niceName' => 'SKU',
                          'css' => array('width' => '100px')
                        ),
                        array(
                          'dataName' => 'user',
                          'niceName' => 'Dealer'
                        ),
                        array(
                          'dataName' => 'price',
                          'niceName' => 'Dealer\'s Price',
                          'options' => array(
                                         'formatAsPrice' => true
                                       ),
                          'css' => array('width' => '100px')
                        ),
                        array(
                          'dataName' => 'rrpprice',
                          'niceName' => 'RRP',
                          'options' => array(
                                         'formatAsPrice' => true
                                       ),
                          'css' => array('width' => '60px')
                        ),
                        array(
                          'dataName' => 'rrp',
                          'niceName' => '&Delta; RRP',
                          'options' => array(
                                         'formatAsPrice' => true,
                                         'formatAsPosNeg' => true
                                       ),
                          'css' => array('width' => '60px')
                        ),
                        array(
                          'dataName' => 'rrp_percent',
                          'niceName' => '% RRP',
                          'options' => array(
                                         'formatAsPosNegPercentage' => true
                                       ),
                          'css' => array('width' => '60px')
                        ),
                        array(
                          'dataName' => 'modification_timestamp',
                          'niceName' => 'Last Update',
                          'options' => array(
                                         'formatAsDate' => true
                                       ),
                          'css' => array('width' => '135px')
                        ),
                        array(
                          'dataName' => 'url',
                          'niceName' => 'URL',
                          'options' => array(
                                         'newContent' => 'View...',
                                         'formatAsLink' => true,
                                         'linkNewWindow' => true,
                                         'linkURL' => '{url}',
                                         'fixedOrder' => true
                                       ),
                          'css' => array('width' => '50px')
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

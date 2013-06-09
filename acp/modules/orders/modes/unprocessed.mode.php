<?php
/**
 * Module: Orders
 * Mode: Unprocessed
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

// Default ordering
if(!$BF->in('or1_order_d') && !$BF->in('or1_order'))
{
  $BF->setIn('or1_order_d', 'd');
  $BF->setIn('or1_order', '2');
}

?>

<h1>Unprocessed Orders</h1>
<br />

<?php

  if($BF->config->get('tips'))
  {
  
?>

<div class="panel">
  <div class="contents">
    <h3>About Unprocessed Orders</h3>
    <p>
      These are orders that have not yet been marked as processed.<br />
      They do not yet count towards statistics.
    </p>
  </div>
</div>

<br />

<?php
  
  }
  
?>

<?php

  // Create a query
  $query = $BF->db->query();
  $query->text(str_replace("\n", '', '
  
    SELECT `bf_orders`.`owner_id`,
           `bf_orders`.`timestamp` AS `order_timestamp`,
           `bf_orders`.`id` AS `order_id`,
          
           `bf_users`.`id` AS `dealer_id`,
           `bf_users`.`account_code` AS `dealer_code`,
           `bf_users`.`description` AS `dealer_name`,
           
           COUNT(`bf_order_lines`.`id`) AS `order_lines`,
           SUM(`bf_order_lines`.`quantity`) AS `order_units`,
           SUM(`bf_order_lines`.`invoice_price_each` * `bf_order_lines`.`quantity`) AS `total`
           
    FROM `bf_orders` INNER JOIN `bf_order_lines` ON `bf_orders`.`id` = `bf_order_lines`.`order_id`
                     INNER JOIN `bf_users` ON `bf_orders`.`owner_id` = `bf_users`.`id`
    
    WHERE `bf_orders`.`processed` = 0
          AND `bf_orders`.`held` = 0
      
    GROUP BY `bf_orders`.`id`
    
  ') . ' ');

  // Create a data table view
  $orders = new DataTable('or1', $BF, $query);
  $orders->setOption('alternateRows');
  $orders->setOption('showTopPager');
  $orders->setOption('defaultOrder', array('order_timestamp', 'desc'));
  
  $toolSet  = "\n";
  $toolSet .= '<a class="tool" title="View" href="./?act=orders&mode=unprocessed_process&id={order_id}">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/tick-circle.png" alt="View" />' . "\n";
  $toolSet .= 'Process</a>' . "\n";
  $toolSet .= '<a target="_blank" class="tool" title="View" href="./?act=orders&mode=print&id={order_id}">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/printer.png" alt="Print Now" />' . "\n";
  $toolSet .= 'Print</a>' . "\n";
  $toolSet .= '<a class="tool" title="View" href="./?act=orders&mode=unprocessed_view&id={order_id}">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/magnifier.png" alt="View" />' . "\n";
  $toolSet .= 'View</a>' . "\n";
  
  $orders->addColumns(array(
                          array(
                            'dataName' => 'order_id',
                            'niceName' => 'Order ID',
                            'options' => array(
                                           'newContent' =>
                                              $BF->config->get('com.b2bfront.ordering.order-id-prefix', true) . 
                                              '{old}'
                                         ),
                            'css' => array(
                                       'width' => '90px'
                                     )  
                          ),
                          array(
                            'dataName' => 'dealer_name',
                            'niceName' => 'Dealer / Account #',
                            'options' => array(
                                           'newContent' =>
                                              '{dealer_name} ({dealer_code})'
                                         )
                          ),
                          array(
                            'dataName' => 'order_timestamp',
                            'niceName' => 'Date',
                            'options' => array(
                                           'formatAsDate' => true
                                         ),
                            'css' => array(
                                       'width' => '200px'
                                     )  
                          ),
                          array(
                            'dataName' => 'order_lines',
                            'niceName' => 'Lines',
                            'css' => array(
                                       'width' => '55px'
                                     )  
                          ),
                          array(
                            'dataName' => 'order_units',
                            'niceName' => 'Units',
                            'css' => array(
                                       'width' => '55px'
                                     )
                          ),
                          array(
                            'dataName' => 'total',
                            'niceName' => 'Total',
                            'css' => array(
                                       'width' => '55px'
                                     )
                          ),
                          array(
                            'dataName' => '',
                            'niceName' => 'Actions',
                            'options' => array(
                                           'fixedOrder' => true
                                         ),
                            'css' => array(
                                       'width' => '175px'
                                     ),
                            'content' => $toolSet
                          )
                        )
                       );
  
  // Render & output content
  print $orders->render();
  
?>
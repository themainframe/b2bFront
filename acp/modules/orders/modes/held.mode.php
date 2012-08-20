<?php
/**
 * Module: Orders
 * Mode: Held
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

<h1>Held Orders</h1>
<br />

<?php

  if($BF->config->get('tips'))
  {
  
?>

<div class="panel">
  <div class="contents">
    <h3>About Held Orders</h3>
    <p>
      These are orders that have been marked as held.<br />
      Held orders generally require some inquiries before processing is possible.
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
          AND `bf_orders`.`held` = 1
      
    GROUP BY `bf_orders`.`id`
    
  ') . ' ');

  // Create a data table view
  $orders = new DataTable('or3', $BF, $query);
  $orders->setOption('alternateRows');
  $orders->setOption('showTopPager');
  
  $toolSet  = "\n";
  $toolSet .= '<a class="tool" title="View" href="./?act=orders&mode=held_unhold&id={order_id}">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/control-double-green.png" alt="View" />' . "\n";
  $toolSet .= 'Unhold</a>' . "\n";
  $toolSet .= '<a class="tool" title="View" href="./?act=orders&mode=unprocessed_view&id={order_id}">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/magnifier.png" alt="View" />' . "\n";
  $toolSet .= 'View Order</a>' . "\n";
  
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
                                       'width' => '150px'
                                     ),
                            'content' => $toolSet
                          )
                        )
                       );
  
  // Render & output content
  print $orders->render();
  
?>
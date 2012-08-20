<?php
/**
 * Module: Inventory
 * Mode: Arrivals
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

// Show loading screen.
?>

<script type="text/javascript">

  loadingScreen();

</script>

<?php

// Non Blocking mode
Tools::nonBlockingMode();

?>

<div class="ghost" id="dealers" style="padding: 0">
  <table class="data">
    <thead>
      <tr class="header">
        <td style="width: 150px">Username</td>
        <td>Description</td>
      </tr>
    </thead>
    <tbody id="dealers_rows">
      
    </tbody>
  </table>
</div>

<script type="text/javascript">

  $(function() {
  
    $('#dealers').dialog({
      autoOpen: false,
      modal : true,
      title : 'Show Dealers',
      width: 600,
      height: 280,
      buttons: {
                  'Ok' : function() {$(this).dialog('close'); }
               },
      draggable: false,
      resizable: false
    });
  
  });
  
  function showdealer(id)
  {
    // Clear table
    $('#dealers_rows').children().remove();
    
    loadingScreen();
  
    $.getJSON('/acp/ajax/arrivals_show_dealers.ajax.php', 
      {'id' : id}, function(data) {
      
      hideLoadingScreen();

      // Populate rows
      $(data).each(function(k, i) {
        // Create a row
        var row = $('<tr />');
        var username_cell = $('<td />').html(i.name);
        var description_cell = $('<td />').html(i.description);

        // Build
        $(row).append(username_cell)
              .append(description_cell);
          
        // Add to table
        $('#dealers_rows').append(row);
      })
      
      // Show table
      $('#dealers').dialog('open');
      
      return true;
      
    });
  }

</script>



<h1>Back In Stock Notifications</h1>
<br />

<div class="panel">
  <div class="contents">
    
    
    <h3>About Back In Stock Notifications</h3>
    
    <p>
      Back In Stock Items are items that have come back in to stock recently.<br />
      You may wish to dispatch Back In Stock notifications to dealers that have purchased these items in the past.
      <br /><br />
      You should note that these notifications can only be sent for stock updated using the 
      <a href="./?act=data" target="_blank" class="new">Import Data</a> section of the ACP.
    </p>
    
    <br />
    
    <span class="button">
      <a href="./?act=inventory&mode=arrivals_send_do" onclick="loadingScreen();">
        <span class="img" style="background-image:url(/acp/static/icon/mail.png)">&nbsp;</span>
        Send Notifications Now
      </a>
    </span>
    
    <br /><br />
  </div>
</div>

<br />

<?php

  // Create a new query to find arrivals
  $query = $BF->db->query();
  $query->text('SELECT *,`bf_items`.`id` AS itemid FROM `bf_items`,  `bf_stock_replenishments` WHERE `bf_items`.`id` = `bf_stock_replenishments`.`item_id` AND `bf_items`.`stock_free` > 0');
        
  // Define a tool set HTML
  $confirmationJS = 'showdealer({itemid})';
  
  // Buttons
  $toolSet  = "\n";
  $toolSet .= '<a onclick="' . $confirmationJS . '" class="tool" title="Show">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/magnifier.png" alt="Show" />' . "\n";
  $toolSet .= 'Dealers</a>' . "\n";
  $toolSet .= '<a href="./?act=inventory&mode=browse_orders&id={itemid}"
                class="tool" title="Show">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/money-coin.png" alt="Show" />' . "\n";
  $toolSet .= 'Orders</a>' . "\n";
  $toolSet .= '<a href="./?act=inventory&mode=arrivals_ignore_do&id={id}"
               class="tool" title="Ignore">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/cross-circle.png" alt="Ignore" />' . "\n";
  $toolSet .= 'Ignore</a>' . "\n";

  
  // Create a data table view to show the arrivals
  $arrivals = new DataTable('arrivals1', $BF, $query);
  $arrivals->setOption('alternateRows');
  $arrivals->setOption('showTopPager');
  $arrivals->addColumns(array(
                          array(
                            'dataName' => 'sku',
                            'niceName' => 'SKU',
                            'css' => array(
                              'width' => '80px'
                            )
                          ),
                          array(
                            'dataName' => 'name',
                            'niceName' => 'Name'
                          ),
                          array(
                            'dataName' => 'timestamp',
                            'niceName' => 'Arrival Date',
                            'options' => array(
                                           'formatAsDate' => true
                                         ),
                            'css' => array(
                                       'width' => '190px'
                                     )
                          ),
                          array(
                            'dataName' => 'stock_free',
                            'niceName' => 'Current Stock (Free)',
                            'options' => array(
                                           'formatAsPosNeg' => true,
                                           'hidePosNegPlus' => true
                                         ),
                            'css' => array(
                                       'width' => '90px'
                                     )
                          ),
                          array(
                            'dataName' => '',
                            'niceName' => 'Relevant Dealers',
                            'css' => array(
                                       'width' => '80px'
                                     ),
                            'options' => array(
                              'formatAsPosNeg' => true,
                              'hidePosNegPlus' => true,
                              'fixedOrder' => true,
                              'callback' => function($row, $parent)
                                            {
                                              $total = 0;
                                          
                                              // Get the timeout period from config
                                              $timeout = $parent->config->get(
                                                'com.b2bfront.crm.purchase-history-length', true) * 86400;
                                            
                                              // Look up how many dealers have ordered this recently
                                              // Find orders
                                              $orders = $parent->db->query();
                                              $orders->select('*', 'bf_orders')
                                                     ->where('(UNIX_TIMESTAMP() - `timestamp`) < {1}', $timeout)
                                                     ->execute();
                                                     
                                              while($order = $orders->next())
                                              {
                                                // Find lines
                                                $lines = $parent->db->query();
                                                $lines->select('*', 'bf_order_lines')
                                                      ->where('`order_id` = \'{1}\' AND `item_id` = \'{2}\'', 
                                                              $order->id, $row->itemid)
                                                      ->execute();
                                                    
                                                   
                                                while($line = $lines->next())
                                                {
                                                  // + 1
                                                  $total ++;
                                                }     
                                              }
                                            
                                              return $total;
                                            }
                            )
                          ),
                          array(
                            'dataName' => '',
                            'niceName' => 'Actions',
                            'options' => array('fixedOrder' => false),
                            'css' => array(
                                       'width' => '188px',
                                       'text-align' => 'right',
                                       'padding-right' => '10px'
                                     ),
                            'content' => $toolSet
                          )
                      ));
  
  // Render & output content
  print $arrivals->render();
?>

<script type="text/javascript">

  hideLoadingScreen();

</script>
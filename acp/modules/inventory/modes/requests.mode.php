<?php
/**
 * Module: Inventory
 * Mode: Requests for Items
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

<div class="ghost" id="dealers" style="padding: 0">
  <table class="data">
    <thead>
      <tr class="header">
        <td style="width: 150px">Username</td>
        <td>Description</td>
        <td>Type</td>
      </tr>
    </thead>
    <tbody id="dealers_rows">
      
    </tbody>
  </table>
</div>

<div class="ghost" id="contact" style="padding: 10px 0px 0px 12px">
  <span style="font-weight:bold;">
    Write a message/update to send to the requesting dealers about this item:
  </span>
  <br /><br />
  <span class="grey">
    The item SKU and Name will be added automatically
    and can be excluded from your message.
  </span>
  <br /><br />
  <textarea style="padding: 5px; width: 565px; height:130px; resize: none"></textarea>

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

    $('#contact').dialog({
      autoOpen: false,
      modal : true,
      title : 'Contact Dealers',
      width: 600,
      height: 290,
      buttons: {
                  'Cancel' : function() {$(this).dialog('close'); },
                  'Send' : function() {$(this).dialog('close'); }
               },
      draggable: false,
      resizable: false
    });
  
  });
  
  function showdealer(id)
  {
    loadingScreen();
    
    // Clear table
    $('#dealers_rows').children().remove();
  
    $.getJSON('/acp/ajax/requests_show_dealers.ajax.php', 
      {'id' : id}, function(data) {

      hideLoadingScreen();
  
      // Populate rows
      $(data).each(function(k, i) {
        // Create a row
        var row = $('<tr />');
        var username_cell = $('<td />').html(i.name);
        var description_cell = $('<td />').html(i.description);
        var type_cell = $('<td />').html(i.type);
        
        // Build
        $(row).append(username_cell)
              .append(description_cell)
              .append(type_cell);
          
        // Add to table
        $('#dealers_rows').append(row);
      })
      
      // Show table
      $('#dealers').dialog('open');
      
      return true;
      
    });
  }
  
  function contactdealers(id)
  {
    $('#contact').dialog('open');
  }

</script>

<h1>Requests for Stock</h1>
<br />

<div class="panel">
  <div class="contents">
    
    <h3>About Requests for Stock</h3>
    
    <p>
      Requests for Items are created when dealers request a notification for
      the replenishment of an item that is out of stock.<br />
      These requests can help your prioritise purchasing and manage your inventory effectively.
    </p>
  </div>
</div>

<br />

<?php

  // Create a new query to retreieve arrivals
  $query = $BF->db->query();
  $query->text('SELECT `bf_items`.`id` AS id, `bf_items`.`name`, `bf_items`.`sku`, COUNT(`bf_users`.`id`) AS `dealers` FROM `bf_items`, `bf_user_stock_notifications`, `bf_users` ' . 
    'WHERE `bf_users`.`id` = `bf_user_stock_notifications`.`user_id` AND `bf_items`.`id` = `bf_user_stock_notifications`.`item_id` GROUP BY `bf_user_stock_notifications`.`item_id`');
        
  // Define a tool set HTML
  $confirmationJS = 'showdealer({id})';
  $contactJS = 'contactdealers({id})';
  $removalJS = 'confirmation(\'Really cancel these notifications?<br />' . 
               'The requesting dealers will not be informed.\', function() { window.location=\'' .
               Tools::getModifiedURL(array('mode' => 'requests_ignore_do')) . '&id={id}\'; })';
               
  // Buttons
  $toolSet  = "\n";
  /*
  $toolSet .= '<a href="#" onclick="' . $contactJS . '"
                class="tool" title="Show">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/mail.png" alt="Show" />' . "\n";
  $toolSet .= 'Contact</a>' . "\n";
  */
  $toolSet .= '<a href="#" onclick="' . $confirmationJS . '"
                class="tool" title="Show">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/magnifier.png" alt="Show" />' . "\n";
  $toolSet .= 'Dealers</a>' . "\n";
  
  $toolSet .= '<a onclick="' . $removalJS . '" href="#"
               class="tool" title="Ignore">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/cross-circle.png" alt="Ignore" />' . "\n";
  $toolSet .= 'Cancel</a>' . "\n";

  
  // Create a data table view to show the arrivals
  $requests = new DataTable('requests1', $BF, $query);
  $requests->setOption('alternateRows');
  $requests->setOption('showTopPager');
  $requests->setOption('defaultOrder', array('dealers', 'desc'));
  $requests->addColumns(array(
                          array(
                            'dataName' => 'sku',
                            'niceName' => 'SKU',
                            'options' => array(
                              'formatAsLink' => true,
                              'linkNewWindow' => true,
                              'linkURL' => $BF->config->get('com.b2bfront.site.url', true)
                                           . '?option=item&id={id}',
                            ),
                            'css' => array(
                              'width' => '80px'
                            )
                          ),
                          array(
                            'dataName' => 'name',
                            'niceName' => 'Name'
                          ),
                          array(
                            'dataName' => 'dealers',
                            'niceName' => 'Dealers',
                            'css' => array(
                                       'width' => '70px'
                                     )
                          ),
                          array(
                            'dataName' => '',
                            'niceName' => 'Actions',
                            'options' => array('fixedOrder' => false),
                            'css' => array(
                                       'width' => '125px',
                                       'text-align' => 'right',
                                       'padding-right' => '10px'
                                     ),
                            'content' => $toolSet
                          )
                      ));
  
  // Render & output content
  print $requests->render();
?>
<?php
/**
 * Module: Orders
 * Mode: Processed View
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

// Load the order to view
$orderID = $BF->inInteger('id');

// Query for it
$BF->db->select('*', 'bf_orders')
           ->where('id = \'{1}\' AND `processed` = 1', $orderID)
           ->limit(1)
           ->execute();
    
// Success?
if($BF->db->count != 1)
{
  // Failed
  header('Location: ./?act=orders&mode=unprocessed');
  exit();
}

$orderRow = $BF->db->next();
$orderID = $orderRow->id;
$dealerRow = $BF->db->getRow('bf_users', $orderRow->owner_id);
$bandRow = $BF->db->getRow('bf_user_bands', $dealerRow->band_id);

?>

<h1>
  Order 
  <?php print $BF->config->get('com.b2bfront.ordering.order-id-prefix', true) . $orderRow->id; ?>
</h1>

<script type="text/javascript">

  function printIframe(id)
  {
      var iframe = document.frames ? document.frames[id] : document.getElementById(id);
      var ifWin = iframe.contentWindow || iframe;
      iframe.focus();
      ifWin.printMe();
      return false;
  }

  /**
   * Print order
   * @return boolean
   */
  function doPrint()
  {
    // Get the frame and print it
    printIframe('print_frame');
    
    return false;
  }
  
</script>


<br />

<div class="panel">
  <div class="title">Actions</div>
  <div class="contents">
      <span class="button" style="float:left;">
        <a href="./?act=orders&mode=processed_unprocess&id=<?php print $orderID; ?>">
          <span class="img" 
          style="background-image:url(/acp/static/icon/stamp--minus.png)">
          &nbsp;</span>Unmark as Processed
        </a>
      </span>
          
      &nbsp;
      
      <span class="button" style="float:left;">
        <a href="./?act=orders&mode=print&id=<?php print $orderRow->id; ?>" target="_blank">
          <span class="img" 
          style="background-image:url(/acp/static/icon/printer.png)">
          &nbsp;</span>Print
        </a>
      </span>

      &nbsp;
      
      <span class="button" style="float:right;">
        <a href="./?act=orders&mode=processed">
          <span class="img" 
          style="background-image:url(/acp/static/icon/navigation-180.png)">
          &nbsp;</span>Back to Orders
        </a>
      </span>
      
      <br class="clear" />


  </div>
</div>

<br />

<div class="panel">
  <div class="title">Order Information</div>
  <div class="contents">

    <table style="width: 100%;" class="info">
      <tr>
        <td class="key">Submitted</td>
        <td><?php print Tools::longDate($orderRow->timestamp); ?></td>
        <td class="key">Processed</td>
        <td><?php print Tools::longDate($orderRow->processed_timestamp); ?></td>
      </tr>
      <tr>
        <td class="key">Dealer</td>
        <td>
          <?php print $dealerRow->description ?> &nbsp; 
          (<a class="new" target="_blank" 
          href="./?act=dealers&mode=browse_modify&id=<?php print $dealerRow->id; ?>"><?php print $dealerRow->name ?></a>)
        </td>
       <td class="key">Dealer Email</td>
        <td>
          <a class="new" target="_blank" 
          href="mailto:<?php print $dealerRow->email; ?>">
            <?php print $dealerRow->email; ?>
          </a>
        </td>
      </tr>
      <tr>
        <td class="key">Account #</td>
        <td><?php print $dealerRow->account_code;  ?></td>
        <td class="key">Dealer Band</td>
        <td><?php print $bandRow->description ?></td>
      </tr>
    </table>
  </div>
</div>

<br />

<?php

  // Create a query
  $query = $BF->db->query();
  $query->select('`bf_order_lines`.*, `bf_items`.*, `bf_items`.`id` AS itemid,' . 
                 'SUM(`bf_order_lines`.`invoice_price_each` * `bf_order_lines`.`quantity`) AS subtotal',
                 'bf_order_lines')
        ->text('LEFT OUTER JOIN `bf_items` ON `bf_order_lines`.`item_id` = ' .
             '`bf_items`.`id` ')
        ->where('`bf_order_lines`.`order_id` = \'{1}\'', $orderID)
        ->group('`bf_order_lines`.`id`');

  // Define total global
  $GLOBALS['total'] = 0.00;

  // Callback for counting the total
  $calculateTotal = function($row, $parent)
  {
    // Add
    $GLOBALS['total'] += floatval($row->subtotal);
    
    // Pass-thru the subtotal value without change
    return $row->subtotal;
  };

  // Create a data table view
  $order = new DataTable('upr1', $BF, $query);
  $order->setOption('alternateRows');
  $order->setOption('showDownloadOption');
  $order->setOption('showTopPager');
  $order->setOption('showAll');
  $order->addColumns(array(
                          array(
                            'dataName' => 'itemid',
                            'niceName' => '#',
                            'options' => array(
                                           'cardinality' => true,
                                           'fixedOrder' => true
                                         )
                          ),
                          array(
                            'dataName' => 'sku',
                            'niceName' => 'SKU',
                            'css' => array(
                                       'width' => '60px'
                                     )  
                          ),
                          array(
                            'dataName' => 'name',
                            'niceName' => 'Name'
                          ),
                          array(
                            'dataName' => 'quantity',
                            'niceName' => 'Quantity',
                            'css' => array(
                                       'width' => '60px'
                                     )  
                          ),
                          array(
                            'dataName' => 'invoice_price_each',
                            'niceName' => 'Each @',
                            'css' => array(
                                       'width' => '60px'
                                     )  
                          ),
                          array(
                            'dataName' => 'subtotal',
                            'niceName' => 'Subtotal',
                            'options' => array(
                                           'callback' => $calculateTotal
                                         ),
                            'css' => array(
                                       'width' => '75px'
                                     )  
                          )                        )
                       );
  
  // Render & output content
  print $order->render();
  
?>

<br />

<table style="width:100%;">

  <tbody>
  
    <tr>
    
      <td style="padding: 0px 15px 0px 0px;">
      
        <div class="panel" style="min-height: 115px; background: #fff;">
          <div class="title">Order Notes</div>
          <div class="contents" style="padding: 0; background: #fff;">
            
<?php
  
  // Find all notes
  $notes = $BF->db->query();
  $notes->select('*', 'bf_order_notes')
        ->where('`order_id` = \'{1}\'', $orderID)
        ->order('timestamp', 'asc')
        ->execute();
        
  // No notes?
  if($notes->count == 0)
  {
    print '<br /><br /><br />';
  }
        
  while($note = $notes->next())
  {
    ?>
          
            <div class="note<?php print ($notes->last() ? ' last' : ''); ?>">
              <?php
                if($note->author_is_staff)
                {
              ?>
              <img src="/acp/static/image/aui-staff.png" alt="Staff" class="middle" />&nbsp;
              <?php
                }
                else
                {
              ?>
              <img src="/acp/static/image/aui-dealer.png" alt="Staff" class="middle" />&nbsp;
              <?php
                }
              ?>
              <?php
                if($note->staff_only)
                {
              ?>
              <img src="/acp/static/image/aui-invisible.png" alt="Staff Only" class="middle" />&nbsp;
              <?php
                }
              ?>
                
              
                On <?php print Tools::longDate($note->timestamp); ?>
                <strong><?php print $note->author_name; ?></strong> wrote:
                            
              <a href="./?act=orders&mode=unprocessed_remove_note_do&id=<?php print $note->id; ?>"
                style="float: right;" title="Remove Note">
                <img src="/acp/static/icon/cross-circle.png" alt="Remove" />
              </a>
              
              <br /><br />
              <?php print str_replace("\n", '<br />', strip_tags($note->content)); ?>
            </div>
          
    <?php
  }

?>
            <div style="text-align: center;">              
              <span class="button">
                <a href="./?act=orders&mode=unprocessed_add_note&id=<?php print $orderID; ?>">
                  <span class="img" 
                  style="background-image:url(/acp/static/icon/plus-circle.png)">
                  &nbsp;</span>Attach a Note...
                </a>
              </span>
            </div>

            <br /><br />
          </div>
        </div>
                  
      </td>

      <td style="width: 130px;">
  
        <div class="panel" style="background: #fff;">
          <div class="title">Total</div>
          <div class="contents" id="statOptionsContainer" style="text-align: center;">
            <span class="grey"><strong><?php print $query->count; ?></strong>
            lines, totalling:</span><br /><br />
            <span style=" font-size: 15pt;">
              <?php
                print number_format($GLOBALS['total'], 2);
              ?>
            </span>
          </div>
        </div>
      
      </td>

    </tr>
    
  </tbody>

</table>

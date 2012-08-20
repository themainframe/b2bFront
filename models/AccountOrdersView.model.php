<?php
/** 
 * Model: Account Orders View
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class AccountOrdersView extends RootModel
{
  /**
   * Produces output and stores it in the value collection.
   * @return boolean
   */
  public function execute()
  {
    // Load parent defaults
    parent::execute();
    
    // Load the order
    $order = new BOMOrder($this->parent->inInteger('id'), $this->parent);
    
    // Exists?
    if(!$order->id || $order->owner_id != $this->parent->security->UID)
    {
      // Stop rendering
      $this->parent->go('./?option=account_orders');
      return false;
    }
    
    $niceOrderID = $this->parent->config->get('com.b2bfront.ordering.order-id-prefix', true) . 
      $order->id;
    
    // Set values
    $this->addValue('order_id', $niceOrderID);
    $this->addValue('id', $order->id);
    $this->addValue('order_date', Tools::longDate($order->timestamp));
    $this->addValue('order_status', 
      ($order->processed == 1 ? 'processed' : ($order->held == 1 ? 'held' : 'unprocessed')));
  
    
    // Make notes available
    $this->addValue('order_notes_count', count($order->notes));
    $this->addValue('order_notes', $order->notes);
    
    // Get order contents
    $orderContents = $this->parent->db->query();
    $orderContents->select('`bf_order_lines`.*, `bf_items`.*, `bf_items`.`id` AS itemid,' . 
                           'SUM(`bf_order_lines`.`invoice_price_each` * `bf_order_lines`.`quantity`) AS subtotal',
                           'bf_order_lines')
                  ->text('LEFT OUTER JOIN `bf_items` ON `bf_order_lines`.`item_id` = ' .
                       '`bf_items`.`id` ')
                  ->where('`bf_order_lines`.`order_id` = \'{1}\'', $order->id)
                  ->group('`bf_order_lines`.`id`');    
                  
  // Build a table
  // Create a data table view
  $order = new DataTable('or1', $this->parent, $orderContents);
  $order->setOption('alternateRows');
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
                            'css' => array(
                                       'width' => '75px'
                                     )  
                          )                        )
                       );
  
    // Render content
    $this->addValue('table', $order->render());
                  
    // Update CCTV
    $this->parent->security->action('View Order: ' . $niceOrderID);

    // Set this model's title and tab
    $this->addValue('title', $this->parent->config->get('com.b2bfront.site.title', true) . 
                    ' - Order: ' . $niceOrderID);
    $this->addValue('tab_account', 'selected');
    
    return true;
  }
}  
?>
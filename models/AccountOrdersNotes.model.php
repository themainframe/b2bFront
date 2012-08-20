<?php
/** 
 * Model: Account Orders Notes View
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class AccountOrdersNotes extends RootModel
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
    
    $niceOrderID = $this->parent->config->get('com.b2bfront.ordering.order-id-prefix', true) . 
      $order->id;
    
    // Exists and belongs to me?
    if(!$order->id || $order->owner_id != $this->parent->security->UID)
    {
      // Stop rendering
      $this->parent->go('./?option=account_orders');
      return false;
    }
    
    // Adding?
    $this->addValue('add', $this->parent->in('add'));
    
    // Perform?
    if($this->parent->in('add') == '1' && $this->parent->in('do') == 1 
      && trim($this->parent->in('note')) != '')
    {
      // Perform add action
      $noteText = trim($this->parent->in('note'));
      
      // Insert
      $this->parent->db->insert('bf_order_notes', array(
                                  'author_name' => $this->parent->security->attributes['description'],
                                  'content' => $noteText,
                                  'timestamp' => time(),
                                  'order_id' => $order->id
                               ))
                       ->execute();
                       
      // Send notifications
      $this->parent->notifier->send(
                                  'note_added',
                                  'Noted Added to Order',
                                  $this->parent->security->attributes['description'] . 
                                  ' has added a note to Order ' . $niceOrderID . '.',
                                  $this->parent->security->attributes['description'] . 
                                  ' has added the following note to Order ' . $niceOrderID . ': <br /><br />' .
                                  $noteText ,
                                  
                                  'sticky-note--plus.png'                         
                               );
                       
      // Unset add screen
      $this->addValue('add', '0');
    }
    

    
    // Set values
    $this->addValue('order_id', $niceOrderID);
    $this->addValue('id', $order->id);
    
    // Make notes available
    $this->addValue('order_notes_count', count($order->notes));
    $this->addValue('order_notes', $order->notes);
    
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
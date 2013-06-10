<?php
/** 
 * Model: Submit Order 
 * Submit the current basket as an order
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class Submit extends RootModel
{
  /**
   * Produces output and stores it in the value collection.
   * @return boolean
   */
  public function execute()
  {
    // Load parent defaults
    parent::execute();
    
    $this->addValue('submit_title', 'Submit Order');
    
    // Count basket items
    if($this->parent->cart->count() == 0)
    {
      // Submission disallowed for empty cart
      $this->parent->go('./?option=basket');
      return false;
    } 

    // Submitting showtime?
    if($this->parent->in('do'))
    {
      $this->addValue('submit_title', 'Order Submitted');
      $this->addValue('done', 1);
      
      // Perform submission
      $this->parent->db->insert('bf_orders', array(
                                 'timestamp' => time(),
                                 'owner_id' => $this->parent->security->UID
                               ))
                       ->execute();
                       
      $orderID = $this->parent->db->insertID;
      
      // Provide order ID to view
      $this->addValue('order_id', $orderID);
      
      // Update CCTV
      $this->parent->security->action('Submitting an order.');
      
      // Load basket items
      $basketItems = $this->parent->db->query();
      $basketItems->select('`bf_user_cart_items`.*, `bf_items`.*, `bf_items`.`id` AS itemid',
                           'bf_user_cart_items')
                  ->text('LEFT OUTER JOIN `bf_items` ON `bf_user_cart_items`.`item_id` = ' .
                       '`bf_items`.`id` ')
                  ->where('`bf_user_cart_items`.`user_id` = \'{1}\'', $this->parent->security->UID)
                  ->group('`bf_user_cart_items`.`id`')
                  ->execute();
                  
      // Any?
      if($basketItems->count == 0)
      {
        // No more rendering
        $this->parent->go('./?option=basket');
        exit();
      }
                
      // Create a pricer to find prices for items
      $pricer = new Pricer(& $this->parent);
      
      // Record totals
      $grossTotal = 0.00;
      $netTotal = 0.00;
              
      // HTML Table for Notifications
      $table = '<table width="100%" style="width: 100%;">
        <thead>
          <tr>
            <td style="padding: 3px; font-weight: bold; background: #dfdfdf; width: 30px;" width="30">#</td>
            <td style="padding: 3px; font-weight: bold; background: #dfdfdf; width: 100px;" width="100">SKU</td>
            <td style="padding: 3px; font-weight: bold; background: #dfdfdf; width: 250px;" width="250">Name</td>
            <td style="padding: 3px; font-weight: bold; background: #dfdfdf; width: 90px;" width="90">Each</td>
            <td style="padding: 3px; font-weight: bold; background: #dfdfdf; width: 90px;" width="90">Qty</td>
            <td style="padding: 3px; font-weight: bold; background: #dfdfdf; width: 90px;" width="90">Subtotal</td>
          </tr>
        </thead>
        <tbody>
      ';
              
      // Copy in to order
      $index = 0;
      while($basketItem = $basketItems->next())
      {
        $index ++;
      
        $this->parent->db->insert('bf_order_lines', array(
                                   'quantity' => $basketItem->quantity,
                                   'invoice_price_each' => 
                                     $pricer->each($basketItem, $basketItem->quantity),
                                   'item_id' => $basketItem->itemid,
                                   'order_id' => $orderID
                                 ))
                         ->execute();
                         
        // Get item
        $item = $this->parent->db->getRow('bf_items', $basketItem->itemid);
                         
        // Tally total
        $subTotal = $pricer->subtotal($basketItem, $basketItem->quantity);
        $grossTotal += $subTotal;
        $netTotal += $subTotal - ($basketItem->cost_price * $basketItem->quantity);
                         
        // Decriment stock values
        $this->parent->db->text('UPDATE `bf_items` SET `stock_free` = `stock_free` - \'' . 
                                $basketItem->quantity . '\'')
                         ->where('`id` = \'{1}\'', $basketItem->itemid)
                         ->limit(1)
                         ->execute();
                         
                         
        // Add to HTML table
        $table .= '      
            <tr>
              <td>' . $index . '</td>
              <td>' . $item->sku . '</td>
              <td>' . $item->name . '</td>
              <td>' . $pricer->each($basketItem, $basketItem->quantity) . '</td>
              <td>' . $basketItem->quantity . '</td>
              <td>' . $pricer->subtotal($basketItem, $basketItem->quantity) . '</td>
            </tr>
        ';
      }
      
      // Finish table
      $table .= '
          </tbody>
        </table>
      ';
      
      

            
      // Add total to statistics
      $this->parent->stats->increment('com.b2bfront.stats.financial.total-gross', $grossTotal);
      $this->parent->stats->increment('com.b2bfront.stats.financial.total-net', $netTotal);
      
      // +1 orders submitted
      $this->parent->stats->increment('com.b2bfront.stats.users.orders-submitted', 1);
      
      // Send notifications
      $this->parent->notifier->send(
                                  'new_order',
                                  
                                  'Order Submitted by ' . $this->parent->security->attributes['description'] . ' (' . $this->parent->security->attributes['account_code'] . ')',
                                  
                                  // Short message:
                                  
                                  $this->parent->security->attributes['description'] . 
                                  ' has submitted an order with ID PN' . $orderID . ' (' . $this->parent->cart->count() . 
                                  ' lines, ~ ' . $grossTotal . ' gross total).' ,
                                  
                                  // Long message:
                                  
                                  $this->parent->security->attributes['description'] . 
                                  ' has submitted an order with ID ' . $orderID . ' (' . $this->parent->cart->count() . 
                                  ' lines, ~ ' . $grossTotal . ' gross total):<br /><br /><br />' . 
                                  
                                  $table . '<br /><br /><br />' .
                                  
                                  ($this->parent->in('notes') != '' ? 
                                    '<strong>Notes: &nbsp;</strong> ' . 
                                    
                                    strip_tags($this->parent->in('notes')) . 
                                    
                                    '<br /><br /><br />'
                                  
                                  : '' ) . 
                                  
                                    '<strong>Account details:</strong><br /><br /> ' .
                                  '&nbsp; &nbsp; <strong>Account code:</strong> &nbsp; &nbsp; ' . $this->parent->security->attributes['account_code'] . '<br /> ' . 
                                  '&nbsp; &nbsp; <strong>Phone Number:</strong> &nbsp; &nbsp; ' . $this->parent->security->attributes['phone_landline'] . '<br /> ' . 
                                  '&nbsp; &nbsp; <strong>Email address:</strong> &nbsp; &nbsp; ' . $this->parent->security->attributes['email'] . '<br /> ' 
                                
                                  ,
                                  
                                  
                                  'money-coin.png',
                                  $this->parent->config->get('com.b2bfront.ordering.order-email-template', true)                        
                               );

      // Add a note if the user provided one
      if($this->parent->in('notes') != '')
      {
        $this->parent->db->insert('bf_order_notes', array(
                                   'content' => $this->parent->in('notes'),
                                   'author_name' => $this->parent->security->attributes['description'],
                                   'order_id' => $orderID,
                                   'timestamp' => time()
                                 ))
                         ->execute();
      }
      
      
      // --------------------------------------------------
      // Generate email for users
      // --------------------------------------------------
      // HTML Table for User
      $userTable = '<table width="100%" style="width: 100%;">
        <thead>
          <tr>
            <td style="padding: 3px; font-weight: bold; background: #dfdfdf; width: 30px;" width="30">#</td>
            <td style="padding: 3px; font-weight: bold; background: #dfdfdf; width: 100px;" width="100">SKU</td>
            <td style="padding: 3px; font-weight: bold; background: #dfdfdf; width: 250px;" width="250">Name</td>
            <td style="padding: 3px; font-weight: bold; background: #dfdfdf; width: 90px;" width="90">Each</td>
            <td style="padding: 3px; font-weight: bold; background: #dfdfdf; width: 90px;" width="90">Qty</td>
            <td style="padding: 3px; font-weight: bold; background: #dfdfdf; width: 90px;" width="90">Subtotal</td>
          </tr>
        </thead>
        <tbody>
      ';
              
      // Copy in to order
      $index = 0;
      $basketItems->rewind();
      
      while($basketItem = $basketItems->next())
      {
        $index ++;
                         
        // Get item
        $item = $this->parent->db->getRow('bf_items', $basketItem->itemid);
                         
        // Tally total
        $subTotal = $pricer->subtotal($basketItem, $basketItem->quantity);
        $grossTotal += $subTotal;
        $netTotal += $subTotal - ($basketItem->cost_price * $basketItem->quantity);

                         
        // Add to HTML table
        $userTable .= '      
            <tr>
              <td>' . $index . '</td>
              <td>' . $item->sku . '</td>
              <td>' . $item->name . '</td>
              <td>' . $pricer->each($basketItem, $basketItem->quantity) . '</td>
              <td>' . $basketItem->quantity . '</td>
              <td>' . $pricer->subtotal($basketItem, $basketItem->quantity) . '</td>
            </tr>
        ';
      }
      
      // Finish table
      $userTable .= '
          </tbody>
        </table>
      ';
      
      // Mail the user table to the user
      // Build an email
      $templateName = $this->parent->config->get('com.b2bfront.mail.default-template', true);
      $XMLfile = BF_ROOT . '/extensions/mail_templates/' . $templateName . '/template.xml';
      
      // Load XML
      $XMLdata = simplexml_load_file($XMLfile);
      $templateTitle = (string)$XMLdata->description;
      $templateContentName = (string)$XMLdata->content;
      
      // Build path
      $contentPath = BF_ROOT . '/extensions/mail_templates/' . $templateName . 
        '/' . $templateContentName;
        
      // Create email object
      $email = new Email(& $this->parent);
      $email->loadFromFile($contentPath);
      
      // Add recipient
      $email->addRecipient($this->parent->security->attributes['email'], $this->parent->security->attributes);
      
      // Set subject
      $subject = 'Order submitted: PN' . $orderID;
      $email->setSubject($subject);
      
      // Build content
      $content  = 'Hello,<br /><br />' . "\n";
      $content .= 'This is a notification that your order (PN' . $orderID . ') has been received. <br /><br /><br />' . "\n";
      $content .= $userTable;
      $content .= '<br /><br /><br />';
      $content .= 'You can log in to your account and visit the <strong>Account -> Orders</strong> section to review this order and attach further notes.<br /><br /><br />Thank you for your business!<br />- ' . $this->parent->config->get('com.b2bfront.site.title', true) . '';
      
      // Set template values
      $email->assign(array(
        'date' => Tools::longDate(),
        'title' => $subject,
        'content' => $content,
        'url' => $this->parent->config->get('com.b2bfront.site.url', true) 
      ));
      
      // Set from address and name
      $email->from = $this->parent->config->get('com.b2bfront.mail.from-auto-address', true);
      $email->fromName = $this->parent->config->get('com.b2bfront.mail.from-auto', true);
      
      // Send
      $email->send();
      
      // Clear basket
      $this->parent->cart->clear();
      $this->addValue('basketCount', 0);
    }

    return true;
  }
}  
?>
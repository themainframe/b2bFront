<?php
/** 
 * Model: Account Past Orders
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class AccountOrders extends RootModel
{
  /**
   * Produces output and stores it in the value collection.
   * @return boolean
   */
  public function execute()
  {
    // Load parent defaults
    parent::execute();

    // Update CCTV
    $this->parent->security->action('Account Past Orders');

    // Set this model's title and tab
    $this->addValue('title', $this->parent->config->get('com.b2bfront.site.title', true) . 
                    ' - My Past Orders');
    $this->addValue('tab_account', 'selected');
  
    // Logged in?
    if(!$this->parent->security->loggedIn())
    {
      $this->parent->loadView('login');
   
      return false;
    }
    
    // Create a query
    $query = $this->parent->db->query();
    $query->text(str_replace("\n", '', '
    
      SELECT `bf_orders`.`owner_id`,
             `bf_orders`.`timestamp` AS `order_timestamp`,
             `bf_orders`.`id` AS `order_id`,
             `bf_orders`.`processed` AS `order_processed`,
             `bf_orders`.`held` AS `order_held`,
            
             `bf_users`.`id` AS `dealer_id`,
             `bf_users`.`account_code` AS `dealer_code`,
             `bf_users`.`description` AS `dealer_name`,
             
             COUNT(`bf_order_lines`.`id`) AS `order_lines`,
             SUM(`bf_order_lines`.`quantity`) AS `order_units`,
             SUM(`bf_order_lines`.`invoice_price_each` * `bf_order_lines`.`quantity`) AS `total`
             
      FROM `bf_orders` INNER JOIN `bf_order_lines` ON `bf_orders`.`id` = `bf_order_lines`.`order_id`
                       INNER JOIN `bf_users` ON `bf_orders`.`owner_id` = `bf_users`.`id`

      WHERE `bf_orders`.`owner_id` = \'' . intval($this->parent->security->UID) . '\'

      GROUP BY `bf_orders`.`id`
      
    ') . ' ');
             
    // Define the button to view an order
    $viewButton  = "\n";
    $viewButton .= '<a href="./?option=account_orders_view&id={order_id}" class="shadowbox">' . "\n";
    $viewButton .= '  <img src="/share/icon/general/navigation.png" ' . 
                   'class="middle" alt="View" />' . "\n";
    $viewButton .= '  View' . "\n";
    $viewButton .= '</a>' . "\n";
             
    // Construct table
    $dataView = new DataTable('orders', $this->parent, $query);
    $dataView->setOption('alternateRows');
    $dataView->setOption('showTopPager');
    $dataView->setOption('defaultOrder', array('order_timestamp', 'desc'));
    $dataView->setOption('subjectName', 'Item');
    $dataView->addColumns(array(
                            array(
                              'dataName' => 'order_id',
                              'niceName' => 'Order ID',
                              'options' => array(
                                             'newContent' =>
                                                $this->parent->config->get('com.b2bfront.ordering.order-id-prefix', true) . 
                                                '{old}'
                                           ),
                              'css' => array(
                                         'width' => '70px'
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
                              'dataName' => 'processed',
                              'niceName' => 'Status',
                              'options' => array(
                                             'callback' => function($row) {
                                               if($row->order_processed == '1')
                                               {
                                                 return '<img src="/share/icon/general/tick-circle.png"' . 
                                                        ' class="middle" /> Processed';
                                               }
                                               else
                                               {
                                                 if($row->order_held == '1')
                                                 {
                                                   return '<img src="/share/icon/general/exclamation-octagon.png"' . 
                                                          ' class="middle" /> On Hold';
                                                 }
                                                 else
                                                 {
                                                   return '<img src="/share/icon/general/hourglass.png"' . 
                                                          ' class="middle" /> Waiting...';
                                                 }
                                               }
                                             }
                                           ),
                              'css' => array(
                                         'width' => '55px'
                                       )
                            ),
                           array(
                             'dataName' => '',
                             'niceName' => '',
                             'css' => array(
                                        'width' => '40px'
                                      ),
                             'options' => array(
                                            'newContent' => $viewButton,
                                            'fixedOrder' => true
                                          )
                           )
                          )
                         );

    // Add the table to the view template
    $this->addValue('table', $dataView->render());
    
    return true;
  }
}  
?>
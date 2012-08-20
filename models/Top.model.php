<?php
/** 
 * Model: Top x Lines
 * Show our best x Lines
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class Top extends RootModel
{
  /**
   * Produces output and stores it in the value collection.
   * @return boolean
   */
  public function execute()
  {
    // Load parent defaults
    parent::execute();

    // How many lines to show
    $lines = $this->parent->config->get('com.b2bfront.site.top-lines-count', true);
    
    // Provide to view
    $this->addValue('count', $lines);
    
    // Update CCTV
    $this->parent->security->action('Top ' . $lines . ' Lines');
    
    // Set this model's title
    $this->addValue('title', $this->parent->config->get('com.b2bfront.site.title', true) 
                             . ' - ' . 'Top ' . $lines . ' Lines');
    
    // Get all order lines
    $orderLines = $this->parent->db->query();
    $orderLines->select('*', 'bf_order_lines')
               ->order('quantity', 'desc')
               ->execute();
               
    // Build an ordered hash of item IDs
    $items = array();
    while($orderLine = $orderLines->next())
    {
      $items[$orderLine->item_id] += $orderLine->quantity;
    }
    
    // Order the array in reverse, maintaining indeces
    arsort($items);

    // Get the keys
    $keys = array_slice(array_keys($items), 0, $lines);
    $keysHash = Tools::CSV($keys);
    
    // Find all items
    $query = $this->parent->db->query();
    $query->select('*', 'bf_items')
          ->where('`id` IN ({1})', ($keysHash == '' ? '0' : $keysHash));
              
    // Precache cart values
    $this->parent->cart->prefetch();
      
    // Construct table
    $dataView = new DataTable('items', & $this->parent, $query);
    $dataView->setOption('alternateRows');
    $dataView->setOption('showTopPager');
    $dataView->setOption('subjectName', 'Item');
    $dataView->addColumns($this->defaultColumns);

    // Add the table to the view template
    $this->addValue('table', $dataView->render());
    
    return true;
  }
}  
?>
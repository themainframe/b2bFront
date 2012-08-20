<?php
/** 
 * Model: Category
 * Provides a category listing
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class Category extends RootModel
{
  /**
   * Produces output and stores it in the value collection.
   * @return boolean
   */
  public function execute()
  {
    // Load parent defaults
    parent::execute();

    // Get the category information
    $category = $this->db->getRow('bf_categories', $this->parent->inInteger('id'));
    
    // Not found?
    if(!$category || !$category->visible)
    {
      // Override the view
      $this->parent->loadView('alert');
      $this->addValue('alertText', 'That category does not exist.');
      
      return false;
    }
    
    // Update CCTV
    $this->parent->security->action('Category: ' . $category->name);
    
    // Set this model's title
    $this->addValue('title', $this->parent->config->get('com.b2bfront.site.title', true) 
      . ' - ' . $category->name);
    
    // Category was found
    $this->addValue('exists', 1); 
    $this->addValue('category_name', $category->name);
    $this->addValue('category_id', $category->id);
    
    // Find subcategories
    $subcategories = $this->parent->db->query();
    $subcategories->select('*', 'bf_subcategories')
                  ->where('`category_id` = \'{1}\'', $category->id)
                  ->execute();
                  
    $this->addValue('subcategory_count', $subcategories->count);
    $this->addValue('subcategories', $subcategories->assoc());
    
    // Selected subcategory?
    $selectedSubcategory = $this->parent->inInteger('subcategory');
    if($selectedSubcategory)
    {
      // Make selected subcategory available
      $this->addValue('current_subcategory', $selectedSubcategory);
      $subcategory = $this->parent->db->getRow('bf_subcategories', $selectedSubcategory);
      $this->addValue('subcategory_name', $subcategory->name);
    }
    
    // Find the contents of the category
    if($this->parent->config->get('com.b2bfront.site.pcr-list-display-mode', true))
    {
      $query = $this->db->query();
      $query->select('0 AS is_parent, parent_item_id, id, sku, name, trade_price, wholesale_price, pro_net_price, pro_net_qty, category_id,' . 
                   'rrp_price, cost_price, stock_free, visible, parent_item_id', 'bf_items')
            ->where('visible = 1 AND category_id = \'{1}\' AND parent_item_id=-1 ' . 
                    ($selectedSubcategory ? ' AND `subcategory_id` = \'{2}\'' : ''), 
                    $this->parent->inInteger('id'), $selectedSubcategory)
            ->text('UNION SELECT 1 AS is_parent, -1 AS parent_item_id, id, sku, name, trade_price, pro_net_price,' .
                   'pro_net_qty,category_id, rrp_price, cost_price, NULL, 1, -1 FROM `bf_parent_items`')
            ->where('((category_id = \'{1}\' ' . ($selectedSubcategory ? ' AND `subcategory_id` = \'{2}\'' : '') .
                    '))' , $this->parent->inInteger('id'), $selectedSubcategory);
    }
    else
    {
      $query = $this->db->query();
      $query->select('0 AS is_parent, parent_item_id, id, sku, name, trade_price, pro_net_price, pro_net_qty, category_id, wholesale_price,' . 
                   'rrp_price, cost_price, stock_free, visible, parent_item_id', 'bf_items')
            ->where('visible = 1 AND category_id = \'{1}\'' . 
                    ($selectedSubcategory ? ' AND `subcategory_id` = \'{2}\' OR (SELECT `subcategory_id` FROM `bf_parent_items` WHERE `id` = parent_item_id) = \'{2}\'' : ''),  
                    $this->parent->inInteger('id'), $selectedSubcategory);
    }
                    
    // Precache cart values
    $this->parent->cart->prefetch();
      
    // Construct table
    $dataView = new DataTable('items', & $this->parent, $query);
    $dataView->setOption('alternateRows');
    $dataView->setOption('showTopPager');
    $dataView->setOption('showBottomPager');
    $dataView->setOption('subjectName', 'Item');
    $dataView->addColumns($this->defaultColumns);

    // Add the table to the view template
    $this->addValue('table', $dataView->render());
    
    return true;
  }
}  
?>
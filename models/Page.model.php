<?php
/** 
 * Model: Page
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class Page extends RootModel
{
  /**
   * Produces output and stores it in the value collection.
   * @return boolean
   */
  public function execute()
  {
    // Load parent defaults
    parent::execute();
    
    // Try to load the page
    $page = $this->db->getRow('bf_pages', $this->parent->inInteger('id'));
    
    // Exists? 
    if(!$page)
    {
      $this->addValue('page_title', 'Not Found');
      $this->addValue('page_content', 'Sorry, we can\'t find the page you are looking for.');
      return false;
    }
    else
    {
      $this->addValue('title', $this->parent->config->get('com.b2bfront.site.title', true) . 
                      ' - ' . $page->title);
      $this->addValue('page_title', $page->title);
      $this->addValue('page_id', $page->id);
      $this->addValue('page_content', $page->content);
    }
    
    // Update CCTV
    $this->parent->security->action('Page: ' . $page->title);
    
    // Select the tab
    $this->addValue('tab_page_' . $this->parent->inInteger('id'), 'selected');
    
    // Pages Viewed + 1
    $this->parent->stats->increment('com.b2bfront.stats.users.pages-viewed', 1);
  
    return true;
  }
}  
?>
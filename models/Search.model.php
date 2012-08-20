<?php
/** 
 * Model: Search
 * Provides a search listing
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class Search extends RootModel
{
  /**
   * Produces output and stores it in the value collection.
   * @return boolean
   */
  public function execute()
  {
    // Load parent defaults
    parent::execute();

    // Get the query
    $queryTerm = $this->parent->in('term');
    
    // Valid term?
    if(trim($queryTerm) == '')
    {
      // Update CCTV
      $this->parent->security->action('Search');
      
      $this->addValue('table', '');
      return true;
    }
    
    // Update CCTV
    $this->parent->security->action('Search: ' . $queryTerm);
    
    // Set this models title
    $this->addValue('title', $this->parent->config->get('com.b2bfront.site.title', true) . 
                    ' - Search');
    $this->addValue('tab_search', 'selected');
    
    // Make term available
    $this->addValue('term', $queryTerm);
    
    // Find the results
    $query = $this->db->query();
    $query->select('*', 'bf_items')
          ->where('`visible` = 1 AND (name LIKE \'%{1}%\' OR sku LIKE \'{1}%\')', $queryTerm);
             
    // Construct table
    $dataView = new DataTable('search', & $this->parent, $query);
    $dataView->setOption('alternateRows');
    $dataView->setOption('showTopPager');
    $dataView->setOption('subjectName', 'Item');
    $dataView->addColumns($this->defaultColumns);

    // Add the table to the view template
    $this->addValue('table', $dataView->render());
    $this->addValue('matchCount', $dataView->rowCount);
    
    // Searches + 1
    $this->parent->stats->increment('com.b2bfront.stats.users.searches', 1);
        
    return true;
  }
}  
?>
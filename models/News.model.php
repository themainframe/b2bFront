<?php
/** 
 * Model: News
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class News extends RootModel
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
    $this->parent->security->action('News');
    
    // Set the title and tab name
    $this->addValue('title', $this->parent->config->get('com.b2bfront.site.title', true) . 
                    ' - News');
    $this->addValue('tab_news', 'selected');
    
    // Load the news article category from config
    $newsCategory = $this->parent->config->get('com.b2bfront.site.news-article-category', true);
    
    // Make ID available to the view
    $this->addValue('id', $this->parent->in('id'));
    
    if($this->parent->in('id') == '')
    {
      
      // Find articles in the category
      $articles = $this->parent->db->query();
      $articles->select('*', 'bf_articles')
               ->where('`article_category_id` = \'{1}\' AND `type` = \'ART_TEXT\' AND ' . 
                       '((`expiry_timestamp` - UNIX_TIMESTAMP()) > 0 OR `expiry_timestamp` = 0)', $newsCategory);
      
      // Build table
      $dataView = new DataTable('news', $this->parent, $articles);
      $dataView->setOption('alternateRows');
      $dataView->setOption('showTopPager');
      $dataView->setOption('defaultOrder', array('timestamp', 'desc'));
      $dataView->setOption('subjectName', 'Article');
      
      // Build columns
      $dataView->addColumns(array(
                              array(
                                'dataName' => 'timestamp',
                                'niceName' => 'Date',
                                'options' => array(
                                               'formatAsDate' => true
                                             ),
                                'css' => array(
                                           'width' => '170px'
                                         )  
                              ),
                              array(
                                'dataName' => 'name',
                                'niceName' => 'Title',
                                'options' => array(
                                               'formatAsLink' => true,
                                               'linkURL' => './?option=news&id={id}'
                                             )
                              )
                           )
                         );
  
      // Add the table to the view template
      $this->addValue('table', $dataView->render());
    }
    else
    {
      // Load the article
      $article = $this->parent->db->getRow('bf_articles', $this->parent->inInteger('id'));
      
      // Readable?
      // Only ART_TEXT type articles are permitted.
      if(!$article || $article->article_category_id != $newsCategory ||
        $article->type != 'ART_TEXT')
      {
        // Stop rendering model
        $this->parent->go('./?option=news');
        return false;
      }
      
      // Make article details available
      $this->addValue('article_name', $article->name);
      $this->addValue('article_text', strval($article->content));
      
    }
  
    return true;
  }
}  
?>
<?php
/** 
 * Model: Item Question Submission
 * Allow the user to ask a question about an item.
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class ItemQuestion extends RootModel
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
    $this->parent->security->action('Ask a Question About an Item');

    // Set this model's title and tab
    $this->addValue('title', $this->parent->config->get('com.b2bfront.site.title', true) . 
                    ' - Ask a Question');
    $this->addValue('tab_home', 'selected');
  
    // Logged in?
    if(!$this->parent->security->loggedIn())
    {
      $this->parent->loadView('login');
   
      return false;
    }
    
    // Get the item that the question is about
    $item = new BOMItem($this->parent->inInteger('id'), $this->parent);
    
    // Valid item?
    if(!$item)
    {
      // Stop rendering
      $this->parent->go('./?');
      exit();
    }
    
    // Perform action?
    if($this->parent->inInteger('done') == 1 && 
      trim($this->parent->in('question')) != '')
    {
      // Insert the question
      $this->parent->db->insert('bf_questions', array(
                                  'timestamp' => time(),
                                  'title' => 'Question about ' . $item->sku,
                                  'content' => $this->parent->in('question'),
                                  'user_id' => $this->parent->security->UID,
                                  'item_id' => $this->parent->inInteger('id')
                               ))
                       ->execute();
                       
      // Questions + 1
      $this->parent->stats->increment('com.b2bfront.stats.users.questions', 1);
    
      // Send notifications
      $this->parent->notifier->send(
                                  'new_question',
                                  'New Question',
                                  $this->parent->security->attributes['description'] . 
                                  ' has submitted a question about ' . $item->sku . '.',
                                  $this->parent->security->attributes['description'] . 
                                  ' has submitted the following question regarding ' . $item->sku . ': <br /><br />' .
                                  $this->parent->in('question') . '<br /><br /><br />Please log in to the ACP to respond ' . 
                                  'to this question.' ,
                                  'question-balloon.png'                         
                               );  
    }
    
    // Provide SKU and ID
    $this->addValue('sku', $item->sku);
    $this->addValue('id', $item->id);
    
    // Provide "Done" flag
    $this->addValue('done', $this->parent->inInteger('done'));
    
    return true;
  }
}  
?>
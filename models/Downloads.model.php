<?php
/** 
 * Model: Downloads
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class Downloads extends RootModel
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
    $this->parent->security->action('Downloads');

    // Set this model's title and tab
    $this->addValue('title', $this->parent->config->get('com.b2bfront.site.title', true) . 
                    ' - Downloads');
    $this->addValue('tab_downloads', 'selected');
    
    // Find downloads
    $downloads = $this->parent->db->query();
    $downloads->select('*', 'bf_downloads');
    
    // Define the button to view a download
    $viewButton  = "\n";
    $viewButton .= '<a href="{path}" class="shadowbox"">' . "\n";
    $viewButton .= '  <img src="/share/icon/general/navigation.png" ' . 
                   'class="middle" alt="View" />' . "\n";
    $viewButton .= '  Download' . "\n";
    $viewButton .= '</a>' . "\n";
    
    // Construct table
    $dataView = new DataTable('downloads', $this->parent, $downloads);
    $dataView->setOption('alternateRows');
    $dataView->setOption('noDataText', 'There are no downloads right now.');
    $dataView->addColumns(array(
                            array(
                              'dataName' => 'name',
                              'niceName' => 'Title'
                            ),/*
                            array(
                              'dataName' => 'size',
                              'niceName' => 'Size',
                              'css' => array(
                                         'width' => '90px'
                                       ),
                              'options' => array(
                                             'callback' => function($row) {
                                               return intval($row->size / 1024) . ' KB';
                                             }
                                           )
                            ),*/
                            array(
                              'dataName' => 'mime_type',
                              'niceName' => 'Type',
                              'css' => array(
                                         'width' => '130px'
                                       )
                            ),
                            array(
                              'dataName' => '',
                              'niceName' => 'Actions',
                              'options' => array('fixedOrder' => false),
                              'css' => array(
                                         'width' => '90px',
                                         'text-align' => 'right',
                                         'padding-right' => '10px'
                                       ),
                              'content' => $viewButton
                            )
                          ));
                          
    // Render and provide to the view
    $this->addValue('table', $dataView->render());
    
    return true;
  }
}  
?>
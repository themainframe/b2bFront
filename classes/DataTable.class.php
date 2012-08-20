<?php
/**
 * DataTable
 * Renders the data views used in the Back Office.
 * Reads the $BF 'Parent' object to gain rendering config.
 * 
 * Example:
 *      
 *    $query = $BF->db->query();
 *    $query->select('*', 'table');
 *    
 *    // [ Options can be set here ]
 *    // [ Use $dataView->setOption('key', 'value') ]
 *
 *    $dataView = new DataTable('d1', $BF, $query);
 *    print $dataView->render(array(
 *                              'column1' => 'Name'
 *                           ));   
 *
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.3
 * @author Damien Walsh
 */
class DataTable extends Configurable
{
  /**
   * The ID/Name of the DataTable in HTML
   * @var string
   */
  protected $name = '';

  /**
   * The current page number of the view
   * @var integer
   */
  protected $pageNumber = 1;
  
  /**
   * The total number of pages in the view
   * @var integer
   */
  protected $pageCount = 1;
  
  /**
   * The number of lines per page of the view
   * @var integer
   */
  protected $linesPerPage = 50;

  /**
   * An unfinished query object
   * @var Query
   */
  private $query = null;
  
  /**
   * A collection of columns to show
   * @var array
   */
  public $columns = array();
  
  /**
   * The number of rows to be displayed in total
   * @var integer
   */
  public $rowCount = 0;
  
  /**
   * The cardinality of the current row being rendered
   * @var integer
   */
  public $currentRow = 1;
  
  /**
   * The filename containing the image of the query used to render this DataTable
   * @var string
   */
  private $fileName = '';
  
  /**
   * HTML to be outputted before rendering
   * May be modified by columns during execution.
   * @var string
   */
  public $preText = '';
  
  /**
   * Create a new DataTable based on a query stub.
   * @param string $name The name of the data table.
   * @param BFClass $parent The BFClass parent object.
   * @param Query $query An unfinished Query object.
   * @return DataTable
   */
  public function __construct($name, $parent, $query)
  {
    // Set properties
    $this->name = $name;
    $this->parent = & $parent;
    $this->query = & $query;
    
    // Load some properties from the input values
    // Limit start point
    $this->pageNumber = $this->parent->inInteger($this->name . '_pg');
    if(empty($this->pageNumber) || $this->pageNumber <= 0)
    {
      $this->pageNumber = 1;
    }

    // Lines per Page
    $this->linesPerPage = $this->parent->inInteger($this->name . '_lpp');
    if(empty($this->linesPerPage) || $this->linesPerPage <= 0)
    {
      $this->linesPerPage = 50;
    }    

    // Set defaults for the object
    $this->defaults();
  }
  
  /**
   * Set the default configurable options for this UI part
   * @return boolean
   */
  public function defaults()
  {
    $this->setOption('noDataText', 'There is no data in this view.');  // Text to display for empty set.
    $this->setOption('subjectName', 'Record');  // The word used for each row in the view.
    
    return true;
  }
  
  /**
   * Add a column to the DataTable
   * @param string $dataName The name of the column in the data result to populate this column with.
   * @param string $niceName The 'nice' name to be provided to the user in the column header.
   * @param string $content Optionally predefined static content for the field.
   * @param array $css Optionally an array of css attributes as 'attribute' => 'value'.
   * @param array $options Optionally an array of formatting options to set for the column.
   * @return boolean
   */
  public function addColumn($dataName, $niceName, $content = '', $css = array(), $options = array())
  {
    // Check CSS
    if(!is_array($css))
    {
      // Empty.
      $css = array();
    }
    
    // Check Formatting Options
    if(!is_array($options))
    {
      // Empty.
      $options = array();
    }
    
    $newColumn = new DataColumn($this, $dataName, $niceName, $css,
       & $this->parent, count($this->columns));
    
    // Set static content
    $newColumn->set_content($content);
    
    // Add options if required
    foreach($options as $key => $value)
    {
      $newColumn->setOption($key, $value);
    }
    
    $this->columns[] = $newColumn;
    return true;
  }                         

  /**
   * Add multiple columns to the DataTable as an array of associative arrays
   * The first argument should contain an array of arrays, each with the keys as follows:
   * 'dataName' => The name of the column in the data result to populate this column with.
   * 'niceName' => The 'nice' name to be provided to the user in the column header.
   * 'css' => May be omitted.  An array of css attributes as 'attribute' => 'value'.
   * 'options' => May be omitted.  An array of formatting options as 'key' => 'value'
   * @param array $columns The array of columns to add.
   * @return boolean True on success, False on failure.
   */
  public function addColumns($columns)
  {
    foreach($columns as $column)
    {
      if(!is_array($column) || empty($column))
      {
        continue;
      }
      
      // Avoid missing indexes
      if(!isset($column['content']))
      {
        $column['content'] = '';
      }
      
      if(!isset($column['css']))
      {
        $column['css'] = array();
      }

      if(!isset($column['options']))
      {
        $column['options'] = array();
      }
      
      if(!isset($column['dataName']))
      {
        $column['dataName'] = '';
      }
      
      if(!isset($column['niceName']))
      {
        $column['niceName'] = '';
      }      
         
      // Create a new column
      $this->addColumn($column['dataName'], $column['niceName'], $column['content'],
                       $column['css'], $column['options']);
    
    }
    
    // Set up default ordering
    if($this->getOption('defaultOrder') && $this->parent->in($this->name . '_order') === false)
    {
      // Set ordering
      $orderSettings = $this->getOption('defaultOrder');
      $ordering = $this->getColumnIDByDataName($orderSettings[0]);
      $defaultOrderDirection = (strtolower($orderSettings[1]) == 'desc' ? 'desc' : 'asc');

      // Real column?
      if($this->columns[$ordering] && $this->columns[$ordering]->get_dataName())
      { 
        // Set global input values to allow external code to see ordering direction
        $this->parent->setIn($this->name . '_order', $ordering);
        $this->parent->setIn($this->name . '_order_d', $defaultOrderDirection == 'desc' ? 'd' : 'a');
      }
    }
    
    return true;
  }
  
  /**
   * Find a column key by data name
   * @param string $dataName The dataName of the column
   * @return integer
   */
  private function getColumnIDByDataName($dataName)
  {
    foreach($this->columns as $id => $column)
    {
      if($column->get_dataName() == $dataName)
      {
        return $id;
      }
    }
    
    return false;
  }
  
  /**
   * Obtain a "Serialisable" representation of the column set
   * NB:  This will not include any closure generated values.
   * @return string
   */
  private function serialisableColumns()
  {
    // Build a new columns array
    $columns = array();
    
    foreach($this->columns as $column)
    {
      // Include this column?
      if($column->getOption('hideInDownload'))
      {
        // Skip
        continue;
      }
    
      $columns[] = array(
        'dataName' => $column->get_dataName(),
        'niceName' => $column->get_niceName(),
        'content' => $column->get_content(),
        'css' => $column->get_css()
      );
    }
    
    return $columns;
  }

  /**
   * Render a pager
   * Produces a HTML dialog to change pagination settings for this DataTable
   * @return string
   */
  private function renderPager()
  {
    // Calculate the next and last pages
    $nextPage = ($this->pageNumber >= $this->pageCount ? $this->pageCount : $this->pageNumber + 1);
    $previousPage = ($this->pageNumber <= 1 ? 1 : $this->pageNumber - 1);
    
    // Start building the DataTable
    $output  = "\n";
    
    // Pager (Top)
    $output .= '<div class="pager">' . "\n";
    $output .= '  <table style="float:left;">' . "\n";
    $output .= '    <tbody>' . "\n";
    $output .= '      <tr>' . "\n";
    $output .= '        <td class="pager_text">Page <strong>' . $this->pageNumber .
               '</strong> of <strong>' . $this->pageCount . '</strong></td>' . "\n";

    // Go back options
    $output .= '        <td class="page"><a class="first" title="First page" href="' . 
               Tools::getModifiedURL(array($this->name . '_pg' => '1')) . 
               '"></a></td>' . "\n";
    $output .= '        <td class="page"><a class="back" title="Previous page" href="' . 
               Tools::getModifiedURL(array($this->name . '_pg' => $previousPage)) . 
               '"></a></td>' . "\n";
 
    // Show nearby pages
    for($page = $this->pageNumber - 2; $page < $this->pageNumber + 3; $page ++)
    {
      // In range?
      if($page > $this->pageCount || $page <= 0)
      {
        continue;
      }
                             
      $output .= '        <td class="page' . ($page == $this->pageNumber ? ' current' : '') . '">' . 
                 '<a title="Page ' . $page . '" href="' . Tools::getModifiedURL(array($this->name . '_pg' => $page)) . '">' .
                 $page . '</a></td>' . "\n";
    }
    
    // Go forward options
    $output .= '        <td class="page"><a class="forward" title="Next page" href="' . 
               Tools::getModifiedURL(array($this->name . '_pg' => $nextPage)) . 
               '"></a></td>' . "\n";
    $output .= '        <td class="page"><a class="last" title="Last page" href="' . 
               Tools::getModifiedURL(array($this->name . '_pg' => $this->pageCount)) . 
               '"></a></td>' . "\n";

    $output .= '        <td class="pager_text">' . $this->rowCount . ' ' . 
               $this->getOption('subjectName') . Tools::plural($this->rowCount) . '</td>' . "\n";

    // Advanced options
    $output .= '      </tr>' . "\n";   
    $output .= '    </tbody>' . "\n";
    $output .= '  </table>' . "\n";
    $output .= '  <table style="float:right;">' . "\n";
    $output .= '    <tbody>' . "\n";
    $output .= '      <tr>' . "\n";
    
    // Show an option to download the current view if enabled
    if($this->getOption('showDownloadOption'))
    {
      $DTabName = str_replace('/temp/', '', str_replace('.dtab', '', $this->getDownloadableFilename()));
    
      $output .= '        <td class="pager_text"> ' . "\n";
      $output .= '          <a href="#" onclick="downloadDTab(\'' . $DTabName . '\', \'xls\')" class="tool download" title="Download as Excel">' . "\n";
      $output .= '            <img src="/acp/static/icon/report-excel.png" alt="Download as Excel" />' . "\n";
      $output .= '            Excel' . "\n";
      $output .= '          </a>' . "\n";
      $output .= '          <a  href="#" onclick="downloadDTab(\'' . $DTabName . '\', \'xlsx\')" class="tool download" title="Download as Excel 2007">' . "\n";
      $output .= '            <img src="/acp/static/icon/report-excel.png" alt="Download as Excel 2007" />' . "\n";
      $output .= '            Excel 2007' . "\n";
      $output .= '          </a>' . "\n";
      $output .= '          <a href="#" onclick="downloadDTab(\'' . $DTabName. '\', \'csv\')" class="tool download" title="Download as CSV">' . "\n";
      $output .= '            <img src="/acp/static/icon/document-excel-csv.png" alt="Download as CSV" />' . "\n";
      $output .= '            CSV' . "\n";
      $output .= '          </a>' . "\n";
      $output .= '        </td>' . "\n";
    }
    
    $output .= '        <td class="pager_text"> ' . "\n";
    $output .= '          &nbsp;Go to page &nbsp;' . "\n";
    $output .= '          <input type="text" onkeyup="if(event.keyCode == 13) { window.location=\'' . 
               Tools::getModifiedURL(array($this->name . '_pg' => ''))  . '&' . 
               $this->name . '_pg=\' + this.value; } " accesskey="G">' . "\n";
    $output .= '          &nbsp;' . "\n";
    $output .= '        </td>' . "\n";
    $output .= '        <td class="pager_text right"> ' . "\n";
    $output .= '          &nbsp;' . "\n";
    $output .= '          <select onchange="window.location=\'' . 
               Tools::getModifiedURL(array($this->name . '_lpp' => '', $this->name . '_pg' => 1))  . '&' . 
               $this->name . '_lpp=\' + this.value;">' . "\n";
    $output .= '          <option value="10">' . $this->linesPerPage . '</option>' . "\n";
    $output .= '          <option value="10">10</option>' . "\n";
    $output .= '          <option value="25">25</option>' . "\n";
    $output .= '          <option value="50">50</option>' . "\n";
    $output .= '          <option value="100">100</option>' . "\n";
    $output .= '          <option value="200">200</option>' . "\n";
    $output .= '          <option value="500">500</option>' . "\n";
    $output .= '          <option value="1000">1000</option>' . "\n";
    $output .= '          </select>' . "\n";
    $output .= '          &nbsp;rows per page.&nbsp;' . "\n";
    $output .= '        </td>' . "\n";
    
    $output .= '      </tr>' . "\n";   
    $output .= '    </tbody>' . "\n";
    $output .= '  </table>' . "\n";

    $output .= '  <br style="clear:both;" />' . "\n";
    $output .= '</div>' . "\n\n";
    $output .= '<br />' . "\n";
    
    return $output;
  }

  /**
   * Render the data table
   * @return string
   */
  public function render()
  {
    // Both pagers off = infinite row display, no way to change page
    if(!$this->getOption('showTopPager') && !$this->getOption('showBottomPager') || 
       $this->getOption('showAll'))
    {
      $this->linesPerPage = 9999;
    }
  
    // Profile the rendering process
    $profiler = new Profiler();
    
    //
    // 1) Detect any column ordering
    //
    
    $profiler->start('ORDER_COLUMNS');
    
    // Get ordering data from global input
    $ordering = $this->parent->in($this->name . '_order');
    
    if($ordering != '')
    {
      // Retrieve the dataName of the column 
      if($this->columns[$ordering])
      {
        $orderingDataName = $this->columns[$ordering]->get_dataName();
          
        if($orderingDataName)
        {
          // Get the ordering direction
          $orderingDirection = $this->parent->in($this->name . '_order_d');
          if($orderingDirection != 'a' && $orderingDirection != 'd')
          {
            $orderingDirection = 'd';
          }
          
          // Make changes to the query object
          $this->query->order($orderingDataName, $orderingDirection == 'd' ? 'DESC' : 'ASC');
        }
      }
    }
    else
    {
      $ordering = -1;
    }
    
    // Branch a copy of the query and execute it to get the number of rows now
    $unlimitedQuery = clone $this->query;
    $unlimitedQuery->execute();
    $this->rowCount = $unlimitedQuery->count;
    
    // Write to file if required
    if($this->getOption('showDownloadOption'))
    {  
      // Grap a text version of the query
      $queryText = $this->query->get_query();
      
      // Get location
      $location = $this->getDownloadableFilename();
      $this->parent->setFileTTL($location);
      
      // Get columns
      $columns = $this->serialisableColumns();
      
      // Build complete serialised data
      $serialisedRepresentation = serialize(array(
        'columns' => $columns,
        'query' => $queryText,
        'table' => $this->name
      ));
      
      // Write to temp file
      file_put_contents(BF_ROOT . '/' . $location,
        gzcompress(base64_encode($serialisedRepresentation)));
    }

    // Calculate total pages
    $this->pageCount = ceil($unlimitedQuery->count / $this->linesPerPage);
      
    // Clean up the page count / page number mechanics
    if($this->pageNumber > $this->pageCount && $this->pageCount != 0)
    {
      $this->pageNumber = $this->pageCount;
    }
    
    $profiler->stop('ORDER_COLUMNS');
    
    //
    // 2) Pagination and limiting
    //
    
    $profiler->start('EXECUTING_QUERY');
    
    $this->query->limit(($this->pageNumber - 1) * $this->linesPerPage,
                        $this->linesPerPage);
  
    // Execute limited query
    $this->query->execute();

    // Start collecting output
    $output  = "\n";
    
    $profiler->stop('EXECUTING_QUERY');
    
    // Show top pager?
    if($this->getOption('showTopPager') && $this->query->count > 0)
    {
      $output .= $this->renderPager();
    }
    
    $output .= '<table id="' . $this->name . '" class="data">' . "\n";
    $output .= '  <thead>' . "\n";
    $output .= '    <tr class="header">' . "\n";
    
    // List columns
    $columnHeaderIndex = -1;
    
    $profiler->start('LISTING_COLUMNS');
    foreach($this->columns as $columnID => $column)
    {
      if($columnID == $ordering)
      {
        // This is the column by which the ordering is done.
        $orderingDirection = $this->parent->in($this->name . '_order_d');
        if($orderingDirection != 'a' && $orderingDirection != 'd')
        {
          $orderingDirection = 'd';
        }
      }
      else
      {
        $orderingDirection = '';
      }
      
      $output .= $column->renderHeader($columnID, $orderingDirection);
      $columnHeaderIndex ++;
    }
    
    $profiler->stop('LISTING_COLUMNS');
    
    $output .= '    </tr>' . "\n";
    $output .= '  </thead>' . "\n";
    $output .= '  <tbody>' . "\n";
    
    // Alternating rows
    $alternation = ''; 
    
    // Track column and row indices
    $columnIndex = -1;
    $rowIndex = 2;
                      
    $profiler->start('LISTING_ROWS');
    
    // List all IDs
    $rowIDs = array();
                      
    // Start listing rows
    while($resultRow = $this->query->next())
    {
      // Decide on Row Styles
      if($this->getOption('rowStyles'))
      {
        // Check each style rule
        $rowStyles = $this->getOption('rowStyles');
        foreach($rowStyles as $rowStyle)
        {
          // Is there a conditional?
          if($rowStyle['if_equal'])
          {
            // Grab resultRow as an assoc array
            $resultRowArray = (array)$resultRow;

            // Evaluate it
            if(Tools::replaceTokensArray($rowStyle['if_equal'][0], $resultRowArray)
               != Tools::replaceTokensArray($rowStyle['if_equal'][1], $resultRowArray))
            {
              continue;
            }
          }
          
          // Parse row style
          $rowStyleRuleString = '';
          foreach($rowStyle['css'] as $cssKey => $cssValue)
          {
            $rowStyleRuleString .= $cssKey . ':' . $cssValue . '; ';
          }
          
          // Build alternation block
          $alternation = ' style="' . $rowStyleRuleString . '"';
        }
      }
    
      $output .= '    <tr' . $alternation . '>' . "\n";
       
      // Record the ID
      $rowIDs[] = $resultRow->id;

      // Output chosen columns
      foreach($this->columns as $column)
      {
        $columnDataName = $column->get_dataName();
        
        if(!empty($columnDataName) && isset($resultRow->{$columnDataName}))
        {
          $output .= $column->value($resultRow->{$columnDataName}, $resultRow);
        }
        else
        {
          $output .= $column->value($column->content($resultRow), $resultRow);
        }
              
        // Increment column
        $columnIndex ++;
      }
      
      if($this->getOption('alternateRows'))
      {
        $alternation = ($alternation == '' ? ' class="alt"' : '');
      }
      
      // End row
      $output .= '    </tr>' . "\n";
      
      // Increment row count
      $rowIndex ++;
      
      // Set cardinality
      $this->currentRow ++;
      
      // Reset column index
      $columnIndex = -1;
    }
    
    $profiler->stop('LISTING_ROWS');
        
    $output .= '  </tbody>' . "\n";
    $output .= '</table>' . "\n";
    
    // Add an <input /> element that contains all the IDs as a CSV.
    $rowIDsCSV = Tools::CSV($rowIDs);
    $output .= '<input type="hidden" name="dv_' . $this->name . '" value="' . $rowIDsCSV . '" />' . "\n";
    
    // No rows?
    if($this->query->count == 0)
    {
      $output .= '<div class="no_data">' . "\n";
      $output .= '  ' . $this->getOption('noDataText') . "\n";
      $output .= '</div>' . "\n";
    }
    
    // Show bottom pager?
    if($this->getOption('showBottomPager') && $this->query->count > 0)
    {
      $output .= '<br />' . $this->renderPager();
    }
    
    // Output profiling results if required
    if($this->getOption('profileRendering'))
    {
      $profiler->createLog(BF_ROOT . '/temp/last_profile.txt');
    }

    // Affix pretext
    $output = $output . "\n\n" . $this->preText;
    
    return $output;
  }
  
  /**
   * Retrieve a filename to save the query image of this instance to
   * @return string
   */
  private function getDownloadableFilename()
  {
    if($this->fileName)
    {
      return $this->fileName;
    }
    
    // Otherwise, generate a location
    $location = '/temp/' . date('m-d-y') . '_' .  $this->name . '_' . 
      uniqid() . '_' . mt_rand() . '.dtab';
    
    $this->fileName = $location;
    
    return $location;
  }          
}
?>

<?php
/** 
 * Model: Stock Download
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class StockDownload extends RootModel
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
    $this->parent->security->action('Stock Downloads');
    $this->addValue('title', $this->parent->config->get('com.b2bfront.site.title', true) . 
                    ' - ' . 'Download Data');
                    
    // Load classifications
    $classifications = $this->parent->db->query();
    $classifications->select('*', 'bf_classifications')
                    ->order('name', 'asc')
                    ->execute();
                    
    $classificationsAssoc = $classifications->assoc();
    $this->addValue('classifications', $classificationsAssoc);
    
    // Columns
    $supportedColumns = array(
      'Name',
      'Trade Price',
      'My Price',
      'RRP',
      'Stock',
      'Barcode',
      'Description'
    );
    
    $dataColumns = array(
      'name',
      'trade_price',
      'my_price_not_column',
      'rrp_price',
      'stock_free_not_column',
      'barcode',
      'description'
    );
    
    $this->addValue('columns', $supportedColumns);

    // Select the tab
    $this->addValue('tab_page_' . $this->parent->inInteger('id'), 'selected');

    // Perform action?
    if($this->parent->inInteger('do') == 1)
    {
      // Mirror value to view
      $this->addValue('do', '1');
    
      // Collect checked classifications 
      $checkedClassifications = array();
      foreach($classificationsAssoc as $id => $classification)
      {
        if($this->parent->inInteger('f_items_' . $id) == 1)
        {
          // Checked
          $checkedClassifications[] = $id;
        }
      }
       
      // Check for emptiness
      if(empty($checkedClassifications))
      {
        // All!
        $checkedClassifications = array_keys($classificationsAssoc);
      }
      
      $classificationsCSV = Tools::CSV($checkedClassifications);
      
      // First build a query
      $query = 'SELECT * FROM `bf_items` WHERE `classification_id` IN (' . $classificationsCSV . ')';
      $result = $this->parent->db->query();
      $result->text($query)
             ->order('sku', 'asc')
             ->execute();
      
      // Build a collection of columns that are checked
      $checkedColumns = array();
      for($index = 1; $index <= count($supportedColumns); $index ++)
      {
        if($this->parent->inInteger('f_col_' . $index) == 1)
        {
          $checkedColumns[] = $index - 1;
        }
      }
      

      // Start building a sheet
      // Open PHPExcel classes
      require_once BF_ROOT . '/libraries/phpexcel/Classes/PHPExcel.php';
      require_once BF_ROOT . '/libraries/phpexcel/Classes/PHPExcel/Writer/Excel5.php';
      require_once BF_ROOT . '/libraries/phpexcel/Classes/PHPExcel/Writer/Excel2007.php';
      require_once BF_ROOT . '/libraries/phpexcel/Classes/PHPExcel/Writer/CSV.php';
      
      // Generate the requested file
      $phpExcel = new PHPExcel();
      $outputSheet = $phpExcel->getActiveSheet();
      
      // Add SKU
      $outputSheet->setCellValueByColumnAndRow($columnHeaderIndex, 1, 'SKU');
      $outputSheet->getStyleByColumnAndRow($columnHeaderIndex, 1)->getFill()
                  ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                  ->getStartColor()->setARGB('FFD5D5D5');
      
      // Add columns
      $columnHeaderIndex = 0;
      foreach($supportedColumns as $column)
      {
        // Skip?
        if(!in_array($columnHeaderIndex, $checkedColumns))
        {
          continue;
        }
    
        $outputSheet->setCellValueByColumnAndRow($columnHeaderIndex + 1, 1, $supportedColumns[$columnHeaderIndex]);
        $outputSheet->getStyleByColumnAndRow($columnHeaderIndex + 1, 1)->getFill()
                    ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFD5D5D5');
      
        $columnHeaderIndex ++;
      }
      
      // Add rows
      $rowIndex = 2;
      
      // Pricer
      $pricer = new Pricer(& $this->parent);

      
      while($row = $result->next())
      {
        // Write SKU
        $outputSheet->setCellValueByColumnAndRow(0, $rowIndex, $row->sku);
        
        $totalColumns = 0;
        
        // For each selected column
        for($columnIndex = 0; $columnIndex < count($supportedColumns); $columnIndex ++)
        {
          // Skip?
          if(!in_array($columnIndex, $checkedColumns))
          {
            continue;
          }
          
          if($totalColumns >= count($supportedColumns))
          {
            break;
          }
        
          // Exceptions
          if($dataColumns[$columnIndex] == 'my_price_not_column')
          {
            $value = $pricer->myPrice($row);
          }
          else if($dataColumns[$columnIndex] == 'stock_free_not_column')
          {
            if($row->stock_free > 100)
            {
              $value = 100;
            }
            else if($row->stock_free < 0)
            {
              $value = 0;
            }
            else
            {
              $value = intval($row->stock_free);
            }
          }
          else
          {
            $value = $row->{$dataColumns[$columnIndex]};
          }
        

          $type = PHPExcel_Cell_DataType::TYPE_STRING;
          $outputSheet->getCellByColumnAndRow($columnIndex + 1, $rowIndex)->setValueExplicit($value, $type);
          
          $totalColumns ++;
        }
        
        // Increment row
        $rowIndex ++;
      }

      
      // Export the sheet to storage
      // What type of file?
      switch($this->parent->in('f_filetype'))
      {
        case 'csv':
        
          // Generate a CSV (Comma Separated Values) file
          $outputWriter = new PHPExcel_Writer_CSV($phpExcel);
          $finishedPath = BF_ROOT . '/temp/usr' . $this->parent->security->UID . '-' . 
            date('m-d-y') . '-' . uniqid() . '.csv';
          $outputWriter->save($finishedPath);
        
          break;
          
        case 'xls':
        
          // Generate an XLS (Excel 95) file
          $outputWriter = new PHPExcel_Writer_Excel5($phpExcel);
          $finishedPath = BF_ROOT . '/temp/usr' . $this->parent->security->UID . '-' . 
            date('m-d-y') . '-' . uniqid() . '.xls';
          $outputWriter->save($finishedPath);
      
          break;
          
        case 'xlsx':
        
          // Generate an XLSX (Excel 2007) file
          $outputWriter = new PHPExcel_Writer_Excel2007($phpExcel);
          $finishedPath = BF_ROOT . '/temp/usr' . $this->parent->security->UID . '-' . 
            date('m-d-y') . '-' . uniqid() . '.xlsx';
          $outputWriter->save($finishedPath);
        
          break;
          
        default:
        
          // Failure - Invalid Type
          $BF->shutdown();
          exit();
        
          break;
      }
      
      // Set TTL for 24 Hours
      $this->parent->setFileTTL($finishedPath, 86400);
      $this->parent->go('./' . str_replace(BF_ROOT, '', $finishedPath));
      
      exit(); 
    }
    
    return true;
  }
}  
?>
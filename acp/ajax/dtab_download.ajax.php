<?php
/**
 * Generate an Excel/CSV file for the specified Data Table query image (.dtab)
 * AJAX Responder
 *
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */
 
// Set context
define('BF_CONTEXT_ADMIN', true);

// Relative path for this - no BF_ROOT yet.
require_once('../admin_startup.php');
require_once(BF_ROOT . 'tools.php');

// New BFClass & Admin class
$BF = new BFClass(true);
$BF->admin = new Admin(& $BF);

if(!$BF->admin->isAdmin)
{
  exit();
}

// Non Blocking mode
Tools::nonBlockingMode();

// Wait for lock
sleep(2);

// Get the specified file
$fileName = $BF->in('dtab');

// Verify that it exists
if(!Tools::exists('/temp/' . $fileName . '.dtab'))
{
  // Failure
  $BF->shutdown();
  exit();
}

// Get path
$path = BF_ROOT . '/temp/' . $fileName . '.dtab';

// Open PHPExcel classes
require_once BF_ROOT . '/libraries/phpexcel/Classes/PHPExcel.php';
require_once BF_ROOT . '/libraries/phpexcel/Classes/PHPExcel/Writer/Excel5.php';
require_once BF_ROOT . '/libraries/phpexcel/Classes/PHPExcel/Writer/Excel2007.php';
require_once BF_ROOT . '/libraries/phpexcel/Classes/PHPExcel/Writer/CSV.php';

// Load and decode the object
$dTabData = Tools::getText($path);
$dTabObject = unserialize(base64_decode(gzuncompress($dTabData)));

// Valid?
if(!$dTabObject)
{
  // Failure
  $BF->shutdown();
  exit();
}

// Get the query and columns
$queryText = $dTabObject['query'];
$columns = $dTabObject['columns'];
$tableName = $dTabObject['table'];

// Perform the query with no limits
$query = $BF->db->query();
$query->text(stripslashes($queryText))
      ->execute();
      
// Generate the requested file
$phpExcel = new PHPExcel();
$outputSheet = $phpExcel->getActiveSheet();

// Add columns
$columnHeaderIndex = 0;
foreach($columns as $column)
{
  // No data name or ID?
  if(empty($column['dataName']) || $column['dataName'] == 'id')
  {
    // Skip
    continue;
  }

  $outputSheet->setCellValueByColumnAndRow($columnHeaderIndex, 1, $column['niceName']);
  $outputSheet->getStyleByColumnAndRow($columnHeaderIndex, 1)->getFill()
              ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
              ->getStartColor()->setARGB('FFD5D5D5');

  $columnHeaderIndex ++;
}

// Add rows
$rowIndex = 2;

while($row = $query->next())
{
  $columnIndex = 0;

  foreach($columns as $column)
  {
    // No data name or ID?
    if(empty($column['dataName']) || $column['dataName'] == 'id')
    {
      // Skip
      continue;
    }
    
    // Write text to the download file
    $outputSheet->setCellValueByColumnAndRow($columnIndex, $rowIndex, $row->{$column['dataName']});

    // Increment column
    $columnIndex ++;
  }
  
  // Increment row
  $rowIndex ++;
}

// What type of file?
switch($BF->in('type'))
{
  case 'csv':
  
    // Generate a CSV (Comma Separated Values) file
    $outputWriter = new PHPExcel_Writer_CSV($phpExcel);
    $finishedPath = BF_ROOT . '/temp/' . $tableName . '-' . date('m-d-y') . '-' . uniqid() . '.csv';
    $outputWriter->save($finishedPath);
  
    break;
    
  case 'xls':
  
    // Generate an XLS (Excel 95) file
    $outputWriter = new PHPExcel_Writer_Excel5($phpExcel);
    $finishedPath = BF_ROOT . '/temp/' . $tableName . '-' . date('m-d-y') . '-' . uniqid() . '.xls';
    $outputWriter->save($finishedPath);

    break;
    
  case 'xlsx':
  
    // Generate an XLSX (Excel 2007) file
    $outputWriter = new PHPExcel_Writer_Excel2007($phpExcel);
    $finishedPath = BF_ROOT . '/temp/' . $tableName . '-' . date('m-d-y') . '-' . uniqid() . '.xlsx';
    $outputWriter->save($finishedPath);
  
    break;
    
  default:
  
    // Failure - Invalid Type
    $BF->shutdown();
    exit();
  
    break;
}

// Set TTL for 24 Hours
$BF->setFileTTL($finishedPath, 86400);

// Create a download
$BF->db->insert('bf_admin_downloads', array(
           'name' => basename($finishedPath),
           'path' => Tools::relativePath($finishedPath),
           'timestamp' => time(),
           'admin_id' => $BF->admin->AID
         ))
       ->execute();

// Send a notification to say the download has finished
$BF->admin->notifyMe('Download Ready', 
  'One or more files have finished generating and can now be downloaded from the ' . 
  '<a href="./?act=dashboard&mode=downloads" target="_blank" class="new">My Downloads</a> view now.', 
  'tick-circle.png');

// Clean up
$BF->shutdown();

?>
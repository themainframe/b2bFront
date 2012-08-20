<?php
/**
 * Module: Images
 * Mode: Do Upload and Show Results
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */

// Context check
if(!defined("BF_CONTEXT_ADMIN") || !defined("BF_CONTEXT_MODULE"))
{
  exit();
}

// Collect the image IDs
$imageIDs = Tools::unCSV($BF->in('f_image_list'));
$imageIDs = Tools::removeEmptyEntries($imageIDs);
          
// Failure?
if(empty($imageIDs))
{ 
  $BF->admin->notifyMe('Upload Failed', 'No images were selected.', 'cross-circle.png');
  $BF->go('./?act=images&mode=upload');
}
          
// Associate images
$index = 1;

foreach($imageIDs as $imageID)
{
  $BF->admin->api('Items')
            ->attachImage($result, $imageID, $index);

  $index ++;
}

// Find all images and obtain URLs
$query = $BF->db->query();
$query->select('*', 'bf_images')
      ->where('`id` IN ({1})', $BF->in('f_image_list'));

if($result !== false)
{
  $BF->admin->notifyMe('OK', 'Upload succeeded.');
}

?>

<h1>Upload Images</h1>
<br />
<div class="panel">
  <div class="contents" style="">
    <h3>Upload Complete</h3>
    <p>
      All images were uploaded correctly.<br />
      You can copy links to the uploaded images below.
    </p>
  </div>
</div>

<script type="text/javascript">
  
  function selectText(text)
  {
    if ($.browser.msie)
    {
      var range = document.body.createTextRange();
      range.moveToElementText(text);
      range.select();
    }
    else if ($.browser.mozilla || $.browser.opera)
    {
      var selection = window.getSelection();
      var range = document.createRange();
      range.selectNodeContents(text);
      selection.removeAllRanges();
      selection.addRange(range);
    }
    else if ($.browser.safari)
    {
      var selection = window.getSelection();
      selection.setBaseAndExtent(text, 0, text, 1);
    }
  }
  
  $(function() {
    
    $('span.autoselect').click(function(element) {
      selectText(this);
    });
    
  });
  
</script>

<br />

<?php

  // Define the columns
  $columnSet = array(
                  array(
                    'dataName' => 'url',
                    'niceName' => 'Name',
                    'options' => array(
                                   'callback' => function($row)
                                                 {
                                                   return basename($row->url);
                                                 },
                                   'formatAsLink' => true,
                                   'linkURL' => '{url}',
                                   'hideInDownload' => true
                                 )
                  )
                );  
    
  $otherColumns = array(
                    array(
                      'dataName' => 'url',
                      'niceName' => 'Full URL',
                      'options' => array(
                                     'callback' => function($row)
                                                   {
                                                     return '<span class="autoselect">' . $row->url . 
                                                       '</span>';
                                                   }
                                   )
                    ),
                    array(
                      'dataName' => 'size_x',
                      'niceName' => 'Full Width (px)',
                      'css' => array(
                                 'width' => '110px'
                               )
                    ),
                    array(
                      'dataName' => 'size_y',
                      'niceName' => 'Full Height (px)',
                      'css' => array(
                                 'width' => '110px'
                               )
                    )
                  );
                
  // Create a data table view
  $images = new DataTable('im2', $BF, $query);
  $images->setOption('alternateRows');
  $images->setOption('showTopPager');
  $images->setOption('showDownloadOption');
  $images->addColumns(array_merge($columnSet, $otherColumns));
  
  // Render & output content
  print $images->render();
  
?>
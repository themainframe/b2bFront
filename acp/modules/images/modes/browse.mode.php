<?php
/**
 * Module: Images
 * Mode: Browse
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

?>

<h1>Browse Images</h1>
<br />

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

<?php

  // Create a query
  $query = $BF->db->query();
  $query->select('*', 'bf_images');
  
  // Define boolean columns CSS text
  $columnCSS = array(
    'width' => '100px'
  );
  
  // Define a tool set HTML
  $confirmationJS = 'confirmation(\'Really remove this image?<br />It will be unlinked from any items or other entities.\', function() { window.location=\'' .
                    Tools::getModifiedURL(array('mode' => 'browse_remove_do')) . '&id={id}\'; })';
  
  // Buttons
  $toolSet  = "\n";
  $toolSet .= '<a onclick="' . $confirmationJS . '" class="tool" title="Remove">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/cross-circle.png" alt="Remove" />' . "\n";
  $toolSet .= 'Remove</a>' . "\n";
  
    // Create a data table view
  $images = new DataTable('im1', $BF, $query);
  $images->setOption('alternateRows');
  $images->setOption('showTopPager');
  $images->setOption('defaultOrder', array('timestamp', 'desc'));
  $images->addColumns(array(
                       array(
                        'dataName' => 'url',
                        'niceName' => '',
                        'options' => array(
                                       'callback' => function($row)
                                                     {
                                                       return Tools::getImageThumbnail($row->url, 'lst');
                                                     },
                                        'formatAsImage' => true,
                                        'fixedOrder' => true
                                     ),
                        'css' => array(
                                   'width' => '40px'
                                 )
                      ),
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
                                        'linkNewWindow' => true
                                     )
                      ),
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
                        'niceName' => 'Width',
                        'css' => array(
                                   'width' => '60px'
                                 )
                      ),
                      array(
                        'dataName' => 'size_y',
                        'niceName' => 'Height',
                        'css' => array(
                                   'width' => '6px'
                                 )
                      ),
                      array(
                        'dataName' => 'timestamp',
                        'niceName' => 'Date Created',
                        'css' => array(
                                   'width' => '140px'
                                 ),
                        'options' => array(
                                        'formatAsDate' => true
                                     )
                      ),

                      array(
                        'niceName' => 'Options',
                        'content' => $toolSet,
                        'css' => array(
                                   'width' => '70px'
                                 )
                      )
                    )
                  );
  
  // Render & output content
  print $images->render();
  
?>
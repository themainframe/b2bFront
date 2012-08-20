<?php
/**
 * Module: Images
 * Mode: Unused
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

<h1>Unused Images</h1>
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

<div class="panel">
  <div class="contents">
    <h3>About Unused Images</h3>
    <p>
      These images have been identified as not being associated with any part of the system.<br />
      <em>However</em>, you should still check to make sure there are no incoming links to images before deleting them.
    </p>
  </div>
</div>

<br />

<?php

  // Create a query
  $query = $BF->db->query();
  $query->select('*', 'bf_images')
        ->where('`id` NOT IN (SELECT `image_id` FROM `bf_item_images`)' . 
                'AND `id` NOT IN (SELECT `image_id` FROM `bf_categories`)');
  
  // Define boolean columns CSS text
  $columnCSS = array(
    'width' => '100px'
  );
  
  // Define a tool set HTML
  $confirmationJS = 'confirmation(\'Really remove this image?<br />It will be unlinked from any items or other entities.\', function() { window.location=\'' .
                    Tools::getModifiedURL(array('mode' => 'unused_remove_do')) . '&id={id}\'; })';
  
  // Buttons
  $toolSet  = "\n";
  $toolSet .= '<a onclick="' . $confirmationJS . '" class="tool" title="Remove">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/cross-circle.png" alt="Remove" />' . "\n";
  $toolSet .= 'Remove</a>' . "\n";
  // $toolSet .= '<a href="./?act=images&mode=browse_editor&id={id}" class="tool" title="Remove">' . "\n";
  // $toolSet .= '  <img src="/acp/static/icon/image--pencil.png" alt="Remove" />' . "\n";
  // $toolSet .= 'Edit</a>' . "\n";
  
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
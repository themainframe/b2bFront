<?php
/**
 * Module: Statistics
 * Mode: Current Period
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

<!--Load the AJAX API-->
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">

  // Load the Visualization API library and the piechart library.
  google.load('visualization', '1.0', {'packages':['corechart']});

  // The current statistic
  var currentStatistic = -1;
  
  // Currently pinned statistic IDs
  var pinned = new Array();

  function drawVisualization() {
  
    // Set up view
    $('#chart_div').html('').show();
    $('#intro').hide();
    
    // Build array of 1 ID
    var arrayID = new Array();
    arrayID.push(currentStatistic);
    
    // Download a JSON defining columns and rows for the DataTable
    $.getJSON('./ajax/statistics_data.ajax.php',
      {'statistics' : arrayID.concat(pinned).join(','), 'count' : $('#previousValues').val()},
      function(statData) {
      
        // Ensure buttons are visible
        $('#addTarget').show();
        
        // Create and populate the data table.
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'x');
        
        $.each(statData.columns, function(i, v)
        {
          data.addColumn('number', v.name);
          
          // Current value?
          if(v.id == currentStatistic)
          {
            $('#currentValue').html(v.value);
            $('#lastValue').html((v.last == undefined ? '0' : Math.round(Math.abs(v.last - v.value))));
            
            // Change meter
            
            console.log('Last: ' + v.last +', Now: ' + v.value);
            
            if(v.value - v.last < 0)
            {
              $('#valueChange').attr('src', '/acp/static/image/aui-value-down.png');
            }
            
            if(v.value - v.last > 0)
            {
              $('#valueChange').attr('src', '/acp/static/image/aui-value-up.png');
            }
          
            if(v.value - v.last == 0)
            {
              $('#valueChange').attr('src', '/acp/static/image/aui-value-same.png');
            }
          }
          
        });
    
        // Show no data
        $('#noData').hide();
        
        $.each(statData.rows, function(i, v)
        { 
          if(v.data == undefined)
          {
            // Found no data
            $('#noData').show();
            $('#chart_div').hide();
            return;
          }
                    
          // Build data array
          var statPoints = Array();
          $.each(v.data, function(index, value)
          {
            statPoints.push(parseInt(value));
          });
          
          data.addRow((new Array(v.time)).concat(statPoints));
        });
                

    
        // Create and draw the visualization.
        new google.visualization.LineChart(document.getElementById('chart_div')).
            draw(data, {curveType: "none",
                        width: '100%', height: 300,
                        vAxis: {maxValue: 10}}
                );
      });
    
  }

  $(function() {
    
    // Redraw on resize
    $(window).resize(function() {
      drawVisualization();
    });
  
    // Change when count is modified
    $('#previousValues').change(function() {
      drawVisualization();
    });
    
    // Pin current value
    $('#pinButton').click(function() {
      $(this).hide();
      $('#unpinButton').show();
      pinned.push(currentStatistic);
    });
    
    // Unpin current value
    $('#unpinButton').click(function() {
      $(this).hide();
      $('#pinButton').show();
        var newPinned = Array();
        $.each(pinned, function(i, v) {
          if(v != currentStatistic)
          {
            newPinned.push(v);
          }
        });
        pinned = newPinned;
    });
  
    $('#statOptions').fileTree({ selectionChanged: function(r, domain, statistic) {
      
        if(statistic == undefined || statistic == -1)
        {
          return;
        }
        
        // Set current statistic ID
        currentStatistic = statistic;

        // Redraw graphics
        drawVisualization();

        // Change pin buttons
        if(currentStatistic in pinned || pinned[0] == currentStatistic)
        {
          $('#pinButton').hide();
          $('#unpinButton').show();
        }
        else
        {
          $('#pinButton').show();
          $('#unpinButton').hide();
        }
      
			}, root: '0', alphaName: 'domain', betaName: 'statistic', script: '/acp/ajax/statistics.ajax.php', selectable: true },
		  function(el, ob, name) { }
		);
		
  });

</script>

<h1>Statistics Overview</h1>
<br />

<input type="hidden" value="-1" name="currentStatistic" id="currentStatistic" />

<table style="width:100%;">

  <tbody>
  
    <tr>
  
      <td style="width: 260px;">
  
        <div class="panel" style="background: #fff;">
          <div class="title">Statistics</div>
          <div class="contents" id="statOptionsContainer">
            <div id="statOptions">&nbsp;</div>
          </div>
        </div>
        
        <br />
        
       <div class="panel" style="background: #fff;">
          <div class="title">Options</div>
          <div class="contents">
            <strong style="float:left; padding-top: 5px;">Previous values:</strong>
            <select id="previousValues" style="float: right; vertical-align: middle;">
              <option value="5">5</option>
              <option value="10">10</option>
              <option value="20">20</option>
              <option value="50">50</option>
              <option value="100">100</option>
            </select>
            <br class="clear" />  
            <br />
            <a href="./?act=statistics&mode=overview_options" title="Advanced Options">
              Advanced Options...
            </a>
          </div>
        </div>
      
      </td>
      <td style="padding: 0px 15px 0px 15px;">
      
        <div class="panel" style="height: 115px;">
          <div class="title">Raw Metrics</div>
          <div id="designer" class="contents" style="height: 100%;">
            <table class="four_view">
              <tr>
                <td class="metric">
                  <div class="value" id="currentValue">
                    -
                  </div><br />
                  <div class="value_explanation">
                    so far this period
                  </div>
                </td>
                <td class="metric">
                  <div class="value">
                    <img src="/acp/static/image/aui-value-same.png" alt="Changed by" id="valueChange" />
                    <span id="lastValue">0</span>
                  </div><br />
                  <div class="value_explanation">
                    on last period
                  </div>
                </td>
                <td class="metric">
                  <br />
                  <div class="value_button">
                    <span class="button" style="display: none;" id="addTarget">
                      <a href="./?act=statistics&mode=targets_add">
                        <span class="img" 
                        style="background-image:url(/acp/static/icon/target--plus.png)"></span>
                        Add Target...
                      </a>
                    </span>
                  </div>
                </td>
                <td class="metric">
                  <br />
                  <div class="value_button">
                    <span class="button" style="display: none;" id="pinButton">
                      <a href="#">
                        <span class="img" 
                        style="background-image:url(/acp/static/icon/pin.png)"></span>
                        Pin
                      </a>
                    </span>
                    <span class="button" style="display: none;" id="unpinButton">
                      <a href="#">
                        <span class="img" 
                        style="background-image:url(/acp/static/icon/cross-circle.png)"></span>
                        Unpin
                      </a>
                    </span>
                  </div>
                </td>
              </tr>
            </table>
          </div>
        </div>
        
        <br />

        <div class="panel">
          <div class="title">Graphical View</div>
          <div id="chart_div" class="contents" style="height: 100%; background: #ffffff ; text-align: center;">

          </div>
          
            <div id="intro" style="text-align: center; background: #fff">            
              <br />
              <strong>Select a statistic from the menu to the left to begin.</strong><br /><br />
              <p class="grey">
                Choose a statistic and use the "Pin to Graph" button to keep it visible as an<br />
                overlay while you view other statistics.
              </p>
              <br /><br /><br />
            </div>
            
            <div style="display: none; text-align: center;" id="noData">
              <br /><br /><br />
              <strong>Not enough data has been recorded for that statistic yet.</strong><br /><br />
              <p class="grey">
                Please choose another statistic from the menu to the left.
              </p>
              <br /><br /><br />
            </div>
          
        </div>
      
      </td>

    </tr>
    
  </tbody>

</table>
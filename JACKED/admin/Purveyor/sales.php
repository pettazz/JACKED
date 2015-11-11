<?php

    $JACKED->loadDependencies(array('Syrup', 'DatasBeard'));

?>
<link href="<?php echo $JACKED->admin->config->entry_point; ?>assets/js/daterangepicker/daterangepicker.css" rel="stylesheet" />

<style type="text/css">
    #spinnerContainer{
        display: block;
        position: relative;
        height: 75px;
        width: 100%;
    }

    #filters div{
        margin-right: 40px;
    }

    #reportsButton[disabled]{
        cursor: not-allowed;
    }
</style>

<script type="text/javascript" src="<?php echo $JACKED->admin->config->entry_point; ?>assets/js/moment.min.js"></script>
<script type="text/javascript" src="<?php echo $JACKED->admin->config->entry_point; ?>assets/js/jquery.ba-throttle-debounce.min.js"></script>
<script type="text/javascript" src="<?php echo $JACKED->admin->config->entry_point; ?>assets/js/daterangepicker/daterangepicker.js"></script>

<script type="text/javascript">
    
    var availableReports = [
        <?php 
            // well this sucks. we need a way to not have to hardcode IDs
            foreach($JACKED->DatasBeard->getRows('d0b2bc36-9f16-4864-9544-f7a73d5d5e7f') as $row){
                echo '{id: "' . $row['id'] . '", name: "' . $row['name'] . '"},';
            }
        ?>
    ];
    
    $(document).ready(function(){
        $('input[name="daterange"]').val(moment(), moment());
        updateRows();

        $("#filters").submit(function(eo){
            eo.preventDefault();
            updateRows();
            return false;
        });

        $("#filters input").change(function(){
            updateRows();
        });

        $("input[name=filter]").keyup($.debounce( 2000, function(){
            updateRows();
        }));

        $("#clearFilter").click(function(){
            $("input[name=filter]").val('');
            updateRows();
        });

        $('#collectTrackingModal').on('hidden', function(){
            $("form#shippingUpdate input#saleGuid").val('');
        });

        $('input[name="daterange"]').daterangepicker({
            buttonClasses: "btn btn-small",
            applyClass: "btn btn-success",
            cancelClass: "btn btn-default",
            ranges: {
               'Today': [moment(), moment()],
               'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
               'Last 7 Days': [moment().subtract(6, 'days'), moment()],
               'Last 30 Days': [moment().subtract(29, 'days'), moment()],
               'This Month': [moment().startOf('month'), moment().endOf('month')],
               'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });
        $('input[name="daterange"]').change(updateRows);

        $('#reportsButton').click(function(el, ev){
            var isDisabled = !!$(this).attr('disabled');
            if(isDisabled){
                el.preventDefault();
            }
            return !isDisabled;
        });

        $.each(availableReports, function(idx, report){
            var newEl = $('<li><a href="#" data-report-id="' + report.id + '">' + report.name + '</a></li>');
            $('#reportsList').append(newEl);
        });

        $("#reportsList li a").click(function(el, ev){
            var reportId = $(this).data('report-id');
            var dateRange = $('input[name=daterange]').val();

            window.location = '<?php echo $JACKED->admin->config->entry_point; ?>handler/reportdownload?' + $.param({
                reportId: reportId,
                dateRange: dateRange
            });
        });
    });

    function updateRows(){
        var dateConstraint = $('#searchWithinDateRange').prop('checked');
        var filterValue = $("input[name=filter]").val();
        var filterActive = $.trim(filterValue) !== '';

        $('form#filters input,button').prop('disabled', true);

        $('tbody').html('');
        $('#spinnerContainer').show();
        var opts = {
          lines: 15// The number of lines to draw
          , length: 15 // The length of each line
          , width: 1 // The line thickness
          , radius: 15 // The radius of the inner circle
          , scale: 1 // Scales overall size of the spinner
          , corners: 0.5 // Corner roundness (0..1)
          , color: '#000' // #rgb or #rrggbb or array of colors
          , opacity: 0.0 // Opacity of the lines
          , rotate: 0 // The rotation offset
          , direction: 1 // 1: clockwise, -1: counterclockwise
          , speed: 1.2 // Rounds per second
          , trail: 50 // Afterglow percentage
          , fps: 20 // Frames per second when using setTimeout() as a fallback for CSS
          , zIndex: 2e9 // The z-index (defaults to 2000000000)
          , className: 'spinner' // The CSS class to assign to the spinner
          , top: '50%' // Top position relative to parent
          , left: '50%' // Left position relative to parent
          , shadow: false // Whether to render a shadow
          , hwaccel: true // Whether to use hardware acceleration
          , position: 'absolute' // Element positioning
        }
        var target = $('#spinnerContainer')[0];
        var spinner = new Spinner(opts).spin(target);

        var data = {};
        if(filterActive){
            $('#reportsButton')
                .attr('disabled', true)
                .addClass('noClicky')
                .attr('title', "Search filter disables reports")
                .tooltip({trigger: 'hover'});

            if(dateConstraint){
                data['dateRange'] = $('input[name=daterange]').val();
            }
            data['filter'] = filterValue;
        }else{
            $('#reportsButton')
                .attr('disabled', false)
                .removeClass('noClicky')
                .attr('title', '')
                .tooltip('destroy');
            data['dateRange'] = $('input[name=daterange]').val();
        }

        $.get('<?php echo $JACKED->admin->config->entry_point; ?>handler/salesfetch', data)
        .done(function(data){
            spinner.stop();   
            $('#spinnerContainer').hide();         
            $('form#filters input,button').prop('disabled', false);

            if($.trim(data)){
                $('tbody').html(data);
            }else{
                $('tbody').html('<tr><td colspan="7"><p class="lead">No Sales matched filters</p></td></tr>');
            }

            $(".saleDetailsOpen").click(function(eo){
                eo.preventDefault();
                $('#detailsRow-' + $(this).attr('data-guid')).show();
            });

            $(".saleDetailsClose").click(function(eo){
                eo.preventDefault();
                $('#detailsRow-' + $(this).attr('data-guid')).hide();
            });

            $(".markShipped").click(function(eo){
                eo.preventDefault();
                $("form#shippingUpdate input#saleGuid").val($(this).attr('data-guid'));
                $('#collectTrackingModal').modal({
                    keyboard: true
                });
            });
        });
    }
</script>


<!-- tracking info collection modal -->
<div id="collectTrackingModal" class="modal hide fade">
    <form id="shippingUpdate" method="POST" action="<?php echo $JACKED->admin->config->entry_point; ?>module/Purveyor">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h3>Mark as Shipped</h3>
        </div>

        <div class="modal-body">
            <p>Marking this sale as shipped will complete the transaction.</p>
            
            <input type="hidden" name="manage_handler" value="sales-edit-handler" />
            <input type="hidden" name="saleGuid" id="saleGuid" value="" />
            <div class="control-group">
                <label class="control-label" for="inputTracking">Tracking Number (FedEx, UPS, etc) if available</label>
                <div class="controls">
                    <input type="text" class="input-large" id="inputTracking" name="inputTracking" />
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
            <button type="submit" class="btn btn-primary">Save as Shipped</button>
        </div>
    </form>
</div>
<!-- end tracking info modal -->


<h3>Sales</h3>

<?php
    if($JACKED->Sessions->check('admin.error.editsale')){
        echo '<div class="alert alert-error alert-block">
                  <a href="#" class="close" data-dismiss="alert">&times;</a>
                  <p><strong>Error: </strong>"' . $JACKED->Sessions->read('admin.error.editsale') .  '" </p>
        </div>';
        $JACKED->Sessions->delete('admin.error.editsale');
    }

    if($JACKED->Sessions->check('admin.success.editsale')){
        echo '<div class="alert alert-success alert-block">
                  <a href="#" class="close" data-dismiss="alert">&times;</a>
                  <p>' . $JACKED->Sessions->read('admin.success.editsale') .  ' </p>
        </div>';
        $JACKED->Sessions->delete('admin.success.editsale');
    }

?>
<form class="form-inline" id="filters">
    <div class="input-prepend input-append">
      <span class="add-on"><i class="icon-calendar"></i></span>
      <input type="text" name="daterange" value="" />
    </div>

    <div class="input-prepend input-append">
      <span class="add-on"><i class="icon-search"></i></span>
      <input type="text" name="filter" placeholder="Search Anything" />
      <button id="clearFilter" class="btn" type="button">Clear</button>
    </div>

    <label class="checkbox">
        <input id="searchWithinDateRange" type="checkbox" checked> Search only within selected date range
    </label>
</form>

<div class="btn-group">
    <a id="reportsButton" class="btn dropdown-toggle btn-primary" data-toggle="dropdown" href="#">
        <i class="icon-list-alt icon-white"></i> Generate Report for Selected Date Range
        <span class="caret"></span>
    </a>
    <ul id="reportsList" class="dropdown-menu"></ul>
</div>

<table class="table">
    <thead>
        <tr>
            <th>Date</th>
            <th>User</th>
            <th>Qty</th>
            <th>Product</th>
            <th>Paid</th>
            <th>Shipped</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>
<div id="spinnerContainer"></div>
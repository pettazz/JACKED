<?php
    require_once('../jacked_conf.php');
    $JACKED = new JACKED("admin, Sessions");
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title><?php echo $JACKED->config->client_name; ?> | JACKED<?php echo isset($_GET['manage_module'])? '::' . $_GET['manage_module'] : '' ?></title>
        
        <link rel="stylesheet" href="<?php echo $JACKED->admin->config->entry_point; ?>assets/css/bootstrap.css" />
        <script type="text/javascript" src="<?php echo $JACKED->admin->config->entry_point; ?>assets/js/jquery-1.10.2.js"></script>
        <script type="text/javascript" src="<?php echo $JACKED->admin->config->entry_point; ?>assets/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="<?php echo $JACKED->admin->config->entry_point; ?>assets/js/spin.min.js"></script>
        <script type="text/javascript" src="<?php echo $JACKED->admin->config->entry_point; ?>assets/js/dropzone.min.js"></script>

        <script type="text/javascript">
            
            $(document).ready(function(){
                var imguploadWebPath = '<?php echo $JACKED->config->base_url . $JACKED->admin->config->imgupload_directory; ?>';
                
                // i wish there was a better way but not in this version of bootstrap
                var imguploadChooserCurrentField; 
                var didimguploadChooserDuck = false;

                $(".alert").alert();

                $(".imguploadLink").click(function(eo){
                    if($('#imguploadChooserModal').is(':visible')){
                        $('#imguploadChooserModal').modal('hide');
                        didimguploadChooserDuck = true;
                    }
                    $('#imguploadModal').modal({
                        keyboard: true
                    });
                });

                Dropzone.options.imguploadDropzone = {
                    paramName: 'img'
                };

                $("#imguploadChooserLink").click(function(eo){
                    imguploadChooserCurrentField = null;
                    $('#imguploadChooserModal').modal({
                        keyboard: true
                    });
                });

                $(".imguploadChooserControl").click(function(){
                    imguploadChooserCurrentField = $(this).siblings('.imguploadChooserField')[0];
                    $('#imguploadChooserModal').modal({
                        keyboard: true
                    });
                });

                $('#imguploadChooserModal').on('show', function(){
                    $('#imguploadChooserSelectionMessage').toggle(!!imguploadChooserCurrentField);

                    $('#imguploadChooserSpinner').show();
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
                    var target = $('#imguploadChooserSpinner')[0];
                    var spinner = new Spinner(opts).spin(target);

                    $.get('<?php echo $JACKED->admin->config->entry_point; ?>handler/imgupload-listing')
                    .done(function(data){
                        for(var i = data.length - 1; i >= 0; i--){
                            $("#imguploadChooserContainer").append('<img class="imguploadPreview" src="' + imguploadWebPath + data[i] + '" data-imgupload-file-name="' + data[i] + '"/>');
                        };
                        spinner.stop();
                        $('#imguploadChooserSpinner').hide();
                        $("img.imguploadPreview").each(function(){
                            $(this).click(function(){
                                if(imguploadChooserCurrentField){
                                    $(imguploadChooserCurrentField).val(imguploadWebPath + $(this).data('imguploadFileName'));
                                    $('#imguploadChooserModal').modal('hide');
                                }
                            })
                        });
                    });

                });

                $('#imguploadModal').on('hide', function(){
                    if(didimguploadChooserDuck){
                        $('#imguploadChooserModal').modal('show');
                        didimguploadChooserDuck = false;
                    }
                });

                $('#imguploadChooserModal').on('hidden', function(){
                    $("#imguploadChooserContainer").html('<div id="imguploadChooserSpinner"></div>');
                });

            });
        </script>

        <!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
        <!--[if lt IE 9]>
          <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->

        <!-- Le styles -->
        <link rel="stylesheet" href="<?php echo $JACKED->admin->config->entry_point; ?>assets/css/dropzone.css" />

        <style type="text/css">
            body{
                margin-top: 80px;
            }
            #menuitems > div{
                width: 40%;
                padding: 20px;
                margin: 15px 0px 15px 30px;
                background: #eee;
                border-radius: 6px;
                float: left;
            }

            #editoroverlay{
                box-shadow: 0px 0px 10px #000;
            }

            footer{
                margin: 40px;
            }
            #imguploadChooserSpinner{
                display: block;
                position: relative;
                height: 75px;
                width: 100%;
            }
            img.imguploadPreview{
                cursor: pointer;
                border: 1px solid #333;
                width: 100%;
                margin-bottom: 10px;
            }
        </style>
    </head>

    <body>
  
<?php
    if(!$JACKED->admin->checkLogin()){
        echo '<div class="container">';
        include('login.php');
        exit();
    }
    
    $modules = $JACKED->getInstalledModules();
?>

    <!-- imgupload chooser modal -->
    <div id="imguploadChooserModal" class="modal hide fade">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h3>Uploaded Images</h3>
        </div>

        <div class="modal-body">
            <p>Showing uploaded images from: <br /><code><?php echo $JACKED->config->base_url . $JACKED->admin->config->imgupload_directory; ?></code></p>
            <p class="lead" id="imguploadChooserSelectionMessage">Click to choose an image</p>

            <div id="imguploadChooserContainer" class="well">
                <div id="imguploadChooserSpinner"></div>
            </div>
        </div>

        <div class="modal-footer">
            <a href="#" class="btn btn-success imguploadLink"><i class="icon-upload icon-white"></i> Upload New</a>
            <a data-dismiss="modal" href="#" class="btn btn-primary">Close</a>
        </div>
    </div>
    <!-- end uploader modal -->

    <!-- image uploader modal -->
    <div id="imguploadModal" class="modal hide fade">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h3>Image Uploader</h3>
        </div>

        <div class="modal-body">
            <p>Uploaded images are available in <br /><code><?php echo $JACKED->config->base_url . $JACKED->admin->config->imgupload_directory; ?></code><br />using their original filename.</p>
            <form action="<?php echo $JACKED->admin->config->entry_point; ?>handler/imgupload"
              class="dropzone"
              id="imguploadDropzone"></form>
        </div>

        <div class="modal-footer">
            <a data-dismiss="modal" href="#" class="btn btn-primary">Done</a>
        </div>
    </div>
    <!-- end uploader modal -->


    <div class="navbar navbar-inverse navbar-fixed-top">
        <div class="navbar-inner">
            <div class="container">
                <a class="brand" href="<?php echo $JACKED->admin->config->entry_point; ?>">JACKED Admin</a>
                <button type="button" class="btn btn-navbar collapsed" data-toggle="collapse" data-target=".nav-collapse">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <div class="nav-collapse collapse">
                    <ul class="nav">
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Modules <b class="caret"></b></a>

                            <ul class="dropdown-menu">
                            <?php
                                foreach($modules as $shortname => $mod){
                                    echo '<li><a href="' . $JACKED->admin->config->entry_point . 'module/' . $shortname . '">' . $mod['name'] . '</a></li>';
                                }
                            ?>
                                <li class="divider"></li>
                                <li><a href="#">Updates</a></li>
                                <li><a href="#">Get More</a></li>
                            </ul>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Tools <b class="caret"></b></a>

                            <ul class="dropdown-menu">
                                <li><a class="imguploadLink" href="#">Upload Images</a></li>
                                <li><a id="imguploadChooserLink" href="#">View Uploaded Images</a></li>
                            </ul>
                        </li>
                    </ul>
                    <form class="navbar-form pull-right">
                        <a class="btn primary" href="<?php echo $JACKED->admin->config->entry_point; ?>?handler=logout">Logout</a>
                    </form>
                    <span class="navbar-text pull-right" style="margin-right:15px;">Logged in as <a href="#"><?php echo $JACKED->Sessions->read('auth.admin.user'); ?></a></span>
                </div>
            </div>
        </div>
    </div>

    <div class="container">

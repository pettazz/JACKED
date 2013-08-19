<?php
    require('../jacked_conf.php');
	$JACKED = new JACKED("admin, Sessions");
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title><?php echo $JACKED->config->client_name; ?> | JACKED</title>
        
        <link rel="stylesheet" href="<?php echo $JACKED->admin->config->entry_point; ?>assets/js/bootstrap-combined.min.css" />
        <script type="text/javascript" src="<?php echo $JACKED->admin->config->entry_point; ?>assets/js/jquery-1.10.2.js"></script>
        <script type="text/javascript" src="<?php echo $JACKED->admin->config->entry_point; ?>assets/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="<?php echo $JACKED->admin->config->entry_point; ?>assets/js/spin.min.js"></script>

        <script type="text/javascript">
            
            $(document).ready(function(){
                $(".alert").alert();
            });
        </script>

        <!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
        <!--[if lt IE 9]>
          <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->

        <!-- Le styles -->
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
        </style>
    </head>

    <body>
  
<?php
    if(!$JACKED->admin->checkLogin()){
        echo '<div class="container">';
        include('login.php');
        exit();
    }
    
    $mods = $JACKED->getInstalledModules();
    $modules = '';
    foreach($mods as $mod){
        $modules .= $mod['name'] . ' ' . $mod['version'] . ', ';
    }
?>

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
                                foreach($mods as $mod){
                                    echo '<li><a href="' . $JACKED->admin->config->entry_point . 'module/' . $mod['name'] . '">' . $mod['name'] . '</a></li>';
                                }
                            ?>
                                <li class="divider"></li>
                                <li><a href="#">Updates</a></li>
                                <li><a href="#">Get More</a></li>
                            </ul>
                        </li>
                    </ul>
                    <form class="navbar-form pull-right">
                        <a class="btn primary" href="/JACKED/admin/logout.php">Logout</a>
                    </form>
                    <span class="navbar-text pull-right" style="margin-right:15px;">Logged in as <a href="#"><?php echo $JACKED->Sessions->read('auth.admin.user'); ?></a></span>
                </div>
            </div>
        </div>
    </div>

    <div class="container">

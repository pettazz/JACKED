<?php
    require('../jacked_conf.php');
	$JACKED = new JACKED("admin, Vitogo, Sessions");
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?php echo $JACKED->config->client_name; ?> | JACKED</title>
    
    <link rel="stylesheet" href="http://twitter.github.com/bootstrap/1.4.0/bootstrap.css" />
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>
    <script type="text/javascript" src="http://twitter.github.com/bootstrap/1.4.0/bootstrap-alerts.js"></script>
    <script type="text/javascript" src="http://twitter.github.com/bootstrap/1.4.0/bootstrap-modal.js"></script>
    <script type="text/javascript" src="http://twitter.github.com/bootstrap/1.4.0/bootstrap-dropdown.js"></script>
    <script type="text/javascript" src="http://autobahn.tablesorter.com/jquery.tablesorter.min.js"></script>
    <script type="text/javascript" src="http://fgnass.github.com/spin.js/spin.min.js"></script>

    <script type="text/javascript">
        
        $(document).ready(function(){
            $("table.sorted").tablesorter({ sortList: [[0,0]] });
            
            $(".alert-message").alert();
        });
    </script>

    <!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Le styles -->
    
    <style type="text/css">
      body {
        padding-top: 40px;
      }
      .topbar .btn {
        border: 0;
      }
      #hero-alert p{
        font-size: 13px;
        font-weight: normal;
        line-height: 18px;
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
    
    $mods = $JACKED->admin->getModules();
    $modules = '';
    foreach($mods as $mod){
        $modules .= '<abbr title="' . $mod['moduleDescription'] . '">' . $mod['moduleName'] . '</abbr>' . ' ' . $mod['moduleVersion'] . ', ';
    }
?>

    <div class="topbar">
      <div class="fill">
        <div class="container">
          <a class="brand" href="<?php echo $JACKED->admin->config->entry_point; ?>">JACKED Admin</a>
          <ul class="nav">
            <li><a href="<?php echo $JACKED->admin->config->entry_point; ?>">Home</a></li>
            <li class="dropdown" data-dropdown="dropdown">
              <a href="#" class="dropdown-toggle">Modules</a>

              <ul class="dropdown-menu">
              <?php
                  foreach($mods as $shortname => $mod){
                      echo '<li><a href="' . $JACKED->admin->config->entry_point . 'module/' . $shortname . '">' . $mod['moduleName'] . '</a></li>';
                  }
              ?>
                <li class="divider"></li>
                <li><a href="#">Updates</a></li>
                <li><a href="#">Get More</a></li>
              </ul>
            </li>

          </ul>
          <p class="pull-right">Logged in as <a href="#"><?php echo $JACKED->Sessions->read('auth.admin.user'); ?></a> <button class="btn" onclick="window.location.href='/JACKED/admin/logout.php'">Logout</button></p>
        </div>
      </div>
    </div>

    <div class="container">

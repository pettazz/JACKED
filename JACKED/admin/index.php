<?php

    $admin_dir = substr(__FILE__, 0, strrpos(__FILE__, '/'));
    
    if(isset($_GET['manage_module'])){
        $module_admin_home = $admin_dir . '/' . $_GET['manage_module'];
        include('bodyTop.php');
        if(!$JACKED->isModuleInstalled($_GET['manage_module']) || !file_exists($module_admin_home)){
            include('404.php');
            include('bodyBottom.php');
            exit();
        }else{
            include($module_admin_home . '/index.php');
            include('bodyBottom.php');
            exit();
        }
    }

    if(isset($_GET['handler'])){
        include($admin_dir . '/' . $_GET['handler'] . '-handler.php');
        include('bodyBottom.php');
        exit();
    }

    include('bodyTop.php');
?>
    

      <div class="hero-unit">
        <h1>JACKED</h1>
        <p>Modules installed: <?php echo $modules; ?>and JACKED Core <?php echo $JACKED::moduleVersion; ?>.</p>
        <p><a class="btn primary large" href="<?php echo $JACKED->admin->config->entry_point; ?>?handler=logout">Logout</a></p>

      </div>

      <div class="row">
        
      </div>

<?php
    require('bodyBottom.php');
?>
<?php
    require('bodyTop.php');
    $admin_dir = substr(__FILE__, 0, strrpos(__FILE__, '/'));
    if(isset($_GET['manage_module'])){
        $module_admin_home = $admin_dir . '/' . $_GET['manage_module'];
        if(!$JACKED->admin->isModuleInstalled($_GET['manage_module']) || !file_exists($module_admin_home)){
            include('404.php');
            require('bodyBottom.php');
            exit();
        }else{
            include($module_admin_home . '/index.php');
            require('bodyBottom.php');
            exit();
        }
    }
?>
    

      <div class="hero-unit">
        <h1>JACKED</h1>
        <p>Modules installed: <?php echo $modules; ?>and JACKED Core <?php echo $JACKED::moduleVersion; ?>.</p>
        <p><a class="btn primary large" href="/JACKED/admin/logout.php">Logout</a></p>

      </div>

      <div class="row">
        
      </div>

<?php
    require('bodyBottom.php');
?>
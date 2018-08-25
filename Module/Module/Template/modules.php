<div class="row">
    <?php
    /**
     * @var $modules \ComposerPack\System\Modules
     */
    $modules = $controller::$modules;
    $allModule = $modules->getAllModules();
    $moduleList = [1 => [], 2 => [], 0 => []];
    foreach($allModule as $module => $config)
    {
        if($module == 'Module')
            continue;
        /**
         * @var $config \ComposerPack\Module\Config
         */
        if(!$config->isVisible()) continue;

        $subs = $config->getModules();

        $moduleIcon = $config->getIcon();
        $moduleName = $config->getName();
        $moduleUrl = url('module/info/'.strtolower($module));
        $installed = $config->isInstalled();
        $level = $installed + $config->isRequired();
        $bg = $installed ? ($config->isRequired() ? "bg-dark" : "bg-site") : "bg-gray";
        ob_start();
        ?>
        <div class="col-lg-4 col-xs-12 col-md-6">
            <!-- small box -->
            <a href="<?php echo $moduleUrl; ?>" class="small-box <?php echo $bg; ?>">
                <div class="inner">
                    <h3><?php echo $moduleName; ?></h3>

                    <p>&nbsp;</p>
                </div>
                <div class="icon">
                    <?php if(!empty($moduleIcon))
                    {
                        ?>
                        <i class="<?php echo $moduleIcon; ?>"></i>
                        <?php
                    }
                    ?>
                </div>
                <span class="small-box-footer">
                    <?php echo lang("info", null, array("hu" => "Infó", "en" => "Info", "de" => "Info")); ?>
                    <i class="fa fa-arrow-circle-right"></i>
                </span>
            </a>
        </div>
        <?php
        $moduleList[$level][$module] = ob_get_clean();
    }
    foreach ($moduleList as $level => $modules)
    {
        foreach ($modules as $module => $html)
        {
            echo $html;
        }
    }
    ?>
</div>
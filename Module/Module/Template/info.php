<?php
/**
 * @var $config \ComposerPack\Module\Config
 */
$moduleIcon = $config->getIcon();
/**
 * @var $controller \ComposerPack\Admin\DefaultController
 */
$moduleAuthor = $config->getAuthor();
?>
<div class="row">
    <div class="col-md-3">

        <!-- Profile Image -->
        <div class="box">
            <div class="box-body box-profile">
                <div class="text-center">
                <?php if(!empty($moduleIcon))
                {
                    ?>
                    <i class="<?php echo $moduleIcon; ?> big-icon"></i>
                    <?php
                }
                ?>
                </div>

                <h3 class="profile-username text-center"><?php echo $config->getName(); ?></h3>

                <p class="text-muted text-center">
                    <?php
                    if(!empty($moduleAuthor))
                    {
                        echo lang("author_at", null, array("hu" => "Készítette", "en" => "Created by", "de" => "Erstellt von"));
                        echo ": ".$moduleAuthor;
                    }
                    else
                    {
                        echo "&nbsp;";
                    }
                    ?>
                </p>

                <ul class="list-group list-group-unbordered">
                    <li class="list-group-item">
                        <b>Followers</b> <a class="pull-right">1,322</a>
                    </li>
                    <li class="list-group-item">
                        <b>Following</b> <a class="pull-right">543</a>
                    </li>
                    <li class="list-group-item">
                        <b>Friends</b> <a class="pull-right">13,287</a>
                    </li>
                </ul>

                <?php
                /**
                 * @var $modules \ComposerPack\System\Modules
                 */
                $modules = $controller::$modules;
                if(!$config->isRequired())
                {
                    if ($config->isInstalled())
                    {
                        ?>
                        <a href="#" class="btn btn-danger btn-block" data-toggle="modal" data-target="#uninstallmodal">
                            <b>
                                <?php echo lang("uninstall", null, array("hu" => "Eltávolítás", "en" => "Uninstall", "de" => "Deinstallieren")); ?>
                            </b>
                        </a>
                        <div class="modal fade" id="uninstallmodal">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?php echo lang("close", null, array("hu" => "Bezárás", "en" => "Close", "de" => "schließen")); ?></span></button>
                                        <h4 class="modal-title"><?php echo lang("are_you_sure_to_uninstall", null, array("hu" => "Biztos eltávolítja?", "en" => "Are you sure you want to uninstall it?", "de" => "Sind Sie sicher, dass Sie es deinstallieren möchten?")); ?></h4>
                                    </div>
                                    <div class="modal-body">
                                        <?php echo lang("if_you_uninstall_all_data_and_settings_will_be_lost", null, array("hu" => "Ha eltávolítja az összes adat és beállítás elvész!", "en" => "If you uninstall all data and settings will be lost!", "de" => "Wenn Sie alle Daten deinstallieren, gehen die Einstellungen verloren!")); ?>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo lang("cancel", null, array("hu" => "Mégse", "en" => "Cancel", "de" => "Stornieren")); ?></button>
                                        <a href="<?php echo url('module/uninstall/'.strtolower($module)); ?>" class="btn btn-danger"><?php echo lang("yes", null, array("hu" => "Igen", "en" => "Yes", "de" => "Ja")); ?></a>
                                    </div>
                                </div><!-- /.modal-content -->
                            </div><!-- /.modal-dialog -->
                        </div><!-- /.modal -->
                        <?php
                    }
                    else
                    {
                        ?>
                        <a href="<?php echo url('module/install/'.strtolower($module)); ?>" class="btn btn-success btn-block">
                            <b>
                                <?php echo lang("install", null, array("hu" => "Telepítés", "en" => "Install", "de" => "Installieren")); ?>
                            </b>
                        </a>
                        <?php
                    }
                }
                else
                {
                    ?>
                    <span class="btn btn-success btn-block disabled">
                        <b>
                            <?php echo lang("installed", null, array("hu" => "Telepítve", "en" => "Installed", "de" => "Eingerichtet")); ?>
                        </b>
                    </span>
                    <?php
                }
                ?>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->

        <!-- About Me Box -->
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">About Me</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <strong><i class="fa fa-book margin-r-5"></i> Education</strong>

                <p class="text-muted">
                    B.S. in Computer Science from the University of Tennessee at Knoxville
                </p>

                <hr>

                <strong><i class="fa fa-map-marker margin-r-5"></i> Location</strong>

                <p class="text-muted">Malibu, California</p>

                <hr>

                <strong><i class="fa fa-pencil margin-r-5"></i> Skills</strong>

                <p>
                    <span class="label label-danger">UI Design</span>
                    <span class="label label-success">Coding</span>
                    <span class="label label-info">Javascript</span>
                    <span class="label label-warning">PHP</span>
                    <span class="label label-primary">Node.js</span>
                </p>

                <hr>

                <strong><i class="fa fa-file-text-o margin-r-5"></i> Notes</strong>

                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam fermentum enim neque.</p>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
    <!-- /.col -->
    <div class="col-md-9">
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#about" data-toggle="tab" aria-expanded="true"><i class="fa fa-info"></i> <?php echo lang("about", null, array("hu" => "Info", "en" => "About", "de" => "Über")); ?></a></li>
                <li class="pull-right"><a href="#settings" data-toggle="tab" aria-expanded="true"><i class="fa fa-gear"></i> <?php echo lang("settings", null, array("hu" => "Beállítások", "en" => "Settings", "de" => "Einstellungen")); ?></a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="about" data-remote="<?php echo url('module/about/'.strtolower($module)); ?>">

                </div>
                <!-- /.tab-pane -->
                <div class="tab-pane" id="settings" data-remote="<?php echo url('module/settings/'.strtolower($module)); ?>">

                </div>
                <!-- /.tab-pane -->
            </div>
            <!-- /.tab-content -->
        </div>
        <!-- /.nav-tabs-custom -->
    </div>
    <!-- /.col -->
</div>
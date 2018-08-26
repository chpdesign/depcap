<!DOCTYPE html>
<html>
<head>
    <base href="<?php echo \ComposerPack\System\Settings::get("base_link"); ?>"/>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo \ComposerPack\System\Settings::get("project_name"); ?> :: Admin</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <?php echo $css->renderHTML(/*new \ComposerPack\System\Minify\CSSMinify()*/); ?>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- Google Font -->
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
</head>
<body class="fixed skin-blue">

<div class="wrapper">

    <header class="main-header">

        <!-- Logo -->
        <a href="admin/" class="logo">
            <!-- mini logo for sidebar mini 50x50 pixels -->
            <span class="logo-mini"><?php echo substr(\ComposerPack\System\Settings::get("project_name"), 0,1); ?></span>
            <!-- logo for regular state and mobile devices -->
            <span class="logo-lg"><b><?php echo \ComposerPack\System\Settings::get("project_name"); ?></b>Admin</span>
        </a>

        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top">
            <!-- Sidebar toggle button-->
            <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                <span class="sr-only">Toggle navigation</span>
            </a>
            <!-- Navbar Right Menu -->
            <ul class="nav navbar-nav navbar-right">
                <li class="dropdown">
                    <span></span>
                    <ul class="dropdown-menu">

                    </ul>
                </li>
            </ul>
            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <img alt="" src="famfamfam-flags-icon/<?php echo \ComposerPack\Module\Language\Language::language(); ?>.png"/>
                        </a>
                        <ul class="dropdown-menu">
                            <?php
                            foreach (\ComposerPack\Module\Language\Language::get_langs() as $lang) {
                                ?><li><a href="<?php
                                if(\ComposerPack\System\Settings::get("langdir"))
                                {
                                    echo \ComposerPack\System\Settings::get("base_link").$lang;
                                    $seo = \ComposerPack\Module\Seo\Seo::where_url(\ComposerPack\System\Controller::$ACTIVE_URL)['url'][$lang];
                                    if(!empty($seo)) {
                                        echo '/'.$seo;
                                    }
                                    else {
                                        echo url();
                                    }
                                }
                                else
                                {
                                    echo \ComposerPack\Module\Seo\Seo::where_url(\ComposerPack\System\Controller::$ACTIVE_URL)['url'][$lang];
                                    echo '?lang='.$lang;
                                }
                                ?>"><img alt="" src="famfamfam-flags-icon/<?php echo $lang; ?>.png"/> <?php echo lang($lang); ?></a></li><?php
                            }
                            ?>
                        </ul>
                    </li>
                    <!-- User Account: style can be found in dropdown.less -->
                    <li class="dropdown user user-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <img src="<?php echo $_SESSION['user']->getProfileImage(); ?>"
                                 class="user-image" alt="User Image">
                            <span class="hidden-xs"><?php echo $_SESSION['user']['family_name']; ?> <?php echo $_SESSION['user']['given_name']; ?></span>
                        </a>
                        <ul class="dropdown-menu">
                            <!-- User image -->
                            <li class="user-header">
                                <img src="<?php echo $_SESSION['user']->getProfileImage(); ?>"
                                     class="img-circle" alt="User Image">

                                <p>
                                    <?php echo $_SESSION['user']['family_name']; ?> <?php echo $_SESSION['user']['given_name']; ?>
                                    <small><?php echo $_SESSION['user']['nick']; ?></small>
                                    <!-- <small><?php echo $_SESSION['user']['email']; ?></small> -->
                                </p>
                            </li>
                            <!-- Menu Footer-->
                            <li class="user-footer">
                                <div class="pull-left">
                                    <a href="#" class="btn btn-default btn-flat"><?php echo lang("profile", null, array("hu" => "Profil", "en" => "Profile", "de" => "Profil")); ?></a>
                                </div>
                                <div class="pull-right">
                                    <a href="admin/logout" class="btn btn-default btn-flat"><?php echo lang("logout", null, array("hu" => "Kijelenetkezés", "en" => "Logout", "de" => "Ausloggen")); ?></a>
                                </div>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>

        </nav>
    </header>

    <aside class="main-sidebar">
        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar" style="height: auto;">
            <!-- Sidebar user panel -->
            <div class="user-panel">
                <div class="pull-left image">
                    <img src="<?php echo $_SESSION['user']->getProfileImage(); ?>" class="img-circle" alt="User Image">
                </div>
                <div class="pull-left info">
                    <p><?php echo $_SESSION['user']['family_name']; ?> <?php echo $_SESSION['user']['given_name']; ?></p>
                    <span><?php echo $_SESSION['user']['nick']; ?></span>
                    <!-- <span><?php echo $_SESSION['user']['email']; ?></span> -->
                </div>
            </div>
            <!-- search form -->
            <form action="#" method="get" class="sidebar-form">
                <div class="input-group">
                    <input type="text" name="q" class="form-control" placeholder="<?php echo lang("search", null, array("hu" => "Keresés", "en" => "Search", "de" => "Suche")); ?>...">
                    <span class="input-group-btn">
                    <button type="submit" name="search" id="search-btn" class="btn btn-flat">
                      <i class="fa fa-search"></i>
                    </button>
                  </span>
                </div>
            </form>
            <!-- /.search form -->
            <!-- sidebar menu: : style can be found in sidebar.less -->
            <ul class="sidebar-menu tree" data-widget="tree">
                <li class="header"><?php echo lang("menu", null, array("hu" => "Menü", "en" => "Menu", "de" => "Menü")); ?></li>

            </ul>
        </section>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
        </section>
        <section class="content">
            <?php
            if(isset($content))
                echo $content;
            ?>
        </section>
    </div>
    <!-- /.content-wrapper -->


    <footer class="main-footer">
        <div class="pull-right hidden-xs">
            <!-- <b>Version</b> 2.4.0 -->
        </div>
        <strong>Copyright &copy; <?php echo date('Y'); ?> <a href="<?php echo \ComposerPack\System\Settings::get("base_link"); ?>"><?php echo \ComposerPack\System\Settings::get("project_name"); ?></a>.</strong> All rights
        reserved.
    </footer>

</div>

<?php echo $js->renderHTML(/*new \ComposerPack\System\Minify\JSMinify()*/); ?>
</body>
</html>
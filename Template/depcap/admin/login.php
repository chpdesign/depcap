<!DOCTYPE html>
<html>
<head>
    <base href="<?php echo \ComposerPack\System\Settings::get("base_link"); ?>" />
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo \ComposerPack\System\Settings::get("project_name"); ?> :: Admin login</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <?php echo $css->renderHTML(/*new \Beacon\System\Minify\CSSMinify()*/); ?>

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
<body class="hold-transition login-page">

<div class="login-box">
    <div class="login-logo">
        <b><?php echo \ComposerPack\System\Settings::get("project_name"); ?></b> Admin
    </div>
    <!-- /.login-logo -->
    <div class="login-box-body">
        <p class="message alert animated fadeIn" style="display: none;"></p>
        <form action="" method="post">
            <div class="form-group has-feedback">
                <input type="text" name="email" class="form-control" placeholder="<?php echo lang("email", null, array("hu" => "Email", "en" => "Email", "de" => "Email")); ?>">
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="password" name="password" class="form-control" placeholder="<?php echo lang("password", null, array("hu" => "Jelszó", "en" => "Password", "de" => "Passwort")); ?>">
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="row">
                <div class="col-xs-8">
                    <a href="#" class="btn btn-default btn-flat btn-block">I forgot my password</a>
                </div>
                <!-- /.col -->
                <div class="col-xs-4">
                    <button type="submit" class="btn btn-primary btn-block btn-flat"><?php echo lang("login", null, array("hu" => "Belépés", "en" => "Login", "de" => "Anmeldung")); ?></button>
                </div>
                <!-- /.col -->
            </div>
        </form>

    </div>
    <!-- /.login-box-body -->
</div>
<!-- /.login-box -->

<?php echo $js->renderHTML(/*new \Beacon\System\Minify\JSMinify()*/); ?>
</body>
</html>
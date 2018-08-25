<?php
namespace ComposerPack\Module\Module;

use ComposerPack\Admin\DefaultController;
use ComposerPack\System\Template;
use ComposerPack\System\Url;

class Controller extends DefaultController
{
    public function actionIndex()
    {
        $this->configToTemplateHeader();
        $this->template->content = new Template("modules");
    }

    public function actionInfo($moduleName)
    {
        $allModule = self::$modules->getAllModules();
        /**
         * @var $moduleConfig \ComposerPack\Module\Config
         */
        $moduleConfig = $allModule[mb_ucfirst($moduleName)];

        $this->configToTemplateHeader();
        $links = $this->template->links;
        $links[Controller::$ACTIVE_URL] = $moduleConfig->getName();
        $this->template->links = $links;

        $this->template->header = $moduleConfig->getName();
        $this->template->icon = $moduleConfig->getIcon();

        $this->template->content = new Template("info");
        $this->template->content->config = $moduleConfig;
        $this->template->content->module = mb_ucfirst($moduleName);
    }

    public function actionInstall($moduleName)
    {
        $allModule = self::$modules->getAllModules();
        /**
         * @var $moduleConfig \ComposerPack\Module\Config
         */
        $moduleConfig = $allModule[mb_ucfirst($moduleName)];

        $moduleConfig->install();

        Url::goBack();
    }

    public function actionUninstall($moduleName)
    {
        $allModule = self::$modules->getAllModules();
        /**
         * @var $moduleConfig \ComposerPack\Module\Config
         */
        $moduleConfig = $allModule[mb_ucfirst($moduleName)];

        $moduleConfig->uninstall();

        Url::goBack();
    }

    public function actionAbout($moduleName)
    {
        $allModule = self::$modules->getAllModules();
        /**
         * @var $moduleConfig \ComposerPack\Module\Config
         */
        $moduleConfig = $allModule[mb_ucfirst($moduleName)];

        $this->template = new Template("about");
        $this->template->config = $moduleConfig;
        $this->template->module = mb_ucfirst($moduleName);
    }

    public function actionSettings($moduleName)
    {
        $allModule = self::$modules->getAllModules();
        /**
         * @var $moduleConfig \ComposerPack\Module\Config
         */
        $moduleConfig = $allModule[mb_ucfirst($moduleName)];

        $this->template = new Template("settings");
        $this->template->config = $moduleConfig;
        $this->template->module = mb_ucfirst($moduleName);
    }
}
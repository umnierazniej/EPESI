<?php
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Utils_CryptoFormatterInstall extends ModuleInstall
{
    public function install()
    {
        return true;
    }

    public function uninstall()
    {
        return true;
    }

    public function requires($v)
    {
        return [

        ];
    }

    public function version()
    {
        return ['0.1'];
    }
}

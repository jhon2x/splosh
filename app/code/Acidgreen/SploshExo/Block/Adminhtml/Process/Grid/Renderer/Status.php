<?php

namespace Acidgreen\SploshExo\Block\Adminhtml\Process\Grid\Renderer;

class Status extends \Acidgreen\Exo\Block\Adminhtml\Process\Grid\Renderer\Status
{
    protected function _construct()
    {
        parent::_construct();
        self::$_statuses[\Acidgreen\SploshExo\Cron\Timeout::STATUS_TIMEOUT] = __('Timeout');
    }
}

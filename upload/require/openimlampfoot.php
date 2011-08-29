<?php
defined('OPEN_IM_ENABLED') || exit('Forbidden');
global $openImLampViewHelper;
if (!$openImLampViewHelper) return;
return $openImLampViewHelper->getShowLamp();
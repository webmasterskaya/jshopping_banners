<?php
/**
 * @package    JShopping - Banners
 * @version    __DEPLOY_VERSION__
 * @author     Artem Vasilev - Webmasterskaya
 * @copyright  Copyright (c) 2018 - 2020 Webmasterskaya. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://webmasterskaya.xyz/
 */

use Joomla\CMS\Helper\ModuleHelper;

defined('_JEXEC') or die;

// Include the banners functions only once
JLoader::register('ModJshoppingBannersHelper', __DIR__ . '/helper.php');

$headerText = trim($params->get('header_text'));
$footerText = trim($params->get('footer_text'));

JLoader::register('BannersHelper', JPATH_ADMINISTRATOR . '/components/com_banners/helpers/banners.php');
BannersHelper::updateReset();
$list = &ModJshoppingBannersHelper::getList($params);

if (!$list)
{
	return;
}

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8');

require ModuleHelper::getLayoutPath('mod_jshopping_banners', $params->get('layout', 'default'));
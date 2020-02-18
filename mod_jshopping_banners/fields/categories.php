<?php
/**
 * @package    JShopping - Banners
 * @version    __DEPLOY_VERSION__
 * @author     Artem Vasilev - Webmasterskaya
 * @copyright  Copyright (c) 2018 - 2020 Webmasterskaya. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://webmasterskaya.xyz/
 */

use Joomla\CMS\Factory as Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

class JFormFieldCategories extends JFormFieldList
{

	public $type = 'categories';

	protected function getOptions()
	{
		$db     = Factory::getDbo();
		$query  = $db->getQuery(true);
		$user   = Factory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$options = [];

		$query->select('a.id, a.title, a.level, a.language')
			->from('#__categories AS a')
			->where('a.parent_id > 0')
			->where('extension = \'com_banners\'')
			->where('a.access IN (' . $groups . ')')
			->where('a.published IN (0, 1)');
		$plugin_params     = json_decode(PluginHelper::getPlugin('system', 'jshopping_banners')->params, true);
		$plugin_categories = ArrayHelper::toInteger($plugin_params['categories']);
		if ($plugin_categories && array_search(0, $plugin_categories) === false)
		{
			$query->where('a.id IN (' . implode(',', $plugin_categories) . ')');
		}
		$query->order('a.lft');



		$db->setQuery($query);
		$items = $db->loadObjectList();
		foreach ($items as &$item)
		{
			$repeat = ($item->level - 1 >= 0) ? $item->level - 1 : 0;
			$item->title = str_repeat('- ', $repeat) . $item->title;

			if ($item->language !== '*')
			{
				$item->title .= ' (' . $item->language . ')';
			}

			$options[] = HTMLHelper::_('select.option', $item->id, $item->title);
		}

		return $options;
	}
}
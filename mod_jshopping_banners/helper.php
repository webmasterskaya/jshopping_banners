<?php
/**
 * @package    JShopping - Banners
 * @version    __DEPLOY_VERSION__
 * @author     Artem Vasilev - Webmasterskaya
 * @copyright  Copyright (c) 2018 - 2020 Webmasterskaya. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://webmasterskaya.xyz/
 */

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Environment\Browser;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\PluginHelper;

defined('_JEXEC') or die;

class ModJshoppingBannersHelper
{
	/**
	 * Retrieve list of banners
	 *
	 * @param   \Joomla\Registry\Registry  &$params  module parameters
	 *
	 * @return  array
	 *
	 * @throws Exception
	 * @since 1.0
	 */
	public static function &getList(&$params)
	{
		$option = Factory::getApplication()->input->getCmd('option', '');
		$ctrl   = Factory::getApplication()->input->getCmd('controller', '');
		$id     = Factory::getApplication()->input->getCmd('category_id', '');

		if ($option == 'com_jshopping' && $ctrl == 'category' && !empty($id))
		{
			BaseDatabaseModel::addIncludePath(JPATH_ROOT . '/components/com_banners/models', 'BannersModel');

			$plugin_params = json_decode(PluginHelper::getPlugin('system', 'jshopping_banners')->params, true);

			$document = JFactory::getDocument();
			$app      = JFactory::getApplication();
			$keywords = explode(',', $document->getMetaData('keywords'));
			$config   = ComponentHelper::getParams('com_banners');

			/** @var BannersModelBanners $model */
			$model = BaseDatabaseModel::getInstance('Banners', 'BannersModel', array('ignore_request' => true));
			$model->setState('filter.client_id', (int) $params->get('cid'));
			$model->setState('filter.category_id', $params->get('catid', $plugin_params['categories'] ? $plugin_params['categories'] : []));
			$model->setState('list.limit', (int) $params->get('count', 1));
			$model->setState('list.start', 0);
			$model->setState('filter.ordering', $params->get('ordering'));
			$model->setState('filter.tag_search', $params->get('tag_search'));
			$model->setState('filter.keywords', $keywords);
			$model->setState('filter.language', $app->getLanguageFilter());

			$banners = $model->getItems();

			if ($banners)
			{

				$items = array_filter($banners, ['self', 'clearList']);

				if ($items)
				{
					if ($config->get('track_robots_impressions', 1) == 1 || !Browser::getInstance()->isRobot())
					{
						self::impress($items);
					}
				}

				return $items;
			}
		}

		return [];
	}

	public static function impress($items)
	{
		$trackDate = Factory::getDate()->toSql();
		$db        = Factory::getDbo();
		$query     = $db->getQuery(true);

		if (!count($items))
		{
			return;
		}

		foreach ($items as $item)
		{
			$bid[] = (int) $item->id;
		}

		// Increment impression made
		$query->clear()
			->update('#__banners')
			->set('impmade = (impmade + 1)')
			->where('id IN (' . implode(',', $bid) . ')');
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (JDatabaseExceptionExecuting $e)
		{
			JError::raiseError(500, $e->getMessage());
		}

		foreach ($items as $item)
		{
			// Track impressions
			$trackImpressions = $item->track_impressions;

			if ($trackImpressions < 0 && $item->cid)
			{
				$trackImpressions = $item->client_track_impressions;
			}

			if ($trackImpressions < 0)
			{
				$config           = JComponentHelper::getParams('com_banners');
				$trackImpressions = $config->get('track_impressions');
			}

			if ($trackImpressions > 0)
			{
				// Is track already created?
				// Update count
				$query->clear();
				$query->update('#__banner_tracks')
					->set($db->quoteName('count') . ' = (' . $db->quoteName('count') . ' + 1)')
					->where('track_type=1')
					->where('banner_id=' . (int) $item->id)
					->where('track_date=' . $db->quote($trackDate));

				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (JDatabaseExceptionExecuting $e)
				{
					JError::raiseError(500, $e->getMessage());
				}

				if ($db->getAffectedRows() === 0)
				{
					// Insert new count
					$query->clear();

					$query->insert('#__banner_tracks')
						->columns(
							array(
								$db->quoteName('count'),
								$db->quoteName('track_type'),
								$db->quoteName('banner_id'),
								$db->quoteName('track_date')
							)
						)
						->values('1, 1, ' . (int) $item->id . ', ' . $db->quote($trackDate));

					$db->setQuery($query);

					try
					{
						$db->execute();
					}
					catch (JDatabaseExceptionExecuting $e)
					{
						JError::raiseError(500, $e->getMessage());
					}
				}
			}
		}
	}

	public static function clearList($item)
	{
		if (in_array(Factory::getApplication()->input->getCmd('category_id'),
			$item->params->get('categories', array(), 'array')))
		{
			return true;
		}
		else
		{
			if (array_search(0, $item->params->get('categories', array(), 'array')) !== false)
			{
				return true;
			}
		}

		return false;
	}
}
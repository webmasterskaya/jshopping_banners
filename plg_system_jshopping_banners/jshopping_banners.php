<?php
/**
 * @package    JShopping - Banners
 * @version    __DEPLOY_VERSION__
 * @author     Artem Vasilev - Webmasterskaya
 * @copyright  Copyright (c) 2018 - 2020 Webmasterskaya. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://webmasterskaya.xyz/
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Plugin\CMSPlugin;

defined('_JEXEC') or die;

/**
 * Class PlgSystemJshopping_banners
 *
 * @property \Joomla\Registry\Registry $params
 *
 * @since 1.0
 */
class PlgSystemJshopping_banners extends CMSPlugin
{

	/**
	 * @param   Form                                $form
	 * @param   \Joomla\CMS\Object\CMSObject|array  $data
	 *
	 * @return bool
	 * @throws Exception
	 *
	 * @since 1.0
	 */
	public function onContentPrepareForm(Form $form, $data)
	{
		if (!Factory::getApplication()->isClient('administrator'))
		{
			return false;
		}
		$name = $form->getName();
		if (in_array($name, ['com_banners.banner']))
		{
			$categories = $this->params->get('categories', array());

			if (in_array(Factory::getApplication()->input->getCmd('task'), ['apply', 'save2new', 'save2copy']))
			{
				$data = Factory::getApplication()->input->get('jform', [], 'array');
			}

			$catid = is_array($data) ? $data['catid'] : $data->get('catid');

			if (in_array($catid, $categories))
			{
				$language     = Factory::getLanguage();
				$language_tag = $language->getTag();
				$language->load('plg_system_jshopping_banners', JPATH_ROOT, $language_tag, true);
				Form::addFormPath(__DIR__ . '/forms');
				FormHelper::addFieldPath(__DIR__ . '/fields');
				$form->loadFile('categories', false);
				FormHelper::addFieldPath(__DIR__ . '/fields');
				Form::addFormPath(__DIR__ . '/forms');
				$form->loadFile('categories', false);
			}
			$form->setFieldAttribute('catid', 'onchange', 'categoryHasChanged(this);');
			Factory::getDocument()->addScriptDeclaration(<<<JS
			function categoryHasChanged(element) {
				var cat = jQuery(element);
				if (cat.val() == '{$catid}')return;
				Joomla.loadingLayer('show');
				jQuery('input[name=task]').val('banner.reload');
				element.form.submit();
			}
			jQuery( document ).ready(function() {
				Joomla.loadingLayer('load');
				var formControl = '#{$form->getFormControl()}_catid';
				if (!jQuery(formControl).val() != '{$catid}'){jQuery(formControl).val('{$catid}');}
			});
JS
			);
		}

		return true;
	}

	public function onContentBeforeSave($context, $item, $isNew, $data)
	{
		if ($context == 'com_banners.banner')
		{
			if (isset($data['params']['categories']) && !empty($data['params']['categories']) && count($data['params']['categories']) > 1)
			{
				$allPosition = array_search(0, $data['params']['categories']);
				if ($allPosition !== false)
				{
					unset($data['params']['categories'][$allPosition]);
					$item->params = json_encode($data['params']);
				}
			}
		}
	}
}
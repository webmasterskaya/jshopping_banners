<?php
/**
 * @package    JShopping - Banners
 * @version    __DEPLOY_VERSION__
 * @author     Artem Vasilev - Webmasterskaya
 * @copyright  Copyright (c) 2018 - 2020 Webmasterskaya. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://webmasterskaya.xyz/
 */

use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

require_once(JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_jshopping' . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "factory.php");
require_once(JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_jshopping' . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "functions.php");

class JFormFieldCategories extends FormField
{

	public $type = 'categories';

	protected function getInput()
	{
		$tmp              = new stdClass();
		$tmp->category_id = "0";
		$tmp->name        = Text::_('JALL');
		$categories       = [$tmp];
		$categories       = array_merge($categories, buildTreeCategory(0));
		$ctrl             = $this->name;
		$ctrl             .= '[]';

		$value = empty($this->value) ? '' : $this->value;

		return HTMLHelper::_('select.genericlist', $categories, $ctrl,
			'class="inputbox" id="category_ordering" multiple="multiple"', 'category_id', 'name', $value);
	}
}
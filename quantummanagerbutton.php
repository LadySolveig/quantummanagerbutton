<?php
/**
 * @package    quantummanagerbutton
 * @author     Dmitry Tsymbal <cymbal@delo-design.ru>
 * @copyright  Copyright © 2019 Delo Design & NorrNext. All rights reserved.
 * @license    GNU General Public License version 3 or later; see license.txt
 * @link       https://www.norrnext.com
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;

class PlgButtonQuantummanagerbutton extends CMSPlugin
{

	/**
	 * Application object
	 *
	 * @var    CMSApplication
	 * @since  1.0.0
	 */
	protected $app;


	/**
	 * Load the language file on instantiation.
	 *
	 * @var  boolean
	 *
	 * @since   1.1.0
	 */
	protected $autoloadLanguage = true;


	/**
	 * Display the button.
	 *
	 * @param   string  $name  The name of the button to add.
	 *
	 * @return  CMSObject  The button options as CMSObject.
	 *
	 * @throws  Exception
	 *
	 * @since   1.1.0
	 */
	public function onDisplay($name, $asset, $author)
	{
		if (!$this->accessCheck())
		{
			return;
		}

		$user = Factory::getUser();

		// Can create in any category (component permission) or at least in one category
		$canCreateRecords = $user->authorise('core.create', 'com_content')
			|| count($user->getAuthorisedCategories('com_content', 'core.create')) > 0;

		// Instead of checking edit on all records, we can use **same** check as the form editing view
		$values           = (array) Factory::getApplication()->getUserState('com_content.edit.article.id');
		$isEditingRecords = count($values);

		// This ACL check is probably a double-check (form view already performed checks)
		$hasAccess = $canCreateRecords || $isEditingRecords;
		if (!$hasAccess)
		{
			return;
		}

		JLoader::register('QuantummanagerHelper', JPATH_ROOT . '/administrator/components/com_quantummanager/helpers/quantummanager.php');
		$function = 'function(){}';
		$isJoomla4 = QuantummanagerHelper::isJoomla4();

		$link = 'index.php?option=com_ajax&amp;plugin=quantummanagerbutton&amp;group=editors-xtd&amp;format=html&amp;tmpl=component&amp;plugin.task=getmodal&amp;e_name=' . $name . '&amp;asset=com_content&amp;author='
			. Session::getFormToken() . '=1&amp;function=' . $function . '&amp;isjoomla4=' . ($isJoomla4 ? '1' : '0');

		$button        = new CMSObject();
		$button->modal = true;
		$button->class = 'btn';
		$button->link  = $link;
		$button->text  = Text::_('PLG_BUTTON_QUANTUMMANAGERBUTTON_BUTTON');

		if ($isJoomla4)
		{
			$button->name    = $this->_type . '_' . $this->_name;
			$button->icon    = 'pictures';
			$button->iconSVG = '<svg width="24" height="24" viewBox="0 0 512 512"><path d="M464 64H48C21.49 64 0 85.49 0 112v288c0 26.51 21.49 48'
				. ' 48 48h416c26.51 0 48-21.49 48-48V112c0-26.51-21.49-48-48-48zm-6 336H54a6 6 0 0 1-6-6V118a6 6 0 0 1 6-6h404a6 6'
				. ' 0 0 1 6 6v276a6 6 0 0 1-6 6zM128 152c-22.091 0-40 17.909-40 40s17.909 40 40 40 40-17.909 40-40-17.909-40-40-40'
				. 'zM96 352h320v-80l-87.515-87.515c-4.686-4.686-12.284-4.686-16.971 0L192 304l-39.515-39.515c-4.686-4.686-12.284-4'
				. '.686-16.971 0L96 304v48z"></path></svg>';
			$button->options = [
				'height'          => '400px',
				'width'           => '800px',
				'bodyHeight'      => '70',
				'modalWidth'      => '80',
				'tinyPath'        => $link,
				'confirmCallback' => 'Joomla.getImage(Joomla.selectedMediaFile, \'' . $name . '\', this)',
			];

			return $button;
		}

		$button->name    = 'file-add';
		$button->options = "{handler: 'iframe', size: {x: 1450, y: 700}, classWindow: 'quantummanager-modal-sbox-window'}";

		$label = Text::_('PLG_BUTTON_QUANTUMMANAGERBUTTON_BUTTON');

		Factory::getDocument()->addStyleDeclaration(<<<EOT
@media screen and (max-width: 1540px) {
	.mce-window[aria-label="{$label}"] {
		left: 2% !important;
		right: 0 !important;
		width: 95% !important;
	}
	
	.mce-window[aria-label="{$label}"] .mce-reset
	{
		width: 100% !important;
		height: 100% !important;
	}
	
	.mce-window[aria-label="{$label}"] .mce-window-body {
		width: 100% !important;
		height: calc(100% - 96px) !important;
	}
	
	.mce-window[aria-label="{$label}"] .mce-foot {
		width: 100% !important;
	}
	
	.mce-window[aria-label="{$label}"] .mce-foot .mce-container-body {
		width: 100% !important;
	}
	
	.mce-window[aria-label="{$label}"] .mce-foot .mce-container-body .mce-widget {
		left: auto !important;
		right: 18px !important;
	}
}

@media screen and (max-height: 700px) {

	.mce-window[aria-label="{$label}"] {
		top: 2% !important;
		height: 95% !important;
	}
		
	.mce-window[aria-label="{$label}"] .mce-window-body {
		height: calc(100% - 96px) !important;
	}
			
}


EOT
		);

		return $button;
	}


	public function onAjaxQuantummanagerbutton()
	{

		JLoader::register('QuantummanagerHelper', JPATH_ROOT . '/administrator/components/com_quantummanager/helpers/quantummanager.php');
		JLoader::register('QuantummanagerbuttonHelper', JPATH_ROOT . '/plugins/editors-xtd/quantummanagerbutton/helper.php');

		$app  = Factory::getApplication();
		$data = $app->input->getArray();
		$task = $app->input->get('plugin_task');
		$html = '';

		if (!$this->accessCheck())
		{
			return;
		}

		if ($task === 'getmodal')
		{
			QuantummanagerHelper::loadlang();
			$layout = new FileLayout('default', JPATH_ROOT . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, [
					'plugins', 'editors-xtd', 'quantummanagerbutton', 'tmpl'
				]));
			echo $layout->render();
		}

		if ($task === 'prepareforcontent')
		{
			if (!isset($data['params'], $data['scope']))
			{
				$app->close();
			}

			$scope           = $data['scope'];
			$params          = json_decode($data['params'], JSON_OBJECT_AS_ARRAY);
			$file            = QuantummanagerHelper::preparePath($data['path'], false, $scope, true);
			$name            = explode('/', $file);
			$filename        = end($name);
			$type            = explode('.', $file);
			$filetype        = end($type);
			$filesize        = filesize(JPATH_ROOT . '/' . $file);
			$scopesTemplate  = $this->params->get('scopes', QuantummanagerbuttonHelper::defaultValues());
			$scopesCustom    = $this->params->get('customscopes', []);
			$variables       = [];
			$variablesParams = [];
			$html            = '';

			$shortCode = false;
			$template  = '<a href="{file}" target="_blank">{name}</a>';

			if(is_array($scopesCustom))
			{
				$scopesCustom = [];
			}

			foreach ($scopesCustom as $scopeCustom)
			{
				$nameTmp                  = 'scopes' . count($scopesTemplate);
				$scopesTemplate->$nameTmp = $scopeCustom;
			}

			foreach ($scopesTemplate as $scopesTemplateCurrent)
			{

				$scopesTemplateCurrent = (object) $scopesTemplateCurrent;

				if ($scopesTemplateCurrent->id === $scope)
				{

					if (empty($scopesTemplateCurrent->templatelist))
					{
						foreach ($params['files'] as $item)
						{
							$file     = QuantummanagerHelper::preparePath($data['path'], false, $scope, true) . DIRECTORY_SEPARATOR . $item['file'];
							$name     = explode('/', $file);
							$filename = end($name);
							$type     = explode('.', $file);
							$filetype = mb_strtolower(end($type));
							$filesize = filesize(JPATH_ROOT . '/' . $file);

							$variables = [
								'{file}'     => $file,
								'{filename}' => $filename,
								'{type}'     => $filetype,
								'{size}'     => QuantummanagerHelper::formatFileSize($filesize),
							];

							if (file_exists(JPATH_ROOT . DIRECTORY_SEPARATOR . $file))
							{
								if (in_array($filetype, ['jpg', 'jpeg', 'png']))
								{
									list($width, $height, $type, $attr) = getimagesize(JPATH_ROOT . DIRECTORY_SEPARATOR . $file);
									$variables['{imagewidth}']  = $width;
									$variables['{imageheight}'] = $height;
								}
							}

							foreach ($item['fields'] as $key => $value)
							{
								if (preg_match("#^\{.*?\}$#isu", $key))
								{
									$variables[$key] = trim($value);
								}
							}

							$template         = '<a href="{file}" target="_blank">{name}</a>';
							$variablesFind    = [];
							$variablesReplace = [];

							foreach ($variables as $key => $value)
							{
								$variablesFind[]    = $key;
								$variablesReplace[] = $value;
							}

							$template = str_replace($variablesFind, $variablesReplace, $template);
							$html     .= preg_replace("#[\s\040]?[a-zA-Z0-9]{1,}\=\"\"#isu", '', $template);
						}
					}
					else
					{
						foreach ($scopesTemplateCurrent->templatelist as $templateList)
						{
							$templateList = (object) $templateList;
							if (isset($params['template']) && $templateList->templatename === $params['template'])
							{
								//собираем по выбранному шаблону
								$templatebefore = '';
								$templateitems  = '';
								$templateafter  = '';
								$shortCode      = false;

								if (preg_match("#^\{\{.*?\}\}$#isu", trim($templateList->templatebefore)))
								{
									$templatebefore = '[before]' . $templateList->templatebefore . '[/before]';
									$shortCode      = true;
								}
								else
								{
									$templatebefore = $templateList->templatebefore;
								}

								$variablesForTemplate = [];
								foreach ($params['files'] as $item)
								{
									$file     = QuantummanagerHelper::preparePath($data['path'], false, $scope, true) . DIRECTORY_SEPARATOR . $item['file'];
									$name     = explode('/', $file);
									$filename = end($name);
									$type     = explode('.', $file);
									$filetype = end($type);
									$filesize = filesize(JPATH_ROOT . '/' . $file);

									$variables = [
										'{file}'     => $file,
										'{filename}' => $filename,
										'{type}'     => $filetype,
										'{size}'     => QuantummanagerHelper::formatFileSize($filesize),
									];

									if (file_exists(JPATH_ROOT . DIRECTORY_SEPARATOR . $file))
									{
										if (in_array($filetype, ['jpg', 'jpeg', 'png']))
										{
											list($width, $height, $type, $attr) = getimagesize(JPATH_ROOT . DIRECTORY_SEPARATOR . $file);
											$variables['{imagewidth}']  = $width;
											$variables['{imageheight}'] = $height;
										}
									}

									foreach ($item['fields'] as $key => $value)
									{
										if (preg_match("#^\{.*?\}$#isu", $key))
										{
											$variables[$key] = trim($value);
										}
									}

									$variablesFind    = [];
									$variablesReplace = [];

									foreach ($variables as $key => $value)
									{
										$variablesFind[]    = $key;
										$variablesReplace[] = $value;
									}

									foreach ($variables as $key => $value)
									{
										$variables[$key] = str_replace($variablesFind, $variablesReplace, $value);
									}

									$variablesFind    = [];
									$variablesReplace = [];

									foreach ($variables as $key => $value)
									{
										$variablesFind[]    = $key;
										$variablesReplace[] = $value;
									}

									if (preg_match("#^\{\{.*?\}\}$#isu", trim($templateList->template)) || $shortCode)
									{
										$shortCode              = true;
										$variablesForTemplate[] = $variables;
									}
									else
									{
										$item          = str_replace($variablesFind, $variablesReplace, $templateList->template);
										$item          = preg_replace("#[\s\040]?[a-zA-Z0-9]{1,}\=\"\"#isu", '', $item);
										$templateitems .= $item;
									}

								}

								if ($shortCode)
								{
									$templateitems = '[item][variables]' . json_encode($variablesForTemplate) . '[/variables][template]' . $templateList->template . '[/template][/item]';
								}

								if (preg_match("#^\{\{.*?\}\}$#isu", trim($templateList->templateafter)))
								{
									$templateafter = '[after]' . $templateList->templateafter . '[/after]';
									$shortCode     = true;
								}
								else
								{
									$templateafter = $templateList->templateafter;
								}

								if ($shortCode)
								{
									$html = '[qmcontent]' . $templatebefore . $templateitems . $templateafter . '[/qmcontent]';
								}
								else
								{
									$html = $templatebefore . $templateitems . $templateafter;
								}

							}
						}
					}

				}
			}

			echo $html;

			$app->close();
		}

	}


	protected function accessCheck()
	{

		if ($this->app->isClient('administrator'))
		{
			return true;
		}

		// проверяем на включенность параметра
		JLoader::register('QuantummanagerHelper', JPATH_ADMINISTRATOR . '/components/com_quantummanager/helpers/quantummanager.php');

		if (!(int) QuantummanagerHelper::getParamsComponentValue('front', 0))
		{
			return false;
		}

		// проверяем что пользователь авторизован
		if (Factory::getUser()->id === 0)
		{
			return false;
		}

		return true;
	}

}

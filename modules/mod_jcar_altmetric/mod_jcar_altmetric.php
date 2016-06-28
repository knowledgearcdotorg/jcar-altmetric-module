<?php
/**
 * @package     JCar.Module
 * @copyright   Copyright (C) 2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

require_once dirname(__FILE__).'/helper.php';

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

$button = ModJCarAltmetricHelper::getButton($params);

require JModuleHelper::getLayoutPath('mod_'.$module->name, $params->get('layout', 'default'));

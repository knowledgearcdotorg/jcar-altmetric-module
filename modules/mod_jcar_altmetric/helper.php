<?php
/**
 * @package     JCar.Module
 * @copyright   Copyright (C) 2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use \Joomla\Utilities\ArrayHelper;

class ModJCarAltmetricHelper
{
    public static function getButton($params)
    {
        $id = null;
        $url = JURI::getInstance();
        $router = JApplicationCms::getRouter();
        $query = $router->parse($url);

        if ($params->get('use_type', 'uri') == 'handle') {
            if (ArrayHelper::getValue($query, 'option') == 'com_jcar' &&
                ArrayHelper::getValue($query, 'view') == 'item') {
                JModelLegacy::addIncludePath(JPATH_ROOT.'/components/com_jcar/models');

                $model = JModelLegacy::getInstance('Item', 'JCarModel');

                $model->setProperties($params->get('jcar'));

                $item = $model->getItem(ArrayHelper::getValue($query, 'id'));

                $id = $item->handle;
            }
        } else {
            $id = (string)$url;
        }

        if ($id) {
            $context = 'com_jcar.item';

            $row = new stdClass();
            $row->text = '{altmetric '.$id.'}';

            $params = array('type'=>$params->get('use_type', 'uri'));

            $dispatcher = JEventDispatcher::getInstance();
            JPluginHelper::importPlugin('content', 'altmetric');

            $responses = $dispatcher->trigger('onContentPrepare', array($context, &$row, &$params));

            if (array_pop($responses) == 1) {
                return $row->text;
            } else {
                return "";
            }
        }
    }

    private static function getButtonLegacy($params)
    {

    }
}

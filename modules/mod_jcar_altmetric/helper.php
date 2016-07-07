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
        $query = static::getQuery();

        $component = ArrayHelper::getValue($query, 'option');
        $view = ArrayHelper::getValue($query, 'view');

        if (array_search($component, array('com_jspace', 'com_jcar')) === false
            || $view !== 'item') {
            return "";
        }

        if ($params->get('use_type', 'uri') == 'handle') {
            if ($component == 'com_jspace' && $view == 'item') {
                $id = static::getHandleLegacy();
            } else if ($component == 'com_jcar') {
                JModelLegacy::addIncludePath(JPATH_ROOT.'/components/com_jcar/models');

                $model = JModelLegacy::getInstance('Item', 'JCarModel');

                $model->setProperties($params->get('jcar'));

                $item = $model->getItem((int)ArrayHelper::getValue($query, 'id'));

                $id = $item->handle;
            }
        } else {
            $id = (string)$url;
        }

        if ($id) {
            // @HACK This can be removed when old com_jspace is no longer supported.
            if ($component == 'com_jspace' && $view == 'item') {
                static::addMetaDataLegacy();
            }

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

    private static function getHandleLegacy()
    {
        $query = static::getQuery();

        JModelLegacy::addIncludePath(JPATH_ROOT.'/components/com_jspace/models');

        $model = JModelLegacy::getInstance('Item', 'JSpaceModel');

        $model->setItemId((int)ArrayHelper::getValue($query, 'id'));

        $item = $model->getItem();

        return $item->dspaceGetRaw()->handle;
    }

    private static function getQuery()
    {
        // copy the JUri object otherwise the router parse removes elements from the path.
        $url = clone JURI::getInstance();
        $router = JApplicationCms::getRouter();
        return $router->parse($url);
    }

    private static function addMetaDataLegacy()
    {
        $query = static::getQuery();

        JModelLegacy::addIncludePath(JPATH_ROOT.'/components/com_jspace/models');

        $model = JModelLegacy::getInstance('Item', 'JSpaceModel');

        $model->setItemId((int)ArrayHelper::getValue($query, 'id'));

        $item = $model->getItem();

        foreach ($item->getMetadataSet() as $key=>$metadata) {
            $metadata->rewind();

            switch ($key) {
                case "dc.publisher":
                    $name = "DC.publisher";
                    break;

                case "dc.title":
                    $name = "DC.title";
                    break;

                case "dc.contributor.author":
                    $name = "DC.author";
                    break;

                case "dc.date.issued":
                    $name = "DC.issued";
                    break;

                default:
                    $name = null;
                    break;
            }

            if ($name) {
                if (is_array($metadata)) {
                    foreach ($metadata as $value) {
                        JFactory::getDocument()->addCustomTag("<meta name=\"".$name."\" content=\"".(string)$value."\"/>");
                    }
                } else {
                    JFactory::getDocument()->addCustomTag("<meta name=\"".$name."\" content=\"".(string)$metadata."\"/>");
                }
            }
        }

        JFactory::getDocument()->setMetaData("DC.identifier", "http://hdl.handle.net/".$item->dspaceGetRaw()->handle);
    }
}

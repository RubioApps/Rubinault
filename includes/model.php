<?php

/**
 +-------------------------------------------------------------------------+
 | Rubinault  - Webapp Renault API                                         |
 | Version 1.0.0                                                           |
 |                                                                         |
 | This program is free software: you can redistribute it and/or modify    |
 | it under the terms of the GNU General Public License as published by    |
 | the Free Software Foundation.                                           |
 |                                                                         |
 | This file forms part of the Rubify software.                            |
 |                                                                         |
 | If you wish to use this file in another project or create a modified    |
 | version that will not be part of the Rubify Software, you               |
 | may remove the exception above and use this source code under the       |
 | original version of the license.                                        |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the            |
 | GNU General Public License for more details.                            |
 |                                                                         |
 | You should have received a copy of the GNU General Public License       |
 | along with this program.  If not, see http://www.gnu.org/licenses/.     |
 |                                                                         |
 +-------------------------------------------------------------------------+
 | Author: Jaime Rubio <jaime@rubiogafsi.com>                              |
 +-------------------------------------------------------------------------+
 */

namespace Rubinault\Framework;

defined('_RBNOEXEC') or die;

use Rubinault\Framework\Factory;
use Rubinault\Framework\Request;
use Rubinault\Framework\Pagination;

class Model
{
    protected $config;
    protected $params;
    protected $user;
    protected $page;
    protected $data;
    protected $link;
    protected $pagination;

    public function __construct(&$params = null)
    {
        // Get the parameters 
        $this->params   = new \stdClass;

        // Get the preferences
        foreach ($params as $k => $p) {
            if (is_object($p) && $k == 'config') {
                $this->config = $p;
            } else {
                if (!is_array($p)) {
                    if (strstr($p, ':') !== false) {
                        $alias = $k . '_alias';
                        $parts = explode(':', $p);
                        $this->params->$k = $parts[0];
                        $this->params->$alias = $parts[1];
                    } else {
                        $this->params->$k = $p;
                    }
                }
            }
        }

        // Get the query string
        $input = Request::get('GET');
        foreach ($input as $k => $p) {
            if (empty($this->params->$k)) {
                if (!is_array($p)) {
                    if (strstr($p, ':') !== false) {
                        $alias = $k . '_alias';
                        $parts = explode(':', $p);
                        $this->params->$k = $parts[0];
                        $this->params->$alias = $parts[1];
                    } else {
                        $this->params->$k = $p;
                    }
                }
            }
        }

        // Get the page
        $this->page         = Factory::getPage();
        $this->page->title  = $this->config->sitename;
        $this->user         = Factory::getUserID();
    }

    public function __destruct()
    {
        unset($this->page);
    }

    public function display( $tpl = null)
    {
        $this->page->menu   = $this->_menu();
        if($tpl !== null ){
            $this->page->setFile($tpl);
        }
        return true;
    }

    protected function _data()
    {
        return $this->data;
    }

    protected function _link()
    {
        return $this->link;
    }

    protected function _menu()
    {
        //Get vehicules
        $array = [];
        $user = Factory::getUser();        

        if ($user->isLogged()) {   
            $data = $user->get();         
            foreach ($data->vehicles as $veh) {
                $vin    = $veh['vin'];
                $images = Helpers::getInfo($vin, 'thumbnails');

                $item = new \stdClass();
                $item->vin          = $vin;
                $item->brand        = Helpers::getInfo($vin, 'brand');
                $item->image        = $images[0];
                $item->properties   = Helpers::getInfo($vin, 'properties');
                $item->plate        = Helpers::getInfo($vin, 'registrationNumber');
                $item->model        = Helpers::getCarModel($vin);
                $item->label        = $item->brand . ' ' . $item->model;
                $item->alias        = Helpers::encode($item->vin);
                $item->link         = Factory::Link('view', $item->vin);
                $array[] = $item;
            }
        }
        return $array;
    }

    protected function _pagination()
    {
        $offset = Request::getInt('offset', 0, 'GET');
        $limit  = Request::getInt('limit', $this->config->list_limit, 'GET');

        if ($this->data) {
            $total  = count($this->data);
            if ($offset > $total) $offset = 0;
            $this->page->data = array_slice($this->data, $offset, $limit, true);
            $this->pagination = new Pagination($total, (int) $offset, (int) $limit);

            // Clean-up redondant parameters (join id and alias)
            $query = Request::get('GET');

            unset($query['limit']);
            unset($query['offset']);
            foreach ($query as $key => $p) {
                if (isset($query[$key . '_alias'])) {
                    $query[$key] .= ':' . $query[$key . '_alias'];
                    unset($query[$key . '_alias']);
                }
            }

            // Add the parameters to the pagination
            foreach ($query as $key => $p)
                $this->pagination->setAdditionalUrlParam($key, $p);
        } else {
            $this->page->data = [];
            $this->pagination = new Pagination(0, (int) $offset, (int) $limit);
        }
        return $this->pagination;
    }
}

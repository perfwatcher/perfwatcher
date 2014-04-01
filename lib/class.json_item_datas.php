<?php # vim: set filetype=php fdm=marker sw=4 ts=4 et : 
/**
 * Copyright (c) 2014 Yves Mettier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @category  Monitoring
 * @author    Yves Mettier <ymettier AT free fr>
 * @copyright 2014 Yves Mettier
 * @license   http://opensource.org/licenses/mit-license.php
 * @link      http://www.perfwatcher.org/
 **/ 

class json_item_data {
    private $datas = array();
    private $datas_unchecked = array();
    private $datas_allowed_keys = array(
    );
    private $mandatory_fields = array(
        'host' => 0,
        'config_widgets' => 0,
        'jstree' => 0,
        'config_source' => 0,
    );

    function __construct($args = null) {
        $this->datas = array(
                'host' => "",
                'plugins' => array(),
                'aggregators' => array(),
                'jstree' => array(),
                'datas' => array(),
                'config' => array(
                    'widgets' => array(),
                    'CdSrc' => array(
                        'db_value' => "",
                        'source' => "",
                        'inherited' => "",
                        )
                    )
                );
    }

    function set_host($host) {
        $this->datas['host'] = $host;
        $this->mandatory_fields['host'] = 1;
    }
    function set_plugins($plugins) {
        $this->datas['plugins'] = $plugins;
    }
    function set_aggregators($aggregators) {
        $this->datas['aggregators'] = $aggregators;
    }
    function set_jstree($jstree) {
        $this->datas['jstree']['id'] = isset($jstree['id'])?$jstree['id']:0;
        $this->datas['jstree']['pwtype'] = isset($jstree['pwtype'])?$jstree['pwtype']:"undef";
        $this->mandatory_fields['jstree'] = 1;
    }
    function set_datas($key,$value) {
        $this->datas_unchecked[$key] = $value;
    }
    function set_config_widgets($widgets) {
        $this->datas['config']['widgets'] = $widgets;
        $this->mandatory_fields['config_widgets'] = 1;
    }
    function set_config_source($db_value, $source, $inherited) {
        $this->datas['config']['CdSrc']['db_value'] = $db_value;
        $this->datas['config']['CdSrc']['source'] = $source;
        $this->datas['config']['CdSrc']['inherited'] = $inherited;
        $this->mandatory_fields['config_source'] = 1;
    }

    function validate() {
        $rc = 1;
        foreach ($this->mandatory_fields as $k => $v) {
            if($v != 1) {
                $rc = 0;
            }
        }
        $this->datas['datas'] = array();
        foreach ($this->datas_allowed_keys as $k) {
            if(isset($this->datas_unchecked[$k])) {
                $this->datas['datas'][$k] = $this->datas_unchecked[$k];
            }
        }
        foreach ($this->datas['config']['widgets'] as $k => $a) {
            if(isset($a['db_config_key']) && isset($this->datas_unchecked[$a['db_config_key']])) {
                $this->datas['datas'][$a['db_config_key']] = $this->datas_unchecked[$a['db_config_key']];
            }
        }
        return($rc);
    }

    function to_json() {
        //TODO : check if $this->mandatory_fields values are all set to 1
        $rc = $this->validate();
        $rv = json_encode(
                $this->datas,
                JSON_FORCE_OBJECT
                );
        return($rv);
    }
}

?>

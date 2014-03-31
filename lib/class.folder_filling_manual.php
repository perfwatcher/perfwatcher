<?php # vim: set filetype=php fdm=marker sw=4 ts=4 et : 
/**
 * Copyright (c) 2012 Cyril Feraudet
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
 * @author    Cyril Feraudet <cyril@feraudet.com>
 * @copyright 2012 Cyril Feraudet
 * @license   http://opensource.org/licenses/mit-license.php
 * @link      http://www.perfwatcher.org/
 **/ 

class folder_filling_manual {
    private $item = array();

    function __construct($item) {
        $this->item =& $item;
    }

    function is_compatible() {
        switch($this->item['pwtype']) {
            case 'container':
                return true;
                break;
            default:
                return false;
                break;
        }
    }

    function get_info() {
        return array(
                'title' => "Autofill this container using manual list",
                'content_url' => 'html/folder_filling_manual.html',
                'db_config_key' => 'serverslist',
                );
    }

    function get() {
        global $jstree;
        $datas = $jstree->get_datas($this->item['id']);
        if (isset($datas['serverslist']) &&  isset($datas['serverslist']['manuallist'])) {
            $r = array();
            foreach (split("\n", $datas['serverslist']['manuallist']) as $l) {
                $r[] = trim($l);
            }
            return $r;
        } else { return array(); }
    }

    function save ($list) {
        global $jstree;
        $datas = $jstree->get_datas($this->item['id']);
        if (!isset($datas['serverslist'])) { $datas['serverslist'] = array(); }
        if($list) {
            $datas['serverslist']['manuallist'] = $list;
        } else {
            unset($datas['serverslist']['manuallist']);
        }
        $jstree->set_datas($this->item['id'], $datas);
        return true;
    }
}

?>

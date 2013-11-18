<?php # vim: set filetype=php fdm=marker sw=4 ts=4 et : 
/*
 * Copyright (C) 2009  Bruno Prémont <bonbons AT linux-vserver.org>
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation; only version 2 of the License is applicable.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 *
 * Modified by Cyril Feraudet for it's needs
 *
 */

define('REGEXP_HOST', '/^[a-zA-Z0-9]([a-zA-Z0-9-_,]{0,61}[a-zA-Z0-9])?(\\.[a-zA-Z0-9]([a-zA-Z0-9-_,]{0,61}[a-zA-Z0-9])?)*$/');
define('REGEXP_PLUGIN', '/^[a-zA-Z0-9_.-]+$/');

/**
 * Read input variable from GET, POST or COOKIE taking
 * care of magic quotes
 * @name Name of value to return
 * @array User-input array ($_GET, $_POST or $_COOKIE)
 * @default Default value
 * @return $default if name in unknown in $array, otherwise
 *         input value with magic quotes stripped off
 */
function read_var($name, &$array, $default = null) {
    if (isset($array[$name])) {
        if (is_array($array[$name])) {
            if (get_magic_quotes_gpc()) {
                $ret = array();
                while (list($k, $v) = each($array[$name]))
                    $ret[stripslashes($k)] = stripslashes($v);
                return $ret;
            } else
                return $array[$name];
        } else if (is_string($array[$name]) && get_magic_quotes_gpc()) {
            return stripslashes($array[$name]);
        } else
            return $array[$name];
    } else
        return $default;
}

/**
 * Alphabetically compare host names, comparing label
 * from tld to node name
 */
function collectd_compare_host($a, $b) {
    $ea = explode('.', $a);
    $eb = explode('.', $b);
    $i = count($ea) - 1;
    $j = count($eb) - 1;
    while ($i >= 0 && $j >= 0)
        if (($r = strcmp($ea[$i--], $eb[$j--])) != 0)
            return $r;
    return 0;
}

/**
 * Fetch list of types found in collectd's datadirs for given host+plugin+instance
 * @arg_host Name of host
 * @arg_plugin Name of plugin
 * @arg_pinst Plugin instance
 * @return Sorted list of types (sorted alphabetically)
 */
function collectd_list_types($collectd_source, $arg_host, $arg_plugin, $arg_pinst, $arg_type = null) {
    $types = get_list_of_types_instances($collectd_source, $arg_host, $arg_plugin, $arg_pinst, $arg_type);
    $types = array_unique($types);
    sort($types);
    return $types;
}

class CollectdColor {
    private $r = 0;
    private $g = 0;
    private $b = 0;

    function __construct($value = null) {
        if (is_null($value)) {
        } else if (is_array($value)) {
            if (isset($value['r']))
                $this->r = $value['r'] > 0 ? ($value['r'] > 1 ? 1 : $value['r']) : 0;
            if (isset($value['g']))
                $this->g = $value['g'] > 0 ? ($value['g'] > 1 ? 1 : $value['g']) : 0;
            if (isset($value['b']))
                $this->b = $value['b'] > 0 ? ($value['b'] > 1 ? 1 : $value['b']) : 0;
        } else if (is_string($value)) {
            $matches = array();
            if ($value == 'random') {
                $this->randomize();
            } else if (preg_match('/([0-9A-Fa-f][0-9A-Fa-f])([0-9A-Fa-f][0-9A-Fa-f])([0-9A-Fa-f][0-9A-Fa-f])/', $value, $matches)) {
                $this->r = ('0x'.$matches[1]) / 255.0;
                $this->g = ('0x'.$matches[2]) / 255.0;
                $this->b = ('0x'.$matches[3]) / 255.0;
            }
        } else if (is_a($value, 'CollectdColor')) {
            $this->r = $value->r;
            $this->g = $value->g;
            $this->b = $value->b;
        }
    }

    function randomize() {
        $this->r = rand(0, 255) / 255.0;
        $this->g = rand(0, 255) / 255.0;
        $this->b = 0.0;
        $min = 0.0;
        $max = 1.0;

        if (($this->r + $this->g) < 1.0) {
            $min = 1.0 - ($this->r + $this->g);
        } else {
            $max = 2.0 - ($this->r + $this->g);
        }
        $this->b = $min + ((rand(0, 255)/255.0) * ($max - $min));
    }

    function fade($bkgnd = null, $alpha = 0.25) {
        if (is_null($bkgnd) || !is_a($bkgnd, 'CollectdColor')) {
            $bg_r = 1.0;
            $bg_g = 1.0;
            $bg_b = 1.0;
        } else {
            $bg_r = $bkgnd->r;
            $bg_g = $bkgnd->g;
            $bg_b = $bkgnd->b;
        }

        $this->r = $alpha * $this->r + ((1.0 - $alpha) * $bg_r);
        $this->g = $alpha * $this->g + ((1.0 - $alpha) * $bg_g);
        $this->b = $alpha * $this->b + ((1.0 - $alpha) * $bg_b);
    }

    function as_array() {
        return array('r'=>$this->r, 'g'=>$this->g, 'b'=>$this->b);
    }

    function as_string() {
        $r = (int)($this->r*255);
        $g = (int)($this->g*255);
        $b = (int)($this->b*255);
        return sprintf('%02x%02x%02x', $r > 255 ? 255 : $r, $g > 255 ? 255 : $g, $b > 255 ? 255 : $b);
    }
}


/**
 * Helper function to strip quotes from RRD output
 * @str RRD-Info generated string
 * @return String with one surrounding pair of quotes stripped
 */
function rrd_strip_quotes($str) {
    if ($str[0] == '"' && $str[strlen($str)-1] == '"')
        return substr($str, 1, strlen($str)-2);
    else
        return $str;
}

function rrd_escape($str) {
    return $str;
    return str_replace(array('\\', ':'), array('\\\\', '\\:'), $str);
}

/**
 * Determine useful information about RRD file
 * @file Name of RRD file to analyse
 * @return Array describing the RRD file
 */
function _rrd_info($collectd_source, $file) {
    $json = json_encode(array(
                "jsonrpc" => "2.0",
                "method" => "pw_rrd_info",
                "params" => array( "rrdfile" => $file),
                "id" => 0)
            );
    $ra = jsonrpc_query($collectd_source, $json);

    $ret = array('filename'=>$file);

    if(!(isset($ra[0]) && isset($ra[1]))) { return($ret); }
    $r = $ra[0];
    $r['filename'] = $file;

    return ($r);
}

function rrd_get_color($code, $line = true) {
    global $config;
    $name = ($line ? 'f_' : 'h_').$code;
    if (!isset($config['rrd_colors'][$name])) {
        $c_f = new CollectdColor('random');
        $c_h = new CollectdColor($c_f);
        $c_h->fade();
        $config['rrd_colors']['f_'.$code] = $c_f->as_string();
        $config['rrd_colors']['h_'.$code] = $c_h->as_string();
    }
    return $config['rrd_colors'][$name];
}

function rrd_check_files($collectd_source, $files) {
    global $config;
    $rrdfiles = array();
    if(! is_array($files)) {
        return($rrdfiles);
    }

    $json = json_encode(array(
                "jsonrpc" => "2.0",
                "method" => "pw_rrd_check_files",
                "params" => $files,
                "id" => 0)
            );
    $ra = jsonrpc_query($collectd_source, $json);

    if(!(isset($ra[0]) && isset($ra[1]))) { return($rrdfiles); }
    $r = $ra[0];

    if(!$r) { return($rrdfiles); }

    foreach($r as $a) {
        if($a["type"] == "REG") {
            if(isset($a["path"])) {
                $rrdfiles[$a["file"]] = $a["path"];
            } else {
                $rrdfiles[$a["file"]] = $a["file"];
            }
        } else if($a["type"] == "LNK") {
            $rrdfiles[$a["file"]] = $a["linked_to"];
        }
    }

    return ($rrdfiles);
}


function rrd_get_files($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances) {
    global $config;
    $rrdfiles = array();

    $json = json_encode(array(
                "jsonrpc" => "2.0",
                "method" => "pw_get_dir_types",
                "params" => array( "hostname" => $host, "plugin" => "$plugin".(!is_null($plugin_instance) ? "-$plugin_instance" : "")),
                "id" => 0)
            );
    $ra = jsonrpc_query($collectd_source, $json);

    if(!(isset($ra[0]) && isset($ra[1]))) { return($rrdfiles); }
    $r = $ra[0];
    if (! isset($r['nb'])) { return($rrdfiles); }

    $data = $r['values'];
    if(!$data) { return($rrdfiles); }

    $datadir = $r['datadir'];
    if(!$datadir) { return($rrdfiles); }

    $hostplugin = "$host/$plugin".(!is_null($plugin_instance) ? "-$plugin_instance" : '');

    while (list($k, $f) = each($data)) {
        if (substr($f, -4) != '.rrd') { continue; }
        $metric = explode('-', substr($f, -0, -4), 2);
        if($metric[0] != $type) { continue; }
        if(!isset($metric[1])) { $metric[1] = ""; }

        if(is_array($type_instances)) {
            foreach($type_instances as $ti) {
                if($ti == $metric[1]) {
                    #$rrdfiles[] = array(fqdn, type, type_instance);
                    $rrdfiles[] = array("$datadir/$hostplugin/$f", $metric[0], $metric[1]);
                    continue;
                }
            }
        } else {
            #$rrdfiles[] = array(fqdn, type, type_instance);
            $rrdfiles[] = array("$datadir/$hostplugin/$f", $metric[0], $metric[1]);
        }
    }

/* Sort result according to $type_instances type */
    if(is_array($type_instances)) {
        usort($rrdfiles, function($a, $b) use ($type_instances) {
                if($a[0] != $b[0]) return($a[0] > $b[0] ? 1 : -1);
                if($a[1] != $b[1]) return($a[1] > $b[1] ? 1 : -1);
                foreach ($type_instances as $ti) {
                    if($a[2] == $ti) return(1);
                    if($b[2] == $ti) return(-1);
                }
            return(0); /* This should not happen. Return whatever */
        });
    }
    return ($rrdfiles);
}

function rrd_sources_from_files_sorted_by_type_instance ($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances) {
    $sources = array();

    $files = rrd_get_files($collectd_source, $host, $plugin, $plugin_instance, $type, array());

    while (list($k, $a) = each($files)) {
        if(preg_match("/^([0-9]+)$/", $a[2], $reg) ) {
            $sources[] = array('name'=> $a[2], 'file'=> $a[0]);
        }
    }
    usort($sources, function($a, $b) {
            if($a['name'] == $b['name']) { return 0; }
            return ($a['name'] < $b['name']) ? -1 : 1;
            }
         );

    return ($sources);
}

function rrd_sources_from_files ($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances) {
    $sources = array();

    $files = rrd_get_files($collectd_source, $host, $plugin, $plugin_instance, $type, $type_instances);

    while (list($k, $a) = each($files)) {
        $sources[] = array('name'=> $a[2], 'file'=> $a[0]);
    }
    return ($sources);
}

/**
 * Draw RRD file based on it's structure
 * @host
 * @plugin
 * @pinst
 * @type
 * @tinst
 * @opts
 * @return Commandline to call RRDGraph in order to generate the final graph
 */
function collectd_draw_rrd($collectd_source, $host, $plugin, $pinst = null, $type, $tinst = null, $opts = array()) {
    global $config, $begin, $end;

    if (!isset($opts['rrd_opts']))
        $opts['rrd_opts'] = array();
    if (isset($opts['logarithmic']) && $opts['logarithmic'])
        array_unshift($opts['rrd_opts'], '-o');
    if (isset($opts['zero']) && $opts['zero']) {
        array_unshift($opts['rrd_opts'], '0');
        array_unshift($opts['rrd_opts'], '-l');
        array_unshift($opts['rrd_opts'], '-r');
    }
    $althost = "";
    if (isset($opts['althost'])) {
        $althost = $opts['althost'];
    }

    $rrdinfo = null;
    $rrdfile = sprintf('%s/%s%s%s/%s%s%s', $host, $plugin, is_null($pinst) ? '' : '-', $pinst, $type, is_null($tinst) ? '' : '-', $tinst);
    $rrdtitle = sprintf('%s/%s%s%s/%s%s%s', $althost?$althost:get_node_name($host), $plugin, is_null($pinst) ? '' : '-', $pinst, $type, is_null($tinst) ? '' : '-', $tinst);

    $rrdinfo = _rrd_info($collectd_source, $rrdfile.'.rrd');
    if (is_null($rrdinfo) || !isset($rrdinfo['RRA']) || !is_array($rrdinfo['RRA']))
        return false;

    $graph = array();
    $has_avg = false;
    $has_max = false;
    $has_min = false;
    reset($rrdinfo['RRA']);
    $l_max = 0;
    while (list($k, $v) = each($rrdinfo['RRA'])) {
        if ($v['cf'] == 'MAX')
            $has_max = true;
        else if ($v['cf'] == 'AVERAGE')
            $has_avg = true;
        else if ($v['cf'] == 'MIN')
            $has_min = true;
    }
    reset($rrdinfo['DS']);
    while (list($k, $v) = each($rrdinfo['DS'])) {
        if (strlen($k) > $l_max)
            $l_max = strlen($k);
        if ($has_min)
            $graph[] = sprintf('DEF:%s_min=%s:%s:MIN', $k, rrd_escape($rrdinfo['rrd']['filename']), $k);
        if ($has_avg)
            $graph[] = sprintf('DEF:%s_avg=%s:%s:AVERAGE', $k, rrd_escape($rrdinfo['rrd']['filename']), $k);
        if ($has_max)
            $graph[] = sprintf('DEF:%s_max=%s:%s:MAX', $k, rrd_escape($rrdinfo['rrd']['filename']), $k);
    }
    if ($has_min && $has_max || $has_min && $has_avg || $has_avg && $has_max) {
        $n = 1;
        reset($rrdinfo['DS']);
        while (list($k, $v) = each($rrdinfo['DS'])) {
            $graph[] = sprintf('LINE:%s_%s', $k, $has_min ? 'min' : 'avg');
            $graph[] = sprintf('CDEF:%s_var=%s_%s,%s_%s,-', $k, $k, $has_max ? 'max' : 'avg', $k, $has_min ? 'min' : 'avg');
            $graph[] = sprintf('AREA:%s_var#%s::STACK', $k, rrd_get_color($n++, false));
        }
    }

    reset($rrdinfo['DS']);
    $n = 1;
    while (list($k, $v) = each($rrdinfo['DS'])) {
        $graph[] = sprintf('LINE1:%s_avg#%s:%s ', $k, rrd_get_color($n++, true), $k.substr('                  ', 0, $l_max-strlen($k)));
        if (isset($opts['tinylegend']) && $opts['tinylegend'])
            continue;
        if ($has_avg)
            $graph[] = sprintf('GPRINT:%s_avg:AVERAGE:Average\:%%5.1lf%%s %s', $k, $has_max || $has_min || $has_avg ? ' ' : "\\l");
        if ($has_min)
            $graph[] = sprintf('GPRINT:%s_min:MIN:Min\:%%5.1lf%%s %s', $k, $has_max || $has_avg ? ' ' : "\\l");
        if ($has_max)
            $graph[] = sprintf('GPRINT:%s_max:MAX:Max\:%%5.1lf%%s %s', $k, $has_avg ? ' ' : "\\l");
        if ($has_avg)
            $graph[] = sprintf('GPRINT:%s_avg:LAST:Last\:%%5.1lf%%s\\l', $k);
    }

    $rrd_cmd = array('-W', 'PERFWATCHER', '-a', 'PNG', '-w', "".$config['rrd_width'], '-h', "".$config['rrd_height'], '-t', $rrdtitle);
    //    $rrd_cmd[] = 'VRULE:'.$GLOBALS['xcenter'].'#888888:'.date("Y/m/d H\\\\:i\\\\:s",$GLOBALS['xcenter']).'\l:dashes';
    $rrd_cmd[] = '-s';
    $rrd_cmd[] = $begin;
    if ($end != '') {
        $rrd_cmd[] = '-e';
        $rrd_cmd[] = $end;
    }
    $rrd_cmd = array_merge($rrd_cmd, $config['rrd_opts'], $opts['rrd_opts'], $graph);

    $cmd = array();
    for ($i = 1; $i < count($rrd_cmd); $i++)
        $cmd .= ' '.escapeshellarg($rrd_cmd[$i]);

    return $rrd_cmd;
}

/**
 * Draw RRD file based on it's structure
 * @timespan
 * @host
 * @plugin
 * @pinst
 * @type
 * @tinst
 * @opts
 * @return Commandline to call RRDGraph in order to generate the final graph
 */
function collectd_draw_generic($collectd_source, $timespan, $host, $plugin, $pinst = null, $type, $tinst = null, $opts = array()) {
    global $config, $GraphDefs, $begin, $end;

    if (!isset($GraphDefs[$type]))
        return false;

    $althost = "";
    if (isset($opts['althost'])) {
        $althost = $opts['althost'];
    }

    $rrd_file = sprintf('%s/%s%s%s/%s%s%s', $host, $plugin, is_null($pinst) ? '' : '-', $pinst, $type, is_null($tinst) ? '' : '-', $tinst);
    $rrdtitle = sprintf('%s/%s%s%s/%s%s%s', $althost?$althost:get_node_name($host), $plugin, is_null($pinst) ? '' : '-', $pinst, $type, is_null($tinst) ? '' : '-', $tinst);
    $rrd_cmd  = array('-W', 'PERFWATCHER', '-a', 'PNG', '-w', "".$config['rrd_width'], '-h', "".$config['rrd_height'], '-t', $rrdtitle);
    $rrd_cmd[] = '-s';
    $rrd_cmd[] = $begin;
    if ($end != '') {
        $rrd_cmd[] = '-e';
        $rrd_cmd[] = $end;
    }
    $rrd_cmd  = array_merge($rrd_cmd, $config['rrd_opts']);
    $rrd_args = $GraphDefs[$type];

/* Build the list of files */
    $file = $rrd_file.'.rrd';
    $rrdfiles = array($file);
    foreach($rrd_args as $l) {
        if(preg_match('/\{pathplugin\}([^:]+)/', $l, $m)) {
            $rrdfiles[] = sprintf('%s/%s%s%s/%s', $host, $plugin, is_null($pinst) ? '' : '-', $pinst, $m[1]);
        }
    }
/* Get the list of existing files */
    $rrd_checked_files = rrd_check_files($collectd_source, $rrdfiles);

/* Initialization of $file and $pathplugin */
    $pathplugin = dirname($file);
    foreach($rrdfiles as $f) {
        if(isset($rrd_checked_files[$f])) {
            $file = $rrd_checked_files[$f];
            $pathplugin = dirname($file);
            break;
        }
    }

/* Replace {file} and {pathplugin} everywhere */
    $file = str_replace(":", "\\:", $file);
    $rrd_args = str_replace('{file}', rrd_escape($file), $rrd_args);
    $rrd_args = str_replace('{pathplugin}', $pathplugin.'/', $rrd_args);
    //        $rrd_args[] = 'VRULE:'.$GLOBALS['xcenter'].'#888888:'.date("Y/m/d H\\\\:i\\\\:s",$GLOBALS['xcenter']).'\l:dashes';

/* Enjoy */
    $rrdgraph = array_merge($rrd_cmd, $rrd_args);

    return $rrdgraph;
}

/**
 * Draw stack-graph for set of RRD files
 * @opts Graph options like colors
 * @sources List of array(name, file, ds)
 * @return Commandline to call RRDGraph in order to generate the final graph
 */
function collectd_draw_meta_stack($collectd_source, &$opts, &$sources) {
    global $config, $begin, $end;

    if (!isset($opts['title']))
        $opts['title'] = 'Unknown title';
    if (!isset($opts['rrd_opts']))
        $opts['rrd_opts'] = array();
    if (!isset($opts['colors']))
        $opts['colors'] = array();
    if (isset($opts['logarithmic']) && $opts['logarithmic'])
        array_unshift($opts['rrd_opts'], '-o');
    if (isset($opts['zero']) && $opts['zero']) {
        array_unshift($opts['rrd_opts'], '0');
        array_unshift($opts['rrd_opts'], '-l');
        array_unshift($opts['rrd_opts'], '-r');
    }

    $cmd = array('-W', 'PERFWATCHER', '-a', 'PNG', '-w', "".$config['rrd_width'], '-h', "".$config['rrd_height'], '-t', $opts['title']);
    $cmd = array_merge($cmd, $config['rrd_opts'], $opts['rrd_opts']);
    $max_inst_name = 0;

    $rrdfiles = array();
    foreach($sources as &$inst_data) {
        $rrdfiles[] = $inst_data['file'];
    }
    $rrd_checked_files = rrd_check_files($collectd_source, $rrdfiles);

    foreach($sources as &$inst_data) {
        $file      = $inst_data['file'];
        if(!isset($rrd_checked_files[$file])) continue;

        $inst_name = str_replace('!', '_', $inst_data['name']);
        $ds        = isset($inst_data['ds']) ? $inst_data['ds'] : 'value';
        $reverse   = isset($inst_data['reverse']) ? $inst_data['reverse'] : false;

        if (strlen($inst_name) > $max_inst_name)
            $max_inst_name = strlen($inst_name);

        $cmd[] = 'DEF:'.$inst_name.'_avg='.rrd_escape($file).':'.$ds.':AVERAGE';
        $cmd[] = 'CDEF:'.$inst_name.'_nnl='.$inst_name.'_avg,UN,0,'.$inst_name.'_avg,IF';
    }
    $inst_data = end($sources);
    $inst_name = $inst_data['name'];
    $cmd[] = 'CDEF:'.$inst_name.'_stk='.$inst_name.'_nnl';

    $inst_data1 = end($sources);
    while (($inst_data0 = prev($sources)) !== false) {
        $inst_name0 = str_replace('!', '_', $inst_data0['name']);
        $inst_name1 = str_replace('!', '_', $inst_data1['name']);

        $cmd[] = 'CDEF:'.$inst_name0.'_stk='.$inst_name0.'_nnl,'.$inst_name1.'_stk,+';
        $inst_data1 = $inst_data0;
    }

    foreach($sources as &$inst_data) {
        $inst_name = str_replace('!', '_', $inst_data['name']);
        $legend = sprintf('%s', $inst_data['name']);
        while (strlen($legend) < $max_inst_name)
            $legend .= ' ';
        $number_format = isset($opts['number_format']) ? $opts['number_format'] : '%6.1lf';

        if (isset($opts['colors'][$inst_name]))
            $line_color = new CollectdColor($opts['colors'][$inst_name]);
        else
            $line_color = new CollectdColor('random');
        $area_color = new CollectdColor($line_color);
        $area_color->fade();

        $cmd[] = 'AREA:'.$inst_name.'_stk#'.$area_color->as_string();
        $cmd[] = 'LINE1:'.$inst_name.'_stk#'.$line_color->as_string().':'.$legend;
        if (!(isset($opts['tinylegend']) && $opts['tinylegend'])) {
            $cmd[] = 'GPRINT:'.$inst_name.'_avg:LAST:Last\:'.$number_format.' ';
            $cmd[] = 'GPRINT:'.$inst_name.'_avg:AVERAGE:Average\:'.$number_format.'\l';
        }
    }
    //    $cmd[] = 'VRULE:'.$GLOBALS['xcenter'].'#888888:'.date("Y/m/d H\\\\:i\\\\:s",$GLOBALS['xcenter']).'\l:dashes';

    $cmd[] = '-s';
    $cmd[] = $begin;
    if ($end != '') {
        $cmd[] = '-e';
        $cmd[] = $end;
    }
    return $cmd;
}

/**
 * Draw stack-graph for set of RRD files
 * @opts Graph options like colors
 * @sources List of array(name, file, ds)
 * @return Commandline to call RRDGraph in order to generate the final graph
 */
function collectd_draw_meta_line(&$opts, &$sources) {
    global $config, $begin, $end;

    if (!isset($opts['title']))
        $opts['title'] = 'Unknown title';
    if (!isset($opts['rrd_opts']))
        $opts['rrd_opts'] = array();
    if (!isset($opts['colors']))
        $opts['colors'] = array();
    if (isset($opts['logarithmic']) && $opts['logarithmic'])
        array_unshift($opts['rrd_opts'], '-o');
    if (isset($opts['zero']) && $opts['zero']) {
        array_unshift($opts['rrd_opts'], '0');
        array_unshift($opts['rrd_opts'], '-l');
        array_unshift($opts['rrd_opts'], '-r');
    }

    $cmd = array('-W', 'PERFWATCHER', '-a', 'PNG', '-w', "".$config['rrd_width'], '-h', "".$config['rrd_height'], '-t', $opts['title']);
    $cmd = array_merge($cmd, $config['rrd_opts'], $opts['rrd_opts']);
    $max_inst_name = 0;

    foreach ($sources as &$inst_data) {
        $inst_name = str_replace('!', '_', $inst_data['name']);
        $file      = $inst_data['file'];
        $ds        = isset($inst_data['ds']) ? $inst_data['ds'] : 'value';
        $reverse   = isset($inst_data['reverse']) && $inst_data['reverse'];

        if (strlen($inst_name) > $max_inst_name)
            $max_inst_name = strlen($inst_name);

        //		$cmd[] = 'DEF:'.$inst_name.'_min='.rrd_escape($file).':'.$ds.':MIN';
        if ($reverse) {
            $cmd[] = 'DEF:'.$inst_name.'_avg='.rrd_escape($file).':'.$ds.':AVERAGE';
            $cmd[] = 'CDEF:rev'.$inst_name.'_avg=0,'.$inst_name.'_avg,-';
        } else {
            $cmd[] = 'DEF:'.$inst_name.'_avg='.rrd_escape($file).':'.$ds.':AVERAGE';
        }
        //		$cmd[] = 'DEF:'.$inst_name.'_max='.rrd_escape($file).':'.$ds.':MAX';
    }

    foreach ($sources as &$inst_data) {
        $inst_name = str_replace('!', '_', $inst_data['name']);
        $legend = sprintf('%s', $inst_name);
        $reverse   = isset($inst_data['reverse']) && $inst_data['reverse'];
        while (strlen($legend) < $max_inst_name)
            $legend .= ' ';
        $number_format = isset($opts['number_format']) ? $opts['number_format'] : '%6.1lf';

        if (isset($opts['colors'][$inst_name]))
            $line_color = new CollectdColor($opts['colors'][$inst_name]);
        else
            $line_color = new CollectdColor('random');

        $cmd[] = 'LINE1:'.($reverse ? 'rev' : '').$inst_name.'_avg#'.$line_color->as_string().':'.$legend;
        if (!(isset($opts['tinylegend']) && $opts['tinylegend'])) {
            $cmd[] = 'GPRINT:'.$inst_name.'_avg:LAST:Last\:'.$number_format.' ';
            $cmd[] = 'GPRINT:'.$inst_name.'_avg:AVERAGE:Average\:'.$number_format.'\l';
        }
    }
    //    $cmd[] = 'VRULE:'.$GLOBALS['xcenter'].'#888888:'.date("Y/m/d H\\\\:i\\\\:s",$GLOBALS['xcenter']).'\l:dashes';

    $cmd[] = '-s';
    $cmd[] = $begin;
    if ($end != '') {
        $cmd[] = '-e';
        $cmd[] = $end;
    }
    return $cmd;
}

?>

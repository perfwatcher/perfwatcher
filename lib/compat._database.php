<?php # vim: set filetype=php fdm=marker sw=4 ts=4 et : 
/*
 * Copyright (C) 2014 Yves Mettier <ymettier AT free fr>
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
function dbcompat_error_not_supported($db, $f,$l,$fc) {
    $db->error("$fc is not supported for your DB (".$db->settings{"dbtype"}.")", "$f/$l/$fc");
}

function dbcompat__remove_items_in_tree_when_they_have_no_parent($db) {
    switch($db->settings{"dbtype"}) {
        case "mysql" :
            $sql = "DELETE X FROM tree X LEFT JOIN tree Y ON X.parent_id = Y.id WHERE Y.id IS NULL AND X.parent_id <> 1";
        break;
        case "pgsql" :
            $sql = "DELETE FROM tree WHERE id IN (SELECT X.id FROM tree X LEFT JOIN tree Y ON X.parent_id = Y.id WHERE Y.id IS NULL AND X.parent_id <> 1);";
        break;
        default : dbcompat_error_not_supported($db,__FILE__,__LINE__,__FUNCTION__);
                  return;
    }
    $db->prepare($sql);
    $db->execute();
}

function dbcompat__reorder_objects_positions($db, $table, $view_id, $ref_id) {
    switch($db->settings{"dbtype"}) {
        case "mysql" :
            $db->query("SET @a=-1");
            $db->prepare("UPDATE $table SET position = @a:=@a+1 WHERE view_id = ? AND parent_id = ? ORDER BY position", array('integer', 'integer'));
            $db->execute(array($view_id, $ref_id));
        break;
        case "pgsql" :
            $db->prepare("UPDATE $table SET position = vtree.p - 1 FROM (SELECT row_number() OVER (ORDER BY position) AS p,id FROM $table WHERE view_id = ? AND parent_id = ?) vtree WHERE vtree.id = tree.id;", array('integer', 'integer'));
            $db->execute(array($view_id, $ref_id));
        break;
        default : dbcompat_error_not_supported($db,__FILE__,__LINE__,__FUNCTION__);
                  return;
    }
}

function dbcompat__copy_with_update($db, $table, $refid, $dstid, $fieldidname, $fields) {
    switch($db->settings{"dbtype"}) {
        case "mysql" :
            $sql_l = array();
            foreach ($fields as $f) {
                $sql_l[] = "x.$f = y.$f";
            }
            $sql = "UPDATE $table x, $table y SET ".implode(", ", $sql_l)." WHERE y.$fieldidname = ? AND  x.$fieldidname = ?";
            $db->prepare($sql, array('integer', 'integer'));
            $db->execute(array((int)$refid, (int)$dstid));
        break;
        case "pgsql" :
            $sql_l = array();
            foreach ($fields as $f) {
                $sql_l[] = "$f = x.$f";
            }
            $sql = "UPDATE $table SET ".implode(", ", $sql_l)." FROM (SELECT ".implode(", ", $fields)." FROM $table WHERE $fieldidname = ?) x WHERE $fieldidname = ?";
            $db->prepare($sql, array('integer', 'integer'));
            $db->execute(array((int)$refid, (int)$dstid));
        break;
        default : dbcompat_error_not_supported($db,__FILE__,__LINE__,__FUNCTION__);
                  return;
    }
}

?>

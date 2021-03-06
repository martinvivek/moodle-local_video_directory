<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * @package    local_video_directory
 * @copyright  2017 Yedidia Klein <yedidia@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('init.php');
defined('MOODLE_INTERNAL') || die();

$id = required_param('video_id', PARAM_INT);

$file = "$converted$id.mp4";


$fp = @fopen($file, 'rb');      
$size   = filesize($file); // File size 
$length = $size;           // Content length
$start  = 0;               // Start byte
$end    = $size - 1;       // End byte  
header('Content-type: video/mp4');
header("Accept-Ranges: 0-$length");
header("Accept-Ranges: bytes"); 
if (isset($_SERVER['HTTP_RANGE'])) {
    $c_start = $start;              
    $c_end   = $end;                
    list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
    if (strpos($range, ',') !== false) {
        header('HTTP/1.1 416 Requested Range Not Satisfiable');
        header("Content-Range: bytes $start-$end/$size");
        header("X-Data: filename $file");
        exit;      
    }              
    if ($range == '-') {            
        $c_start = $size - substr($range, 1);
    }else{         
        $range  = explode('-', $range); 
        $c_start = $range[0];           
        $c_end   = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
    }
    $c_end = ($c_end > $end) ? $end : $c_end;
    if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) { 
        header('HTTP/1.1 416 Requested Range Not Satisfiable');
        header("Content-Range: bytes $start-$end/$size");
        header("X-Data: filename $file");
        exit;      
    }
    $start  = $c_start;             
    $end    = $c_end;               
    $length = $end - $start + 1;    
    fseek($fp, $start);             
    header('HTTP/1.1 206 Partial Content');
}                  
header("Content-Range: bytes $start-$end/$size");
header("Content-Length: ".$length);
$buffer = 1024 * 8;
while(!feof($fp) && ($p = ftell($fp)) <= $end) { 
    if ($p + $buffer > $end) {      
        $buffer = $end - $p + 1;        
    }              
    set_time_limit(0);              
    echo fread($fp, $buffer);       
    ob_flush();    
}
fclose($fp);       
exit();

<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package        CodeIgniter
 * @author        ExpressionEngine Dev Team
 * @copyright    Copyright (c) 2008 - 2014, EllisLab, Inc.
 * @license        http://codeigniter.com/user_guide/license.html
 * @link        http://codeigniter.com
 * @since        Version 1.0
 * @filesource
 */


//return translation
if (!function_exists('translate')) {
    function translate($word)
    {
        return $word;
    }
}

if (!function_exists('recache')) {
    function recache()
    {
        $CI =& get_instance();
        $CI->benchmark->mark_time();
        $files = glob(APPPATH . 'cache/*'); // get all file names
        foreach ($files as $file) { // iterate files
            if (is_file($file) && $file !== '.htaccess' && $file !== 'index.html') {
                unlink($file); // delete file
            }
        }
        //file_get_contents('home/index');
    }
}


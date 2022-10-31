<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class PhpMailerLib
{
    function __construct($config = array())
    {

    }

    public function load()
    {
        require_once("PHPMailer.php");
        $objMail = new \PHPMailer\PHPMailer\PHPMailer;
        return $objMail;
    }
}


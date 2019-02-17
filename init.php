<?php


//define("P4A_APPLICATION_PATH","/");
//define("P4A_DSN","mysql://root:@localhost/condgest");
//define("P4A_DSN_LOG_ALTERACAO_TABELA","mysql://condgest_log:@localhost/condgest");
define("P4A_DSN","mysql://jeform:formje@177.153.7.161/condominioVillaVerde");
define("P4A_AJAX_ENABLED",false);
define("P4A_AUTO_DB_SEQUENCES",false);
define("P4A_EXTENDED_ERRORS",true);
define("P4A_LOCALE","pt_BR");
define('FPDF_FONTPATH','classes/pdf/font/');

require_once("classes/pdf/satecmax_pdf.php");
require_once("classes/phpexcel-1.8.0/PHPExcel.php");
require_once('classes/phpexcel-1.8.0/PHPExcel/IOFactory.php');
require_once("classes/p4a-3.8.4/p4a.php");

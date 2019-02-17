<?php
include(dirname(__FILE__)."/fpdf.php");

class satecmax_pdf extends FPDF
    {
    var $DisplayPreferences='';
	var $javascript;
	var $n_js;
	var $encrypted;          //whether document is protected
	var $Uvalue;             //U entry in pdf document
	var $Ovalue;             //O entry in pdf document
	var $Pvalue;             //P entry in pdf document
	var $enc_obj_id;         //encryption object id
	var $last_rc4_key;       //last RC4 key encrypted (cached for optimisation)
	var $last_rc4_key_c;     //last RC4 computed key
	// bookmark
	
	var $outlines=array();
	var $OutlineRoot;
	
	
	//proteção do pdf
	
	public function __construct($orientation='P',$unit='mm',$format='A4')
		{
		return $this->satecmax_pdf($orientation,$unit,$format);
		}

	public function satecmax_pdf($orientation='P',$unit='mm',$format='A4')
	    {
		parent::FPDF($orientation,$unit,$format);

		$this->encrypted=false;
		$this->last_rc4_key='';
		$this->padding="\x28\xBF\x4E\x5E\x4E\x75\x8A\x41\x64\x00\x4E\x56\xFF\xFA\x01\x08".
						"\x2E\x2E\x00\xB6\xD0\x68\x3E\x80\x2F\x0C\xA9\xFE\x64\x53\x69\x7A";
		
		$this->AddFont("ArialBlack","","arial_black.php");
		$this->AddFont("ArialBlack","U","arial_black.php");
		$this->AddFont("tahoma","","tahoma.php");  // fonte tahoma normal...
		$this->AddFont("tahomabd","","tahomabd.php"); // fonte tahoma negrito
		$this->AddFont("tahomabd","U","tahomabd.php"); // fonte tahoma negrito com underline( sublinhado )
		
	    }

	/**
	* Function to set permissions as well as user and owner passwords
	*
	* - permissions is an array with values taken from the following list:
	*   copy, print, modify, annot-forms
	*   If a value is present it means that the permission is granted
	* - If a user password is set, user will be prompted before document is opened
	* - If an owner password is set, document can be opened in privilege mode with no
	*   restriction if that password is entered
	*/
	function SetProtection($permissions=array(),$user_pass='',$owner_pass=null)
	    {
		$options = array('print' => 4, 'modify' => 8, 'copy' => 16, 'annot-forms' => 32 );
		$protection = 192;
		foreach($permissions as $permission)
		    {
			if (!isset($options[$permission]))
				$this->Error('Incorrect permission: '.$permission);
			$protection += $options[$permission];
		    }
		    
		if ($owner_pass === null)
			$owner_pass = uniqid(rand());
			
		$this->encrypted = true;
		$this->_generateencryptionkey($user_pass, $owner_pass, $protection);
    	}

	/****************************************************************************
	*                                                                           *
	*                              Private methods                              *
	*                                                                           *
	****************************************************************************/

	function _putstream($s)
    	{
		if ($this->encrypted) 
		    {
			$s = $this->_RC4($this->_objectkey($this->n), $s);
		    }
		parent::_putstream($s);
    	}

	function _textstring($s)
	    {
		if ($this->encrypted) {
			$s = $this->_RC4($this->_objectkey($this->n), $s);
		}
		return parent::_textstring($s);
	    }

	/**
	* Compute key depending on object number where the encrypted data is stored
	*/
	function _objectkey($n)
	    {
		return substr($this->_md5_16($this->encryption_key.pack('VXxx',$n)),0,10);
	    }

	/**
	* Escape special characters
	*/
	function _escape($s)
	    {
		$s=str_replace('\\','\\\\',$s);
		$s=str_replace(')','\\)',$s);
		$s=str_replace('(','\\(',$s);
		$s=str_replace("\r",'\\r',$s);
		return $s;
	    }

	function _putresources()
	    {
		parent::_putresources();
		if ($this->encrypted) 
		    {
			$this->_newobj();
			$this->enc_obj_id = $this->n;
			$this->_out('<<');
			$this->_putencryption();
			$this->_out('>>');
			$this->_out('endobj');
		    }
		    
		if (!empty($this->javascript)) 
		    {
			$this->_putjavascript();
		    }
		$this->_putbookmarks();		    
	    }

	function _putencryption()
	    {
		$this->_out('/Filter /Standard');
		$this->_out('/V 1');
		$this->_out('/R 2');
		$this->_out('/O ('.$this->_escape($this->Ovalue).')');
		$this->_out('/U ('.$this->_escape($this->Uvalue).')');
		$this->_out('/P '.$this->Pvalue);
	    }

	function _puttrailer()
	    {
		parent::_puttrailer();
		if ($this->encrypted) 
		    {
			$this->_out('/Encrypt '.$this->enc_obj_id.' 0 R');
			$this->_out('/ID [()()]');
		    }
	    }

	/**
	* RC4 is the standard encryption algorithm used in PDF format
	*/
	function _RC4($key, $text)
	    {
		if ($this->last_rc4_key != $key) 
		    {
			$k = str_repeat($key, 256/strlen($key)+1);
			$rc4 = range(0,255);
			$j = 0;
			for ($i=0; $i<256; $i++)
			    {
				$t = $rc4[$i];
				$j = ($j + $t + ord($k{$i})) % 256;
				$rc4[$i] = $rc4[$j];
				$rc4[$j] = $t;
			    }
			$this->last_rc4_key = $key;
			$this->last_rc4_key_c = $rc4;
		    }
        else 
            {
			$rc4 = $this->last_rc4_key_c;
		    }
		    
		$len = strlen($text);
		$a = 0;
		$b = 0;
		$out = '';
		for ($i=0; $i<$len; $i++)
		    {
			$a = ($a+1)%256;
			$t= $rc4[$a];
			$b = ($b+$t)%256;
			$rc4[$a] = $rc4[$b];
			$rc4[$b] = $t;
			$k = $rc4[($rc4[$a]+$rc4[$b])%256];
			$out.=chr(ord($text{$i}) ^ $k);
		    }

		return $out;
	    }

	/**
	* Get MD5 as binary string
	*/
	function _md5_16($string)
	    {
		return pack('H*',md5($string));
	    }

	/**
	* Compute O value
	*/
	function _Ovalue($user_pass, $owner_pass)
	    {
		$tmp = $this->_md5_16($owner_pass);
		$owner_RC4_key = substr($tmp,0,5);
		return $this->_RC4($owner_RC4_key, $user_pass);
	    }

	/**
	* Compute U value
	*/
	function _Uvalue()
	    {
		return $this->_RC4($this->encryption_key, $this->padding);
	    }

	/**
	* Compute encryption key
	*/
	function _generateencryptionkey($user_pass, $owner_pass, $protection)
	    {
		// Pad passwords
		$user_pass = substr($user_pass.$this->padding,0,32);
		$owner_pass = substr($owner_pass.$this->padding,0,32);
		// Compute O value
		$this->Ovalue = $this->_Ovalue($user_pass,$owner_pass);
		// Compute encyption key
		$tmp = $this->_md5_16($user_pass.$this->Ovalue.chr($protection)."\xFF\xFF\xFF");
		$this->encryption_key = substr($tmp,0,5);
		// Compute U value
		$this->Uvalue = $this->_Uvalue();
		// Compute P value
		$this->Pvalue = -(($protection^255)+1);
	    }
	
	//impressao do codigo de barras no PDF
	function Code39($x, $y, $code, $ext = true, $cks = false, $w = 0.4, $h = 20, $wide = true) 
	    {
		//Display code
		$this->SetFont('Arial', '', 10);
	//	$this->Text($x, $y+$h+4, $code);
	
		if($ext)
			{
			//Extended encoding
			$code = $this->encode_code39_ext($code);
			}
		else
	    	{
			//Convert to upper case
			$code = strtoupper($code);
			//Check validity
			if(!preg_match('|^[0-9A-Z. $/+%-]*$|', $code))
				$this->Error('Invalid barcode value: '.$code);
		    }
	
		//Compute checksum
		if ($cks)
			$code .= $this->checksum_code39($code);
	
		//Add start and stop characters
		$code = '*'.$code.'*';
	
		//Conversion tables
		$narrow_encoding = array (
			'0' => '101001101101', '1' => '110100101011', '2' => '101100101011',
			'3' => '110110010101', '4' => '101001101011', '5' => '110100110101',
			'6' => '101100110101', '7' => '101001011011', '8' => '110100101101',
			'9' => '101100101101', 'A' => '110101001011', 'B' => '101101001011',
			'C' => '110110100101', 'D' => '101011001011', 'E' => '110101100101',
			'F' => '101101100101', 'G' => '101010011011', 'H' => '110101001101',
			'I' => '101101001101', 'J' => '101011001101', 'K' => '110101010011',
			'L' => '101101010011', 'M' => '110110101001', 'N' => '101011010011',
			'O' => '110101101001', 'P' => '101101101001', 'Q' => '101010110011',
			'R' => '110101011001', 'S' => '101101011001', 'T' => '101011011001',
			'U' => '110010101011', 'V' => '100110101011', 'W' => '110011010101',
			'X' => '100101101011', 'Y' => '110010110101', 'Z' => '100110110101',
			'-' => '100101011011', '.' => '110010101101', ' ' => '100110101101',
			'*' => '100101101101', '$' => '100100100101', '/' => '100100101001',
			'+' => '100101001001', '%' => '101001001001' );
	
		$wide_encoding = array (
			'0' => '101000111011101', '1' => '111010001010111', '2' => '101110001010111',
			'3' => '111011100010101', '4' => '101000111010111', '5' => '111010001110101',
			'6' => '101110001110101', '7' => '101000101110111', '8' => '111010001011101',
			'9' => '101110001011101', 'A' => '111010100010111', 'B' => '101110100010111',
			'C' => '111011101000101', 'D' => '101011100010111', 'E' => '111010111000101',
			'F' => '101110111000101', 'G' => '101010001110111', 'H' => '111010100011101',
			'I' => '101110100011101', 'J' => '101011100011101', 'K' => '111010101000111',
			'L' => '101110101000111', 'M' => '111011101010001', 'N' => '101011101000111',
			'O' => '111010111010001', 'P' => '101110111010001', 'Q' => '101010111000111',
			'R' => '111010101110001', 'S' => '101110101110001', 'T' => '101011101110001',
			'U' => '111000101010111', 'V' => '100011101010111', 'W' => '111000111010101',
			'X' => '100010111010111', 'Y' => '111000101110101', 'Z' => '100011101110101',
			'-' => '100010101110111', '.' => '111000101011101', ' ' => '100011101011101',
			'*' => '100010111011101', '$' => '100010001000101', '/' => '100010001010001',
			'+' => '100010100010001', '%' => '101000100010001');
	
		$encoding = $wide ? $wide_encoding : $narrow_encoding;
	
		//Inter-character spacing
		$gap = ($w > 0.29) ? '00' : '0';
	
		//Convert to bars
		$encode = '';
		for ($i = 0; $i< strlen($code); $i++)
			$encode .= $encoding[$code{$i}].$gap;
	
		//Draw bars
		$this->draw_code39($encode, $x, $y, $w, $h);
	    }
	
	function checksum_code39($code) 
	    {
	
		//Compute the modulo 43 checksum
	
		$chars = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
								'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K',
								'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V',
								'W', 'X', 'Y', 'Z', '-', '.', ' ', '$', '/', '+', '%');
		$sum = 0;
		
		for ($i=0 ; $i<strlen($code); $i++) 
		    {
			$a = array_keys($chars, $code{$i});
			$sum += $a[0];
		    }
		    
		$r = $sum % 43;
		return $chars[$r];
	    }
	
	function encode_code39_ext($code) 
	    {
	
		//Encode characters in extended mode
	
		$encode = array(
			chr(0) => '%U', chr(1) => '$A', chr(2) => '$B', chr(3) => '$C',
			chr(4) => '$D', chr(5) => '$E', chr(6) => '$F', chr(7) => '$G',
			chr(8) => '$H', chr(9) => '$I', chr(10) => '$J', chr(11) => '�K',
			chr(12) => '$L', chr(13) => '$M', chr(14) => '$N', chr(15) => '$O',
			chr(16) => '$P', chr(17) => '$Q', chr(18) => '$R', chr(19) => '$S',
			chr(20) => '$T', chr(21) => '$U', chr(22) => '$V', chr(23) => '$W',
			chr(24) => '$X', chr(25) => '$Y', chr(26) => '$Z', chr(27) => '%A',
			chr(28) => '%B', chr(29) => '%C', chr(30) => '%D', chr(31) => '%E',
			chr(32) => ' ', chr(33) => '/A', chr(34) => '/B', chr(35) => '/C',
			chr(36) => '/D', chr(37) => '/E', chr(38) => '/F', chr(39) => '/G',
			chr(40) => '/H', chr(41) => '/I', chr(42) => '/J', chr(43) => '/K',
			chr(44) => '/L', chr(45) => '-', chr(46) => '.', chr(47) => '/O',
			chr(48) => '0', chr(49) => '1', chr(50) => '2', chr(51) => '3',
			chr(52) => '4', chr(53) => '5', chr(54) => '6', chr(55) => '7',
			chr(56) => '8', chr(57) => '9', chr(58) => '/Z', chr(59) => '%F',
			chr(60) => '%G', chr(61) => '%H', chr(62) => '%I', chr(63) => '%J',
			chr(64) => '%V', chr(65) => 'A', chr(66) => 'B', chr(67) => 'C',
			chr(68) => 'D', chr(69) => 'E', chr(70) => 'F', chr(71) => 'G',
			chr(72) => 'H', chr(73) => 'I', chr(74) => 'J', chr(75) => 'K',
			chr(76) => 'L', chr(77) => 'M', chr(78) => 'N', chr(79) => 'O',
			chr(80) => 'P', chr(81) => 'Q', chr(82) => 'R', chr(83) => 'S',
			chr(84) => 'T', chr(85) => 'U', chr(86) => 'V', chr(87) => 'W',
			chr(88) => 'X', chr(89) => 'Y', chr(90) => 'Z', chr(91) => '%K',
			chr(92) => '%L', chr(93) => '%M', chr(94) => '%N', chr(95) => '%O',
			chr(96) => '%W', chr(97) => '+A', chr(98) => '+B', chr(99) => '+C',
			chr(100) => '+D', chr(101) => '+E', chr(102) => '+F', chr(103) => '+G',
			chr(104) => '+H', chr(105) => '+I', chr(106) => '+J', chr(107) => '+K',
			chr(108) => '+L', chr(109) => '+M', chr(110) => '+N', chr(111) => '+O',
			chr(112) => '+P', chr(113) => '+Q', chr(114) => '+R', chr(115) => '+S',
			chr(116) => '+T', chr(117) => '+U', chr(118) => '+V', chr(119) => '+W',
			chr(120) => '+X', chr(121) => '+Y', chr(122) => '+Z', chr(123) => '%P',
			chr(124) => '%Q', chr(125) => '%R', chr(126) => '%S', chr(127) => '%T');
	
		$code_ext = '';
		for ($i = 0 ; $i<strlen($code); $i++) 
		    {
			if (ord($code{$i}) > 127)
				$this->Error('Invalid character: '.$code{$i});
			$code_ext .= $encode[$code{$i}];
		    }
		return $code_ext;
	    }
	
	function draw_code39($code, $x, $y, $w, $h)
	    {
	
		//Draw bars
	
		for($i=0; $i<strlen($code); $i++)
		    {
			if($code{$i} == '1')
				$this->Rect($x+$i*$w, $y, $w, $h, 'F');
		    }
	    }
	
	// metodo para sumir com os controles do Reader...
	function DisplayPreferences($preferences) 
	    {
	    $this->DisplayPreferences.=$preferences;
	    }
	
	function _putcatalog()
	    {
	    parent::_putcatalog();
	    
	    if(is_int(strpos($this->DisplayPreferences,'FullScreen')))
	        $this->_out('/PageMode /FullScreen');
	    if($this->DisplayPreferences) 
	        {
	        $this->_out('/ViewerPreferences<<');
	        if(is_int(strpos($this->DisplayPreferences,'HideMenubar')))
	            $this->_out('/HideMenubar true');
	        if(is_int(strpos($this->DisplayPreferences,'HideToolbar')))
	            $this->_out('/HideToolbar true');
	        if(is_int(strpos($this->DisplayPreferences,'HideWindowUI')))
	            $this->_out('/HideWindowUI true');
	        if(is_int(strpos($this->DisplayPreferences,'DisplayDocTitle')))
	            $this->_out('/DisplayDocTitle true');
	        if(is_int(strpos($this->DisplayPreferences,'CenterWindow')))
	            $this->_out('/CenterWindow true');
	        if(is_int(strpos($this->DisplayPreferences,'FitWindow')))
	            $this->_out('/FitWindow true');
	        $this->_out('>>');
	        }
	        
		if (isset($this->javascript)) 
		    {
			$this->_out('/Names <</JavaScript '.($this->n_js).' 0 R>>');
		    }
		    
	    if(count($this->outlines)>0)
		    {
	        $this->_out('/Outlines '.$this->OutlineRoot.' 0 R');
	        $this->_out('/PageMode /UseOutlines');
		    }
	        
	    }
    // Javascript no PDF -- Impressao direta...

	function IncludeJS($script) 
	    {
		$this->javascript=$script;
    	}

	function _putjavascript()
	    {
		$this->_newobj();
		$this->n_js=$this->n;
		$this->_out('<<');
		$this->_out('/Names [(EmbeddedJS) '.($this->n+1).' 0 R ]');
		$this->_out('>>');
		$this->_out('endobj');
		$this->_newobj();
		$this->_out('<<');
		$this->_out('/S /JavaScript');
		$this->_out('/JS '.$this->_textstring($this->javascript));
		$this->_out('>>');
		$this->_out('endobj');
	    }

/*
	    function _putresources() 
	    {
		parent::_putresources();
		if (!empty($this->javascript)) 
		    {
			$this->_putjavascript();
		    }
	    }
*/
//	function _putcatalog() 
//	    {
//		parent::_putcatalog();
//	    }

	function AutoPrint($dialog=false)
	    {
		//Launch the print dialog or start printing immediately on the standard printer
		$param=($dialog ? 'true' : 'false');
		//$script="print($param);";
        $script = "	var pp = this.getPrintParams();
					pp.pageHandling = pp.constants.handling.shrink;
				";
        if ( !$dialog)
	        {
	        $script.="\n pp.interactive = pp.constants.interactionLevel.automatic;";    
	        }
        $script.="\n this.print(pp); ";
        		
		$this->IncludeJS($script);
	    }
	
	function AutoPrintToPrinter($server, $printer, $dialog=false)
	    {
		//Print on a shared printer (requires at least Acrobat 6)
		$script = "var pp = getPrintParams();";
		if($dialog)
			$script .= "pp.interactive = pp.constants.interactionLevel.full;";
		else
			$script .= "pp.interactive = pp.constants.interactionLevel.automatic;";
		$script .= "pp.printerName = '\\\\\\\\".$server."\\\\".$printer."';";
		$script .= "print(pp);";
		$this->IncludeJS($script);
	    }

	function Bookmark($txt,$level=0,$y=0)
		{
		    if($y==-1)
		        $y=$this->GetY();
		    $this->outlines[]=array('t'=>$txt,'l'=>$level,'y'=>$y,'p'=>$this->PageNo());
		}
		
	function _putbookmarks()
		{
		    $nb=count($this->outlines);
		    if($nb==0)
		        return;
		    $lru=array();
		    $level=0;
		    foreach($this->outlines as $i=>$o)
		    {
		        if($o['l']>0)
		        {
		            $parent=$lru[$o['l']-1];
		            //Set parent and last pointers
		            $this->outlines[$i]['parent']=$parent;
		            $this->outlines[$parent]['last']=$i;
		            if($o['l']>$level)
		            {
		                //Level increasing: set first pointer
		                $this->outlines[$parent]['first']=$i;
		            }
		        }
		        else
		            $this->outlines[$i]['parent']=$nb;
		        if($o['l']<=$level and $i>0)
		        {
		            //Set prev and next pointers
		            $prev=$lru[$o['l']];
		            $this->outlines[$prev]['next']=$i;
		            $this->outlines[$i]['prev']=$prev;
		        }
		        $lru[$o['l']]=$i;
		        $level=$o['l'];
		    }
		    //Outline items
		    $n=$this->n+1;
		    foreach($this->outlines as $i=>$o)
		    {
		        $this->_newobj();
		        $this->_out('<</Title '.$this->_textstring($o['t']));
		        $this->_out('/Parent '.($n+$o['parent']).' 0 R');
		        if(isset($o['prev']))
		            $this->_out('/Prev '.($n+$o['prev']).' 0 R');
		        if(isset($o['next']))
		            $this->_out('/Next '.($n+$o['next']).' 0 R');
		        if(isset($o['first']))
		            $this->_out('/First '.($n+$o['first']).' 0 R');
		        if(isset($o['last']))
		            $this->_out('/Last '.($n+$o['last']).' 0 R');
		        $this->_out(sprintf('/Dest [%d 0 R /XYZ 0 %.2f null]',1+2*$o['p'],($this->h-$o['y'])*$this->k));
		        $this->_out('/Count 0>>');
		        $this->_out('endobj');
		    }
		    //Outline root
		    $this->_newobj();
		    $this->OutlineRoot=$this->n;
		    $this->_out('<</Type /Outlines /First '.$n.' 0 R');
		    $this->_out('/Last '.($n+$lru[0]).' 0 R>>');
		    $this->_out('endobj');
		}	    
	    
	function cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
		{
		$font = $this->FontFamily;
		$stilo = $this->FontStyle;
		$tamanho = $this->FontSize;
		if (stripos(trim($txt),"<b>")!==false )
			{
			
			$txt = str_replace(array("<b>","</b>","<B>","</B>"),"",$txt);
			
			if ( $font == "tahoma")
				{
				$this->setFont("tahomabd");
				}
			else
				{
				$this->SetFont('',"B");
				}
			
			}
		parent::Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);
		$this->SetFont('');
		}
		
    function drawTextBox($strText, $w, $h, $align='L', $valign='T', $border=1)
{
    $xi=$this->GetX();
    $yi=$this->GetY();
    
    $hrow=$this->FontSize;
    $textrows=$this->drawRows($w,$hrow,$strText,0,$align,0,0,0);
    $maxrows=floor($h/$this->FontSize);
    $rows=min($textrows,$maxrows);

    $dy=0;
    if (strtoupper($valign)=='M')
        $dy=($h-$rows*$this->FontSize)/2;
    if (strtoupper($valign)=='B')
        $dy=$h-$rows*$this->FontSize;

    $this->SetY($yi+$dy);
    $this->SetX($xi);

    $this->drawRows($w,$hrow,$strText,0,$align,0,$rows,1);

    if ($border==1)
        $this->Rect($xi,$yi,$w,$h);
}

function drawRows($w,$h,$txt,$border=0,$align='J',$fill=0,$maxline=0,$prn=0)
{
    $cw=&$this->CurrentFont['cw'];
    if($w==0)
        $w=$this->w-$this->rMargin-$this->x;
    $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
    $s=str_replace("\r",'',$txt);
    $nb=strlen($s);
    if($nb>0 and $s[$nb-1]=="\n")
        $nb--;
    $b=0;
    if($border)
    {
        if($border==1)
        {
            $border='LTRB';
            $b='LRT';
            $b2='LR';
        }
        else
        {
            $b2='';
            if(is_int(strpos($border,'L')))
                $b2.='L';
            if(is_int(strpos($border,'R')))
                $b2.='R';
            $b=is_int(strpos($border,'T')) ? $b2.'T' : $b2;
        }
    }
    $sep=-1;
    $i=0;
    $j=0;
    $l=0;
    $ns=0;
    $nl=1;
    while($i<$nb)
    {
        //Get next character
        $c=$s[$i];
        if($c=="\n")
        {
            //Explicit line break
            if($this->ws>0)
            {
                $this->ws=0;
                if ($prn==1) $this->_out('0 Tw');
            }
            if ($prn==1) {
                $this->Cell($w,$h,substr($s,$j,$i-$j),$b+1,2,$align,$fill);
            }
            $i++;
            $sep=-1;
            $j=$i;
            $l=0;
            $ns=0;
            $nl++;
            if($border and $nl==2)
                $b=$b2;
            if ( $maxline && $nl > $maxline )
                return substr($s,$i);
            continue;
        }
        if($c==' ')
        {
            $sep=$i;
            $ls=$l;
            $ns++;
        }
        $l+=$cw[$c];
        if($l>$wmax)
        {
            //Automatic line break
            if($sep==-1)
            {
                if($i==$j)
                    $i++;
                if($this->ws>0)
                {
                    $this->ws=0;
                    if ($prn==1) $this->_out('0 Tw');
                }
                if ($prn==1) {
                    $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
                }
            }
            else
            {
                if($align=='J')
                {
                    $this->ws=($ns>1) ? ($wmax-$ls)/1000*$this->FontSize/($ns-1) : 0;
                    if ($prn==1) $this->_out(sprintf('%.3f Tw',$this->ws*$this->k));
                }
                if ($prn==1){
                    $this->Cell($w,$h,substr($s,$j,$sep-$j),$b,2,$align,$fill);
                }
                $i=$sep+1;
            }
            $sep=-1;
            $j=$i;
            $l=0;
            $ns=0;
            $nl++;
            if($border and $nl==2)
                $b=$b2;
            if ( $maxline && $nl > $maxline )
                return substr($s,$i);
        }
        else
            $i++;
    }
    //Last chunk
    if($this->ws>0)
    {
        $this->ws=0;
        if ($prn==1) $this->_out('0 Tw');
    }
    if($border and is_int(strpos($border,'B')))
        $b.='B';
    if ($prn==1) {
        $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
    }
    $this->x=$this->lMargin;
    return $nl;
}	
function DashedRect($x1, $y1, $x2, $y2, $width=1, $nb=15)
    {
        $this->SetLineWidth($width);
        $longueur=abs($x1-$x2);
        $hauteur=abs($y1-$y2);
        if($longueur>$hauteur) {
            //$Pointilles=($longueur/$nb)/2; // length of dashes
            $Pointilles=$longueur; // length of dashes
        }
        else {
            //$Pointilles=($hauteur/$nb)/2;
            $Pointilles=$hauteur;
        }
        for($i=$x1;$i<=$x2;$i+=$Pointilles+$Pointilles) {
            for($j=$i;$j<=($i+$Pointilles);$j++) {
                if($j<=($x2-1)) {
                    $this->Line($j,$y1,$j+1,$y1); // upper dashes
                    $this->Line($j,$y2,$j+1,$y2); // lower dashes
                }
            }
        }
        for($i=$y1;$i<=$y2;$i+=$Pointilles+$Pointilles) {
            for($j=$i;$j<=($i+$Pointilles);$j++) {
                if($j<=($y2-1)) {
                    $this->Line($x1,$j,$x1,$j+1); // left dashes
                    $this->Line($x2,$j,$x2,$j+1); // right dashes
                }
            }
        }
    }

		
    }
?>
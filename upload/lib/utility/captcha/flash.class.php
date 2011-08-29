<?php
/**
 * 
 * @author pw team, Nov 14, 2010
 * @copyright 2003-2010 phpwind.net. All rights reserved.
 * @version 
 * @package default
 */

! defined ( 'P_W' ) && exit ( 'Forbidden' );

class PW_Flash extends PW_FlashComplier{
	var $codes;
	var $frameWidth;
	var $frameHeight;
	var $imageWidth;
	var $imageHeight;
	
	function PW_Flash($width,$height){
		$this->frameWidth = $width;
		$this->frameHeight = $height;
        $this->setFrameSize($width*20, $height*20);
		$this->setSwfVersion(7);
	}

	function display(){
		header("Content-Type: application/x-shockwave-flash");
		$this->beginMovie();
		$num = strlen($this->codes);
		if($num > 0){
			$this->imageWidth = floor($this->frameWidth / $num);
			$this->imageHeight = $this->frameHeight;
	        $X1 = $Y1 = 0;
	        $X2 = $this->imageWidth*20;
	        $Y2 = $this->imageHeight*20;
	        $imgpath = $GLOBALS['imgdir'].'/ck/flash/';
			for($i = 0; $i < strlen($this->codes); $i++){
				$file = $imgpath.strtolower($this->codes[$i]).'.jpg';
				$image = $this->defineBitmapJPEG($file);
	        	$CharacterInfo = $this->defineRectangleBitmap($X1, $Y1, $X2, $Y2, $this->imageWidth, false, false, 0, 0, 0, 0, 'c', $image, true, null);
		        $characterdepth = $this->easyPlaceObject($CharacterInfo['charid']);
				$X1 += $this->imageWidth*20;
				$X2 += $this->imageWidth*20;
			}
			$this->endFrame();
			S::filter();
		}
		$this->endMovie();
		print $this->getMovie();exit;
	}
	
	function setSwfVersion($version){
		array_push($this->pushpop, "setSwfVersion");
		($version < $this->swfversionlower) && ($version > $this->swfversionupper) ? exit : $this->swfversion = (int) $version;
		array_pop($this->pushpop);
		return $this->swfversion;
	} 

	function setFrameSize($Xmax, $Ymax){
		array_push($this->pushpop, "setFrameSize");
		$this->framesize["Xmin"] = 0;
		$this->framesize["Xmax"] = (int) $Xmax;
		$this->framesize["Ymin"] = 0;
		$this->framesize["Ymax"] = (int) $Ymax;
		array_pop($this->pushpop);
		return $this->framesize;
	} 

	function beginMovie(){
		array_push($this->pushpop, "beginMovie");
		$this->packSetbackgroundTag((int)$this->backgroundcolor["R"], (int)$this->backgroundcolor["G"], (int)$this->backgroundcolor["B"]);
		array_pop($this->pushpop);
	}

	function getFrameSize(){
		array_push($this->pushpop, "getFrameSize");
		array_pop($this->pushpop);
		return $this->framesize;
	} 
	
	function defineBitmapJPEG($file){
		array_push($this->pushpop, "defineBitmapJPEG");  //push the name of this function onto the debug stack.
		++$this->charid;  //  increment the Character ID counter.
		$this->charid > $this->charidlimit && exit;  	//  check character ID limit.
		$bitmapjpeg = $this->parseJPEGfile($file);
		$this->packDefineBitsJPEG2Tag($this->charid, $bitmapjpeg["JPEGEncoding"], $bitmapjpeg["JPEGImage"]);
		$this->bitmaps[$this->charid] = array("width" => $bitmapjpeg["width"], "height" => $bitmapjpeg["height"]);
		array_pop($this->pushpop);  //  pop the name of this function off the debug stack
		return $this->charid; //  return the charid of this bitmap.
	}

	function defineRectangleBitmap($X1, $Y1, $X2, $Y2, $width, $alphaflag, $edgeflag, $R, $G, $B, $A, $bitmaptype, $bitmapid, $autofitflag, $bitmapmatrix){
		array_push($this->pushpop, "defineRectangleBitmap");  // push the name of this function onto the debug stack
		++$this->charid;
		$this->charid > $this->charidlimit && exit;  //  check character ID limit.
		array_key_exists("Alpha", $this->bitmaps[$bitmapid]) && $alphaflag = true;   //  check if the bitmap has alpha channel information
		if ($edgeflag) {  	//  define line styles (just one in this case).
			$linestyle = $this->packLineStyle($width, $R, $G, $B, $alphaflag, $A);
			$linestyleArray = $this->packLineStyleAray(1, $linestyle);
			$nlinebits = 1;
			$linestyleIndex = 1;
		} else {  	//  define line styles (none in this case).
			$linestyle = "";
			$linestyleArray = $this->packLineStyleAray(0, $linestyle);
			$nlinebits = 0;
			$linestyleIndex = 0;
		}
		$Y1 = $this->framesize["Ymax"] - $Y1;     //  translate coordinates.
		$Y2 = $this->framesize["Ymax"] - $Y2;
		$stylechangerecord = $this->packStyleChangeRecord(0, $edgeflag, 1, 0, 1, $X1, $Y1, 1, $nlinebits, 0, 1, $linestyleIndex, "", "", 0, 0);			//  select line and fill style, move pen to X1, Y1.
		$deltaX = $X2 - $X1;   	//  compute deltas.
		$deltaY = $Y2 - $Y1;
		$edgerecords  = $this->packStraightRecord(0, 1, 0, $deltaY);  //  add vertical and horizontal straight edges.
		$edgerecords .= $this->packStraightRecord(0, 0, $deltaX, 0);
		$edgerecords .= $this->packStraightRecord(0, 1, 0, -$deltaY);
		$edgerecords .= $this->packStraightRecord(0, 0, -$deltaX, 0);
		$endshape = $this->packEndSharpRecord();   //  mark the end of the shape.
		$margin = round($width / 2);   	//  compute shape bounds.
		$X_min = min($X1, $X2) - $margin;
		$X_max = max($X1, $X2) + $margin;
		$Y_min = min($Y1, $Y2) - $margin;
		$Y_max = max($Y1, $Y2) + $margin;
		$shaperecords = $this->packBitValues($stylechangerecord["Bitstream"] . $edgerecords . $endshape);   	//  pack shape records.
		$autofitflag && $bitmapmatrix = $this->packMATRIX(true, ($X_max - $X_min) / $this->bitmaps[$bitmapid]["width"], ($Y_max - $Y_min) / $this->bitmaps[$bitmapid]["height"], false, 0, 0, $X_min, $Y_min);   //check if the bitmap is to be fitted automatically. 
		($bitmaptype == "c") ? $btype = 0x41 : ($bitmaptype == "t")? $btype = 0x40 : exit;
		$FillStyle = $this->packFillStyle($btype, "", "", "", $alphaflag, "", "", "", $bitmapid, $bitmapmatrix);  //  define fill styles (just one in this case).
		$fillstylearray = $this->packStyleArray(1, $FillStyle); 
		$ShapeWithStyle = $this->packShapeWithStyle($fillstylearray, $linestyleArray, 1, $nlinebits, $shaperecords);
		$alphaflag ? $this->packDefineShape3Tag($this->charid, $this->packRECT($X_min, $X_max, $Y_min, $Y_max), $ShapeWithStyle) : $this->packDefineShapeTag($this->charid, $this->packRECT($X_min, $X_max, $Y_min, $Y_max), $ShapeWithStyle);;    //  test if AlphaFlag is set and use the appropriate shape tag.
		$CharacterInfo = array("charid" => $this->charid, "X_min" => $X_min, "X_max" => $X_max, "Y_min" => $Y_min, "Y_max" => $Y_max);  	//  create the CharacterInfo array for this shape.
		array_pop($this->pushpop);   //  pop the name of this function off the debug stack
		return $CharacterInfo;  //  return the CharacterInfo array for this shape.
	}
	
	function easyPlaceObject($charid){
		array_push($this->pushpop, "easyPlaceObject");
		$characterdepth = $this->nextLayer();
		$characterdepth > $this->layerlimit && exit;  //  check layer depth.
		$this->packplaceObjectTag($charid, $characterdepth, $this->defineMATRIX(false, null, null, false, null, null, 0, 0), "");
		array_pop($this->pushpop);
		return $characterdepth;
	}
	
	function defineMATRIX($hasscale, $scaleX, $scaleY, $hasrotate, $rotateskew0, $rotateskew1, $translateX, $translateY)  {
		array_push($this->pushpop, "defineMATRIX");   	//  push the name of this function onto the debug stack
		$matrix = $this->packMATRIX($hasscale, $scaleX, $scaleY, $hasrotate, $rotateskew0, $rotateskew1, $translateX, -$translateY); 
		array_pop($this->pushpop);  	//  pop the name of this function off the debug stack
		return $matrix;  //  return the SWF matrix atom string.
	}

	function nextLayer(){
		array_push($this->pushpop, "nextLayer");
		$this->characterdepth++;
		$this->characterdepth > $this->layerlimit && exit;
		array_pop($this->pushpop);
		return $this->characterdepth;
	}

	function getMovie(){
		array_push($this->pushpop, "getMovie");
		array_pop($this->pushpop);
		return $this->flashdata;
	} 

	function endMovie(){
		array_push($this->pushpop, "endMovie");
		$this->packEndTag();
		$this->packMacromediaFlashSWFHeader();
		array_pop($this->pushpop);
	}
	
	function endFrame(){
		array_push($this->pushpop, "endFrame");
		$this->packShowFrameTag();
		$this->framecount += 1;
		$this->framecount > $this->framenumlimit && exit;  //  The real limit is 65535 frames, but older versions of Flash Player only display the first 16000 frames.
		array_pop($this->pushpop);
		return $this->framecount;
	} 
}


class PW_FlashComplier{
	
	var $setmode = true;
	var $bitmaps = array();
	var $swfversion = 7;	
	var $swfversionlower = 1;
	var $swfversionupper = 10;
	var $framesize = array("Xmin" => 0, "Xmax" => 11000, "Ymin" => 0, "Ymax" => 8000);
	var $framerate = 24;
	var $backgroundcolor = array("R" => 255, "G" => 255, "B" => 255);
	var $flashdata = "";
	var $framecount = 0;
	var $characterdepth = 0;
	var $pushpop = array();
	var $charidlimit = 65535;
	var $framenumlimit = 16000;
	var $charid = 0;
	var $layerlimit = 16000;

	function autoSwfVersion($version){	
		array_push($this->pushpop, "autoSwfVersion");
		if (!empty($swfversion) && $this->swfversion < $version) {
		$this->swfversion = (int) $version;
		}
		array_pop($this->pushpop);
	} 

	function packShowFrameTag(){
		array_push($this->pushpop, "packShowFrameTag");
		$tagid = 1;
		$taglength = 0;
		$this->autoSwfVersion(1);
		$this->flashdata .= $this->packRecordHeader($tagid, $taglength);
		array_pop($this->pushpop);
	}
	
	function packSetbackgroundTag($R, $G, $B){
		array_push($this->pushpop, "packSetbackgroundTag");
		$tagid = 9;
		$RGB = $this->packRGB($R, $G, $B);
		$taglength = strlen($RGB);
		$this->autoSwfVersion(1);
		$this->flashdata .= $this->packRecordHeader($tagid, $taglength) . $RGB;
		array_pop($this->pushpop);
	}

	function packRGB($R, $G, $B){
		array_push($this->pushpop, "packRGB");
		$atom  = $this->packUI8($R);
		$atom .= $this->packUI8($G);
		$atom .= $this->packUI8($B);
		array_pop($this->pushpop);
		return $atom;
	}

	function packUI8($number){
		array_push($this->pushpop, "packUI8");
		!(is_numeric($number)) && exit;
		$number = (int) $number;
		$lower_limit = 0;
		$upper_limit = 255;
		$number < $lower_limit && $number = $lower_limit;
		$number > $upper_limit && $number = $upper_limit;
		$atom = chr($number);
		array_pop($this->pushpop);
		return $atom;
	}

	function packUI16($number){
		array_push($this->pushpop, "packUI16");
		!(is_numeric($number)) && exit;
        $number = (int) $number;
		$lower_limit = 0;
		$upper_limit = 65535;
		$number < $lower_limit && $number = $lower_limit;
		$number > $upper_limit && $number = $upper_limit;
		$number = sprintf("%04x", $number);
		$low_byte  = base_convert(substr($number, 2, 2), 16, 10);
		$high_byte = base_convert(substr($number, 0, 2), 16, 10);
		$atom  = chr($low_byte) . chr($high_byte);
		array_pop($this->pushpop);
		return $atom;
	}

	function packSI8($number){
		array_push($this->pushpop, "packSI8");
		!(is_numeric($number)) && exit;
		$number = (int) $number;
		$lower_limit = -127;
		$upper_limit = 127;
		$number < $lower_limit && $number = $lower_limit;
		$number > $upper_limit && $number = $upper_limit;
		$number < 0 && $number = $upper_limit + 1 + abs($number);
		$atom = chr($number);
		array_pop($this->pushpop);
		return $atom;
	}

	function packUI32($number){	
		array_push($this->pushpop, "packUI32");
		!(is_integer($number)) && exit;
        $lower_limit = 0;
		$upper_limit = 2147483647;  	// the real limit is 4294967295  but PHP 4 cannot handle such large unsigned integers 
		$number < $lower_limit && $number = $lower_limit;
		$number > $upper_limit && $number = $upper_limit;
        $number = sprintf("%08x", $number);
        $low_byte_low_word  = base_convert(substr($number, 6, 2), 16, 10);
		$high_byte_low_word = base_convert(substr($number, 4, 2), 16, 10); 
		$low_byte_high_word  = base_convert(substr($number, 2, 2), 16, 10);
		$high_byte_high_word = base_convert(substr($number, 0, 2), 16, 10);
		$atom  = chr($low_byte_low_word)  . chr($high_byte_low_word);
		$atom .= chr($low_byte_high_word) . chr($high_byte_high_word);
		array_pop($this->pushpop);
		return $atom;
	}

	function packFIXED8($number){
		array_push($this->pushpop, "packFIXED8");
		$lower_limit_high_byte = -127;
		$upper_limit_high_byte = 127;
		$lower_limit_low_byte = 0;
		$upper_limit_low_byte = 99;
		!(is_numeric($number)) && exit;
        $number = round($number, 2);
        $high_byte = intval($number);
        $high_byte < $lower_limit_high_byte && $high_byte = $lower_limit_high_byte;
		$high_byte > $upper_limit_high_byte && $high_byte = $upper_limit_high_byte;
		$low_byte = (int) ((abs($number) - intval(abs($number))) * 100);
		$atom  = $this->packUI8(intval($low_byte * 256 / 100));
		$atom .= $this->packSI8($high_byte);
		array_pop($this->pushpop);
		return $atom;
	}

	function packStyleArray($stylecount, $fillstyles){
		array_push($this->pushpop, "packStyleArray");
		($stylecount < 0xff) ? $atom = $this->packUI8($stylecount) : $atom = chr(0xff) . $this->packUI16($stylecount);
		$atom .= $fillstyles; 
		array_pop($this->pushpop);
		return $atom;
	}

	function packRecordHeader($tagid, $taglength){
		array_push($this->pushpop, "packRecordHeader");
		$lower_limit = 0;
		$upper_short_tag_limit = 62;
		$upper_long_tag_limit = 2147483647;
		
		if (!(is_integer($taglength))) {
			exit;
        	}
        	$taglength < $lower_limit && $this->exit;
			if ($taglength > $upper_short_tag_limit) {
				if ($taglength > $upper_long_tag_limit) {
				exit;
				} else {
				$atom  = $tagid << 6;
				$atom += 0x3f;
				$atom  = $this->packUI16($atom);
				$atom .= $this->packUI32($taglength);
			    }
        	} else {
			$atom  = $tagid << 6;
			$atom += $taglength;
			$atom  = $this->packUI16($atom);
		}
		array_pop($this->pushpop);
		return $atom;
	}


	function parseJPEGfile($filename){
		array_push($this->pushpop, "parseJPEGfile");
		$SOI  = chr(0xff) . chr(0xd8);   //图像开始标记
		$APP0 = chr(0xff) . chr(0xe0);
		$DQT  = chr(0xff) . chr(0xdb);
		$SOF0 = chr(0xff) . chr(0xc0);
		$SOF1 = chr(0xff) . chr(0xc1);
		$SOF2 = chr(0xff) . chr(0xc2);
		$DHT  = chr(0xff) . chr(0xc4);
		$DRI  = chr(0xff) . chr(0xdd);
		$SOS  = chr(0xff) . chr(0xda);
		$EOI  = chr(0xff) . chr(0xd9);
		$COM  = chr(0xff) . chr(0xfe);
		$filearray = array("JPEGEncoding" => "", "JPEGImage" => "");
		is_readable($filename) && $jpeg = readover($filename);
		$marker = strpos($jpeg, $SOI);
		$jpeg = substr($jpeg, $marker);
		$loop = True;
		$foundsos = false;
		
		while ($loop == True) {
			strlen($jpeg) == 0 && $loop = False;
			switch (substr($jpeg, 0, 2)) {
				case $SOI:
					$filearray["JPEGEncoding"] = $SOI;
					$filearray["JPEGImage"] = $SOI;
					$jpeg = substr($jpeg, 2);
					break;
				case $APP0:
					$blocklength = ord(substr($jpeg, 2, 1)) * 256 + ord(substr($jpeg, 3, 1));	
					$filearray["JPEGImage"] .= substr($jpeg, 0, $blocklength + 2);
					$jpeg = substr($jpeg, $blocklength + 2);
					break;
				case $DQT:
					$blocklength = ord(substr($jpeg, 2, 1)) * 256 + ord(substr($jpeg, 3, 1));	
					$filearray["JPEGEncoding"] .= substr($jpeg, 0, $blocklength + 2);
					$jpeg = substr($jpeg, $blocklength + 2);
					break;
				case $SOF0:
				case $SOF1:
				case $SOF2:
					$blocklength = ord(substr($jpeg, 2, 1)) * 256 + ord(substr($jpeg, 3, 1));	
					$filearray["JPEGImage"] .= substr($jpeg, 0, $blocklength + 2);
					$filearray["width"] = ord(substr($jpeg, 7, 1)) * 256 + ord(substr($jpeg, 8, 1));	
					$filearray["height"] = ord(substr($jpeg, 5, 1)) * 256 + ord(substr($jpeg, 6, 1));	
					$jpeg = substr($jpeg, $blocklength + 2);
					break;
				case $DHT:
					$blocklength = ord(substr($jpeg, 2, 1)) * 256 + ord(substr($jpeg, 3, 1));	
					$filearray["JPEGEncoding"] .= substr($jpeg, 0, $blocklength + 2);
					$jpeg = substr($jpeg, $blocklength + 2);
					break;
				case $DRI:
					$blocklength = ord(substr($jpeg, 2, 1)) * 256 + ord(substr($jpeg, 3, 1));	
					$filearray["JPEGImage"] .= substr($jpeg, 0, $blocklength + 2);
					$jpeg = substr($jpeg, $blocklength + 2);
					break;
				case $COM:
					$blocklength = ord(substr($jpeg, 2, 1)) * 256 + ord(substr($jpeg, 3, 1));	
					$jpeg = substr($jpeg, $blocklength + 2);
					break;
				case $EOI:
					$filearray["JPEGEncoding"] .= $EOI;
					$filearray["JPEGImage"] .= $EOI;
					$loop = False;
					break;
				default:
					if (substr($jpeg, 0, 2) == $SOS) {
						$blocklength = ord(substr($jpeg, 2, 1)) * 256 + ord(substr($jpeg, 3, 1));	
						$filearray["JPEGImage"] .= substr($jpeg, 0, $blocklength + 2);
						$jpeg = substr($jpeg, $blocklength + 2);
						$marker = strpos($jpeg, chr(255));
						$filearray["JPEGImage"] .= substr($jpeg, 0, $marker);
						$jpeg = substr($jpeg, $marker);
						$foundsos = True;
					} elseif ($foundsos){
							$filearray["JPEGImage"] .= substr($jpeg, 0, 1);
							$jpeg = substr($jpeg, 1);
							$marker = strpos($jpeg, chr(255));
							$filearray["JPEGImage"] .= substr($jpeg, 0, $marker);
							$jpeg = substr($jpeg, $marker);
					} else {
							exit;
					}
				}
		};
		array_pop($this->pushpop);
		return $filearray;
	}

	function packDefineBitsJPEG2Tag($bitmapid, $bitmapjpegencoding, $bitmapjpegimage){
		array_push($this->pushpop, "packDefineBitsJPEG2Tag");
		$tagid = 21;
		$bitmapid = $this->packUI16($bitmapid);
		$taglength = strlen($bitmapid . $bitmapjpegencoding . $bitmapjpegimage);
		$this->flashdata .= $this->packRecordHeader($tagid, $taglength) . $bitmapid . $bitmapjpegencoding . $bitmapjpegimage;
		array_pop($this->pushpop);
	}

	function packDefineShape2Tag($shapeid, $shapebounds, $shapewithstyle){
		array_push($this->pushpop, "packDefineShape2Tag");
		$tagid = 22;
		$DefineShapeTag = $this->packUI16($shapeid) . $shapebounds . $shapewithstyle;
		$taglength = strlen($DefineShapeTag);
		$this->flashdata .= $this->packRecordHeader($tagid, $taglength) . $DefineShapeTag;
		array_pop($this->pushpop);
	}

	function packDefineShapeTag($shapeid, $shapebounds, $shapewithstyle){
		array_push($this->pushpop, "packDefineShapeTag");
		$tagid = 2;
		$DefineShapeTag = $this->packUI16($shapeid) . $shapebounds . $shapewithstyle;
		$taglength = strlen($DefineShapeTag);
		$this->autoSwfVersion(1);
		$this->flashdata .= $this->packRecordHeader($tagid, $taglength) . $DefineShapeTag;
		array_pop($this->pushpop);
	}

	function packLineStyle($width, $R, $G, $B, $alphaflag, $A){
		array_push($this->pushpop, "packLineStyle");
		$atom  = $this->packUI16($width);
		if ($alphaflag) {
			$A == "" && $A = 0xff;
			$atom  .= $this->packRGBA($R, $G, $B, $A);
		} else {
			$atom  .= $this->packRGB($R, $G, $B);
		}
		array_pop($this->pushpop);
		return $atom;
	}
	
	function packRGBA($R, $G, $B, $A){
		array_push($this->pushpop, "packRGBA");
		$atom  = $this->packUI8($R);
		$atom .= $this->packUI8($G);
		$atom .= $this->packUI8($B);
		$atom .= $this->packUI8($A);
		array_pop($this->pushpop);
		return $atom;
	}

	function packLineStyleAray($linestyleCount, $linestyles){
		array_push($this->pushpop, "packLineStyleAray");
		$linestyleCount < 0xff ? $atom  = $this->packUI8($linestyleCount) : $atom .= char(0xff) . $this->packUI16($linestyleCount);
		$atom .= $linestyles; 
		array_pop($this->pushpop);
		return $atom;
	}
	
	function packStyleChangeRecord($statenewstyles, $statelinestyle, $statefillstyle1, $statefillstyle0, $statemoveto, $movedeltaX, $movedeltaY, $nfillbits, $nlinebits, $fillstyle0, $fillstyle1, $linestyle, $fillstyles, $linestyles, $numfillbits, $numlinebits){
		array_push($this->pushpop, "packStyleChangeRecord");
		$atom = array("Bitstream" => "", "Bytestream" => "");
		$atom["Bitstream"] .= "0";
		$atom["Bitstream"] .= sprintf("%1d", $statenewstyles);
		$atom["Bitstream"] .= sprintf("%1d", $statelinestyle);
		$atom["Bitstream"] .= sprintf("%1d", $statefillstyle1);
		$atom["Bitstream"] .= sprintf("%1d", $statefillstyle0);
		$atom["Bitstream"] .= sprintf("%1d", $statemoveto);

		if ($statemoveto == 1) {
			$movedeltaX = $this->packSBchunk($movedeltaX); 
			$movedeltaY = $this->packSBchunk($movedeltaY);
			$movebits = (int) max(strlen($movedeltaX), strlen($movedeltaY));
			$nmovebits = $this->packnBits($movebits, 5);
			$movedeltaX = str_repeat(substr($movedeltaX, 0, 1), ($movebits - strlen($movedeltaX))) . $movedeltaX;
			$movedeltaY = str_repeat(substr($movedeltaY, 0, 1), ($movebits - strlen($movedeltaY))) . $movedeltaY;
			$atom["Bitstream"] .= $nmovebits . $movedeltaX . $movedeltaY;
		}
		$statefillstyle0 && $atom["Bitstream"] .= $this->packnBits($fillstyle0, $nfillbits);
		$statefillstyle1 && $atom["Bitstream"] .= $this->packnBits($fillstyle1, $nfillbits);
		$statelinestyle && $atom["Bitstream"] .= $this->packnBits($linestyle, $nlinebits);
		$statenewstyles && $atom["Bytestream"] = $fillstyles . $linestyles . $this->packUI8((int)($this->packnBits($numfillbits, 4) . $this->packnBits($numlinebits, 4)));
		array_pop($this->pushpop);
		return $atom;
	}

	function packSBchunk($number){
		array_push($this->pushpop, "packSBchunk");
		!(is_numeric($number)) && exit;
		$number = (int) $number;
		$lower_limit = -1073741823;
		$upper_limit = 1073741823;
		$number < $lower_limit && $number = $lower_limit;
		$number > $upper_limit && $number = $upper_limit;
		if ($number < 0) {
			if ($number == -1) {
				$atom = "11";
			} else {
				$atom = decbin($number);
				$atom = substr($atom, strpos($atom, "10"));
			}
		} else {
			$atom = "0" . decbin($number);
		}
		array_pop($this->pushpop);
		return $atom;
	}

	function packnBits($number, $n){
		array_push($this->pushpop, "packnBits");
		!(is_numeric($number)) && exit;
		$number = (int) $number;
		$lower_limit = 0;
		$upper_limit = 32;
		($number < $lower_limit) && ($number > $upper_limit) && exit;
        !(is_numeric($n)) && exit;
        $n < $lower_limit && exit;
        $n = (int) $n;
        $number > (pow(2, $n) - 1) && exit;
		$atom = sprintf("%b", $number);
		$atom = str_repeat("0", ($n - strlen($atom))) . $atom;
		array_pop($this->pushpop);
		return $atom;
	}

	function packStraightRecord($generallineflag, $vertlineflag, $deltaX, $deltaY){
		array_push($this->pushpop, "packStraightRecord");
		$TypeFlag = "1";
		$StraightEdge = "1";
		if ($deltaX == 0 && $deltaY == 0) {
			$atom = sprintf("%1d", $TypeFlag) . sprintf("%1d", $StraightEdge) . "0" ;
		} else {
			$deltaX = $this->packSBchunk($deltaX); 
			$deltaY = $this->packSBchunk($deltaY);
			$NumBits = (int) max(strlen($deltaX), strlen($deltaY));
			$nBits = $this->packnBits(($NumBits - 2), 4);
			$deltaX = str_repeat(substr($deltaX, 0, 1), ($NumBits - strlen($deltaX))) . $deltaX;
			$deltaY = str_repeat(substr($deltaY, 0, 1), ($NumBits - strlen($deltaY))) . $deltaY;
			$atom = sprintf("%1d", $TypeFlag) . sprintf("%1d", $StraightEdge) . $nBits . sprintf("%1d", $generallineflag);
			($generallineflag) ? $atom .= $deltaX . $deltaY :($vertlineflag) ? $atom .= sprintf("%1d", $vertlineflag) . $deltaY : $atom .= sprintf("%1d", $vertlineflag) . $deltaX; 
		}
		array_pop($this->pushpop);
		return $atom;
	}

	function packEndSharpRecord(){
		array_push($this->pushpop, "packEndSharpRecord");
		$TypeFlag = "0";
		$EndOfShape = "00000";
		$atom = $TypeFlag . $EndOfShape;
		array_pop($this->pushpop);
		return $atom;
	}

	function packBitValues($atoms){
		array_push($this->pushpop, "packBitsValues");
		!(is_string($atoms)) && exit;
		$atoms = $atoms . str_repeat("0", (int) ((ceil(strlen($atoms) / 8)) * 8 - strlen($atoms)));
		$limit = ceil(strlen($atoms) / 8);
		$bytestream = "";
		for ($n = 0; $n < $limit; $n++) {
			$bytestream .= chr(base_convert(substr($atoms, 0, 8), 2, 10));
			$atoms = substr($atoms, 8);
		}
		array_pop($this->pushpop);
		return $bytestream;
	}

	function packEndTag(){
		array_push($this->pushpop, "packEndTag");
		$tagid = 0;
		$taglength = 0;
		$this->autoSwfVersion(1);
		$this->flashdata .= $this->packRecordHeader($tagid, $taglength);
		array_pop($this->pushpop);
	}

	function packMATRIX($hasscale, $scaleX, $scaleY, $hasrotate, $rotateskew0, $rotateskew1, $translateX, $translateY){
		array_push($this->pushpop, "packMATRIX");
		$atom = "";
		if ($hasscale) {
			$scaleX = $this->packFBchunk($scaleX);
			$scaleY = $this->packFBchunk($scaleY);
			$nScaleBits = (int) max(strlen($scaleX), strlen($scaleY));
			$scaleX = str_repeat(substr($scaleX, 0, 1), ($nScaleBits - strlen($scaleX))) . $scaleX;
			$scaleY = str_repeat(substr($scaleY, 0, 1), ($nScaleBits - strlen($scaleY))) . $scaleY;
			$atom = "1" . $this->packnBits($nScaleBits, 5) . $scaleX . $scaleY;
		} else {
			$atom = "0";
		}

		if ($hasrotate) {
			$rotateskew0 = $this->packFBchunk($rotateskew0);
			$rotateskew1 = $this->packFBchunk($rotateskew1);
			$nRotateBits = (int) max(strlen($rotateskew0), strlen($rotateskew1));
			$rotateskew0 = str_repeat(substr($rotateskew0, 0, 1), $nRotateBits - strlen($rotateskew0)) . $rotateskew0;
			$rotateskew1 = str_repeat(substr($rotateskew1, 0, 1), $nRotateBits - strlen($rotateskew1)) . $rotateskew1;
			$atom .= "1" . $this->packnBits($nRotateBits, 5) . $rotateskew0 . $rotateskew1;
		} else {
			$atom .= "0";
		}

		if ($translateX == 0 && $translateY == 0) {
			$atom .= "00000";
		} else {
			$translateX = $this->packSBchunk($translateX); 
			$translateY = $this->packSBchunk($translateY);
			$nTranslateBits = (int) max(strlen($translateX), strlen($translateY));
			$translateX = str_repeat(substr($translateX, 0, 1), $nTranslateBits - strlen($translateX)) . $translateX;
			$translateY = str_repeat(substr($translateY, 0, 1), $nTranslateBits - strlen($translateY)) . $translateY;
			$atom .= $this->packnBits($nTranslateBits, 5) . $translateX . $translateY;
		}
		$atom  = $this->packBitValues($atom);
		array_pop($this->pushpop);
		return $atom;
	}

	function packFBchunk($number){
		array_push($this->pushpop, "packFBchunk");
		$lower_limit_high_word = -16383;
		$upper_limit_high_word = 16383;
		$lower_limit_low_word = 0;
		$upper_limit_low_word = 9999;
		!(is_numeric($number)) && exit;
        $number = round($number, 4);
        $high_word = intval($number);
		$low_word = (int) ((abs($number) - intval(abs($number))) * 10000);
		$high_word < $lower_limit_high_word && $high_word = $lower_limit_high_word;
		$high_word > $upper_limit_high_word && $high_word = $upper_limit_high_word;
		$low_word < $lower_limit_low_word && $low_word = $lower_limit_low_word;
		$low_word > $upper_limit_low_word && $low_word = $upper_limit_low_word;
		if ($number < 0) {
			$high_word == 0 ? $high_word = "1" : $high_word = "1" . substr(decbin($high_word), 18);
		} else {
			$high_word == 0 ? $high_word = "0" : $high_word = "0" . decbin($high_word);
		}

		if ($number < 0) {
			if ($low_word == 0) {
				$low_word = "0000000000000000";
			} else {
				$low_word = ~$low_word;
				$low_word = substr(decbin(intval($low_word * 65536 / 10000)), 16);
			}
		} else {
			$low_word == 0 ? $low_word = "0000000000000000" : $low_word = sprintf("%016s", decbin(intval($low_word * 65536 / 10000)));
			}
		$atom = $high_word . $low_word;
		array_pop($this->pushpop);
		return $atom;
	}

	function packFillStyle($fillstyletype, $R, $G, $B, $alphaflag, $A, $gradientmatrix, $gradient, $bitmapid, $bitmapmatrix){
		array_push($this->pushpop, "packFillStyle");
		!isset($atom) && $this->setmode && ($atom = "");
		switch ($fillstyletype) {
			case 0x00:
				if ($alphaflag) {
					$A == "" && $A = 0xff;
					$atom .= $this->packRGBA($R, $G, $B, $A);
				} else {
					$atom .= $this->packRGB($R, $G, $B);
				}
				break;
			case 0x10:
				$atom .= $gradientmatrix . $gradient;
				break;
			case 0x12:
				$atom .= $gradientmatrix . $gradient;
				break;
			case 0x40:
				$atom .= $this->packUI16($bitmapid) . $bitmapmatrix;
				break;
			case 0x41:
				$atom .= $this->packUI16($bitmapid) . $bitmapmatrix;
				break;
				default:
				exit;
		}

		$atom  = $this->packUI8($fillstyletype) . $atom;

		array_pop($this->pushpop);

		return $atom;
	}

	function packShapeWithStyle($fillstyles, $linestyles, $numfillbits, $numlinebits, $shaperecords){
		array_push($this->pushpop, "packShapeWithStyle");
		$lower_limit = 0;
		$upper_limit = 15;
		($numfillbits < $lower_limit) && ($numfillbits > $upper_limit) && exit;
		($numlinebits < $lower_limit) && ($numlinebits > $upper_limit) && exit;
		$atom  = $fillstyles;
		$atom .= $linestyles;
		$numfillbits = $this->packnBits($numfillbits, 4);
		$numlinebits = $this->packnBits($numlinebits, 4);
		$atom .= $this->packBitValues($numfillbits . $numlinebits);
		$atom .= $shaperecords;
		array_pop($this->pushpop);
		return $atom;
	}

	function packDefineShape3Tag($shapeid, $shapebounds, $shapewithstyle){
		array_push($this->pushpop, "packDefineShape3Tag");
		$tagid = 32;
		$DefineShapeTag = $this->packUI16($shapeid) . $shapebounds . $shapewithstyle;
		$taglength = strlen($DefineShapeTag);
		$this->autoSwfVersion(1);
		$this->flashdata .= $this->packRecordHeader($tagid, $taglength) . $DefineShapeTag;
		array_pop($this->pushpop);
	}

	function packRECT($Xmin, $Xmax, $Ymin, $Ymax){
		array_push($this->pushpop, "packRECT");
		if (!($Xmin == 0 && $Xmax == 0 && $Ymin == 0 && $Ymax == 0)) {
			$Xmin = $this->packSBchunk($Xmin); 
			$Xmax = $this->packSBchunk($Xmax); 
			$Ymin = $this->packSBchunk($Ymin); 
			$Ymax = $this->packSBchunk($Ymax);
			$nBits = (int) max(strlen($Xmin), strlen($Xmax), strlen($Ymin), strlen($Ymax));
			$Xmin = str_repeat(substr($Xmin, 0, 1), $nBits - strlen($Xmin)) . $Xmin;
			$Xmax = str_repeat(substr($Xmax, 0, 1), $nBits - strlen($Xmax)) . $Xmax;
			$Ymin = str_repeat(substr($Ymin, 0, 1), $nBits - strlen($Ymin)) . $Ymin;
			$Ymax = str_repeat(substr($Ymax, 0, 1), $nBits - strlen($Ymax)) . $Ymax;
			$atom = $this->packnBits($nBits, 5) . $Xmin . $Xmax . $Ymin . $Ymax;
		} else {
			$atom = "00000";
		}
		$atom = $this->packBitValues($atom);
		array_pop($this->pushpop);
		return $atom;
	}

	function nextLayer(){
		array_push($this->pushpop, "nextLayer");
		$this->characterdepth++;
		$this->characterdepth > $this->layerlimit && exit;
		array_pop($this->pushpop);
		return $this->characterdepth;

	}

	function packplaceObjectTag($charid, $depth, $matrix, $cxform){
		array_push($this->pushpop, "packplaceObjectTag");
		$tagid = 4;
		$charid = $this->packUI16($charid);
		$depth = $this->packUI16($depth);
		$taglength = strlen($charid . $depth . $matrix . $cxform);
		$this->autoSwfVersion(1);
		$this->flashdata .= $this->packRecordHeader($tagid, $taglength) . $charid . $depth . $matrix . $cxform;
		array_pop($this->pushpop);
	}

	function packMacromediaFlashSWFHeader(){
		array_push($this->pushpop, "packMacromediaFlashSWFHeader");
		$headerlength = 21;
		$atom  = "FWS";
		$atom .= $this->packUI8((int)$this->swfversion);
		$atom .= $this->packUI32($headerlength + strlen($this->flashdata));
		$Xmin = (int)$this->framesize["Xmin"]; 
		$Xmax = (int)$this->framesize["Xmax"]; 
		$Ymin = (int)$this->framesize["Ymin"]; 
		$Ymax = (int)$this->framesize["Ymax"];
		min($Xmax, $Ymax) < 360 && exit;
		max($Xmax, $Ymax) > 57600 && exit;
		$Xmin = $this->packUBchunk($Xmin); 
		$Xmax = $this->packUBchunk($Xmax); 
		$Ymin = $this->packUBchunk($Ymin); 
		$Ymax = $this->packUBchunk($Ymax);
		$nBits = 16;
		$Xmin = str_repeat("0", ($nBits - strlen($Xmin))) . $Xmin;
		$Xmax = str_repeat("0", ($nBits - strlen($Xmax))) . $Xmax;
		$Ymin = str_repeat("0", ($nBits - strlen($Ymin))) . $Ymin;
		$Ymax = str_repeat("0", ($nBits - strlen($Ymax))) . $Ymax;
		$RECT = $this->packnBits($nBits, 5) . $Xmin . $Xmax . $Ymin . $Ymax;
		$atom .= $this->packBitValues($RECT);
		$atom .= $this->packFIXED8((float)$this->framerate);
		$atom .= $this->packUI16((int)$this->framecount);
		$this->flashdata = $atom . $this->flashdata;
		array_pop($this->pushpop);
	}

	function packUBchunk($number){
		array_push($this->pushpop, "packUBchunk");
		$lower_limit = 0;
		$upper_limit = 2147483647;
		!(is_numeric($number)) && $this->e;
		$number < $lower_limit && $number = $lower_limit;
		$number > $upper_limit && $number = $upper_limit;
		$atom = sprintf("%b", $number);
		array_pop($this->pushpop);
		return $atom;
	}

}
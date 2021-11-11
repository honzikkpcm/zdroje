<?php
  function resizeTo( $origWidth, $origHeight, $width, $height, $resizeOption = 'default' )
	{
		switch(strtolower($resizeOption))
		{
			case 'exact':
				$resizeWidth = $width;
				$resizeHeight = $height;
			break;

			case 'maxwidth':
				$resizeWidth  = $width;
				$resizeHeight = resizeHeightByWidth($origWidth,$origHeight,$width);
			break;

			case 'maxheight':
				$resizeWidth  = resizeWidthByHeight($origWidth,$origHeight,$height);
				$resizeHeight = $height;
			break;

			default:
				if($origWidth > $width || $origHeight > $height)
				{
					if ( $origWidth > $origHeight ) {
						 $resizeHeight = resizeHeightByWidth($origWidth,$origHeight,$width);
						 $resizeWidth  = $width;
					} else if( $origWidth < $origHeight ) {
						$resizeWidth  = resizeWidthByHeight($origWidth,$origHeight,$height);
						$resizeHeight = $height;
					}  else {
						$resizeWidth = $width;
						$resizeHeight = $height;  
					}
				} else {
					$resizeWidth = $width;
					$resizeHeight = $height;
				}
				$resized = array();
				$resized =[$resizeWidth, $resizeHeight];
			break;
		}



		return $resized;
	}

function resizeHeightByWidth($origWidth,$origHeight,$width){
	return floor(($origHeight/$origWidth)*$width);
}

function resizeWidthByHeight($origWidth,$origHeight,$height){
	return floor(($origWidth/$origHeight)*$height);
}

?>
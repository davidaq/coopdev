<?php
//生成缩略图  
function make_thumb($srcFile,$dstFile,$dstPicW,$dstPicH){  
    ini_set('memory_limit', '-1'); //没有最大内存限制  
    set_time_limit(0); //设置无超时  
  
    $pattern = "/(.+).(jpg|JPG|jpeg|JPEG|png|PNG|gif|GIF)$/";  
    preg_match($pattern,$dstFile,$matches);  
    $type = strtolower($matches[2]);  
    $type = ($type=='jpg')? 'jpeg' : $type;  
        
  
    list($srcPicW, $srcPicH) = @getimagesize($srcFile);  
  
    $srcRatio = $srcPicW / $srcPicH;
    $tgtRatio = $dstPicW / $dstPicH;
    //原图裁切的坐标    
    $srcPicX = 0;
    $srcPicY = 0;
    if($srcRatio > $tgtRatio) {
      $o = $srcPicW;
      $srcPicW = $srcPicH * $tgtRatio;
      $srcPicX = ($o - $srcPicW) / 2;
    } else {
      $o = $srcPicH;
      $srcPicH = $srcPicW / $tgtRatio;
      $srcPicY = ($o - $srcPicH) / 2;
    }
      
        
    //缩略图在画布中显示的坐标    
    $dstPicX = 0;    
    $dstPicY = 0;    
        
      
    // 加载图像  
    $srcIm = @imagecreatefromjpeg($srcFile);  
    if(!$srcIm)
        $srcIm = @imagecreatefrompng($srcFile);  
    if(!$srcIm)
        $srcIm = @imagecreatefromgif($srcFile);  
        
    $dstIm = @imagecreatetruecolor($dstPicW, $dstPicH);  
        
    // 调整大小    
    @imagecopyresampled($dstIm,$srcIm,$dstPicX,$dstPicY,$srcPicX,$srcPicY,$dstPicW,$dstPicH,$srcPicW,$srcPicH);  
        
    //另存为自定义的文件  
    switch($type){  
        case 'jpeg': {  
            @imagejpeg($dstIm,$dstFile,95); 
        }  
        break;  
        case 'gif': {  
            @imagegif($dstIm,$dstFile,100);  
        }  
        break;  
        case 'png': {  
            @imagepng($dstIm,$dstFile,9);  
        }  
    }  
        
    //释放内存    
    @imagedestroy($dstIm);    
    @imagedestroy($srcIm);    
}

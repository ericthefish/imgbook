<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////
// NAME:
//  ImgBook - Single script PHP gallery - http://code.google.com/p/imgbook/
    $version=1.45;
//
// AUTHOR:
//  Ivan Lucas, ivanlucas@users.sourceforge.net
//
// COPYRIGHT:
// Copyright (C) 2001-2008 Ivan N Lucas. Some rights reserved.
// This software may be used and distributed according to the terms
// of the GNU General Public License v2, incorporated herein by reference.
// See http://www.gnu.org/copyleft/gpl.html
//
// HISTORY:
//  Version
//          1.45   14/08/08 Added optional headers/footer configuration
//                          Added support for using PHPThumb to draw thumbnails
//                          Made powered by optional
//          1.44 - 20/11/05 Improved wording when there are no images
//          1.43 - 25/10/05 Support for config vars in external file
//          1.41 - 01/10/05 Better paging, support for title in external file
//          1.40 - 24/09/05 Added EXIF Support (via external lib) and List All Images page
//          1.39 - 23/09/05 Added subdirectories
//          1.38 - 07/03/04 Fixed some HTML problems
//                          Fixed a bug with watermark images
//                          Made thumbnails use truecolor
//                          Added more file types
//          1.37 - 06/03/04 Tidied up URL's when autosizing is off
//                          Added filename to images, with filename_prefix
//                          and suffix config vars
//          1.36 - 20/12/03 Removed requrement for register globals to be on
//          1.35 - 16/12/03 Referer protection, ban robots
//          1.34 - 07/12/02 Added tooltips for all thumbnails
//                          Doesn't display page number with only one page
//                          Display file size for downloads
//                          The gallery is now sorted alphabetical using a natural
//                          order sorting algorithm
//                          Config var for autosizing on/off
//          1.31 - 29/11/02 Bugfix release.  Many bugs that were reported by
//                          subwolf are now fixed.
//          1.30 - 24/11/02 Auto-thumbnail creation using GD Library where installed.
//                          Watermarking (requires GD)
//                          Description/Filename on hover
//                          Auto-resolution sizing
//          1.21 - 15/11/02 Bugfix release, non-image thumbnail size fixed
//          1.20 - 15/11/02 Support for non-images filetypes, a single page,
//                          and other improvements as suggested by Cybex
//          1.10 - 25/08/02 Made a little bit more generic and improved defaults
//          1.00 - 18/07/01 Initial Release
//
// FUTURE:
//  Option to hide original filenames, and show generated filenames in tooltips
//  or to use original filenames as part of the name when saving.
//  Hit Counter?
//  Directories
//  XHTML 1.0 Conformity
//  allow user to select where next/prev links go
//  Proper mime types for downloads
//
// DESCRIPTION:
//  Drop this file in any directory that you want and it automaticaly generates
//  an easy to use browsable interface for your images
//
// Configuration Variables
// ======================
//
//    imgperpage : the number of thumbnail images displayed
//                 its best to give this a value that is a multiple of imgperrow
//                 if you set this to zero everything will be displayed on a single
//                 page (ie. pages disabled)
//    imgperrow  : the number of thumbnail images per row
//    typelist   : array that contains the file types that are displayed by the browser
//    imagetypes : array containing file types that can be thumbnailed (ie. image types)
//    currentdir : default the directory where this php file resides,
//                 can be replaced by any directory of your choice
//    title      : enter the title of your page here
//    homeurl    : enter url your main website, a return link for your gallery visitors
//    hometext   : text for the link back to your main website
//    stylesheet : enter the path to your stylesheet here
//                 the stylesheet should have these classes:
//
//                 .imag { border-style : solid;
//                         border-color: blue;
//                         border-width : 1px;}
//                 .thumb { border-style : solid;
//                          border-color: #999999;
//                          border-width : 2px;}
//                  A:link { color: #999999;
//                           text-decoration : none; }
//                  A:visited { color: #999999;
//                              text-decoration : none; }
//                  A:hover { color:blue; }
//                  any of these classses can be adjusted to your needs
//
// USAGE:
//  to browse through the images use the back and forward images
//  click on one of the thumbnails
//  or use one of the pagelinks to go directly to another set of images
//  clicking on the large image will give you the full image
//
// More documentation maybe found in the README file accompanying this script
/////////////////////////////////////////////////////////////////////////////////////////////////////////


// --- Configuration -----------------------------------------------------------
$imgbook['title']            = "";
$imgbook['subtitle']         = "";
$imgbook['imgperpage']       = 42;
$imgbook['imgperrow']        = 7;
$imgbook['thumbnailwidth']   = 75;
$imgbook['thumbnailheight']  = 75;
$imgbook['maxwidth']         = 800;
$imgbook['maxheight']        = 1500;
$imgbook['gdthumbnails']     = FALSE;
$imgbook['gdlargeimages']    = FALSE;
$imgbook['autosizing']       = FALSE;
$imgbook['watermark_text']   = "Copyright (c) ".date('Y')." {$_SERVER['SERVER_NAME']} - All Rights Reserved";
$imgbook['watermark_image']  = "";
$imgbook['watermark_opacity'] = 30;
$imgbook['watermark_position'] = "bottom-right";
$imgbook['imagetypes']       = "jpg,JPG,jpeg,JPEG,gif,GIF,png,PNG,bmp,BMP";
$imgbook['downloadtypes']    = "mp3,wav,avi,mpg,swf,fla,zip,rar,psd,htm,html,exe,doc,xar,p,bsp";
$imgbook['homeurl']          = "";
$imgbook['hometext']         = "Back to Website";
$imgbook['okreferrers']      = "www.example.com,example.com";
$imgbook['stylesheet']       = "";
$imgbook['banrobots']        = TRUE;
$imgbook['filename_prefix']  = "imgbook-";
$imgbook['filename_suffix']  = "";
$imgbook['linksubdirs']      = TRUE;
$imgbook['phpthumb']         = "../phpthumb/phpThumb.php";
$imgbook['header']           = "";
$imgbook['footer']           = "";
$imgbook['poweredby']        = TRUE;
$imgbook['bordercolor']      = "#CCC;";

// --- END of Configuration ----------------------------------------------------

// turn off error reporting
// error_reporting(E_NONE);
// Add localhost into referers list, so we always allow ourselves
$okreferrers[]    = $_SERVER['HTTP_HOST'];


if (empty($imgbook['webpath']))
{
    if (substr($_SERVER['REQUEST_URI'], -1) == '/') 
    {
        $imgbookpath = $_SERVER['REQUEST_URI'];
    }
    else $imgbookpath = dirname($_SERVER['REQUEST_URI']) . '/';
    // $imgbookpath = '/'.str_replace($_SERVER['DOCUMENT_ROOT'], '', str_replace('\\', '/', dirname($_SERVER['REQUEST_URI']))).'/';
}
else $imgbookpath = $imgbook['webpath'];

$imagelist = array();
$imagetypes = explode(',', $imgbook['imagetypes']);
$downloadtypes = explode(',', $imgbook['downloadtypes']);
$typelist = $imagetypes + $downloadtypes;
$currentdir = getcwd();
$okreferrers = explode(',', $imgbook['okreferrers']);

if (!empty($imgbook['phpthumb']) AND !file_exists($imgbook['phpthumb'])) $imgbook['phpthumb'] = '';

$mimetype['png'] = 'image/png';
$mimetype['jpg'] = 'image/jpeg';
$mimetype['jpeg'] = 'image/jpeg';
$mimetype['gif'] = 'image/gif';
$mimetype['bmp'] = 'image/bmp';

function check_referrer()
{
    global $okreferrers;
    // Check Referers and halt if referal not allowed
    if (!empty($_SERVER['HTTP_REFERER']))
    {
        $referer=parse_url($_SERVER['HTTP_REFERER']);
        if (!in_array($referer[host],$okreferrers))
        {
            header("HTTP/1.0 403 Forbidden");
            echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\"\n\"http://www.w3.org/TR/html4/loose.dtd\">\n";
            echo "<html><head>\n<title>403 Forbidden</title>\n</head><body>\n<h1>Forbidden</h1>\nYou don't have permission to access this resource.\n";
            echo "\n\n<!-- \n Referer: {$_SERVER['HTTP_REFERER']} \n Host: {$referer[host]} \n-->\n\n";
            echo "</body></html>";
            exit;
        }
    }
}


function draw_thumbnail($index)
{
    global $imgbook, $imgbookpath, $imagelist, $sub;
    $thumbfilename='t_'.$imagelist[$index];
    $html .= "<div class='imgbook-thumbframe'>";
    if (file_exists($thumbfilename)) $html .= "<a href='{$_REQUEST['PHP_SELF']}?ind={$index}&amp;sub=$sub'><img src='$thumbfilename' width='{$imgbook['thumbnailwidth']}' height='{$imgbook['thumbnailheight']}' class='imgbook-thumb' alt='Thumbnail'/></a>\n" ;
    else
    {
        // Check to see if phpthumb is configured
        if (!empty($imgbook['phpthumb']))
        {
            $html .= "<a href='{$_REQUEST['PHP_SELF']}?ind={$index}&amp;sub={$sub}' title='{$imagelist[$i]}'>";
            $html .= "<img src='{$imgbook['phpthumb']}?src={$imgbookpath}{$imagelist[$index]}&amp;w={$imgbook['thumbnailwidth']}&amp;h={$imgbook['thumbnailheight']}&amp;zc=C' width='{$imgbook['thumbnailwidth']}' height='{$imgbook['thumbnailheight']}' class='imgbook-thumb' alt='' /></a>" ;
        }
        elseif (extension_loaded('gd') && $imgbook['gdthumbnails'])
        {
            // --- If we have GD Library, we can use this to draw a thumbnail
            $html .= "<a href='{$_REQUEST['PHP_SELF']}?ind={$index}&amp;sub=$sub' title='{$imagelist[$i]}'><img src='{$_REQUEST['PHP_SELF']}?ind=$index&amp;draw=thumb' width='{$imgbook['thumbnailwidth']}' height='{$imgbook['thumbnailheight']}' class='imgbook-thumb' alt='' /></a>" ;
        }
        else
        {
            $subdir=substr($sub,1,strlen($sub));
            $html .= "<a href='{$_REQUEST['PHP_SELF']}?ind={$index}&amp;sub=$sub' title='{$subdir}{$imagelist[$i]}'><img src='{$imagelist[$index]}' width='{$imgbook['thumbnailwidth']}' height='{$imgbook['thumbnailheight']}' class='imgbook-thumb' alt='{$i}' /></a>";
        }
    }
    $html .= "</div>";
    return $html;
}

function list_galleries($dirname, $level, $recursive = 1, $content = '')
{
    global $imgbook;
    // try to figure out what delimeter is being used (for windows or unix)...
    $delim = (strstr($dirname,"/")) ? "/" : "\\";

    if ($dirname[strlen($dirname)-1]!=$delim)
    $dirname.=$delim;

    $handle = opendir($dirname);
    if ($handle==FALSE) trigger_error("Error opening directory: {$dirname}",E_USER_ERROR);
    else
    {
        $filecount=0;
        while (false !== ($file = readdir($handle)))
        {
            // Skip certain files/directories
            if ($file=='.' || $file=='..' || substr($file,0,1)=='.') continue;
            else
            {
                if (is_dir($dirname.$file))
                {
                    // Display a link to the directory, use the title of the index.html page as a label
                    $level++;
                    // removed strong from dirlinks

                    if ($imgbook['linksubdirs']) $listcontent .= " <li><a href='".substr($dirname, strlen($$currentdir)+1).$file."'>$file</a>";
                    else $listcontent .= " <li><a href='{$_SERVER['PHP_SELF']}?sub=".substr($dirname, strlen($$currentdir)).$file."'>$file</a>";
                    if ($recursive) $listcontent .= list_tree($dirname.$file.$delim, $level, 1, ''); // Recurse
                    $listcontent .= "</li>\n";
                    $filecount++;
                }
            }
        }
        // Only surround the list with ul and /ul tags if there are li entries (sounds simple)
        if ($filecount > 0) $content .= "\n<ul>$listcontent</ul>\n";
        else $content .= "\n$listcontent";
    }
    closedir($handle);

    return $content;
}


// --- Set index to 0 for first run ---
## if (!isset($_REQUEST['ind'])) $index = 0;
$index = $_REQUEST['ind'];
$offset = $_REQUEST['offset'];
$sub = $_REQUEST['sub'];
if (!empty($sub)) $subdir=substr($sub,1,strlen($sub))."/";
else $subdir='';


// --- Iterate the directory and put files found in the imagelist array ---
if (!empty($sub)) $dp = opendir($currentdir.$sub);
else $dp=opendir($currentdir);
while ( false != ( $file = readdir($dp) ) )
{
    if (is_file($subdir.$file) && $subdir.$file!="." && $subdir.$file!="..")
    {
        $extension = explode(".",$file);
        $extension = strtolower($extension['1']);

        if ( in_array($extension,$typelist) AND substr($file,0,2)!='t_' )
        {
            array_push ($imagelist,$subdir.$file);
        }
    }
}

// Use Natural sorting to order the imagelist array
natcasesort($imagelist);
foreach ($imagelist AS $key => $val)
{
    $imagetmp[]=$imagelist[$key];
}
$imagelist=$imagetmp;
// sort($imagelist);
$imagecount=count($imagelist);
// reset($imagelist);

// print_r($imagelist);

// Calc number of pages
if ($imgbook['imgperpage'] == 0) $nrpages=1;
else $nrpages = ceil( $imagecount / $imgbook['imgperpage'] );


if (extension_loaded('gd') && $_REQUEST['draw']=='full')
{
    if (empty($imagelist[$index])) die('No File');

    check_referrer();

    // create the image
    // $gif = ImageCreate(200,200);
    $gfx = ImageCreateFromPNG($imagelist[$index]);
    $bg = ImageColorAllocate($gfx,0,0,0);
    $tx = ImageColorClosest($gfx,255,255,255);

    $x = imagesx($gfx);
    $y = imagesy($gfx);

    // Only Add Watermark if image is large and watermark is set
    if (($x >= 500 OR $y >= 500) AND (empty($imgbook['watermark_text'])==FALSE || empty($imgbook['watermark_image'])==FALSE))
    {
        // ImageFilledRectangle($gif,0,0,200,200,$bg);
        if (!empty($imgbook['watermark_image']) && file_exists($imgbook['watermark_image']))
        {
            $wmk = imageCreateFromPNG($imgbook['watermark_image']);
            $wmkx = imageSX($wmk);
            $wmky = imageSY($wmk);

            // middle
            switch ($imgbook['watermark_position'])
            {
                case 'center':
                $draw_x = ( $x / 2 ) - ( $wmkx / 2 );
                $draw_y = ( $y / 2 ) - ( $wmky / 2 );
                break;

                case 'top-left':
                $draw_x = 0;
                $draw_y = 0;
                break;

                case 'top-right':
                $draw_x = $x - $wmkx;
                $draw_y = 0;
                break;

                case 'bottom-right':
                $draw_x = $x - $wmkx;
                $draw_y = $y - $wmky;
                break;

                case 'bottom-left':
                $draw_x = 0;
                $draw_y = $y - $wmky;
                break;

                case 'top-center':
                $draw_x = ( ( $x - $wmkx ) / 2 );
                $draw_y = 0;
                break;

                case 'center-right':
                $draw_x = $x - $wmkx;
                $draw_y = ( $y / 2 ) - ( $wmky / 2 );
                break;

                case 'bottom-center':
                $draw_x = ( ( $x - $wmkx ) / 2 );
                $draw_y = $y - $wmky;
                break;

                case 'center-left':
                $draw_x = 0;
                $draw_y = ( $y / 2 ) - ( $wmky / 2 );
                break;

                default:
                $draw_x = ( $x / 2 ) - ( $wmkx / 2 );
                $draw_y = ( $y / 2 ) - ( $wmky / 2 );
                break;
            }
            // The main thing : merge the two pix
            imageCopyMerge($gfx, $wmk,$draw_x,$draw_y,0,0,$wmkx,$wmky,$imgbook['watermark_opacity']);
        }
        else
        {
            // ImageString($gfx,2,15,$y-15, $imgbook['watermark_text'],$tx);
            ImageString($gfx,2,($x/2)-200,$y/2, $imgbook['watermark_text'],$tx);
        }
    }
    // check to see if the image we've got is too big
    if ($screenx > 1 && ($x > ($screenx-50)))
    {
        $newx = ($screenx - 50);
        $newy = $screeny;
        $dst_img=imagecreate($newx, $newy);
        imagecopyresized($dst_img,$gfx,0,0,0,0,$newx,$newy,imagesx($gfx),imagesy($gfx));
        // send the image
        header("content-type: image/png");
        header("Content-Disposition-Type: attachment\r\n");
        header("Content-Disposition: filename={$imgbook['filename_prefix']}$index{$imgbook['filename_suffix']}.jpg\r\n");

        imagepng($dst_img);
    }
    else
    {
        if (!$gfx)
        { /* See if it failed */
            $gfx = imagecreate (150, 30); /* Create a blank image */
            $bgc = imagecolorallocate ($gfx, 255, 255, 255);
            $tc = imagecolorallocate ($gfx, 0, 0, 0);
            imagefilledrectangle ($gfx, 0, 0, 150, 30, $bgc);
            /* Output an errmsg */
            imagestring ($gfx, 1, 5, 5, "Error loading $src_imgname",$tc);
        }
        // send the image
        header("content-type: image/png");
        header("Content-Disposition-Type: attachment\r\n");
        header("Content-Disposition: filename={$imgbook['filename_prefix']}$index{$imgbook['filename_suffix']}.jpg\r\n");

        Imagepng($gfx);
    }
}
elseif (extension_loaded('gd') && $_REQUEST['draw']=='thumb')
{
    check_referrer();
    if (is_file($imagelist[$index]))
    {
        header("content-type: image/png");
        $dst_img=imagecreatetruecolor($imgbook['thumbnailwidth'], $imgbook['thumbnailheight']);
        $src_img=imagecreatefrompng($imagelist[$index]);
        imagecopyresized($dst_img,$src_img,0,0,0,0,$imgbook['thumbnailwidth'],$imgbook['thumbnailheight'],imagesx($src_img),imagesy($src_img));

        if (!$dst_img)
        { /* See if it failed */
            $dst_img = imagecreate (150, 30); /* Create a blank image */
            $bgc = imagecolorallocate ($dst_img, 255, 255, 255);
            $tc = imagecolorallocate ($dst_img, 0, 0, 0);
            imagefilledrectangle ($dst_img, 0, 0, 150, 30, $bgc);
            /* Output an errmsg */
            imagestring ($dst_img, 1, 5, 5, "Error loading $src_imgname",$tc);
        }
        imagepng($dst_img);
    }
}
else
{
    // Look for an index.txt file which contains filenames, one or more spaces and a description (one per line)
    if (file_exists("index.txt"))
    {
        $indexstr = file_get_contents("index.txt");
        $indexlines = nl2br($indexstr);
        $indexlines = explode("<br />", $indexlines);
        foreach ($indexlines AS $linekey => $line)
        {
            $linetmp=explode(" ",$line, 2);
            // print_r($linetmp);
            // $linetmp=preg_split("/{\s}3/",$line);
            $linetmp[0] = trim($linetmp[0]);
            $descindex[$linetmp[0]] = trim($linetmp[1]);
        }
    }
    // Read config vars from index.txt file
    foreach ($imgbook AS $key => $value)
    {
        if (($value === '' OR $value === TRUE) AND $descindex[$key] != '')
        {
            if ($descindex[$key] == 'FALSE') $descindex[$key] = FALSE;
            if ($descindex[$key] == 'TRUE') $descindex[$key] = TRUE;
            $imgbook[$key] = $descindex[$key];
        }
    }
    if (!empty($imgbook['header']))
    {
        include($imgbook['header']);
    }
    else
    {
        echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"\n";
        echo "\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n";
        echo "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">\n";
        echo "<head>\n";
        echo "<meta http-equiv=\"Content-Type\" content=\"text/html;charset=UTF-8\" />";
        echo "<meta name=\"generator\" content=\"ImgBook Instant Image Gallery Script v$version\" />\n";
        if ($imgbook['banrobots']==TRUE) echo "<meta name='robots' content='noindex,nofollow' />\n";
        echo "<meta http-equiv=\"imagetoolbar\" content=\"no\" />\n";
        echo "<title>{$imgbook['title']}</title>";
        if (!empty($stylesheet)) echo "<link rel=\"STYLESHEET\" href=\"$stylesheet\">\n";
        echo "<style type='text/css'>table.info th { text-align: right; }</style>";
        if (empty($stylesheet))
        {
            // --- Cascading Style Sheet -----------------------------------------------
            echo "<style type='text/css'>\n
                    #imgbook-page { font-family: 'Lucida Grande',Verdana,Arial,Sans-Serif; font-size: 12px; text-align: center; }
                    h2.imgbook { color:#3377C3; background: none; border: none; }
                    #imgbook-front { }
                    .imgbook-navi a:link, .imgbook-navi a:visited, .imgbook-navi a:active { border-bottom:1px dotted #CCCCCC; color:#3377C3; text-decoration:none; }
                    .imgbook-navi a:hover { border-bottom:1px solid #FF9933; text-decoration:none; }
                    .imgbook-navi { color: #CCC; }
                    .imgbook-subtitle { color: #222; }
                    .imgbook-thumbnails {}
                    .imgbook-thumbframe { border: 1px solid {$imgbook['bordercolor']}; padding: 2px; }
                    .imgbook-thumbframe:hover { border: 1px solid black; padding: 2px; }
                    .imgbook-thumbframe a { border: 0px; }
                    .imgbook-thumb { border: none; }
                    .imgbook-imageframe {  }
                    .imgbook-image { border: 1px solid {$imgbook['bordercolor']}; padding: 2px; }
                    .imgbook-infoframe { color: #222; }
                    .imgbook-infoframe th { color: #356AA0; }
                 </style>\n";
            /*
            echo ".imag { border-style : $borderstyle;";
            echo "        border-color: $bordercolor;";
            echo "        border-width : $borderwidth;}";
            echo ".thumb { border-style : $borderstyle;";
            echo "         border-color: $bordercolor;";
            echo "         border-width : $borderwidth;}";
            echo ".icon {  border-style : $borderstyle;";
            echo "         border-color: $bordercolor;";
            echo "         border-width : $borderwidth;";
            echo "         background-image: url(\"$iconimage\");";
            echo "         background-position: center center;";
            echo "         background-repeat: no-repeat;";
            echo "         padding: 0px;";
            echo "         }";
            echo "A:link { color: $alinkcolor;";
            echo "        text-decoration : none; }";
            echo "A:visited { color: $vlinkcolor;";
            echo "       text-decoration : none; }";
            echo "A:hover { color: $hlinkcolor; }";
        */
            echo "";
            // --- YOU SHOULD NOT NEED TO ALTER ANYTHING BELOW THIS LINE ---------------
        }
        echo "</head>\n<body>\n";
    }
    echo "<div id='imgbook-page'>";

    // -- Large Image
    if (is_numeric($index))
    {
        if (!empty($descindex[$imagelist[$index]]))
        {
            echo "<div style='font-size: 180%; padding-left: 50px; padding-right: 50px; padding-bottom: 5px; text-align: center; color: #9AD7F5;'>{$descindex[$imagelist[$index]]}</div>";
        }

        echo "<table align=\"center\" summary=\"Image View Table\" border=\"0\">";
        echo "<tr>";
        if ($index > 0)
        {
            echo "<td  valign='top' style='padding-top: 200px;'>".draw_thumbnail($index-1);
            echo "<p class='imgbook-navi'>[<a href='{$_REQUEST['PHP_SELF']}?ind=".($index-1)."&amp;sub=$sub";
            if ($imgbook['autosizing']) echo "&amp;screenx=$screenx&amp;screeny=$screeny";
            echo "'>previous</a>]</p>";
        }
        echo "</td>";

        echo "<td>";
        $imageinfo = getimagesize($imagelist[$index]);
        $basefilename=explode('.',$imagelist[$index]);
        if (in_array($basefilename['1'], $imagetypes))
        {
            echo "<div class='imgbook-imageframe'>";
            // echo "<a href=\"{$imagelist[$index]}\" target=\"_blank\">";
            if (extension_loaded('gd') && $imgbook['gdlargeimages']==TRUE)
            {
                echo "<img src=\"$PHP_SELF?ind=$index&amp;draw=full";
                if ($imgbook['autosizing']) echo "&amp;screenx=$screenx&amp;screeny=$screeny";
                echo "\" width='{$imageinfo['0']}' height='{$imageinfo['1']}' class=\"imgbook-image\" alt=\"Image $index\">";
            }
            else
            {
                $width=$imageinfo['0'] < $imgbook['maxwidth'] ? $imageinfo['0'] : $imgbook['maxwidth'];
                $height=$imageinfo['1'] < $imgbook['maxheight'] ? $imageinfo['1'] : $imgbook['maxheight'];
                echo "<img src=\"{$imagelist[$index]}\" class=\"imgbook-image\" width='$width' height='$height' alt=\"Image $index\" />\n";
                if ($imageinfo['0']>$imgbook['maxwidth'] OR $imageinfo['1'] > $imgbook['maxheight']) echo "<p><a href='$imagelist[$index]'>View full sized image ({$imageinfo['0']}x{$imageinfo['1']})</a></p>";
            }
            // echo "</a>";
            echo "</div>";
        }
        else
        {
            echo "<table summary='File Link' width='320' height='240' class='icon'>";
            echo "<tr><td valign='center' align='center'><a href=\"{$imagelist[$index]}\" target=\"_blank\">Download {$basefilename['0']}.{$basefilename['1']}</a><br />";
            echo "(".number_format((filesize($imagelist[$index]))/1024, 0, '.', ',')." KBytes)";
            echo "</td></tr>\n</table>\n";
        }

        echo "</td>";
        if ($index < $imagecount-1)
        {
            echo "<td valign='top' style='padding-top: 200px; text-align: right;'>".draw_thumbnail($index+1);
            echo "<p class='imgbook-navi'>[<a href='{$_REQUEST['PHP_SELF']}?ind=".($index+1)."&amp;sub=$sub";
            if ($imgbook['autosizing']) echo "&amp;screenx=$screenx&amp;screeny=$screeny";
            echo "'>next</a>]</p>";
            echo "</td>";
        }
        echo "</tr>\n";
        echo "</table>\n";
        echo "<br />\n";
        // End Large Image

        // Information Table
        echo "<div class='imgbook-infoframe'>";
        echo "<table align='center' class='info' border='0' width='50%'>";

        // Get the image description if there is one, same filename but a .txt extension
        $basefilename=$basefilename['0'];
        $txtfilename=$basefilename.'.txt';
        if (file_exists($txtfilename))
        {
            echo "<tr><th>Description</th><td>";
            readfile($txtfilename);
            echo "</td></tr>";
        }
        echo "<tr><th>Image</th><td>{$imagelist[$index]} (".($index+1)." of ".($imagecount).")</td>";
        echo "<th>Date Modified</th><td>".date('d M Y H:i',filemtime($imagelist[$index]))."</td>";
        echo "<tr><th>Dimensions</th><td>{$imageinfo['0']} x {$imageinfo['1']}, {$imageinfo['bits']} bit</td>";
        if (file_exists('exif.php'))
        {
            include('exif.php');
            $verbose = 0;
            $imageexif = read_exif_data_raw($imagelist[$index],$verbose);
            if ($_REQUEST['debug'])
            {
            echo "<tr><th>EXIF</th><td><pre>";
            print_r($imageexif);
            echo "</pre></td></tr>";

            }
            $dateorig=str_replace(':','-',substr($imageexif['SubIFD']['DateTimeOriginal'],0,10)).substr($imageexif['SubIFD']['DateTimeOriginal'],10,strlen($imageexif['SubIFD']['DateTimeOriginal']));
            $datetaken=strtotime($dateorig);
            echo "<th>Date Taken</th><td>".date('d M Y H:i', $datetaken)."</td></tr>";

            if (!empty($imageexif['IFD0']['Make']))
            {
                echo "<tr><th>Camera</th><td>{$imageexif['IFD0']['Make']} {$imageexif['IFD0']['Model']}</td>";
                // echo "<tr><th>Date Taken</th><td>".$imageexif['SubIFD']['DateTimeOriginal']."</td>";
                echo "<th>Exposure</th><td>{$imageexif['SubIFD']['ExposureTime']} (ISO {$imageexif['SubIFD']['ISOSpeedRatings']}) {$imageexif['SubIFD']['FNumber']}</td></tr>";

                if ($imageexif['SubIFD']['Flash'] != "No Flash" AND trim($imageexif['SubIFD']['MakerNote']['FlashSetting'])!=''
                    AND strpos($imageexif['SubIFD']['Flash'],'Unknown')===FALSE) echo "<th>Flash</th><td>{$imageexif['SubIFD']['Flash']}, Setting {$imageexif['SubIFD']['MakerNote']['FlashSetting']}</td></tr>";
                else "<th></th><td></td></tr>";

                echo "<tr><th>Original Size</th><td>{$imageexif['SubIFD']['ExifImageWidth']} x {$imageexif['SubIFD']['ExifImageHeight']}</td>";
                if (trim($imageexif['SubIFD']['MakerNote']['unknown:008f'])!='') echo "<th>Mode</th><td>{$imageexif['SubIFD']['MakerNote']['unknown:008f']}</td>";
                echo "</tr>";
            }
            if (trim($imageexif['COM']['Data'])!='') echo "<tr><th>Comment</th><td>{$imageexif['COM']['Data']}</td></tr>";

        }
        echo "</table>";
        echo "</div>";
    }
    elseif ($index=='list')
    {
        // Look for an index.txt file which contains filenames, one or more spaces and a description (one per line)
        if (file_exists("index.txt"))
        {
            $indexstr=file_get_contents("index.txt");
            $indexlines=nl2br($indexstr);
            $indexlines=explode("<br />", $indexlines);
            foreach ($indexlines AS $linekey => $line)
            {
                $linetmp=explode(" ",$line, 2);
                //print_r($linetmp);
                // $linetmp=preg_split("/{\s}3/",$line);
                $linetmp[0]=trim($linetmp[0]);
                $descindex[$linetmp[0]]=trim($linetmp[1]);
            }
            // print_r($descindex);
        }
        echo "<div class='imgbook-thumbnails'>";
        if (!empty($imgbook['title'])) echo "<h2 class='imgbook'>{$imgbook['title']}</h2>";
        echo "<table align='center' summary='Thumbnail List Table'>\n";
        foreach ($imagelist AS $imagekey => $image)
        {
            echo "<tr><td>".draw_thumbnail($imagekey)."</td><td style='text-align: left; vertical-align: top;'><a name='$imagekey'>$image</a><br />";
            if (!empty($descindex[$image]))
            {
                echo "{$descindex[$image]}";
            }
            echo "</td></tr>";
        }
        echo "</table>";
        echo "</div>";
    }
    else
    {
        // Front page
        echo "<div id='imgbook-front'>";
        if (!empty($imgbook['title'])) echo "<h2 class='imgbook'>{$imgbook['title']}</h2>";
        // Information Table
        if ($imagecount > 0)
        {
            echo "<p align='center' class='imgbook-subtitle'>";
            if (!empty($imgbook['subtitle'])) echo "{$imgbook['subtitle']}: ";
            elseif (!empty($imgbook['title'])) echo "{$imgbook['title']}: ";
            $lastthumb=($_REQUEST['offset'] + $imgbook['imgperpage']);
            if ($lastthumb>$imagecount) $lastthumb=$imagecount;
            if ($imgbook['imgperpage'] > 0 && $imagecount > $imgbook['imgperpage']) echo ($_REQUEST['offset']+1)."-{$lastthumb} of ";
            echo "{$imagecount} Images";
            echo "</p>";
        }
    //echo "<tr><th>Date Modified</th><td>".date('d M Y H:i',filemtime($imagelist[$index]))."</td>";

    // echo "<p align='center'><a href='{$_SERVER['PHP_SELF']}?ind=0'>Browse Images one at a time</a></p>";

    //--- Thumbnails index table
    if ($imgbook['imgperpage'] == 0 OR $dispimages > $imagecount) $dispimages = $imagecount;
    else $dispimages = $imgbook['imgperpage'];
    echo "<table align=\"center\" summary=\"Thumbnails Table\">\n";
    for($j=0;$j<$nrpages;$j++)
    {
        if ( $offset >= ($j*$dispimages) && $offset < (($j+1) * $dispimages))
        {
            for($i=($j*$dispimages);$i<(($j+1) * $dispimages);$i++)
            {
                if ($i % $imgbook['imgperrow'] == 0 && $i > 1 && $c < $dispimages) echo "\n<!-- $c/ $dispimages /$imagecount --></tr>\n";
                if ($c == $dispimages-1) echo "\n<!--end--></tr>\n";
                if ($i % $imgbook['imgperrow'] == 0) echo "\n<tr>\n";
                if ($i < $imagecount)
                {
                
                
                    $path = "{$_SERVER['PHP_SELF']}?ind=$i&amp;sub=$sub";
                    if ($imgbook['autosizing']) $path.="&amp;screenx=$screenx&amp;screeny=$screeny";
                    echo "<td>";
                    if ($_REQUEST['sel']!='' AND $_REQUEST['sel']==$i) echo "<div style='border: 1px dashed yellow;'>";
                    $thumbbasefilename=explode(".",$imagelist[$i]);
                    if (in_array($thumbbasefilename['1'], $imagetypes))
                    {
                        // display thumbnail if we have one, otherwise display big image (with small display dimensions)
                        //$thumbfilename='t_'.$imagelist[$i];
                        echo draw_thumbnail($i);
                    }
                    else
                    {
                        // echo "<a href='$path'>{$imagelist[$i]}</a>";
                        // non image thumbnail
                        echo "<table summary='File Link' width='".($imgbook['thumbnailwidth']+3)."' height='".($imgbook['thumbnailheight']+3)."' class='icon'>";
                        echo "<tr><td valign='center' align='center'>[<a href='$path' title='{$imagelist[$i]}'>{$thumbbasefilename['1']}</a>]</td></tr>\n</table>\n";
                    }
                    if ($_REQUEST['sel']!='' AND $_REQUEST['sel']==$i) echo "</div>";
                    echo "</td>\n";
                    // tr needed?
                }
                $c++;
            }
        }
    }
    unset($c);
    echo "</table>\n";

    //---this code generates page links based on the configuration settings---
    if ($imgbook['imgperpage'] > 0 && $imagecount > $imgbook['imgperpage'])
    {
        echo "<p align='center' class='imgbook-navi'>";
        for($j=0;$j<$nrpages;$j++)
        {
            echo "[<a href='{$_SERVER['PHP_SELF']}?ind=index&amp;offset=".($j * $imgbook['imgperpage']);
        if (!empty($sub)) echo "&amp;sub=$sub";
            if ($imgbook['autosizing']) echo "&amp;screenx=$screenx&amp;screeny=$screeny";
            echo "'>";
            if ($_REQUEST['offset']==($j * $imgbook['imgperpage'])) echo "<strong>";
            echo "page ".($j+1);
            if ($_REQUEST['offset']==($j * $imgbook['imgperpage'])) echo "</strong>";
            echo "</a>] ";
            if ((($j+1) % 10)==0) echo "<br />";
        }
        echo "</p>";
    }

    echo "<br />";


    echo "<div style='width: 40%; text-align: left; margin-left: auto; margin-right: auto;'>";
    // echo "<h3>Galleries:</h3>";
    echo list_galleries($currentdir, 0, 0, '');
    echo "</div>";

    echo "</div>";
}



echo "<form action='{$_SERVER['PHP_SELF']}' name='resform' id='resform' method='get'>\n";
echo "<input type='hidden' name='screenx' id='screenx' value='800' />";
echo "<input type='hidden' name='screeny' value='600' />";
echo "<input type='hidden' name='ind' value='$ind' />";
echo "<input type='hidden' name='sub' value='$sub' />";
// echo "<input type='submit' name='AutoSize' />";
echo "<p align='center' class='imgbook-navi'>";
if ($_REQUEST['ind']!='index')
{
    $offset=0;
    for($j=1;$j<=$nrpages;$j++)
    {
        if (($j * $imgbook['imgperpage']) <= $index
        AND ($j * $imgbook['imgperpage']) < (($index-1) + $imgbook['imgperpage'])) $offset=($j * $imgbook['imgperpage']);
    }
    echo " [<a href='{$_REQUEST['PHP_SELF']}?ind=index&amp;sub=$sub&amp;sel=$index&amp;offset=$offset'>Back to index</a>] &nbsp;";
}
if ($_REQUEST['ind']!='list') echo " [<a href='{$_REQUEST['PHP_SELF']}?ind=list&amp;sub=$sub#$index'>List</a>]";
if (!empty($imgbook['homeurl'])) echo " &nbsp;[<a href=\"{$imgbook['homeurl']}\">{$imgbook['hometext']}</a>]";
if ($imgbook['autosizing'])
{
    if ($screenx < 10) echo " | [<a href=\"javascript:document.resform.submit();\">AutoSize</a>]";
    else echo " | [<a href=\"$PHP_SELF?ind=$ind&amp;sub=$sub\">Disable AutoSize</a>]";
}
echo "</p>";
echo "</form>";
if ($imgbook['poweredby']) echo "<p align='center' style='font-size: 12px; color: #ccc;' class='imgbook-poweredby'>Powered by ImgBook v{$version}</p>";
echo "<script language=\"JavaScript\" type=\"text/javascript\"><!--\n";
echo "var height, width;\n";
echo "if (document.all)\n";  // IE
echo "{\n";
echo "  resform.screenx.value = document.body.offsetWidth;\n";
echo "  resform.screeny.value = document.body.offsetHeight;\n";
// echo "  resform.screenx.value = 555; ";
echo "}\n";
echo "else "; //if (document.layers)\n";
echo "{\n";
echo "  document.resform.screenx.value = window.innerWidth;\n";
echo "  document.resform.screeny.value = window.innerHeight;\n";
// echo "  document.resform.screeny.value = 666;";
echo "}\n";
echo "//--></script>\n";

echo "</div>";
if (!empty($imgbook['footer']))
{
    include($imgbook['footer']);
}
else
{
    echo "\n</body>\n</html>\n";
}
}
?>
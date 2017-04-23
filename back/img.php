<?php
function imageCreateCorners($src, $w, $h, $radius)
{

# create corners

    $q = 10; # change this if you want
    $radius *= $q;

# find unique color
    do {
        $r = rand(0, 255);
        $g = rand(0, 255);
        $b = rand(0, 255);
    } while (imagecolorexact($src, $r, $g, $b) < 0);

    $nw = $w * $q;
    $nh = $h * $q;

    $img = imagecreatetruecolor($nw, $nh);
    $alphacolor = imagecolorallocatealpha($img, $r, $g, $b, 127);
    imagealphablending($img, false);
    imagesavealpha($img, true);
    imagefilledrectangle($img, 0, 0, $nw, $nh, $alphacolor);

    imagefill($img, 0, 0, $alphacolor);
    imagecopyresampled($img, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

    imagearc($img, $radius - 1, $radius - 1, $radius * 2, $radius * 2, 180, 270, $alphacolor);
    imagefilltoborder($img, 0, 0, $alphacolor, $alphacolor);
    imagearc($img, $nw - $radius, $radius - 1, $radius * 2, $radius * 2, 270, 0, $alphacolor);
    imagefilltoborder($img, $nw - 1, 0, $alphacolor, $alphacolor);
    imagearc($img, $radius - 1, $nh - $radius, $radius * 2, $radius * 2, 90, 180, $alphacolor);
    imagefilltoborder($img, 0, $nh - 1, $alphacolor, $alphacolor);
    imagearc($img, $nw - $radius, $nh - $radius, $radius * 2, $radius * 2, 0, 90, $alphacolor);
    imagefilltoborder($img, $nw - 1, $nh - 1, $alphacolor, $alphacolor);
    imagealphablending($img, true);
    imagecolortransparent($img, $alphacolor);

# resize image down
    $dest = imagecreatetruecolor($w, $h);
    imagealphablending($dest, false);
    imagesavealpha($dest, true);
    imagefilledrectangle($dest, 0, 0, $w, $h, $alphacolor);
    imagecopyresampled($dest, $img, 0, 0, 0, 0, $w, $h, $nw, $nh);

# output image
    imagedestroy($src);
    imagedestroy($img);

    return $dest;
}
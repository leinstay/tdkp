<?php

if (!function_exists("imagecreatefrombmp")) {
    function imagecreatefrombmp($filename)
    {
        // version 1.1
        if (!($fh = fopen($filename, 'rb'))) {
            trigger_error('imagecreatefrombmp: Can not open ' . $filename, E_USER_WARNING);
            return false;
        }

        // read file header
        $meta = unpack('vtype/Vfilesize/Vreserved/Voffset', fread($fh, 14));

        // check for bitmap
        if ($meta['type'] != 19778) {
            trigger_error('imagecreatefrombmp: ' . $filename . ' is not a bitmap!', E_USER_WARNING);
            return false;
        }

        // read image header
        $meta += unpack('Vheadersize/Vwidth/Vheight/vplanes/vbits/Vcompression/Vimagesize/Vxres/Vyres/Vcolors/Vimportant', fread($fh, 40));
        $bytes_read = 40;

        // read additional bitfield header
        if ($meta['compression'] == 3) {
            $meta += unpack('VrMask/VgMask/VbMask', fread($fh, 12));
            $bytes_read += 12;
        }

        // set bytes and padding
        $meta['bytes'] = $meta['bits'] / 8;
        $meta['decal'] = 4 - (4 * (($meta['width'] * $meta['bytes'] / 4) - floor($meta['width'] * $meta['bytes'] / 4)));
        if ($meta['decal'] == 4) {
            $meta['decal'] = 0;
        }

        // obtain imagesize
        if ($meta['imagesize'] < 1) {
            $meta['imagesize'] = $meta['filesize'] - $meta['offset'];
            // in rare cases filesize is equal to offset so we need to read physical size
            if ($meta['imagesize'] < 1) {
                $meta['imagesize'] = @filesize($filename) - $meta['offset'];
                if ($meta['imagesize'] < 1) {
                    trigger_error('imagecreatefrombmp: Can not obtain filesize of ' . $filename . '!', E_USER_WARNING);
                    return false;
                }
            }
        }

        // calculate colors
        $meta['colors'] = !$meta['colors'] ? pow(2, $meta['bits']) : $meta['colors'];

        // read color palette
        $palette = array();
        if ($meta['bits'] < 16) {
            $palette = unpack('l' . $meta['colors'], fread($fh, $meta['colors'] * 4));
            // in rare cases the color value is signed
            if ($palette[1] < 0) {
                foreach ($palette as $i => $color) {
                    $palette[$i] = $color + 16777216;
                }
            }
        }

        // ignore extra bitmap headers
        if ($meta['headersize'] > $bytes_read) {
            fread($fh, $meta['headersize'] - $bytes_read);
        }

        // create gd image
        $im = imagecreatetruecolor($meta['width'], $meta['height']);
        $data = fread($fh, $meta['imagesize']);

        // uncompress data
        switch ($meta['compression']) {
            case 1:
                $data = rle8_decode($data, $meta['width']);
                break;
            case 2:
                $data = rle4_decode($data, $meta['width']);
                break;
        }

        $p = 0;
        $vide = chr(0);
        $y = $meta['height'] - 1;
        $error = 'imagecreatefrombmp: ' . $filename . ' has not enough data!';

        // loop through the image data beginning with the lower left corner
        while ($y >= 0) {
            $x = 0;
            while ($x < $meta['width']) {
                switch ($meta['bits']) {
                    case 32:
                    case 24:
                        if (!($part = substr($data, $p, 3 /*$meta['bytes']*/))) {
                            trigger_error($error, E_USER_WARNING);
                            return $im;
                        }
                        $color = unpack('V', $part . $vide);
                        break;
                    case 16:
                        if (!($part = substr($data, $p, 2 /*$meta['bytes']*/))) {
                            trigger_error($error, E_USER_WARNING);
                            return $im;
                        }
                        $color = unpack('v', $part);

                        if (empty($meta['rMask']) || $meta['rMask'] != 0xf800) {
                            $color[1] = (($color[1] & 0x7c00) >> 7) * 65536 + (($color[1] & 0x03e0) >> 2) * 256 + (($color[1] & 0x001f) << 3); // 555
                        } else {
                            $color[1] = (($color[1] & 0xf800) >> 8) * 65536 + (($color[1] & 0x07e0) >> 3) * 256 + (($color[1] & 0x001f) << 3); // 565
                        }
                        break;
                    case 8:
                        $color = unpack('n', $vide . substr($data, $p, 1));
                        $color[1] = $palette[$color[1] + 1];
                        break;
                    case 4:
                        $color = unpack('n', $vide . substr($data, floor($p), 1));
                        $color[1] = ($p * 2) % 2 == 0 ? $color[1] >> 4 : $color[1] & 0x0F;
                        $color[1] = $palette[$color[1] + 1];
                        break;
                    case 1:
                        $color = unpack('n', $vide . substr($data, floor($p), 1));
                        switch (($p * 8) % 8) {
                            case 0:
                                $color[1] =  $color[1] >> 7;
                                break;
                            case 1:
                                $color[1] = ($color[1] & 0x40) >> 6;
                                break;
                            case 2:
                                $color[1] = ($color[1] & 0x20) >> 5;
                                break;
                            case 3:
                                $color[1] = ($color[1] & 0x10) >> 4;
                                break;
                            case 4:
                                $color[1] = ($color[1] & 0x8) >> 3;
                                break;
                            case 5:
                                $color[1] = ($color[1] & 0x4) >> 2;
                                break;
                            case 6:
                                $color[1] = ($color[1] & 0x2) >> 1;
                                break;
                            case 7:
                                $color[1] = ($color[1] & 0x1);
                                break;
                        }
                        $color[1] = $palette[$color[1] + 1];
                        break;
                    default:
                        trigger_error('imagecreatefrombmp: ' . $filename . ' has ' . $meta['bits'] . ' bits and this is not supported!', E_USER_WARNING);
                        return false;
                }
                imagesetpixel($im, $x, $y, $color[1]);
                $x++;
                $p += $meta['bytes'];
            }
            $y--;
            $p += $meta['decal'];
        }
        fclose($fh);
        return $im;
    }
}

/** 
 * Decoder for RLE8 compression in windows bitmaps
 * http://msdn.microsoft.com/library/default.asp?url=/library/en-us/gdi/bitmaps_6x0u.asp
 *
 * @param string  $str   Data to decode
 * @param integer $width Image width
 *
 * @return string
 */
function rle8_decode($str, $width)
{
    $lineWidth = $width + (3 - ($width - 1) % 4);
    $out = '';
    $cnt = strlen($str);

    for ($i = 0; $i < $cnt; $i++) {
        $o = ord($str[$i]);
        switch ($o) {
            case 0: # ESCAPE
                $i++;
                switch (ord($str[$i])) {
                    case 0: # NEW LINE
                        $padCnt = $lineWidth - strlen($out) % $lineWidth;
                        if ($padCnt < $lineWidth) $out .= str_repeat(chr(0), $padCnt); # pad line
                        break;
                    case 1: # END OF FILE
                        $padCnt = $lineWidth - strlen($out) % $lineWidth;
                        if ($padCnt < $lineWidth) $out .= str_repeat(chr(0), $padCnt); # pad line
                        break 3;
                    case 2: # DELTA
                        $i += 2;
                        break;
                    default: # ABSOLUTE MODE
                        $num = ord($str[$i]);
                        for ($j = 0; $j < $num; $j++)
                            $out .= $str[++$i];
                        if ($num % 2) $i++;
                }
                break;
            default:
                $out .= str_repeat($str[++$i], $o);
        }
    }
    return $out;
}
/** 
 * Decoder for RLE4 compression in windows bitmaps
 * see http://msdn.microsoft.com/library/default.asp?url=/library/en-us/gdi/bitmaps_6x0u.asp
 *
 * @param string  $str   Data to decode
 * @param integer $width Image width
 *
 * @return string
 */
function rle4_decode($str, $width)
{
    $w = floor($width / 2) + ($width % 2);
    $lineWidth = $w + (3 - (($width - 1) / 2) % 4);
    $pixels = array();
    $cnt = strlen($str);
    $c = 0;

    for ($i = 0; $i < $cnt; $i++) {
        $o = ord($str[$i]);
        switch ($o) {
            case 0: # ESCAPE
                $i++;
                switch (ord($str[$i])) {
                    case 0: # NEW LINE
                        while (count($pixels) % $lineWidth != 0) {
                            $pixels[] = 0;
                        }
                        break;
                    case 1: # END OF FILE
                        while (count($pixels) % $lineWidth != 0) {
                            $pixels[] = 0;
                        }
                        break 3;
                    case 2: # DELTA
                        $i += 2;
                        break;
                    default: # ABSOLUTE MODE
                        $num = ord($str[$i]);
                        for ($j = 0; $j < $num; $j++) {
                            if ($j % 2 == 0) {
                                $c = ord($str[++$i]);
                                $pixels[] = ($c & 240) >> 4;
                            } else {
                                $pixels[] = $c & 15;
                            }
                        }

                        if ($num % 2 == 0) {
                            $i++;
                        }
                }
                break;
            default:
                $c = ord($str[++$i]);
                for ($j = 0; $j < $o; $j++) {
                    $pixels[] = ($j % 2 == 0 ? ($c & 240) >> 4 : $c & 15);
                }
        }
    }

    $out = '';
    if (count($pixels) % 2) {
        $pixels[] = 0;
    }

    $cnt = count($pixels) / 2;

    for ($i = 0; $i < $cnt; $i++) {
        $out .= chr(16 * $pixels[2 * $i] + $pixels[2 * $i + 1]);
    }

    return $out;
}

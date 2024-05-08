<?php


namespace App\Helper;


trait ImageHelper
{
    public function renameImage($dir,$path,$id)
    {
        if (file_exists($path)) {
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            if(rename($path, $dir .'/'. $id .'.'. $extension)){
                return $id .'.'. $extension;
            }
        }
        return false;
    }

    /**
     * 图片格式转换
     * @param string $image_path 文件路径或url
     * @param string $to_ext 待转格式，支持png,gif,jpeg,wbmp,webp,xbm
     * @param null|string $save_path 存储路径，null则返回二进制内容，string则返回true|false
     * @return boolean|string $save_path是null则返回二进制内容，是string则返回true|false
     * @throws Exception
     * @author klinson
     */

    function transform_image($image_path, $to_ext = 'webp', $save_path = null)
    {
        if (! in_array($to_ext, ['png', 'gif', 'jpeg', 'wbmp', 'webp', 'xbm'])) {
            throw new \Exception('unsupport transform image to ' . $to_ext);
        }
        switch (\exif_imagetype($image_path)) {
            case IMAGETYPE_GIF :
                $img = \imagecreatefromgif($image_path);

                break;

            case IMAGETYPE_JPEG :

            case IMAGETYPE_JPEG2000:

                $img = imagecreatefromjpeg($image_path);

                break;

            case IMAGETYPE_PNG:

                $img = imagecreatefrompng($image_path);

                break;

            case IMAGETYPE_BMP:

            case IMAGETYPE_WBMP:

                $img = imagecreatefromwbmp($image_path);

                break;

            case IMAGETYPE_XBM:

                $img = imagecreatefromxbm($image_path);

                break;

            case IMAGETYPE_WEBP: //(从 PHP 7.1.0 开始支持)

                $img = imagecreatefromwebp($image_path);

                break;

            default :

                throw new \Exception('Invalid image type');

        }

        $function = 'image'.$to_ext;

        if ($save_path) {
            return @$function($img, $save_path);

        } else {

            $tmp = __DIR__.'/'.uniqid().'.'.$to_ext;

            if ($function($img, $tmp)) {

                $content = file_get_contents($tmp);

                unlink($tmp);

                return $content;

            } else {

                unlink($tmp);

                throw new \Exception('the file '.$tmp.' can not write');

            }

        }

    }


    /**
     * 修改图片大小
     * @param $filename
     * @param $tmpname
     * @param $xmax
     * @param $ymax
     * @return false|\GdImage|resource
     */
    function resize_image($filename, $tmpname, $xmax, $ymax)
    {
        $ext = explode(".", $filename);
        $ext = $ext[count($ext)-1];

        if($ext == "jpg" || $ext == "jpeg")
            $im = imagecreatefromjpeg($tmpname);
        elseif($ext == "png")
            $im = imagecreatefrompng($tmpname);
        elseif($ext == "gif")
            $im = imagecreatefromgif($tmpname);
        elseif($ext == "webp")
            $im = imagecreatefromwebp($tmpname);

        $x = imagesx($im);
        $y = imagesy($im);



        if($x>$y){

            $sx = abs(($y-$x)/2);

            $sy = 0;

            $jtdw = $y;

            $jtdgd = $y;

        } else {

            $sy = abs(($x-$y)/2);

            $sx = 0;

            $jtdw = $x;

            $jtdgd = $x;

        }

        $im2 = imagecreatetruecolor($xmax, $ymax);
        imagecopyresized($im2, $im, 0, 0, $sx, $sy, floor($xmax), floor($ymax), $jtdw, $jtdgd);
        return imagewebp($im2,$filename);
    }
}

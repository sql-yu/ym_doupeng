<?php


namespace App\Admin\Controllers;


use App\Helper\ImageHelper;
use Dcat\Admin\Traits\HasUploadedFile;

class ImageController
{

    use HasUploadedFile;
    use ImageHelper;

    public function handle()
    {
        $disk = $this->disk('local');

        // 判断是否是删除文件请求
        if ($this->isDeleteRequest()) {
            // 删除文件并响应
            return $this->deleteFileAndResponse($disk);
        }

        // 获取上传的文件
        $file = $this->file();

        // 获取上传的字段名称
        $inputDir = request()->get('dir');
        $id = request()->get('data_id');
        if(!$id){
            $id = uniqid();
        }


        switch ($inputDir){
            case "game":
                $newName = '/image/'. $inputDir .'/'. $id . '.' . $file->getClientOriginalExtension();
                $newPath = $disk->path('/image/'. $inputDir);
//                $this->transform_image($file,'webp',$newPath);
//                $result = $this->resize_image($newPath,$newPath,300,300);
                $result = $file->move($newPath,$newName);
                return $result
                    ? $this->responseUploaded($newName, $disk->url($newName))
                    : $this->responseErrorMessage('文件上传失败');
            case "cate":
                $dir = '/image/'. $inputDir;
                $newName = $id . '.'. $file->getClientOriginalExtension();
                break;
            case "favicon":
                $dir = '/';
                $newName = 'favicon.ico';
                break;
            case "logo":
                $dir = '/';
                $newName = 'logo.png';
                break;
            case "bg":
                $dir = '/';
                $newName = 'bg02.png';
                break;
            case "loading":
                $dir = '/';
                $newName = 'loading.png';
                $newPath = $disk->path($newName);
//                $result = $this->transform_image($file,'webp',$newPath);
//                $result = $this->resize_image($newPath,$newPath,300,300);
//                return $result
//                    ? $this->responseUploaded($newName, $disk->url($newName))
//                    : $this->responseErrorMessage('文件上传失败');
                break;
            default:
                break;
        }

        $result = $disk->putFileAs($dir, $file, $newName);
        $path = "{$dir}/$newName";

        return $result
            ? $this->responseUploaded($path, $disk->url($path))
            : $this->responseErrorMessage('文件上传失败');
    }
}

<?php
namespace libs;
class Read {
    function smartReadFile( $filepath, $mimeType='application/octet-stream')
    {

        date_default_timezone_set('GMT');  //注意时区必须是GMT，否则可能产生错误缓存

        //$filepath=iconv("utf-8","gb2312",$filepath);
        if(!file_exists($filepath))
        {
            header ("HTTP/1.0 404 Not Found");
            return;
        }
        $size=filesize($filepath);

        $time=date('D, j M Y H:i:s e',filemtime($filepath)); //转为格林尼治时间,同时注意php中文件时间写入的函数是  touch

        $fm=@fopen($filepath,'rb'); //测试能否打开文
        if(!$fm)
        {
            header ("HTTP/1.0 505 Internal server error");
            return;
        }

        $stat = stat($filepath);
        $md5str = md5_file($filepath); //使用md5校验，更加精确
        $etag =  $md5str.'-'.sprintf('%x-%x-%x', $stat['ino'], $stat['size'], $stat['mtime'] * 1000000);

        if(isset($_SERVER['HTTP_IF_RANGE']) && (($_SERVER['HTTP_IF_RANGE'] == $etag) || (strtotime($_SERVER['HTTP_IF_RANGE']) >= $stat['mtime'])))
        {

            header('Etag: "' . $etag . '"');
            header('Last-Modified: ' . date('D, j M Y H:i:s e', $stat['mtime']));
            header('HTTP/1.0 304 Not Modified');
            return ;
        }

        if(isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $etag)
        {
            header('Etag: "' . $etag . '"');
            header('HTTP/1.0 304 Not Modified');
            return ;
        } elseif(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) &&strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $stat['mtime']) {
            header('Last-Modified: ' . date('D, j M Y H:i:s e', $stat['mtime']));
            header('HTTP/1.0 304 Not Modified');
            return;
        }

        $begin=0;
        $end=$size;

        if(isset($_SERVER['HTTP_RANGE']))
        { if(preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $_SERVER['HTTP_RANGE'], $matches))
        { $begin=intval($matches[0]);
            if(!empty($matches[1]))
                $end=intval($matches[1]);
        }
        }

        if($begin>0||$end<$size)
            header('HTTP/1.0 206 Partial Content');
        else
            header('HTTP/1.0 200 OK');

        header("Content-Type: $mimeType"); //指定文件minetype，//注意，部分浏览器mineType需要明确指定（如image/png）,否则不能下载
        header('Cache-Control: public, must-revalidate, max-age=0'); //控制client缓存，要求不缓存
        header('Pragma: no-cache');
        header('Accept-Ranges: bytes'); //表示浏览器接受bytes的断点续传
        header('Content-Length:'.($end-$begin)); //如果未指定长度，这以chunked编码传输文件到客户端
        header("Content-Range: bytes $begin-$end/$size");
        header("Content-Disposition: attachment; filename=".basename($filepath)."");  //文件下载
        header('Content-Description: File Transfer');//非标准头信息，可以不要
        header("Content-Transfer-Encoding: binary\n"); //非标准头信息，可以不要
        header("Last-Modified: $time"); //用于校验
        header('Etag: "' . $etag . '"');
        header('Connection: close');

        $cur=$begin;
        fseek($fm,$begin,0); //将指针定位到要读取的位置

        while(!feof($fm)&&$cur<$end&&(connection_status()==0))
        {
            echo fread($fm,min(1024*16,$end-$cur));
            $cur+=1024*16;
        }

        fclose($fm);

    }
}
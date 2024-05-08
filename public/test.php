<?php

function video_time($file) {
    $info = file_get_contents($file);
    var_dump($info);
}

$url = "https://cdn.tubia.com/media/videos/022020/55285da11f8a46b28cca7494ec474403_360_240.mp4";
$a = video_time($url);
var_dump($a);

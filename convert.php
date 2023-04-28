<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $url = $_POST['url'];
    $video_id = parse_video_id($url);
    $mp3_file = convert_video_to_mp3($video_id);
    download_mp3_file($mp3_file);
}

function parse_video_id($url) {
    $pattern = '/^.*(youtu.be\/|v\/|e\/|u\/\w+\/|embed\/|v=)([^#\&\?]*).*/';
    preg_match($pattern, $url, $matches);
    return $matches[2];
}

function convert_video_to_mp3($video_id) {
    $video_url = "https://www.youtube.com/watch?v=$video_id";
    $api_url = "https://www.youtube.com/get_video_info?video_id=$video_id&el=embedded&ps=default&eurl=&gl=US&hl=en";
    $video_info = file_get_contents($api_url);
    parse_str($video_info, $info);

    if ($info['status'] === 'fail') {
        die('Error: ' . $info['reason']);
    }

    $stream_map = explode(',', $info['url_encoded_fmt_stream_map']);
    $audio_stream_map = array_filter($stream_map, function($stream) {
        return strpos($stream, 'audio/') !== false;
    });
    $audio_stream = reset($audio_stream_map);
    parse_str($audio_stream, $stream_info);

    $audio_url = $stream_info['url'];
    $audio_data = file_get_contents($audio_url);
    $mp3_file = "$video_id.mp3";
    file_put_contents($mp3_file, $audio_data);

    return $mp3_file;
}

function download_mp3_file($mp3_file) {
    header('Content-Description: File Transfer');
    header('Content-Type: audio/mpeg');
    header('Content-Disposition: attachment; filename="' . basename($mp3_file) . '"');
    header('Content-Length: ' . filesize($mp3_file));
    readfile($mp3_file);
    exit;
}

?>

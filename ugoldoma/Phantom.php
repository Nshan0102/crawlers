<?php

class Phantom
  {
    public static function getContent($urls){
      {
        $options = [
          'https' => [
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'requestSettings' => [
              'resourceModifier' => [
                [
                  'regex'=>".*css.*",
                  'isBlacklisted'=>'true'
                ],
                'clearCache'=>'true',
                'loadImages'=>'false'
              ]
            ]
          ]
        ];

        $phantomAPI = [ 
            'ak-wx3q9-5jktm-1hg24-kmzjg-1wqdw',
            'ak-b0eh8-6md2c-5vs0b-kf8wq-qpn84',
            'ak-m6eey-2qjc8-2gzj2-b08pn-axhv7'
        ];
        
        $context = stream_context_create($options);
        
        $url = 'https://phantomjscloud.com/api/browser/v2/'.$phantomAPI[2].'/?request={url:"'.$urls.'",renderType:"html"}';
        $file = file_get_contents($url, false, $context);
        echo '---API [2]';

        if (empty($file)) {
            $url = 'https://phantomjscloud.com/api/browser/v2/'.$phantomAPI[3].'/?request={url:"'.$urls.'",renderType:"html"}';
            $file = file_get_contents($url, false, $context);
            echo '---API [3]';
        }

        if (empty($file)) {
            $url = 'https://phantomjscloud.com/api/browser/v2/'.$phantomAPI[4].'/?request={url:"'.$urls.'",renderType:"html"}';
            $file = file_get_contents($url, false, $context);
            echo '---API [4]';
        }

        if (empty($file)) {
            $url = 'https://phantomjscloud.com/api/browser/v2/'.$phantomAPI[5].'/?request={url:"'.$urls.'",renderType:"html"}';
            $file = file_get_contents($url, false, $context);
            echo '---API [5]';
        }

        echo '_____'.$urls.'<br><br>';
        $name = 'rendered.html';
        $w = fopen($name, "wb");
        fwrite($w,$file);
        fclose($w);
      }
    }
 }

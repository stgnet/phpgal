<?php

class PhpGalScannerClass
{
    public $headers = array();
    public $links = array();

    public function http_parse_headers($text)
    {
        $headers=array();
        $lines=explode("\n",$text);
        $headers['status_line']=array_shift($lines);
        foreach ($lines as $line)
        {
            $split=explode(': ',$line,2);
            if (!empty($split[1]))
                $headers[$split[0]]=$split[1];
        }
        return($headers);
    }
    public function http_parse_links($linklist)
    {
        $links=array();

        preg_match_all('|\<([^ ]*)\>;[ ]*rel=\"([^ ]*)\"|',$linklist,$matches,PREG_SET_ORDER);
        foreach ($matches as $match)
            if (!empty($match[1]) && !empty($match[2]))
                $links[$match[2]]=$match[1];
        return($links);
    }
    public function curl_get_json($url)
    {
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_USERAGENT,"stgnet/phpgal");
        curl_setopt($ch,CURLOPT_HEADER,1);
        $response=curl_exec($ch) or die('CURL ERROR: '.curl_error($ch));
        $info=curl_getinfo($ch);
        curl_close($ch);
        $header=substr($response,0,$info['header_size']);
        $this->headers=$this->http_parse_headers($header);
        $this->links=$this->http_parse_links($this->headers['Link']);
        $body=substr($response,-$info['download_content_length']);
        $json=json_decode($body);
        return($json);
    }

}

    $scanner=new PhpGalScannerClass();

    $test=$scanner->curl_get_json('https://api.github.com/search/repositories?q=language:php&sort=stars');

    print_r($scanner->links);
    print_r($scanner->headers);
    print_r($test);

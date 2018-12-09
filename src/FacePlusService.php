<?php

namespace Serrt\LaravelFace;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class FacePlusService
{
    protected $api_key;
    protected $api_secret;
    const TYPE_TOKEN = 'token';
    const TYPE_URL = 'url';
    const TYPE_FILE = 'file';
    const TYPE_BASE64 = 'base64';

    public function __construct(Array $config)
    {
        $this->api_key = isset($config['api_key'])?$config['api_key']:'';
        $this->api_secret = isset($config['api_secret'])?$config['api_secret']:'';
    }

    public function compare($file, $compare_file)
    {
        $result = ['code' => 200, 'message' => '', 'data' => []];
        $options = [];
        $file_type = $this->checkFile($file);
        $compare_file_type = self::checkFile($compare_file);

        if ($file_type !== $compare_file_type) {
            $result['code'] = 400;
            $result['message'] = '比对两种文件类型不一致, '.$file_type.' !== '.$compare_file_type;
            return $result;
        }

        if ($file_type === self::TYPE_BASE64) {
            $content1 = str_replace($result[1], '', $file);
            $content2 = str_replace($result[1], '', $compare_file);
            $options = [
                'form_params' => [
                    'api_key' => $this->api_key,
                    'api_secret' => $this->api_secret,
                    'image_base64_1' => $content1,
                    'image_base64_2' => $content2,
                ],
            ];
        } else if ($file_type === self::TYPE_FILE) {
            $options = [
                'multipart' => [
                    ['name' => 'api_key', 'contents' => $this->api_key],
                    ['name' => 'api_secret', 'contents' => $this->api_secret],
                    ['name' => 'image_file1', 'contents' => fopen($file->path(), 'r')],
                    ['name' => 'image_file2', 'contents' => fopen($compare_file->path(), 'r')]
                ]
            ];
        } else if ($file_type === self::TYPE_URL) {
            $options = [
                'form_params' => [
                    'api_key' => $this->api_key,
                    'api_secret' => $this->api_secret,
                    'image_url1' => $file,
                    'image_url2' => $compare_file,
                ],
            ];
        } else if ($file_type === self::TYPE_TOKEN) {
            $options = [
                'form_params' => [
                    'api_key' => $this->api_key,
                    'api_secret' => $this->api_secret,
                    'face_token1' => $file,
                    'face_token2' => $compare_file,
                ],
            ];
        }

        try {
            $result = json_decode($this->client()->post('compare', $options)->getBody()->getContents(), true);
        } catch (ClientException $e) {
            $result['code'] = 400;
            $result['error'] = $e->getMessage();
        }

        return $result;
    }

    protected function client()
    {
        $client = new Client(['base_uri' => 'https://api-cn.faceplusplus.com/facepp/v3']);

        return $client;
    }

    public function checkFile($file)
    {
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $file, $result)) {
            return self::TYPE_BASE64;
        } else if (gettype($file) == 'object') {
            return self::TYPE_FILE;
        } else if (preg_match('/^https?:\/\//i', $file) > 0) {
            return self::TYPE_URL;
        }
        return self::TYPE_TOKEN;
    }
}
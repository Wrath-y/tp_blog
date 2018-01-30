<?php
namespace app\controller;
/**
 * 
 */
class CurlController
{

    //保存cookie
    protected $cookieF = '';



    /**
     * 发送请求 
     * @param string $url 请求地址
     * @param string $cookie 用户的cookie
     * @param string $post_data 用户的账号密码
     * @param boolean $https 判断是否为https
     * @return json
     */
    private function curl_request($url,$header='',$cookie='',$post_data='',$https=false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, $header);
        if (!empty($cookie)) {
            //创建系统不存在的目录来存放缓存文件
            $cookieFile = tempnam('/cookie/', 'cookies');
            //将cookie文件保存进变量
            $this->cookieF = $cookieFile;
            //储存cookie进缓存文件中
            curl_setopt($ch,CURLOPT_COOKIEJAR, $cookieFile);
            //读取cookie
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieF);
        }
        if (!empty($post_data)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            //使用http_build_query实现更好的兼容性，更小的请求数据包
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        }
        if ($https=true) {
            //不验证证书
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            //不验证主机名
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

    /**
     * 获取网易云信息
     * @param string $vali 判断获取个人信息还是歌单信息
     * @return json
     */
    public function music($vali='')
    {
        $url = 'http://music.163.com/api/playlist/detail?id=139310909'; //id为歌单id
        $res = $this->curl_request($url,$header='',$cookie='',$post_data='');
        $res_arr = json_decode($res, true);
        if (!empty($vali)) {
            $res = $res_arr['result']['creator'];
        } else {
            $res = $res_arr['result']['tracks'];
        }
        return json($res);
    }

    /**
     * 获取psn信息
     * @param string $vali 判断获取个人信息还是游戏信息
     * @return json
     */
    public function psn($vali)
    {
        
    }
}
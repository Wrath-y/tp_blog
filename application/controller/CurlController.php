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
    private function curl_request($url,$header,$cookie='',$post_data='',$https=false)
    {
        $ch = curl_init();
        $header = [];
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
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
     * 保存网易云信息
     * @param array $res_arr 网易云信息
     */
    public function music_save()
    {
        $url = 'http://music.163.com/api/playlist/detail?id=139310909'; //id为歌单id
        $res = $this->curl_request($url,$header='',$cookie='',$post_data='');
        $res_arr = json_decode($res, true);
        $result['creator'] = $res_arr['result']['creator'];
        $length = count($res_arr['result']['tracks']);
        $file = ROOT_PATH . 'public' . DS . 'static' . DS . 'json' . DS .'music.json';
        for ($i=0; $i < $length; $i++) {
            $result['tracks'][$i]['index'] = $i+1;
            $result['tracks'][$i]['name'] = $res_arr['result']['tracks'][$i]['name'];
            $result['tracks'][$i]['id'] = $res_arr['result']['tracks'][$i]['id'];
            if (!empty($res_arr['result']['tracks'][$i]['alias'])) {
                $result['tracks'][$i]['alias'] = $res_arr['result']['tracks'][$i]['alias'];
            } else {
                $result['tracks'][$i]['alias'][0] = '未知';
            }
            $result['tracks'][$i]['artists'] = $res_arr['result']['tracks'][$i]['artists'][0]['name'];
            $result['tracks'][$i]['artists_id'] = $res_arr['result']['tracks'][$i]['artists'][0]['id'];
        }
        $json = json_encode($result);
        file_put_contents($file, $json);
        return;
    }
    /**
     * 获取网易云信息
     * @param array $res_arr 网易云信息
     * @return json
     */
    public function music_get()
    {
        $file = ROOT_PATH . 'public' . DS . 'static' . DS . 'json' . DS .'music.json';
        $res = file_get_contents($file);
        $res_arr = json_decode($res, true);
        return json($res_arr);
    }
    /**
     * 保存npsn信息
     * @param string $time unix时间戳/ms
     */
    public function psn_save()
    {
        $time = explode (" ", microtime () );   
        $time = $time [1] . ($time [0] * 1000);   
        $time2 = explode ( ".", $time );   
        $time = $time2 [0];  //unix时间戳/ms
        $url = "https://io.playstation.com/playstation/psn/profile/public/userData?onlineId=Gilgamesh_y&_=".$time;
        $header[] = "Referer:https://www.playstation.com/en-us/my/public-trophies/";
        $header[] = "User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.140 Safari/537.36";
        $user_data = $this->curl_request($url,$header,$cookie='',$post_data='',$https=true);
        $url = "https://io.playstation.com/playstation/psn/public/trophies/?onlineId=Gilgamesh_y&_=".$time;
        $online = $this->curl_request($url,$header,$cookie='',$post_data='',$https=true);
        $user_data = json_decode($user_data, true);
        $online = json_decode($online, true);
        $res['userData'] = $user_data;
        $res['online'] = $online;
        $json = json_encode($res);
        dump($res);
        exit();
        $file = ROOT_PATH . 'public' . DS . 'static' . DS . 'json' . DS .'psn.json';
        file_put_contents($file, $json);
    }
    /**
     * 获取psn信息
     * @return json
     */
    public function psn_get()
    {
        $file = ROOT_PATH . 'public' . DS . 'static' . DS . 'json' . DS .'psn.json';
        $res = file_get_contents($file);
        $res_arr = json_decode($res, true);
        return json($res_arr);
    }
}
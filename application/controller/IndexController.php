<?php
namespace app\controller;

class IndexController
{
    public function index()
    {
        return view();
    }
    public function article()
    {
    	return view();
    }
    public function music()
    {
    	return view();
    }
    public function psn()
    {
        return view();
    }
    public function money($user)
    {
        $data = db('money')->where('user',$user)->order('create_time desc')->select();
        $zAll = array_sum($data['z']);
        $gaAll = array_sum($data['ga']);
        return json_encode([
            $user.'总共支出:'=>$data['all'],
            'z占了其中:'=>$zAll,
            'ga占了其中:'=>$gaAll
        ]);
    }
}

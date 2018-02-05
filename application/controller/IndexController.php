<?php
namespace app\controller;

use think\Controller;
use think\Request;
use app\model\Article;

class IndexController extends Controller
{
    public function index()
    {
        $list = Article::article_res(1);
        $this->assign('list', $list);
        return view();
    }
    public function article()
    {
        $id = Request::instance()->param('id');
        $article = Article::article_res(1,$id)[0];
        $this->assign('article', $article);
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

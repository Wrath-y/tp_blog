<?php
namespace app\controller;

use think\Controller;
use think\Request;
use app\model\Article;
use think\Db;

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
    public function link()
    {
        $data = Db::table('harem')->select();
        $this->assign('link', $data);
        return view();
    }
    public function message()
    {
        $res = Article::article_res('',1)[0];
        $this->assign('article', $res);
        return view('article');
    }
    public function about()
    {
        $res = Article::article_res('',2)[0];
        $this->assign('article', $res);
        return view('article');
    }
    //评论
    public function reply_submit()
    {
        $data = Request::instance()->param();
        $data['create_time'] = time();
        $res = Db::table('reply')->insert($data);
        return json([
            'code' => 2000,
            'msg' => '提交成功'
        ]);
    }
    public function reply_get($id)
    {
        $data = Db::table('reply')->where('article_id', $id)->order('id desc')->paginate(10);
        return json([
            'code' => 2000,
            'msg' => $data
        ]);
    }
}

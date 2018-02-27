<?php
namespace app\controller;

use think\Controller;
use think\Request;
use app\model\Article;
use think\Db;
use think\Session;

class IndexController extends Controller
{
    public function login() {
        if (Request::instance()->isAjax()) {
            $param = Request::instance()->param();
            $result = Db::table('user')->where('username', $param['username'])->value('passwd');
            if ($result) {
                    if ($result === md5($param['passwd']) ) {
                        Session::set('user',$param['username']);
                        return json([
                            'code' => 2000,
                            'msg' => "登陆成功"
                        ]);
                } else {
                    return json([
                        'code' => 2004,
                        'msg' => "密码错误"
                    ]);
                }
            } else {
                return json([
                    'code' => 2005,
                    'msg' => "用户名错误"
                ]);  
            }
        }
        return view('admin/login');
    }
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
        Article::where('id', $id)->setInc('hits');
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

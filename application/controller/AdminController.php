<?php
namespace app\controller;
use think\Request;
use think\Controller;
use app\model\Article;

class AdminController extends Controller
{
    public function index()
    {
        return view();
    }
    public function welcome()
    {
        $request = Request::instance();
        $data = [
            'sip' => $_SERVER['SERVER_ADDR'],
            'domain' => $request->domain(),
            'port' => $_SERVER["SERVER_PORT"],
            'os' => PHP_OS,
            'date' => date("Y-m-d G:i:s"),
            'time' => explode(",", exec('uptime'))[0],
            'm' => @get_cfg_var("mysql.max_links")==-1 ? "不限" : @get_cfg_var("mysql.max_links"),
        ];
        $this->assign('data', $data);
        return view();
    }
    public function article($id='')
    {
        if (!empty($id)) {
            //渲染文章单页
            $article = Article::get($id);
            $this->assign('article', $article);
            return view('admin/article');
        } else {
            //渲染所有文章
            $list = Article::list();
            $this->assign('list', $list);
            return view('admin/article_list');
        }
    }
    public function article_status($id,$action)
    {
        $res = Article::change_article_status($id,$action);
        return json([
            'code'=>'2000',
            'msg'=>$res
        ]);
    }
    public function article_category()
    {
        $action = Request::instance()->param('action');
        //渲染添加分类框
        if ($action === 'add') {
            return view('admin/article_category_edit');
        }
        if ($action === 'edit') {
            $id = Request::instance()->param('id');
            $name = db('article_category')->where('id', $id)->field('name')->find();
            return view('admin/article_category_edit',[
                'name' => $name
            ]);
        }
        if (Request::instance()->isAjax()) {
            $name = Request::instance()->param('name');
            $res = Article::add_category($name);
            if ($res != 0) {
                return json([
                    'code' => 2000,
                    'msg' => '添加成功'
                ]);
            } else {
                return json([
                    'code' => 2001,
                    'msg' => '添加失败'
                ]);
            }

        }
        else {
            $list = Article::category_list();
            $this->assign('list', $list);
            return view();
        }
    }
    public function article_edit()
    {
        if (Request::instance()->param('id')) {
            $id = Request::instance()->param('id');
            $article = Article::get($id);
            $category = Article::get_category($id);
            $this->assign([
                'article' => $article,
                'category' => $category['name']
            ]);
        } else {
            $id = '';
        }
        if (Request::instance()->isAjax()) {
            $data['article_title'] = Request::instance()->param('title');
            $data['article_con'] = Request::instance()->param('editormd-markdown-doc');
            $data['category'] = Request::instance()->param('category');
            if (Request::instance()->param('id')) {
                $id = Request::instance()->param('id');
            } else {
                $id = '';
            }
            if (!empty($id)) {
                $res = Article::article_edit($data, $id);
                    if ($res != 0) {
                        return json([
                            'code' => 2000,
                            'msg' => '修改成功'
                        ]);
                    } else {
                        return json([
                            'code' => 2002,
                            'msg' => '修改失败'
                        ]);
                    }
            } else {
                $res = Article::article_edit($data);
                return json($res);
                if ($res != 0) {
                    return json([
                        'code' => 2000,
                        'msg' => '发布成功'
                    ]);
                } else {
                    return json([
                        'code' => 2002,
                        'msg' => '发布失败'
                    ]);
                }
            }
        }
    	return view();
    }
    public function del()
    {
        $id = Request::instance()->param('id');
        $table = Request::instance()->param('table');
        if ($table === 'article') {
            $res = Article::del($id, $table);
        }
        if ($table === 'article_category') {
            $res = Article::del($id, $table);
        }
        if ($res != 0) {
            return json([
                'code' => 2000,
                'msg' => '删除成功'
            ]);
        } else {
            return json([
                'code' => 2003,
                'msg' => '删除失败'
            ]);
        }
    }
}

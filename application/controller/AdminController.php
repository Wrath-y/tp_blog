<?php
namespace app\controller;
use think\Request;
use think\Controller;
use think\Db;
use app\model\Article;
use app\model\Reply;
use app\service\Action;
use think\Session;
use qiniu\Qiniu;

class AdminController extends Controller
{
    public function _initialize()
    {
        if (!Session::has('user')) {
            return $this->error('请登录','/login');
        }
    }
    //注销登陆
    public function unsetse()
    {
        Session::delete('user');
        die("<script>location.href='/';</script>");
    }
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
            $article = Article::article_res('',$id)[0];
            $this->assign('article', $article);
            return view('admin/article');
        } else {
            //渲染所有文章
            $list = Article::article_res();
            $this->assign('list', $list);
            return view('admin/article_list');
        }
    }
    public function article_status()
    {
        $id = Request::instance()->param('id');
        $action = Request::instance()->param('action');
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
            $name = Db::table('article_category')->where('id', $id)->field('name')->find();
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
                'category' => $category
            ]);
        } else {
            $category = Article::get_category();
            $this->assign('category', $category);
        }
        if (Request::instance()->isAjax()) {
            $data['article_title'] = Request::instance()->param('title');
            $data['article_con'] = Request::instance()->param('article_con');
            $data['category'] = Request::instance()->param('category');
            $data['article_html'] = Request::instance()->param('html');
            $data['create_time'] = time();
            $data['update_time'] = time();
            preg_match('/[\s\S]*?<img src="(.+?)"[\s\S]*?/i', $data['article_html'], $img_src);
            if ($img_src) {
                $data['img'] = $img_src[1];
            }
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
    public function get_img_token()
    {
        $token = (new Qiniu())->get_img_token();
        return $token;
    }
    public function del()
    {
        $id = Request::instance()->param('id');
        $table = Request::instance()->param('table');
        if ($table === 'article') {
            $res = Action::del($id, $table);
        }
        if ($table === 'article_category') {
            $res = Action::del($id, $table);
        }
        if ($table === 'reply') {
            $res = Action::del($id, $table);
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
    /**
    * @param int $guid  文件序列号
    */
    public function imgUpload()
    {
        $guid = Request::instance()->param('guid');
        $file = Request::instance()->file('file');
        if($file){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'static'. DS . 'upload' . DS . 'images');
            if($info){
                // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
                $url = '/static/upload/images/'.$info->getSaveName();
                return json([
                    'code' => 1,
                    'msg' => '上传成功',
                    'url' => $url
                ]);
            }else{
                // 上传失败获取错误信息
                $url = $file->getError();
            }
        }
        
    }
    /**
    * 图片管理
    */
    public function picture()
    {
        $domain = Request::instance()->domain();
        $dir = ROOT_PATH . 'public' . DS . 'static' . DS . 'upload' . DS . 'images' . DS;
        /**
        * scandir($dir)
        * array(3) {
        *      [0] => string(1) "."
        *      [1] => string(2) ".."
        *      [2] => string(8) "20180201"
        *    }
        */
        $dir_nums = scandir($dir);
        for ($i=2; $i < count($dir_nums); $i++) {
            $dir_[$i - 2] = $dir . $dir_nums[$i] . DS;
            $picture[$i - 2] = scandir($dir_[$i - 2])[2];
            $true_dir[$i - 2]['dir'] = DS . 'static' . DS . 'upload' . DS . 'images' . DS . $dir_nums[$i] . DS . $picture[$i - 2];
        }
        $this->assign('true_dir', $true_dir);
        $this->assign('nums', count($dir_nums));
        return view();
    }
    /**
    * 评论管理
    */
    public function reply()
    {
        $res = Reply::reply_res();
        $this->assign('list', $res);
        return view();
    }
    /**
    * 友情链接管理
    */
    public function link()
    {
        $instance = Request::instance();
        $action = $instance->param('action');
        if ($action === 'add') {
            return view('admin/link_edit');
        }
        if ($action === 'edit') {
            $id = $instance->param('id');
            $data = Db::table('harem')->where('id', $id)->find();
            return view('admin/link_edit',[
                'name' => $data['name'],
                'email' => $data['email'],
                'url' => $data['url']
            ]);
        }
        if ($instance->isAjax()) {
            $data = $instance->param();
            $data['create_time'] = time();
            $res = Db::table('harem')->insert($data);
            if ($res) {
                return json([
                    'code' => 2000,
                    'msg' => '添加成功'
                ]);
            }
        }
        $res = Db::table('harem')->paginate(15);
        $this->assign('list', $res);
        return view();
    }
}

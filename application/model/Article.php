<?php
namespace app\model;
use think\Model;
use think\Db;

/**
* 
*/
class Article extends Model
{
	public static function list($status='')
	{
		if (!empty($status)) {
			$article = Db::table('article')->where('status',1)->order('id desc')->buildSql();
		} else {
			$article = Db::table('article')->order('id desc')->buildSql();
		}
		$res = Db::table('article_category_relation')
			->alias('acr')
			->field('a.*,ac.name')
			->join([$article=>'a'],'acr.article_id = a.id')
			->join('article_category ac', 'acr.category_id = ac.id')
			->paginate(15);
		return $res;
	}
	public static function category_list()
	{
		$res = Db::table('article_category')->order('id desc')->paginate(15);
		return $res;
	}
	public static function add_category($name)
	{
		$res = Db::table('article_category')->insert(['name'=>$name,'update_time'=>time()]);
		return $res;
	}
	//发布或下架文章
	public static function change_article_status($id, $action)
	{
		if ($action === 'active') {
            $res = Db::table('article')->where('id', $id)->setField('status', 1);
        }
        else if ($action === 'hid') {
            $res = Db::table('article')->where('id', $id)->setField('status', 0);
        }
        return $res;
	}
	//发布或编辑文章
	public static function article_edit($data='',$id=false)
	{
		//获取分类id
		$category_id = Db::table('article_category')->where('name', $data['category'])->field('id')->find()['id'];
		unset($data['category']);
		if (!empty($id)) {
			$data['update_time'] = time();
			(new self())->article_category_edit($category_id,$id);
			$res = Db::table('article')->where('id', $id)->update($data);
		} else {
			$data['create_time'] = time();
			$data['update_time'] = time();
			$res = Db::table('article')->insert($data);
			if ($res != 0) {
				$relation_data['category_id'] = $category_id;
				$relation_data['article_id'] = Db::table('article')->where('article_title', $data['article_title'])->field('id')->find()['id'];
				$res = (new self())->article_category_edit($relation_data);
			}
		}
		return $res;
	}
	//编辑文章时获取分类
	public static function get_category($id)
	{
		$category_id = Db::table('article_category_relation')->where('article_id', $id)->field('category_id')->find()['category_id'];
		$res = Db::table('article_category')->where('id', $category_id)->find();
		return $res;
	}
	/**
	* @param int $id 文章id
	* @return int $res 成功插入关联表的条数
	*/
	public function article_category_edit($data='',$id=false)
	{
		if ($id) {
			$res = Db::table('article_category_relation')->where('article_id', $id)->setField('category_id', $data);
		} else {
			$res = Db::table('article_category_relation')->insert($data);
		}
		return $res;
	}
	/**
	* @param int $id 删除对象的id
	* @param int $table 删除对象所在表
	*/
	public static function del($id,$table)
	{
		if ($table === 'article') {
			$res = Db::table('article')->where('id', $id)->delete();
			if ($res != 0) {
				$res = Db::table('article_category_relation')->where('article_id', $id)->delete();
			}
		} else {
			$res = Db::table($table)->where('id', $id)->delete();
		}
		return $res;
	}
}
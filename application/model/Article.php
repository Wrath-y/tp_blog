<?php
namespace app\model;
use think\Model;
use think\Db;

/**
* 
*/
class Article extends Model
{
	public static function article_res($status='',$id='')
	{
		$sql = Db::table('article')
				->alias('a')
				->field('a.*,ac.name')
				->join('article_category ac','ac.id = a.category','LEFT');
		//前台显示
		if (!empty($status)) {
			if (!empty($id)) {
				$res = $sql->where('a.status',$status)->where('a.id', $id)->select();

			} else {
				$res = $sql->where('a.status',$status)->order('id DESC')->paginate(3);
			}
		} 
		//后台显示
		else {
			if (!empty($id)) {
				$res = $sql->where('a.id', $id)->select();
			} else {
				$res = $sql->paginate(15);
			}	
		}
		
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
	public static function article_edit($data,$id=false)
	{
		if (!empty($id)) {
			$data['update_time'] = time();
			$res = Db::table('article')->where('id', $id)->update($data);
		} else {
			$res = Db::table('article')->insert($data);
		}
		return $res;
	}
	//获取文章分类
	public static function get_category($id=false)
	{
		if ($id) {
			$category_id = Db::table('article')->where('id', $id)->value('category');
			$res = Db::table('article_category')->where('id', $category_id)->select();
		} else {
			$res = Db::table('article_category')->select();
		}
		
		return $res;
	}
	/**
	* @param int $id 删除对象的id
	* @param int $table 删除对象所在表
	*/
	public static function del($id,$table)
	{
		$res = Db::table($table)->where('id', $id)->delete();
		return $res;
	}
}
<?php
namespace app\model;
use think\Model;
use think\Db;

/**
* 
*/
class Reply extends Model
{
	public static function reply_res($id='')
	{
		$sql = Db::table('reply')
				->alias('r')
				->field('r.*,a.article_title')
				->join('article a','r.article_id=a.id','LEFT');
		if (empty($id)) {
			$res = $sql->order('create_time desc')->paginate(15);
		} else {
			$res = $sql->where('a.id', $id)->order('create_time desc')->paginate(15);
		}
		return $res;
	}
}
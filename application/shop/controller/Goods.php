<?php
namespace app\shop\controller;
use think\Request;
use think\Db;
/**
 * 商品
 */
class Goods extends Base
{
	
	public function detail(Request $request)
	{
		$id = (int)$request->param('id');
		if(empty($id)){
			return $this->fetch('index@public/tips', ['type' => 'little', 'code' => '商品不存在']);
		}

		$where = [
			'g.id' => $id,
			'g.status' => 1,
			'g.is_sold' => 1
		];

		$info = Db::table('shop_goods')->alias('g')->field('g.*,s.price,s.market_price')->join('(SELECT a.*,b.market_price,b.status,is_sold FROM (SELECT goods_id,MIN(price) as price FROM shop_goods_sku WHERE status = 1 AND is_sold = 1 GROUP BY goods_id)a,shop_goods_sku b WHERE a.price=b.price AND a.goods_id=b.goods_id) s', 'g.id=s.goods_id')->where($where)->find();

		if(empty($info)){
			return $this->fetch('index@public/tips', ['type' => 'little', 'code' => '商品不存在']);
		}

		$info['main_img'] = explode(',', $info['goods_imgs']);
		$info['imgcount'] = count($info['main_img']);

		$info['sold'] = Db::table('shop_order_detail')->alias('a')->leftjoin('shop_order b', 'a.order_no=b.order_no')->where(['a.user_id' => $this->userId, 'a.goods_id' => $id])->where('b.order_status', 'IN', '1,2,3,5,6')->count();

		$info['collectd'] = Db::table('user_goods_collection')->where(['user_id' => $this->userId, 'goods_id' => $id])->find();
		// dump($info)
		$this->assign('info', $info);
		return $this->fetch();
	}
}
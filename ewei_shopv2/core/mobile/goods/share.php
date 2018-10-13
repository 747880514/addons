<?php
if (!(defined('IN_IA'))) {
	exit('Access Denied');
}

/**
 * 分享图片生成
 */

class Share_EweiShopV2Page extends MobilePage
{
	public function main()
	{
		global $_W;
		global $_GPC;

		$mid 			= intval($_GPC['mid']);
		$name 			= strval($_GPC['nickname']);
		$avatar 		= strval($_GPC['avatar']);
		$goods_id 		= intval($_GPC['goods_id']);
		$goods_title 	= strval($_GPC['goods_title']);	//26+18
		$goods_thumb 	= strval($_GPC['goods_thumb']);
		$price 			= sprintf("%.2f", floatval($_GPC['goods_marketprice']));
		$oldprice 		= sprintf("%.2f", floatval($_GPC['goods_productprice']));

		//标题处理
		$goods_pinkage 	= strval($_GPC['goods_pinkage']);
		if($goods_pinkage == 1)
		{
			$goods_title = "【包邮】" . $goods_title;
		}
		if( mb_strlen($goods_title) < 24 )
		{
			$title = $goods_title;
		}
		if( mb_strlen($goods_title) >= 24 )
		{
			$title = mb_substr($goods_title, 0, 24) . "\n" . mb_substr($goods_title, 25, 42);
		}
		if( mb_strlen($goods_title) > 42 )
		{
			$title .= '......';
		}

		$youhuiquanstr 	= $_GPC['ticket'];	//获取优惠券金额
		preg_match_all('/\d+/',$youhuiquanstr,$youhuiquan);
		$youhuiquan = $youhuiquan[0][0];

		$org 			= $_W['siteroot'];	//获取主域名
		$me 			= "我是 \n我为 花蒜 代言";
		$qrtext 		= "长按图片识别二维码购买";

		$mobile_share_path 		= './../addons/ewei_shopv2/static/images/mobile_share'; //分享资源图片文件
		$mobile_share_save_path = './../addons/ewei_shopv2/data/mobile_share'; //生成的分享文件
		$phpqrcode_path 		= './../addons/ewei_shopv2/vendor/phpqrcode';	//QR核心文件
		$ttf_path 				= $phpqrcode_path . '/fonts/simhei.ttf';	//字体文件

		$path_1 		= $mobile_share_path . "/main.jpg";
		$path_2 		= $mobile_share_path . "/logo.png";
		$path_3 		= $goods_thumb;	//商品图片
		$path_4 		= $avatar;	//会员头像
		$path_6 		= $mobile_share_path . "/zoom.png";	//头像圆形处理
		$path_7 		= $mobile_share_path . "/del.png";	//删除线

		//会员qr保存的文件名称
		$qrfilename = 'share_' . $mid . '_' . $goods_id . '_' . '.png';

		//会员分享图片保存的文件名称
		$filename   = 'share_' . $mid . '_' . $goods_id . '_' . $price . '_' . $youhuiquan . '.jpg';

		//未登录，返回错误状态，跳转到登录页
		if( $mid <= 0 )
		{
			show_json(0);
		}

		//查看是否存在分享文件
		if( is_file($mobile_share_save_path . "/share_img/" . $filename) )
		{
			//返回路径
			$share_img_path = $org . 'addons/ewei_shopv2/data/mobile_share/share_img/' . $filename;

			show_json(1, array('mid' => $mid, 'goods_id' => $goods_id, 'type'=>'old', 'share_img_path'=>$share_img_path));

			exit;
		}

		//生成分享二维码
		if( !is_file($mobile_share_save_path . "/qrcode/" . $qrfilename) )
		{
			//引入核心库文件
			include $phpqrcode_path . "/phpqrcode/phpqrcode.php";
			$errorLevel = "L";
			$size = "6";
			$url = $org . "/app/index.php?i=2&c=entry&m=ewei_shopv2&do=mobile&r=goods.detail&id=".$goods_id."&mid=".$mid;
			QRcode::png($url, $mobile_share_save_path . '/qrcode/' . $qrfilename, $errorLevel, $size);
		}
		$path_5 = $mobile_share_save_path . "/qrcode/" . $qrfilename;	//带会员mid参数的专属二维码

		//固定图层
		$image_1 = imagecreatefromjpeg($path_1);
		$image_2 = imagecreatefrompng($path_2);
		$image_6 = imagecreatefrompng($path_6);
		$image_7 = imagecreatefrompng($path_7);

		//提取动态图层后缀
		$ext3 = explode(".",$path_3);
		$ext4 = explode(".",$path_4);
		$ext5 = explode(".",$path_5);

		//根据后缀动态设定属性
		$image_3 = end($ext3) == 'png' ? imagecreatefrompng($path_3) : imagecreatefromjpeg($path_3);
		$image_4 = end($ext4) == 'png' ? imagecreatefrompng($path_4) : imagecreatefromjpeg($path_4);
		$image_5 = end($ext5) == 'png' ? imagecreatefrompng($path_5) : imagecreatefromjpeg($path_5);

		//创建一个画布
		$image = imageCreatetruecolor(750,1333);
		$color = imagecolorallocate($image, 255, 255, 255);
		imagefill($image, 0, 0, $color);
		imageColorTransparent($image, $color);

		//图片合成
		imagecopyresampled($image,$image_1,0,0,0,0,750,1333,imagesx($image_1),imagesy($image_1));
		imagecopyresampled($image,$image_2,330,5,0,0,90,90,imagesx($image_2),imagesy($image_2));
		imagecopyresampled($image,$image_3,50,265,0,0,650,650,imagesx($image_3),imagesy($image_3));
		imagecopyresampled($image,$image_4,40,1190,0,0,100,100,imagesx($image_4),imagesy($image_4));
		imagecopyresampled($image,$image_5,470,1100,0,0,180,180,imagesx($image_5),imagesy($image_5));
		imagecopyresampled($image,$image_6,40,1190,0,0,100,100,imagesx($image_6),imagesy($image_6));
		if ($oldprice > 0)
		{
			if($price < 100)
			{
				imagecopyresampled($image,$image_7,210,1013,0,0,95,1,imagesx($image_7),imagesy($image_7));
			}
			else
			{
				imagecopyresampled($image,$image_7,230,1013,0,0,95,1,imagesx($image_7),imagesy($image_7));
			}
		}


		//文字合成
		$black = imagecolorallocate($image,  0, 0, 0);//文字颜色
		imagettftext($image, 22, 0, 16, 190, $black, $ttf_path, $title);// 设置中文文字

		$black = imagecolorallocate($image,  255, 85, 85);//文字颜色
		imagettftext($image, 34, 0, 40, 1020, $black, $ttf_path, '￥'.$price);// 设置中文文字

		if($oldprice > 0)
		{
			$black = imagecolorallocate($image,  174, 174, 174);//文字颜色
			if($price < 100)
			{
				imagettftext($image, 18, 0, 210, 1020, $black, $ttf_path, '￥'.$oldprice);// 设置中文文字
			}
			else
			{
				imagettftext($image, 18, 0, 230, 1020, $black, $ttf_path, '￥'.$oldprice);// 设置中文文字
			}
		}

		$black = imagecolorallocate($image,  255, 255, 255);//文字颜色
		imagettftext($image, 20, 0, 195, 1100, $black, $ttf_path, $youhuiquan);// 设置中文文字

		$black = imagecolorallocate($image,  0, 0, 0);//文字颜色
		imagettftext($image, 20, 0, 155, 1235, $black, $ttf_path, $me);// 设置中文文字

		$black = imagecolorallocate($image,  255, 85, 85);//文字颜色
		imagettftext($image, 24, 0, 220, 1235, $black, $ttf_path, $name);// 设置中文文字

		$black = imagecolorallocate($image,  196, 196, 196);//文字颜色
		imagettftext($image, 16, 0, 445, 1310, $black, $ttf_path, $qrtext);// 设置中文文字

		//将画布保存到指定的gif文件  会员id_商品id_商品价格.jpg
		imagejpeg($image, $mobile_share_save_path . "/share_img/" . $filename);

		//返回路径
		$share_img_path = $org . 'addons/ewei_shopv2/data/mobile_share/share_img/' . $filename;

		show_json(1, array('mid' => $mid, 'goods_id' => $goods_id, 'type'=>'new', 'share_img_path'=>$share_img_path));
	}

	public function main2()
	{
		global $_W;
		global $_GPC;

		$mid 			= intval($_GPC['mid']);
		$name 			= strval($_GPC['nickname']).'为您推荐';
		$avatar 		= strval($_GPC['avatar']);
		$goods_id 		= intval($_GPC['goods_id']);
		$goods_title 	= strval($_GPC['goods_title']);	//8+7
		$goods_thumb 	= strval($_GPC['goods_thumb']);
		$price 			= sprintf("%.2f", floatval($_GPC['goods_marketprice']));
		$oldprice 		= sprintf("%.2f", floatval($_GPC['goods_productprice']));

		//标题处理
		if( mb_strlen($goods_title) < 18 )
		{
			if(mb_strlen($goods_title) <= 10)
			{
				//0-10
				$title = $goods_title;
			}
			else
			{
				//10-18
				$title = mb_substr($goods_title, 0, 10) . "\n" . mb_substr($goods_title, 10, mb_strlen($goods_title));
			}
		}
		if( mb_strlen($goods_title) >= 18 )
		{
			$title = mb_substr($goods_title, 0, 10) . "\n" . mb_substr($goods_title, 10, 8);
		}
		if( mb_strlen($goods_title) > 18 )
		{
			$title .= '...';
		}

		$org 			= $_W['siteroot'];	//获取主域名
		$mobile_share_path 		= './../addons/ewei_shopv2/static/images/mobile_share'; //分享资源图片文件
		$mobile_share_save_path = './../addons/ewei_shopv2/data/mobile_share'; //生成的分享文件
		$phpqrcode_path 		= './../addons/ewei_shopv2/vendor/phpqrcode';	//QR核心文件
		$ttf_path 				= $phpqrcode_path . '/fonts/simhei.ttf';	//字体文件
		$ttf_path_heijian 		= $phpqrcode_path . '/fonts/heijian.ttf';	//字体文件

		$path_1 		= $mobile_share_path . "/main2.jpg";
		$path_2 		= $mobile_share_path . "/vip2.jpg";
		$path_3 		= $goods_thumb;	//商品图片
		$path_4 		= $avatar;	//会员头像
		$path_6 		= $mobile_share_path . "/zoom.png";	//头像圆形处理
		$path_7 		= $mobile_share_path . "/vip2.png";	//vip

		//会员qr保存的文件名称
		$qrfilename = 'share_' . $mid . '_' . $goods_id . '_' . '.png';

		//会员分享图片保存的文件名称
		$filename   = 'share_' . $mid . '_' . $goods_id . '_' . $price . '.jpg';

		//未登录，返回错误状态，跳转到登录页
		if( $mid <= 0 )
		{
			show_json(0);
		}

		//删除文件

		//查看是否存在分享文件
		if( is_file($mobile_share_save_path . "/share_img/" . $filename) )
		{
			//返回路径
			$share_img_path = $org . 'addons/ewei_shopv2/data/mobile_share/share_img/' . $filename;

			show_json(1, array('mid' => $mid, 'goods_id' => $goods_id, 'type'=>'old', 'share_img_path'=>$share_img_path));

			exit;
		}

		//生成分享二维码
		if( !is_file($mobile_share_save_path . "/qrcode/" . $qrfilename) )
		{
			//引入核心库文件
			include $phpqrcode_path . "/phpqrcode/phpqrcode.php";
			$errorLevel = "L";
			$size = "6";
			$url = $org . "/app/index.php?i=2&c=entry&m=ewei_shopv2&do=mobile&r=goods.detail&id=".$goods_id."&mid=".$mid;
			QRcode::png($url, $mobile_share_save_path . '/qrcode/' . $qrfilename, $errorLevel, $size);
		}
		$path_5 = $mobile_share_save_path . "/qrcode/" . $qrfilename;	//带会员mid参数的专属二维码

		//固定图层
		$image_1 = imagecreatefromjpeg($path_1);
		$image_2 = imagecreatefromjpeg($path_2);
		$image_6 = imagecreatefrompng($path_6);
		$image_7 = imagecreatefrompng($path_7);

		//提取动态图层后缀
		$ext3 = explode(".",$path_3);
		$ext4 = explode(".",$path_4);
		$ext5 = explode(".",$path_5);

		//根据后缀动态设定属性
		$image_3 = end($ext3) == 'png' ? imagecreatefrompng($path_3) : imagecreatefromjpeg($path_3);
		$image_4 = end($ext4) == 'png' ? imagecreatefrompng($path_4) : imagecreatefromjpeg($path_4);
		$image_5 = end($ext5) == 'png' ? imagecreatefrompng($path_5) : imagecreatefromjpeg($path_5);

		//创建一个画布
		$image = imageCreatetruecolor(780,1356);
		$color = imagecolorallocate($image, 255, 255, 255);
		imagefill($image, 0, 0, $color);
		imageColorTransparent($image, $color);

		//图片合成
		imagecopyresampled($image,$image_1,0,0,0,0,780,1356,imagesx($image_1),imagesy($image_1));
		imagecopyresampled($image,$image_2,135,812,0,0,120,30,imagesx($image_2),imagesy($image_2));
		imagecopyresampled($image,$image_3,40,40,0,0,700,700,imagesx($image_3),imagesy($image_3));
		imagecopyresampled($image,$image_4,40,770,0,0,70,70,imagesx($image_4),imagesy($image_4));
		imagecopyresampled($image,$image_5,510,850,0,0,220,220,imagesx($image_5),imagesy($image_5));
		imagecopyresampled($image,$image_6,40,770,0,0,70,70,imagesx($image_6),imagesy($image_6));

		//文字合成
		$black = imagecolorallocate($image,  88, 88, 88);//文字颜色
		imagettftext($image, 32, 0, 40, 900, $black, $ttf_path, $title);// 设置中文文字

		$black = imagecolorallocate($image,  254, 32, 79);//文字颜色
		imagettftext($image, 40, 0, 72, 1020, $black, $ttf_path_heijian, $price);// 设置中文文字

		$black = imagecolorallocate($image,  211, 137, 84);//文字颜色
		imagettftext($image, 20, 0, 265, 840, $black, $ttf_path, $name);// 设置中文文字

		//将画布保存到指定的gif文件  会员id_商品id_商品价格.jpg
		imagejpeg($image, $mobile_share_save_path . "/share_img/" . $filename);

		//返回路径
		$share_img_path = $org . 'addons/ewei_shopv2/data/mobile_share/share_img/' . $filename;

		show_json(1, array('main'=>2, 'mid' => $mid, 'goods_id' => $goods_id, 'type'=>'new', 'share_img_path'=>$share_img_path));
	}
}
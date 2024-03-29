<?php
/* 
*yi zi分销微分销分销中心
*/
define('IN_ECTOUCH', true);

require(dirname(__FILE__) . '/include/init.php');
require(ROOT_PATH . 'include/lib_weixintong.php');
/* 载入语言文件 */
require_once(ROOT_PATH . 'lang/' .$_CFG['lang']. '/user.php');
$user_id = $_SESSION['user_id'];
$action  = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'default';
$affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
$smarty->assign('affiliate', $affiliate);
$back_act='';

// 不需要登录的操作或自己验证是否登录（如ajax处理）的act
$not_login_arr =
array('login','act_login','register','act_register','act_edit_password','get_password','send_pwd_email','send_pwd_sms','password', 'signin', 'add_tag', 
    'collect', 'return_to_cart', 'logout', 'email_list', 'validate_email', 'send_hash_mail', 'order_query', 'is_registered', 'check_email','clear_history','qpassword_name', 
    'get_passwd_question', 'check_answer', 'oath', 'oath_login');

/* 显示页面的action列表 */
$ui_arr = array('register', 'login', 'profile','dianpu', 'act_dianpu', 'order_list', 'order_detail', 'order_tracking', 'address_list', 'act_edit_address', 'collection_list',
'message_list', 'tag_list', 'get_password', 'reset_password', 'booking_list', 'add_booking', 'account_raply',
'account_deposit', 'account_log', 'account_detail', 'act_account', 'pay', 'default', 'bonus', 'group_buy', 'group_buy_detail', 'affiliate', 'comment_list','validate_email','track_packages', 'transform_points','qpassword_name', 'get_passwd_question', 'check_answer','point','user_card','fenxiao1','myorder','myorder_detail','fenxiao2','fenxiao3','fenxiao4','fenxiao5','fenxiao6','fenxiao7','fenxiao8');
/* 未登录处理 */
if (empty($_SESSION['user_id']))
{
    if (!in_array($action, $not_login_arr))
    {
        if (in_array($action, $ui_arr))
        {
            /* 如果需要登录,并是显示页面的操作，记录当前操作，用于登录后跳转到相应操作
            if ($action == 'login')
            {
                if (isset($_REQUEST['back_act']))
                {
                    $back_act = trim($_REQUEST['back_act']);
                }
            }
            else
            {}*/
            if (!empty($_SERVER['QUERY_STRING']))
            {
                $back_act = 'user.php?' . strip_tags($_SERVER['QUERY_STRING']);
            }
            $action = 'login';
        }
        else
        {
            //未登录提交数据。非正常途径提交数据！
            die($_LANG['require_login']);
        }
    }
}




/* 如果是显示页面，对页面进行相应赋值 */
if (in_array($action, $ui_arr))
{
    assign_template();
    $position = assign_ur_here(0, $_LANG['user_center']);
    $smarty->assign('page_title', "分销中心"); // 页面标题
    $smarty->assign('ur_here',    $position['ur_here']);
    $sql = "SELECT value FROM " . $ecs->table('touch_shop_config') . " WHERE id = 419";
    $row = $db->getRow($sql);
    $car_off = $row['value'];
    $smarty->assign('car_off',       $car_off);
    /* 是否显示积分兑换 */
    if (!empty($_CFG['points_rule']) && unserialize($_CFG['points_rule']))
    {
        $smarty->assign('show_transform_points',     1);
    }
    $smarty->assign('helps',      get_shop_help());        // 网店帮助
    $smarty->assign('data_dir',   DATA_DIR);   // 数据目录
    $smarty->assign('action',     $action);
    $smarty->assign('lang',       $_LANG);
}

//用户中心欢迎页
if ($action == 'default')
{
    include_once(ROOT_PATH .'include/lib_clips.php');
    if ($rank = get_rank_info())
    {
        $smarty->assign('rank_name', sprintf($_LANG['your_level'], $rank['rank_name']));
        if (!empty($rank['next_rank_name']))
        {
            $smarty->assign('next_rank_name', sprintf($_LANG['next_level'], $rank['next_rank'] ,$rank['next_rank_name']));
        }
    }
	$info = get_user_default($user_id);
	
	$sql = "SELECT wxid FROM " .$GLOBALS['ecs']->table('users'). " WHERE user_id = '$user_id'";
    $wxid = $GLOBALS['db']->getOne($sql);
	if(!empty($wxid)){
		$weixinInfo = $GLOBALS['db']->getRow("SELECT nickname, headimgurl FROM wxch_user WHERE wxid = '$wxid'");
		$info['avatar'] = empty($weixinInfo['headimgurl']) ? '':$weixinInfo['headimgurl'];
		$info['username'] = empty($weixinInfo['nickname']) ? $info['username']:$weixinInfo['nickname'];
	}
	/*实实  微分销系统分销微分销新增显示分销会员标准*/
	$affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
	$level_register_up = (float)$affiliate['config']['level_register_up'];
	$rank_points =  $GLOBALS['db']->getOne("SELECT rank_points FROM " . $GLOBALS['ecs']->table('users')."where user_id=".$_SESSION["user_id"]);
	if($rank_points>$level_register_up||$rank_points==$level_register_up){
		$shishi="资格：分销商";
	}else{

		show_message('您还不是分销商哦', '请先购买商品获取权限', 'user.php', 'error'); 
	}
	/*显示各级会员数量*/
	$affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
	$user_list['user_list'] = array();
	$auid = $_SESSION['user_id'];
	$up_uid = "'$auid'";
	$res=array();
	$sql = "SELECT  count(*)  as num  FROM " . $ecs->table('users') . " WHERE parent_id=$up_uid";
    $count1 = $GLOBALS['db']->getOne($sql);
	$res[1]=$count1;
	$sql = "SELECT  user_id  FROM " . $ecs->table('users') . " WHERE parent_id=$up_uid";
	$up_uid_all_two=$GLOBALS['db']->getAll($sql);
	$count2=0;
	$count3=0;
	$count4=0;
	$count5=0;
	$count6=0;
	$count7=0;
	$count8=0;
	$count9=0;
	if(!empty($up_uid_all_two)){
	foreach($up_uid_all_two as $key =>$value){
	
		$sql = "SELECT  count(*)  as num  FROM " . $ecs->table('users') . " WHERE parent_id=$value[user_id]";
        $count = $GLOBALS['db']->getOne($sql);
		$count2=$count2+$count;
		$sql = "SELECT  user_id  FROM " . $ecs->table('users') . " WHERE parent_id=$value[user_id]";
		$up_uid_all_tree=$GLOBALS['db']->getAll($sql);
		if(!empty($up_uid_all_tree)){
		foreach($up_uid_all_tree as $key =>$value ){
		
			$sql = "SELECT  count(*)  as num  FROM " . $ecs->table('users') . " WHERE parent_id=$value[user_id]";
			$count = $GLOBALS['db']->getOne($sql);
			$count3=$count3+$count;
			$sql = "SELECT  user_id  FROM " . $ecs->table('users') . " WHERE parent_id=$value[user_id]";
			$up_uid_all_four=$GLOBALS['db']->getAll($sql);
			if(!empty($up_uid_all_four)){
			foreach($up_uid_all_four as $key =>$value){
				$sql = "SELECT  count(*)  as num  FROM " . $ecs->table('users') . " WHERE parent_id=$value[user_id]";
				$count = $GLOBALS['db']->getOne($sql);
				$count4=$count4+$count;
				$sql = "SELECT  user_id  FROM " . $ecs->table('users') . " WHERE parent_id=$value[user_id]";
				$up_uid_all_five=$GLOBALS['db']->getAll($sql);
				if(!empty($up_uid_all_five)){
					foreach($up_uid_all_five as $key =>$value){					
						$sql = "SELECT  count(*)  as num  FROM " . $ecs->table('users') . " WHERE parent_id=$value[user_id]";
						$count = $GLOBALS['db']->getOne($sql);
						$count5=$count5+$count;
						
						$sql = "SELECT  user_id  FROM " . $ecs->table('users') . " WHERE parent_id=$value[user_id]";
						$up_uid_all_six=$GLOBALS['db']->getAll($sql);
						if(!empty($up_uid_all_six)){
							foreach($up_uid_all_six as $key =>$value){					
								$sql = "SELECT  count(*)  as num  FROM " . $ecs->table('users') . " WHERE parent_id=$value[user_id]";
								$count = $GLOBALS['db']->getOne($sql);
								$count6=$count6+$count;
								
								$sql = "SELECT  user_id  FROM " . $ecs->table('users') . " WHERE parent_id=$value[user_id]";
								$up_uid_all_seven=$GLOBALS['db']->getAll($sql);
								if(!empty($up_uid_all_seven)){
									foreach($up_uid_all_seven as $key =>$value){					
										$sql = "SELECT  count(*)  as num  FROM " . $ecs->table('users') . " WHERE parent_id=$value[user_id]";
										$count = $GLOBALS['db']->getOne($sql);
										$count7=$count7+$count;
										
										$sql = "SELECT  user_id  FROM " . $ecs->table('users') . " WHERE parent_id=$value[user_id]";
										$up_uid_all_eight=$GLOBALS['db']->getAll($sql);
										if(!empty($up_uid_all_eight)){
											foreach($up_uid_all_eight as $key =>$value){					
												$sql = "SELECT  count(*)  as num  FROM " . $ecs->table('users') . " WHERE parent_id=$value[user_id]";
												$count = $GLOBALS['db']->getOne($sql);
												$count8=$count8+$count;
										
										$sql = "SELECT  user_id  FROM " . $ecs->table('users') . " WHERE parent_id=$value[user_id]";
										$up_uid_all_nine=$GLOBALS['db']->getAll($sql);
										if(!empty($up_uid_all_nine)){
											foreach($up_uid_all_nine as $key =>$value){					
												$sql = "SELECT  count(*)  as num  FROM " . $ecs->table('users') . " WHERE parent_id=$value[user_id]";
												$count = $GLOBALS['db']->getOne($sql);
												$count9=$count9+$count;
											}
										}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		}
		}
	}
	}
	$res[2]=$count2;
	$res[3]=$count3;
	$res[4]=$count4;
	$res[5]=$count5;
	$res[6]=$count6;
	$res[7]=$count7;
	$res[8]=$count8;
	$res[9]=$count9;
	/*
    $num = 4;
    $up_uid = "'$auid'";
    $all_count = 0;
	$res=array();
    for ($i = 1; $i<=$num; $i++)
    {
        $count = 0;
		$res[$i]=0;
        if ($up_uid)
        {
            $sql = "SELECT  count(*)  as num  FROM " . $ecs->table('users') . " WHERE parent_id=$up_uid";
            $count = $GLOBALS['db']->getOne($sql);
			$sql = "SELECT  user_id  FROM " . $ecs->table('users') . " WHERE parent_id=$up_uid";

			
			$res[$i]=$count;
        }
		$up_uid=$GLOBALS['db']->getOne($sql);
        $all_count += $count;
	}*/
	//print_r($res);
	$smarty->assign('shishi',        $shishi);
	/*实实微分销系统分销微分销新增显示分销会员标准*/
	$smarty->assign('service_phone',        $_CFG['service_phone']);
	
	/*实-实微分销系统分销微分销新增查询佣金*/
	//推荐注册分成
	$affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
	$user_list['user_list'] = array();
	$auid = $_SESSION['user_id'];
    $num =  count($affiliate['item']);
    $up_uid = "'$auid'";
    $all_count = 0;
	$allarr=array();
    for ($i = 1; $i<=$num; $i++)
    {
        $count = 0;
        if ($up_uid)
        {
            $sql = "SELECT user_id FROM " . $ecs->table('users') . " WHERE parent_id IN($up_uid)";
            $query = $db->query($sql);
            $up_uid = '';
            while ($rt = $db->fetch_array($query))
            {
                $up_uid .= $up_uid ? ",'$rt[user_id]'" : "'$rt[user_id]'";
                $count++;
            }
        }
        $all_count += $count;
	
        if ($count)
        {
            $sql = "SELECT user_id, user_name, '$i' AS level, email, is_validated, user_money, frozen_money, rank_points, pay_points, reg_time,wxid ".
                    " FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id IN($up_uid)" .
                    " ORDER by level, user_id";
			$user_info=$db->getAll($sql);
			$shishiarr=array();
			foreach($user_info as $key=>$value){
				/*实-实微分销系统分销微分销开发未付款的佣金统计*/
				$sql="SELECT count(*) as order_num ,sum(fencheng)  as order_amount FROM ".$GLOBALS['ecs']->table('order_info')."WHERE user_id=".$value['user_id']." and  pay_status=0";
				$order_info=$db->getRow($sql);
				$k=$i-1;
				$affiliate['item'][$k]['level_money'] = (float)$affiliate['item'][$k]['level_money'];
				if($key==0){
                if ($affiliate['item'][$k]['level_money'])
                {
                    $affiliate['item'][$k]['level_money'] /= 100;
                }
				}
				$setmoney = round($order_info['order_amount'] * $affiliate['item'][$k]['level_money'], 2);
				
				$shishiarr['weifukuan']['order_num']+=$order_info['order_num'];
				$shishiarr['weifukuan']['order_amount']+=$order_info['order_amount'];
				$shishiarr['weifukuan']['setmoney']+=$setmoney;
				/*实-实- 微分销系统分销微分销开发已经付款且未收货佣金统计*/
				$sql="SELECT count(*) as order_num ,sum(fencheng)  as order_amount FROM ".$GLOBALS['ecs']->table('order_info')."WHERE user_id=".$value['user_id']." and  pay_status=2 and   shipping_status  <> 2";
				$order_info=$db->getRow($sql);
				$k=$i-1;

   
				$setmoney = round($order_info['order_amount'] * $affiliate['item'][$k]['level_money'], 2);
				
				$shishiarr['yifukuan']['order_num']+=$order_info['order_num'];
				$shishiarr['yifukuan']['order_amount']+=$order_info['order_amount'];
				$shishiarr['yifukuan']['setmoney']+=$setmoney;
				
				/*实-实- 微分销系统分销微分销开发已经收货佣金统计*/
				$sql="SELECT count(*) as order_num ,sum(fencheng)  as order_amount FROM ".$GLOBALS['ecs']->table('order_info')."WHERE user_id=".$value['user_id']."  and  shipping_status  =2";
				$order_info=$db->getRow($sql);
				$k=$i-1;

				$setmoney = round($order_info['order_amount'] * $affiliate['item'][$k]['level_money'], 2);
				
				$shishiarr['yishouhuo']['order_num']+=$order_info['order_num'];
				$shishiarr['yishouhuo']['order_amount']+=$order_info['order_amount'];
				$shishiarr['yishouhuo']['setmoney']+=$setmoney;				
			}
			foreach($shishiarr as $key=>$vale){
				if($key=="weifukuan"){
					$allarr[$key]['order_num']+=$vale['order_num'];
					$allarr[$key]['order_amount']+=$vale['order_amount'];
					$allarr[$key]['setmoney']+=$vale['setmoney'];
				}
				if($key=="yifukuan"){
					$allarr[$key]['order_num']+=$vale['order_num'];
					$allarr[$key]['order_amount']+=$vale['order_amount'];
					$allarr[$key]['setmoney']+=$vale['setmoney'];
				}
				if($key=="yishouhuo"){
					$allarr[$key]['order_num']+=$vale['order_num'];
					$allarr[$key]['order_amount']+=$vale['order_amount'];
					$allarr[$key]['setmoney']+=$vale['setmoney'];
				}				
			}
        }
    }
	$shishiall=array();
	foreach($allarr  as $key =>$value){
				
		$shishiall['order_num']+=$value['order_num'];
		$shishiall['order_amount']+=$value['order_amount'];
		$shishiall['setmoney']+=$value['setmoney'];
	}
	$smarty->assign('shishiall',        $shishiall);
	$smarty->assign('shishiarr',        $allarr);
	
	/*实实- 微分销系统 add 显示可体现佣金金额*/
	$surplus_amount = get_user_surplus($user_id);
    if (empty($surplus_amount))
    {
        $surplus_amount = 0;
    }
	$smarty->assign('surplus_amount',       $surplus_amount);
	
	/*实实- 微分销系统分销微分销新增查询佣金*/
    $smarty->assign('info',        $info);
	$menuarr=array();
	for ($i = 1; $i<=$num; $i++){
		$smarty->assign('count'.$i,        $res[$i]);
		$menuarr[$i]=$i;
	}
        
	$smarty->assign('menuarr',       $menuarr);
	$smarty->assign('user_id',        $_SESSION['user_id']);
	$smarty->assign('all_count',        $all_count);
    $smarty->assign('user_notice', $_CFG['user_notice']);
    $smarty->assign('prompt',      get_user_prompt($user_id));
    $smarty->display('distribute.dwt');
}
/*-分销微分销开发*/
if ($action == 'dianpu')
{
    if ((!isset($back_act)||empty($back_act)) && isset($GLOBALS['_SERVER']['HTTP_REFERER']))
    {
        $back_act = strpos($GLOBALS['_SERVER']['HTTP_REFERER'], 'user.php') ? './index.php' : $GLOBALS['_SERVER']['HTTP_REFERER'];
    }
	
	$dianpu = $db->getOne('SELECT nicheng FROM ' . $ecs->table('users') . ' WHERE user_id='.$user_id.'');
	$smarty->assign('dianpu', $dianpu);

    $smarty->display('user_transaction.dwt');
}
if ($action == 'act_dianpu')
{
    if ((!isset($back_act)||empty($back_act)) && isset($GLOBALS['_SERVER']['HTTP_REFERER']))
    {
        $back_act = strpos($GLOBALS['_SERVER']['HTTP_REFERER'], 'user.php') ? './index.php' : $GLOBALS['_SERVER']['HTTP_REFERER'];
    }
	
	$nicheng=trim($_REQUEST['nicheng']);
	if($nicheng=='')
	{
	show_message('店铺名不能为空');
	}
	else
	{
	$u_id = $db->getOne("SELECT user_id FROM " . $ecs->table('users') . " WHERE nicheng='".$nicheng."' and user_id!=".$user_id."");
	if($u_id>0)
	{
	show_message('店铺名重复');
	}
	}
	
	$db->query("update " . $ecs->table('users') . " set nicheng='".$nicheng."' WHERE user_id=".$user_id."");
	$smarty->assign('dianpu', $nicheng);

    $smarty->display('user_transaction.dwt');
}
//  第三方登录接口
elseif($action == 'oath')
{
	$type = empty($_REQUEST['type']) ?  '' : $_REQUEST['type'];
	
	include_once(ROOT_PATH . 'include/website/jntoo.php');

	$c = &website($type);
	if($c)
	{
		if (empty($_REQUEST['callblock']))
		{
			if (empty($_REQUEST['callblock']) && isset($GLOBALS['_SERVER']['HTTP_REFERER']))
			{
				$back_act = strpos($GLOBALS['_SERVER']['HTTP_REFERER'], 'user.php') ? 'index.php' : $GLOBALS['_SERVER']['HTTP_REFERER'];
			}
			else
			{
                           
				$back_act = 'user.php';
			}
		}
		else
		{
			$back_act = trim($_REQUEST['callblock']);
		}

		if($back_act[4] != ':') $back_act = $ecs->url().$back_act;
		$open = empty($_REQUEST['open']) ? 0 : intval($_REQUEST['open']);
		
		$url = $c->login($ecs->url().'user.php?act=oath_login&type='.$type.'&callblock='.urlencode($back_act).'&open='.$open);

		if(!$url)
		{
			show_message( $c->get_error() , '首页', $ecs->url() , 'error');
		}
		header('Location: '.$url);
	}
	else
	{
		show_message('服务器尚未注册该插件！' , '首页',$ecs->url() , 'error');
	}
}

//  处理第三方登录接口
elseif ($action == 'oath_login') {
    $type = empty($_REQUEST['type']) ? '' : $_REQUEST['type'];

    include_once(ROOT_PATH . 'include/website/jntoo.php');
    $c = &website($type);

    if ($c) {
        $access = $c->getAccessToken();

        if (!$access) {
            show_message($c->get_error(), '首页', $ecs->url(), 'error');
        }

        $c->setAccessToken($access);
        $info = $c->getMessage();
        if($type =='renn' ){
            
             $info =  $info['response'];
             $info['user_id'] = $info['id'];
            
        }
        
        if (!$info) {
            show_message($c->get_error(), '首页', $ecs->url(), 'error', false);
        }
        if (!$info['user_id'] || !$info['user_id']) {

            show_message($c->get_error(), '首页', $ecs->url(), 'error', false);
        }
        $info_user_id = $type . '_' . $info['user_id']; //  加个标识！！！防止 其他的标识 一样  // 以后的ID 标识 将以这种形式 辨认
        $info['name'] = str_replace("'", "", $info['name']); // 过滤掉 逗号 不然出错  很难处理   不想去  搞什么编码的了
        if (!$info['user_id'])
            show_message($c->get_error(), '首页', $ecs->url(), 'error', false);


        $sql = 'SELECT user_name,password,aite_id FROM ' . $ecs->table('users') . ' WHERE aite_id = \'' . $info_user_id . '\'';

        $count = $db->getRow($sql);


        if (!$count) {   // 没有当前数据
            if ($user->check_user($info['name'])) {  // 重名处理
                $info['name'] = $info['name'] . '_' . $type . (rand(10000, 99999));
            }
            $user_pass = $user->compile_password(array('password' => $info['user_id']));
            $sql = 'INSERT INTO ' . $ecs->table('users') . '(user_name , password, aite_id , sex , reg_time , user_rank , is_validated) VALUES ' .
                    "('$info[name]' , '$user_pass' , '$info_user_id' , '$info[sex]' , '" . gmtime() . "' , '$info[rank_id]' , '1')";

            $db->query($sql);
        } else {
            $sql = '';
            if ($count['aite_id'] == $info['user_id']) {
                $sql = 'UPDATE ' . $ecs->table('users') . " SET aite_id = '$info_user_id' WHERE aite_id = '$count[aite_id]'";
                $db->query($sql);
            }
            if ($info['name'] != $count['user_name']) {   // 这段可删除
                if ($user->check_user($info['name'])) {  // 重名处理
                    $info['name'] = $info['name'] . '_' . $type . (rand() * 1000);
                }
                $sql = 'UPDATE ' . $ecs->table('users') . " SET user_name = '$info[name]' WHERE aite_id = '$info_user_id'";
                $db->query($sql);
            }
        }
        $user->set_session($info['name']);
        $user->set_cookie($info['name']);
        update_user_info();
        recalculate_price();

        if (!empty($_REQUEST['open'])) {
            die('<script>window.opener.window.location.reload(); window.close();</script>');
        } else {
            ecs_header('Location: ' . $_REQUEST['callblock']);
        }
    }
}

/* 显示会员注册界面 */
if ($action == 'register')
{
    if ((!isset($back_act)||empty($back_act)) && isset($GLOBALS['_SERVER']['HTTP_REFERER']))
    {
        $back_act = strpos($GLOBALS['_SERVER']['HTTP_REFERER'], 'user.php') ? './index.php' : $GLOBALS['_SERVER']['HTTP_REFERER'];
    }

    /* 取出注册扩展字段 */
    $sql = 'SELECT * FROM ' . $ecs->table('reg_fields') . ' WHERE type < 2 AND display = 1 ORDER BY dis_order, id';
    $extend_info_list = $db->getAll($sql);
    $smarty->assign('extend_info_list', $extend_info_list);

    /* 验证码相关设置 */
    if ((intval($_CFG['captcha']) & CAPTCHA_REGISTER) && gd_version() > 0)
    {
        $smarty->assign('enabled_captcha', 1);
        $smarty->assign('rand',            mt_rand());
    }
    
    /* 短信发送设置 by carson */
    if(intval($_CFG['sms_signin']) > 0){
        $smarty->assign('enabled_sms_signin', 1);
    }

    /* 密码提示问题 */
    $smarty->assign('passwd_questions', $_LANG['passwd_questions']);

    /* 增加是否关闭注册 */
    $smarty->assign('shop_reg_closed', $_CFG['shop_reg_closed']);
//    $smarty->assign('back_act', $back_act);
    $smarty->display('user_passport.dwt');
}

/* 注册会员的处理 */
elseif ($action == 'act_register')
{
    /* 增加是否关闭注册 */
    if ($_CFG['shop_reg_closed'])
    {
        $smarty->assign('action',     'register');
        $smarty->assign('shop_reg_closed', $_CFG['shop_reg_closed']);
        $smarty->display('user_passport.dwt');
    }
    else
    {
        include_once(ROOT_PATH . 'include/lib_passport.php');

        //注册类型 by carson start
        $enabled_sms = intval($_POST['enabled_sms']);
        if($enabled_sms){
            $username = $other['mobile_phone'] = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
            $password = isset($_POST['password']) ? trim($_POST['password']) : '';
            $email    = $username .'@qq.com';
        }else{
            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $password = isset($_POST['password']) ? trim($_POST['password']) : '';
           $email    = $username .'@qq.com';
        }
        //注册类型 by carson end

        $back_act = isset($_POST['back_act']) ? trim($_POST['back_act']) : '';

        if(empty($_POST['agreement']))
        {
            show_message($_LANG['passport_js']['agreement']);
        }
        if (strlen($username) < 3)
        {
            show_message($_LANG['passport_js']['username_shorter']);
        }

        if (strlen($password) < 6)
        {
            show_message($_LANG['passport_js']['password_shorter']);
        }

        if (strpos($password, ' ') > 0)
        {
            show_message($_LANG['passwd_balnk']);
        }



        if (register($username, $password, $email, $other) !== false)
        {
            /*把新注册用户的扩展信息插入数据库*/
            $sql = 'SELECT id FROM ' . $ecs->table('reg_fields') . ' WHERE type = 0 AND display = 1 ORDER BY dis_order, id';   //读出所有自定义扩展字段的id
            $fields_arr = $db->getAll($sql);

            $extend_field_str = '';    //生成扩展字段的内容字符串
            foreach ($fields_arr AS $val)
            {
                $extend_field_index = 'extend_field' . $val['id'];
                if(!empty($_POST[$extend_field_index]))
                {
                    $temp_field_content = strlen($_POST[$extend_field_index]) > 100 ? mb_substr($_POST[$extend_field_index], 0, 99) : $_POST[$extend_field_index];
                    $extend_field_str .= " ('" . $_SESSION['user_id'] . "', '" . $val['id'] . "', '" . compile_str($temp_field_content) . "'),";
                }
            }
            $extend_field_str = substr($extend_field_str, 0, -1);

            if ($extend_field_str)      //插入注册扩展数据
            {
                $sql = 'INSERT INTO '. $ecs->table('reg_extend_info') . ' (`user_id`, `reg_field_id`, `content`) VALUES' . $extend_field_str;
                $db->query($sql);
            }

            /* 写入密码提示问题和答案 */
            if (!empty($passwd_answer) && !empty($sel_question))
            {
                $sql = 'UPDATE ' . $ecs->table('users') . " SET `passwd_question`='$sel_question', `passwd_answer`='$passwd_answer'  WHERE `user_id`='" . $_SESSION['user_id'] . "'";
                $db->query($sql);
            }
            /* 判断是否需要自动发送注册邮件 */
            if ($GLOBALS['_CFG']['member_email_validate'] && $GLOBALS['_CFG']['send_verify_email'])
            {
                send_regiter_hash($_SESSION['user_id']);
            }
            $ucdata = empty($user->ucdata)? "" : $user->ucdata;
            show_message(sprintf($_LANG['register_success'], $username . $ucdata), array($_LANG['back_up_page'], $_LANG['profile_lnk']), array($back_act, 'user.php'), 'info');
        }
        else
        {
            $err->show($_LANG['sign_up'], 'user.php?act=register');
        }
    }
}

/* 验证用户注册邮件 */
elseif ($action == 'validate_email')
{
    $hash = empty($_GET['hash']) ? '' : trim($_GET['hash']);
    if ($hash)
    {
        include_once(ROOT_PATH . 'include/lib_passport.php');
        $id = register_hash('decode', $hash);
        if ($id > 0)
        {
            $sql = "UPDATE " . $ecs->table('users') . " SET is_validated = 1 WHERE user_id='$id'";
            $db->query($sql);
            $sql = 'SELECT user_name, email FROM ' . $ecs->table('users') . " WHERE user_id = '$id'";
            $row = $db->getRow($sql);
            show_message(sprintf($_LANG['validate_ok'], $row['user_name'], $row['email']),$_LANG['profile_lnk'], 'user.php');
        }
    }
    show_message($_LANG['validate_fail']);
}

/* 验证用户注册用户名是否可以注册 */
elseif ($action == 'is_registered')
{
    include_once(ROOT_PATH . 'include/lib_passport.php');

    $username = trim($_GET['username']);
    $username = json_str_iconv($username);

    if ($user->check_user($username) || admin_registered($username))
    {
        echo 'false';
    }
    else
    {
        echo 'true';
    }
}

/* 验证用户邮箱地址是否被注册 */
elseif($action == 'check_email')
{
    $email = trim($_GET['email']);
    if ($user->check_email($email))
    {
        echo 'false';
    }
    else
    {
        echo 'ok';
    }
}
/* 用户登录界面 */
elseif ($action == 'login')
{
    if (empty($back_act)) {
        if (empty($back_act) && isset($GLOBALS['_SERVER']['HTTP_REFERER'])) {
            $back_act = strpos($GLOBALS['_SERVER']['HTTP_REFERER'], 'user.php') ? './index.php' : $GLOBALS['_SERVER']['HTTP_REFERER'];
        } else {
            $back_act = 'user.php';
        }
    }

    /* 短信发送设置 by carson */
    if(intval($_CFG['sms_signin']) > 0){
        $smarty->assign('enabled_sms_signin', 1);
    }

    $captcha = intval($_CFG['captcha']);
    if (($captcha & CAPTCHA_LOGIN) && (!($captcha & CAPTCHA_LOGIN_FAIL) || (($captcha & CAPTCHA_LOGIN_FAIL) && $_SESSION['login_fail'] > 2)) && gd_version() > 0 || (intval($_CFG['captcha']) & CAPTCHA_REGISTER))
    {
        $GLOBALS['smarty']->assign('enabled_captcha', 1);
        $GLOBALS['smarty']->assign('rand', mt_rand());
    }

    $smarty->assign('back_act', $back_act);
    $smarty->display('user_passport_shishi.dwt');
}

/* 处理会员的登录 */
elseif ($action == 'act_login')
{
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $back_act = isset($_POST['back_act']) ? trim($_POST['back_act']) : '';

    /* 关闭验证码 by wang
    $captcha = intval($_CFG['captcha']);
    if (($captcha & CAPTCHA_LOGIN) && (!($captcha & CAPTCHA_LOGIN_FAIL) || (($captcha & CAPTCHA_LOGIN_FAIL) && $_SESSION['login_fail'] > 2)) && gd_version() > 0)
    {
        if (empty($_POST['captcha']))
        {
            show_message($_LANG['invalid_captcha'], $_LANG['relogin_lnk'], 'user.php', 'error');
        }

        // 检查验证码
        include_once('include/cls_captcha.php');

        $validator = new captcha();
        $validator->session_word = 'captcha_login';
        if (!$validator->check_word($_POST['captcha']))
        {
            show_message($_LANG['invalid_captcha'], $_LANG['relogin_lnk'], 'user.php', 'error');
        }
    }
    */
	$login_type = intval($_POST['login_type']);
  if($login_type==3)
  {
           $card_info = $db->getRow("select * from " . $ecs->table('user_card') ." where card_no='$username' and card_pass='$password' and is_show =1 " );
		   //var_dump($card_info);exit;
		  if($card_info)
		  {
			 $user_id = intval($card_info['user_id']);
			 if($user_id)
			  {
				 $user_name = $db->getOne("select user_name from " . $ecs->table('users') ." where user_id='$user_id'" );
				 
					 if ($user_name)
				   {
					  $_SESSION['user_id'] = $user_id;
					  $_SESSION['user_name']   = $username;
					show_message($_LANG['login_success'] . $ucdata , array($_LANG['back_up_page'], $_LANG['profile_lnk']), array($back_act,'user.php'), 'info');
				   }
				   else
				   {
					$_SESSION['login_fail'] ++ ;
					show_message($_LANG['login_failure'], $_LANG['relogin_lnk'], 'user.php', 'error');
				   }
			  }
			  else
			  {
				include_once(ROOT_PATH . 'include/lib_passport.php');
			        $cu_user_name = 'cu_'.$card_info['card_no'];
					$user_name = 'cu_'.$card_info['card_no'];
					$email = $card_info['email']==''?$cu_user_name.'@temp.com':$card_info['email'];
					$other = array();
					include_once(ROOT_PATH . 'includes/lib_passport.php');
					if (register($cu_user_name, $password, $email, $other) !== false)
					{
						$db->autoExecute($ecs->table('user_card'), array('user_id'=>$_SESSION['user_id'],'bind_time'=>gmtime(),'card_status'=>1), 'UPDATE', " id='$card_info[id]' ");
						$ucdata = empty($user->ucdata)? "" : $user->ucdata;
						show_message(sprintf($_LANG['login_success'], $cu_user_name . $ucdata), array($_LANG['back_up_page'], $_LANG['profile_lnk']), array($back_act, 'user.php'), 'info');
					}
					else
					{
						$_SESSION['login_fail'] ++ ;
					    show_message($_LANG['login_failure'], $_LANG['relogin_lnk'], 'user.php', 'error');
					}
			  
			  }

		  }
		  else
		  {
			   $num = $db->getOne("select count(*) from " . $ecs->table('user_card') ." where card_no='$username' and card_pass ='$password'  and user_id=0 and is_show=1 " );
			   if($num==1)
			   {
			  
				  show_message('此卡号还未绑定，您可以用此新注册一个会员帐号并绑定此卡号，如果您已有本站会员帐号，请登录后在会员中心绑定此卡号后方可登录!', array('立即注册并绑定此卡号','重新登录'), array('user.php?act=register&card_no='.$username.'&card_pass='.$password,'user.php'), 'error'); 
			   
			   }
			   
			   show_message('会员卡卡号不存在', '请重新登录', 'user.php', 'error'); 
		  }
  
  }
    //用户名是邮箱格式 by wang
    if(is_email($username))
    {
        $sql ="select user_name from ".$ecs->table('users')." where email='".$username."'";
        $username_try = $db->getOne($sql);
        $username = $username_try ? $username_try:$username;
    }

    //用户名是手机格式 by wang
    if(is_mobile($username))
    {
        $sql ="select user_name from ".$ecs->table('users')." where mobile_phone='".$username."'";
        $username_try = $db->getOne($sql);
        $username = $username_try ? $username_try:$username;
    }

    if ($user->login($username, $password,isset($_POST['remember'])))
    {
        update_user_info();
        recalculate_price();

        $ucdata = isset($user->ucdata)? $user->ucdata : '';
        show_message($_LANG['login_success'] . $ucdata , array($_LANG['back_up_page'], "我的分销中心"), array($back_act,'distribute.php'), 'info');
    }
    else
    {
        $_SESSION['login_fail'] ++ ;
        show_message($_LANG['login_failure'], $_LANG['relogin_lnk'], 'user.php', 'error');
    }
}

/* 处理 ajax 的登录请求 */
elseif ($action == 'signin')
{
    include_once('include/cls_json.php');
    $json = new JSON;

    $username = !empty($_POST['username']) ? json_str_iconv(trim($_POST['username'])) : '';
    $password = !empty($_POST['password']) ? trim($_POST['password']) : '';
    $captcha = !empty($_POST['captcha']) ? json_str_iconv(trim($_POST['captcha'])) : '';
    $result   = array('error' => 0, 'content' => '');

    $captcha = intval($_CFG['captcha']);
    if (($captcha & CAPTCHA_LOGIN) && (!($captcha & CAPTCHA_LOGIN_FAIL) || (($captcha & CAPTCHA_LOGIN_FAIL) && $_SESSION['login_fail'] > 2)) && gd_version() > 0)
    {
        if (empty($captcha))
        {
            $result['error']   = 1;
            $result['content'] = $_LANG['invalid_captcha'];
            die($json->encode($result));
        }

        /* 检查验证码 */
        include_once('include/cls_captcha.php');

        $validator = new captcha();
        $validator->session_word = 'captcha_login';
        if (!$validator->check_word($_POST['captcha']))
        {

            $result['error']   = 1;
            $result['content'] = $_LANG['invalid_captcha'];
            die($json->encode($result));
        }
    }

    if ($user->login($username, $password))
    {
        update_user_info();  //更新用户信息
        recalculate_price(); // 重新计算购物车中的商品价格
        $smarty->assign('user_info', get_user_info());
        $ucdata = empty($user->ucdata)? "" : $user->ucdata;
        $result['ucdata'] = $ucdata;
        $result['content'] = $smarty->fetch('library/member_info.lbi');
    }
    else
    {
        $_SESSION['login_fail']++;
        if ($_SESSION['login_fail'] > 2)
        {
            $smarty->assign('enabled_captcha', 1);
            $result['html'] = $smarty->fetch('library/member_info.lbi');
        }
        $result['error']   = 1;
        $result['content'] = $_LANG['login_failure'];
    }
    die($json->encode($result));
}

/* 退出会员中心 */
elseif ($action == 'logout')
{
    if ((!isset($back_act)|| empty($back_act)) && isset($GLOBALS['_SERVER']['HTTP_REFERER']))
    {
        $back_act = strpos($GLOBALS['_SERVER']['HTTP_REFERER'], 'user.php') ? './index.php' : $GLOBALS['_SERVER']['HTTP_REFERER'];
    }

    $user->logout();
    $ucdata = empty($user->ucdata)? "" : $user->ucdata;
    show_message($_LANG['logout'] . $ucdata, array($_LANG['back_up_page'], $_LANG['back_home_lnk']), array($back_act, 'index.php'), 'info');
}

/* 个人资料页面 */
elseif ($action == 'profile')
{
    include_once(ROOT_PATH . 'include/lib_transaction.php');

    $user_info = get_profile($user_id);

    /* 取出注册扩展字段 */
    $sql = 'SELECT * FROM ' . $ecs->table('reg_fields') . ' WHERE type < 2 AND display = 1 ORDER BY dis_order, id';
    $extend_info_list = $db->getAll($sql);

    $sql = 'SELECT reg_field_id, content ' .
           'FROM ' . $ecs->table('reg_extend_info') .
           " WHERE user_id = $user_id";
    $extend_info_arr = $db->getAll($sql);

    $temp_arr = array();
    foreach ($extend_info_arr AS $val)
    {
        $temp_arr[$val['reg_field_id']] = $val['content'];
    }

    foreach ($extend_info_list AS $key => $val)
    {
        switch ($val['id'])
        {
            case 1:     $extend_info_list[$key]['content'] = $user_info['msn']; break;
            case 2:     $extend_info_list[$key]['content'] = $user_info['qq']; break;
            case 3:     $extend_info_list[$key]['content'] = $user_info['office_phone']; break;
            case 4:     $extend_info_list[$key]['content'] = $user_info['home_phone']; break;
            case 5:     $extend_info_list[$key]['content'] = $user_info['mobile_phone']; break;
            default:    $extend_info_list[$key]['content'] = empty($temp_arr[$val['id']]) ? '' : $temp_arr[$val['id']] ;
        }
    }

    $smarty->assign('extend_info_list', $extend_info_list);

    /* 密码提示问题 */
    $smarty->assign('passwd_questions', $_LANG['passwd_questions']);

    $smarty->assign('profile', $user_info);
    $smarty->display('user_transaction.dwt');
}

/* 修改个人资料的处理 */
elseif ($action == 'act_edit_profile')
{
    include_once(ROOT_PATH . 'include/lib_transaction.php');

    $birthday = $_POST['birthday'];
    $email = trim($_POST['email']);
    $other['msn'] = $msn = isset($_POST['extend_field1']) ? trim($_POST['extend_field1']) : '';
    $other['qq'] = $qq = isset($_POST['extend_field2']) ? trim($_POST['extend_field2']) : '';
    $other['office_phone'] = $office_phone = isset($_POST['extend_field3']) ? trim($_POST['extend_field3']) : '';
    $other['home_phone'] = $home_phone = isset($_POST['extend_field4']) ? trim($_POST['extend_field4']) : '';
    $other['mobile_phone'] = $mobile_phone = isset($_POST['extend_field5']) ? trim($_POST['extend_field5']) : '';
    $sel_question = empty($_POST['sel_question']) ? '' : compile_str($_POST['sel_question']);
    $passwd_answer = isset($_POST['passwd_answer']) ? compile_str(trim($_POST['passwd_answer'])) : '';

    /* 更新用户扩展字段的数据 */
    $sql = 'SELECT id FROM ' . $ecs->table('reg_fields') . ' WHERE type = 0 AND display = 1 ORDER BY dis_order, id';   //读出所有扩展字段的id
    $fields_arr = $db->getAll($sql);

    foreach ($fields_arr AS $val)       //循环更新扩展用户信息
    {
        $extend_field_index = 'extend_field' . $val['id'];
        if(isset($_POST[$extend_field_index]))
        {
            $temp_field_content = strlen($_POST[$extend_field_index]) > 100 ? mb_substr(htmlspecialchars($_POST[$extend_field_index]), 0, 99) : htmlspecialchars($_POST[$extend_field_index]);
            $sql = 'SELECT * FROM ' . $ecs->table('reg_extend_info') . "  WHERE reg_field_id = '$val[id]' AND user_id = '$user_id'";
            if ($db->getOne($sql))      //如果之前没有记录，则插入
            {
                $sql = 'UPDATE ' . $ecs->table('reg_extend_info') . " SET content = '$temp_field_content' WHERE reg_field_id = '$val[id]' AND user_id = '$user_id'";
            }
            else
            {
                $sql = 'INSERT INTO '. $ecs->table('reg_extend_info') . " (`user_id`, `reg_field_id`, `content`) VALUES ('$user_id', '$val[id]', '$temp_field_content')";
            }
            $db->query($sql);
        }
    }

    /* 写入密码提示问题和答案 */
    if (!empty($passwd_answer) && !empty($sel_question))
    {
        $sql = 'UPDATE ' . $ecs->table('users') . " SET `passwd_question`='$sel_question', `passwd_answer`='$passwd_answer'  WHERE `user_id`='" . $_SESSION['user_id'] . "'";
        $db->query($sql);
    }

    if (!empty($office_phone) && !preg_match( '/^[\d|\_|\-|\s]+$/', $office_phone ) )
    {
        show_message($_LANG['passport_js']['office_phone_invalid']);
    }
    if (!empty($home_phone) && !preg_match( '/^[\d|\_|\-|\s]+$/', $home_phone) )
    {
         show_message($_LANG['passport_js']['home_phone_invalid']);
    }
    if (!is_email($email))
    {
        show_message($_LANG['msg_email_format']);
    }
    if (!empty($msn) && !is_email($msn))
    {
         show_message($_LANG['passport_js']['msn_invalid']);
    }
    if (!empty($qq) && !preg_match('/^\d+$/', $qq))
    {
         show_message($_LANG['passport_js']['qq_invalid']);
    }
    if (!empty($mobile_phone) && !preg_match('/^[\d-\s]+$/', $mobile_phone))
    {
        show_message($_LANG['passport_js']['mobile_phone_invalid']);
    }


    $profile  = array(
        'user_id'  => $user_id,
        'email'    => isset($_POST['email']) ? trim($_POST['email']) : '',
        'sex'      => isset($_POST['sex'])   ? intval($_POST['sex']) : 0,
        'birthday' => $birthday,
        'other'    => isset($other) ? $other : array()
        );


    if (edit_profile($profile))
    {
        show_message($_LANG['edit_profile_success'], $_LANG['profile_lnk'], 'user.php?act=profile', 'info');
    }
    else
    {
        if ($user->error == ERR_EMAIL_EXISTS)
        {
            $msg = sprintf($_LANG['email_exist'], $profile['email']);
        }
        else
        {
            $msg = $_LANG['edit_profile_failed'];
        }
        show_message($msg, '', '', 'info');
    }
}

/* 密码找回-->修改密码界面 */
elseif ($action == 'get_password')
{
    include_once(ROOT_PATH . 'include/lib_passport.php');

    if (isset($_GET['code']) && isset($_GET['uid'])) //从邮件处获得的act
    {
        $code = trim($_GET['code']);
        $uid  = intval($_GET['uid']);

        /* 判断链接的合法性 */
        $user_info = $user->get_profile_by_id($uid);
        if (empty($user_info) || ($user_info && md5($user_info['user_id'] . $_CFG['hash_code'] . $user_info['reg_time']) != $code))
        {
            show_message($_LANG['parm_error'], $_LANG['back_home_lnk'], './', 'info');
        }

        $smarty->assign('uid',    $uid);
        $smarty->assign('code',   $code);
        $smarty->assign('action', 'reset_password');
        $smarty->display('user_passport.dwt');
    }
    else
    {
        /* 短信发送设置 by carson */
        if(intval($_CFG['sms_signin']) > 0){
            $smarty->assign('enabled_sms_signin', 1);
        }
        //显示用户名和email表单
        $smarty->display('user_passport.dwt');
    }
}

/* 密码找回-->输入用户名界面 */
elseif ($action == 'qpassword_name')
{
    //显示输入要找回密码的账号表单
    $smarty->display('user_passport.dwt');
}

/* 密码找回-->根据注册用户名取得密码提示问题界面 */
elseif ($action == 'get_passwd_question')
{
    if (empty($_POST['user_name']))
    {
        show_message($_LANG['no_passwd_question'], $_LANG['back_home_lnk'], './', 'info');
    }
    else
    {
        $user_name = trim($_POST['user_name']);
    }

    //取出会员密码问题和答案
    $sql = 'SELECT user_id, user_name, passwd_question, passwd_answer FROM ' . $ecs->table('users') . " WHERE user_name = '" . $user_name . "'";
    $user_question_arr = $db->getRow($sql);

    //如果没有设置密码问题，给出错误提示
    if (empty($user_question_arr['passwd_answer']))
    {
        show_message($_LANG['no_passwd_question'], $_LANG['back_home_lnk'], './', 'info');
    }

    $_SESSION['temp_user'] = $user_question_arr['user_id'];  //设置临时用户，不具有有效身份
    $_SESSION['temp_user_name'] = $user_question_arr['user_name'];  //设置临时用户，不具有有效身份
    $_SESSION['passwd_answer'] = $user_question_arr['passwd_answer'];   //存储密码问题答案，减少一次数据库访问

    $captcha = intval($_CFG['captcha']);
    if (($captcha & CAPTCHA_LOGIN) && (!($captcha & CAPTCHA_LOGIN_FAIL) || (($captcha & CAPTCHA_LOGIN_FAIL) && $_SESSION['login_fail'] > 2)) && gd_version() > 0)
    {
        $GLOBALS['smarty']->assign('enabled_captcha', 1);
        $GLOBALS['smarty']->assign('rand', mt_rand());
    }

    $smarty->assign('passwd_question', $_LANG['passwd_questions'][$user_question_arr['passwd_question']]);
    $smarty->display('user_passport.dwt');
}

/* 密码找回-->根据提交的密码答案进行相应处理 */
elseif ($action == 'check_answer')
{
    $captcha = intval($_CFG['captcha']);
    if (($captcha & CAPTCHA_LOGIN) && (!($captcha & CAPTCHA_LOGIN_FAIL) || (($captcha & CAPTCHA_LOGIN_FAIL) && $_SESSION['login_fail'] > 2)) && gd_version() > 0)
    {
        if (empty($_POST['captcha']))
        {
            show_message($_LANG['invalid_captcha'], $_LANG['back_retry_answer'], 'user.php?act=qpassword_name', 'error');
        }

        /* 检查验证码 */
        include_once('include/cls_captcha.php');

        $validator = new captcha();
        $validator->session_word = 'captcha_login';
        if (!$validator->check_word($_POST['captcha']))
        {
            show_message($_LANG['invalid_captcha'], $_LANG['back_retry_answer'], 'user.php?act=qpassword_name', 'error');
        }
    }

    if (empty($_POST['passwd_answer']) || $_POST['passwd_answer'] != $_SESSION['passwd_answer'])
    {
        show_message($_LANG['wrong_passwd_answer'], $_LANG['back_retry_answer'], 'user.php?act=qpassword_name', 'info');
    }
    else
    {
        $_SESSION['user_id'] = $_SESSION['temp_user'];
        $_SESSION['user_name'] = $_SESSION['temp_user_name'];
        unset($_SESSION['temp_user']);
        unset($_SESSION['temp_user_name']);
        $smarty->assign('uid',    $_SESSION['user_id']);
        $smarty->assign('action', 'reset_password');
        $smarty->display('user_passport.dwt');
    }
}

/* 发送密码修改确认邮件 */
elseif ($action == 'send_pwd_email')
{
    include_once(ROOT_PATH . 'include/lib_passport.php');

    /* 初始化会员用户名和邮件地址 */
    $user_name = !empty($_POST['user_name']) ? trim($_POST['user_name']) : '';
    $email     = !empty($_POST['email'])     ? trim($_POST['email'])     : '';

    //用户名和邮件地址是否匹配
    $user_info = $user->get_user_info($user_name);

    if ($user_info && $user_info['email'] == $email)
    {
        //生成code
         //$code = md5($user_info[0] . $user_info[1]);

        $code = md5($user_info['user_id'] . $_CFG['hash_code'] . $user_info['reg_time']);
        //发送邮件的函数
        if (send_pwd_email($user_info['user_id'], $user_name, $email, $code))
        {
            show_message($_LANG['send_success'] . $email, $_LANG['back_home_lnk'], './', 'info');
        }
        else
        {
            //发送邮件出错
            show_message($_LANG['fail_send_password'], $_LANG['back_page_up'], './', 'info');
        }
    }
    else
    {
        //用户名与邮件地址不匹配
        show_message($_LANG['username_no_email'], $_LANG['back_page_up'], '', 'info');
    }
}

/* 发送短信找回密码 */
elseif ($action == 'send_pwd_sms')
{
    include_once(ROOT_PATH . 'include/lib_passport.php');

    /* 初始化会员手机 */
    $mobile = !empty($_POST['mobile']) ? trim($_POST['mobile']) : '';
    
    $sql = "SELECT user_id FROM " . $ecs->table('users') . " WHERE mobile_phone='$mobile'";
    $user_id = $db->getOne($sql);
    if ($user_id > 0)
    {
        //生成新密码
        $newPwd = random(6, 1);
        $message = "您的新密码是：" . $newPwd . "，请不要把密码泄露给其他人，如非本人操作，可不用理会！";
        include(ROOT_PATH . 'include/cls_sms.php');
        $sms = new sms();
        $sms_error = array();
        if ($sms->send($mobile, $message, $sms_error)) {
            $sql="UPDATE ".$ecs->table('users'). "SET `ec_salt`='0',password='". md5($newPwd) ."' WHERE mobile_phone= '".$mobile."'";
            $db->query($sql);
            show_message($_LANG['send_success_sms'] . $mobile, $_LANG['relogin_lnk'], './user.php', 'info');
        } else {
            //var_dump($sms_error);
            //发送邮件出错
            show_message($sms_error, $_LANG['back_page_up'], './', 'info');
        }
    }
    else
    {
        //不存在
        show_message($_LANG['username_no_mobile'], $_LANG['back_page_up'], '', 'info');
    }
}

/* 重置新密码 */
elseif ($action == 'reset_password')
{
    //显示重置密码的表单
    $smarty->display('user_passport.dwt');
}

/* 修改会员密码 */
elseif ($action == 'act_edit_password')
{
    include_once(ROOT_PATH . 'include/lib_passport.php');

    $old_password = isset($_POST['old_password']) ? trim($_POST['old_password']) : null;
    $new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
    $user_id      = isset($_POST['uid'])  ? intval($_POST['uid']) : $user_id;
    $code         = isset($_POST['code']) ? trim($_POST['code'])  : '';

    if (strlen($new_password) < 6)
    {
        show_message($_LANG['passport_js']['password_shorter']);
    }

    $user_info = $user->get_profile_by_id($user_id); //论坛记录

    if (($user_info && (!empty($code) && md5($user_info['user_id'] . $_CFG['hash_code'] . $user_info['reg_time']) == $code)) || ($_SESSION['user_id']>0 && $_SESSION['user_id'] == $user_id && $user->check_user($_SESSION['user_name'], $old_password)))
    {
		
        if ($user->edit_user(array('username'=> (empty($code) ? $_SESSION['user_name'] : $user_info['user_name']), 'old_password'=>$old_password, 'password'=>$new_password), empty($code) ? 0 : 1))
        {
			$sql="UPDATE ".$ecs->table('users'). "SET `ec_salt`='0' WHERE user_id= '".$user_id."'";
			$db->query($sql);
            $user->logout();
            show_message($_LANG['edit_password_success'], $_LANG['relogin_lnk'], 'user.php?act=login', 'info');
        }
        else
        {
            show_message($_LANG['edit_password_failure'], $_LANG['back_page_up'], '', 'info');
        }
    }
    else
    {
        show_message($_LANG['edit_password_failure'], $_LANG['back_page_up'], '', 'info');
    }

}

/* 添加一个红包 */
elseif ($action == 'act_add_bonus')
{
    include_once(ROOT_PATH . 'include/lib_transaction.php');

    $bouns_sn = isset($_POST['bonus_sn']) ? intval($_POST['bonus_sn']) : '';

    if (add_bonus($user_id, $bouns_sn))
    {
        show_message($_LANG['add_bonus_sucess'], $_LANG['back_up_page'], 'user.php?act=bonus', 'info');
    }
    else
    {
        $err->show($_LANG['back_up_page'], 'user.php?act=bonus');
    }
}

/* 查看订单列表 */
elseif ($action == 'order_list')
{
    include_once(ROOT_PATH . 'include/lib_transaction.php');

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$ecs->table('order_info'). " WHERE user_id = '$user_id'");

    $pager  = get_pager('user.php', array('act' => $action), $record_count, $page);

    $orders = get_user_orders($user_id, $pager['size'], $pager['start']);
    $merge  = get_user_merge($user_id);

    $smarty->assign('merge',  $merge);
    $smarty->assign('pager',  $pager);
    $smarty->assign('orders', $orders);
    $smarty->display('user_transaction.dwt');
}

/* 异步显示订单列表 by wang */
elseif ($action == 'async_order_list')
{
    include_once(ROOT_PATH . 'include/lib_transaction.php');
    
    $start = $_POST['last'];
    $limit = $_POST['amount'];
    
    $orders = get_user_orders($user_id, $limit, $start);
    if(is_array($orders)){
        foreach($orders as $vo){
            //获取订单第一个商品的图片
            $img = $db->getOne("SELECT g.goods_thumb FROM " .$ecs->table('order_goods'). " as og left join " .$ecs->table('goods'). " g on og.goods_id = g.goods_id WHERE og.order_id = ".$vo['order_id']." limit 1");
            $tracking = ($vo['shipping_id'] > 0) ? '<a href="user.php?act=order_tracking&order_id='.$vo['order_id'].'" class="c-btn3">订单跟踪</a>':'';
            $asyList[] = array(
                'order_status' => '订单状态：'.$vo['order_status'],
                'order_handler' => $vo['handler'],
                'order_content' => '<a href="user.php?act=order_detail&order_id='.$vo['order_id'].'"><table width="100%" border="0" cellpadding="5" cellspacing="0" class="ectouch_table_no_border">
            <tr>
                <td><img src="'.$config['site_url'].$img.'" width="50" height="50" /></td>
                <td>订单编号：'.$vo['order_sn'].'<br>
                订单金额：'.$vo['total_fee'].'<br>
                下单时间：'.$vo['order_time'].'</td>
                <td style="position:relative"><span class="new-arr"></span></td>
            </tr>
          </table></a>',
                'order_tracking' => $tracking
            );
        }
    }
    echo json_encode($asyList);
}

/* 包裹跟踪 by wang */
elseif ($action == 'order_tracking')
{
    $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
    $ajax = isset($_GET['ajax']) ? intval($_GET['ajax']) : 0;
    
    include_once(ROOT_PATH . 'include/lib_transaction.php');
    include_once(ROOT_PATH .'include/lib_order.php');

    $sql = "SELECT order_id,order_sn,invoice_no,shipping_name,shipping_id FROM " .$ecs->table('order_info').
            " WHERE user_id = '$user_id' AND order_id = ".$order_id;
    $orders = $db->getRow($sql);
    //生成快递100查询接口链接
    $shipping   = get_shipping_object($orders['shipping_id']);
    $query_link = $shipping->kuaidi100($orders['invoice_no']);
    //优先使用curl模式发送数据
    if (function_exists('curl_init') == 1){
      $curl = curl_init();
      curl_setopt ($curl, CURLOPT_URL, $query_link);
      curl_setopt ($curl, CURLOPT_HEADER,0);
      curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt ($curl, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
      curl_setopt ($curl, CURLOPT_TIMEOUT,5);
      $get_content = curl_exec($curl);
      curl_close ($curl);
    }
    
    $smarty->assign('trackinfo',      $get_content);
    $smarty->display('user_transaction.dwt');
}

/* 查看订单详情 */
elseif ($action == 'order_detail')
{
    include_once(ROOT_PATH . 'include/lib_transaction.php');
    include_once(ROOT_PATH . 'include/lib_payment.php');
    include_once(ROOT_PATH . 'include/lib_order.php');
    include_once(ROOT_PATH . 'include/lib_clips.php');

    $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

    /* 订单详情 */
    $order = get_order_detail($order_id, $user_id);

    if ($order === false)
    {
        $err->show($_LANG['back_home_lnk'], './');

        exit;
    }

    /* 是否显示添加到购物车 */
    if ($order['extension_code'] != 'group_buy' && $order['extension_code'] != 'exchange_goods')
    {
        $smarty->assign('allow_to_cart', 1);
    }

    /* 订单商品 */
    $goods_list = order_goods($order_id);
    foreach ($goods_list AS $key => $value)
    {
        $goods_list[$key]['market_price'] = price_format($value['market_price'], false);
        $goods_list[$key]['goods_price']  = price_format($value['goods_price'], false);
        $goods_list[$key]['subtotal']     = price_format($value['subtotal'], false);
    }

     /* 设置能否修改使用余额数 */
    if ($order['order_amount'] > 0)
    {
        if ($order['order_status'] == OS_UNCONFIRMED || $order['order_status'] == OS_CONFIRMED)
        {
            $user = user_info($order['user_id']);
            if ($user['user_money'] + $user['credit_line'] > 0)
            {
                $smarty->assign('allow_edit_surplus', 1);
                $smarty->assign('max_surplus', sprintf($_LANG['max_surplus'], $user['user_money']));
            }
        }
    }

    /* 未发货，未付款时允许更换支付方式 */
    if ($order['order_amount'] > 0 && $order['pay_status'] == PS_UNPAYED && $order['shipping_status'] == SS_UNSHIPPED)
    {
        $payment_list = available_payment_list(false, 0, true);

        /* 过滤掉当前支付方式和余额支付方式 */
        if(is_array($payment_list))
        {
            foreach ($payment_list as $key => $payment)
            {
                if ($payment['pay_id'] == $order['pay_id'] || $payment['pay_code'] == 'balance')
                {
                    unset($payment_list[$key]);
                }
            }
        }
        $smarty->assign('payment_list', $payment_list);
    }

    /* 订单 支付 配送 状态语言项 */
    $order['order_status'] = $_LANG['os'][$order['order_status']];
    $order['pay_status'] = $_LANG['ps'][$order['pay_status']];
    $order['shipping_status'] = $_LANG['ss'][$order['shipping_status']];

    $smarty->assign('order',      $order);
    $smarty->assign('goods_list', $goods_list);
    $smarty->display('user_transaction.dwt');
}

/* 取消订单 */
elseif ($action == 'cancel_order')
{
    include_once(ROOT_PATH . 'include/lib_transaction.php');
    include_once(ROOT_PATH . 'include/lib_order.php');

    $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

    if (cancel_order($order_id, $user_id))
    {
        ecs_header("Location: user.php?act=order_list\n");
        exit;
    }
    else
    {
        $err->show($_LANG['order_list_lnk'], 'user.php?act=order_list');
    }
}

/* 收货地址列表界面*/
elseif ($action == 'address_list')
{
    include_once(ROOT_PATH . 'include/lib_transaction.php');
    include_once(ROOT_PATH . 'lang/' .$_CFG['lang']. '/shopping_flow.php');
    $smarty->assign('lang',  $_LANG);

    /* 取得国家列表、商店所在国家、商店所在国家的省列表 */
    $smarty->assign('country_list',       get_regions());
    $smarty->assign('shop_province_list', get_regions(1, $_CFG['shop_country']));

    /* 获得用户所有的收货人信息 */
    $consignee_list = get_consignee_list($_SESSION['user_id']);

    if (count($consignee_list) < 5 && $_SESSION['user_id'] > 0)
    {
        /* 如果用户收货人信息的总数小于5 则增加一个新的收货人信息 by wang */
        //$consignee_list[] = array('country' => $_CFG['shop_country'], 'email' => isset($_SESSION['email']) ? $_SESSION['email'] : '');
    }

    $smarty->assign('consignee_list', $consignee_list);

    //取得国家列表，如果有收货人列表，取得省市区列表
    foreach ($consignee_list AS $region_id => $consignee)
    {
        $consignee['country']  = isset($consignee['country'])  ? intval($consignee['country'])  : 0;
        $consignee['province'] = isset($consignee['province']) ? intval($consignee['province']) : 0;
        $consignee['city']     = isset($consignee['city'])     ? intval($consignee['city'])     : 0;

        $province_list[$region_id] = get_regions(1, $consignee['country']);
        $city_list[$region_id]     = get_regions(2, $consignee['province']);
        $district_list[$region_id] = get_regions(3, $consignee['city']);
    }

    /* 获取默认收货ID */
    $address_id  = $db->getOne("SELECT address_id FROM " .$ecs->table('users'). " WHERE user_id='$user_id'");

    //赋值于模板
    $smarty->assign('real_goods_count', 1);
    $smarty->assign('shop_country',     $_CFG['shop_country']);
    $smarty->assign('shop_province',    get_regions(1, $_CFG['shop_country']));
    $smarty->assign('province_list',    $province_list);
    $smarty->assign('address',          $address_id);
    $smarty->assign('city_list',        $city_list);
    $smarty->assign('district_list',    $district_list);
    $smarty->assign('currency_format',  $_CFG['currency_format']);
    $smarty->assign('integral_scale',   $_CFG['integral_scale']);
    $smarty->assign('name_of_region',   array($_CFG['name_of_region_1'], $_CFG['name_of_region_2'], $_CFG['name_of_region_3'], $_CFG['name_of_region_4']));

    $smarty->display('user_transaction.dwt');
}
/* 添加/编辑收货地址的处理 */
elseif ($action == 'act_edit_address')
{
    include_once(ROOT_PATH . 'include/lib_transaction.php');
    include_once(ROOT_PATH . 'lang/' .$_CFG['lang']. '/shopping_flow.php');
    $smarty->assign('lang', $_LANG);
    
    if($_GET['flag'] == 'display'){
        $id = intval($_GET['id']);
        
        /* 取得国家列表、商店所在国家、商店所在国家的省列表 */
        $smarty->assign('country_list',       get_regions());
        $smarty->assign('shop_province_list', get_regions(1, $_CFG['shop_country']));

        /* 获得用户所有的收货人信息 */
        $consignee_list = get_consignee_list($_SESSION['user_id']);

        foreach ($consignee_list AS $region_id => $vo)
        {
            if($vo['address_id'] == $id){
                $consignee = $vo;
                $smarty->assign('consignee', $vo);                
            }
        }
        $province_list = get_regions(1, 1);
        $city_list     = get_regions(2, $consignee['province']);
        $district_list = get_regions(3, $consignee['city']);

        $smarty->assign('province_list',    $province_list);
        $smarty->assign('city_list',        $city_list);
        $smarty->assign('district_list',    $district_list);
        
        $smarty->display('user_transaction.dwt');
        return false;
    }

    $address = array(
        'user_id'    => $user_id,
        'address_id' => intval($_POST['address_id']),
        'country'    => isset($_POST['country'])   ? intval($_POST['country'])  : 0,
        'province'   => isset($_POST['province'])  ? intval($_POST['province']) : 0,
        'city'       => isset($_POST['city'])      ? intval($_POST['city'])     : 0,
        'district'   => isset($_POST['district'])  ? intval($_POST['district']) : 0,
        'address'    => isset($_POST['address'])   ? compile_str(trim($_POST['address']))    : '',
        'consignee'  => isset($_POST['consignee']) ? compile_str(trim($_POST['consignee']))  : '',
        'email'      => isset($_POST['email'])     ? compile_str(trim($_POST['email']))      : '',
        'tel'        => isset($_POST['tel'])       ? compile_str(make_semiangle(trim($_POST['tel']))) : '',
        'mobile'     => isset($_POST['mobile'])    ? compile_str(make_semiangle(trim($_POST['mobile']))) : '',
        'best_time'  => isset($_POST['best_time']) ? compile_str(trim($_POST['best_time']))  : '',
        'sign_building' => isset($_POST['sign_building']) ? compile_str(trim($_POST['sign_building'])) : '',
        'zipcode'       => isset($_POST['zipcode'])       ? compile_str(make_semiangle(trim($_POST['zipcode']))) : '',
        );

    if (update_address($address))
    {
        show_message($_LANG['edit_address_success'], $_LANG['address_list_lnk'], 'user.php?act=address_list');
    }
}

/* 删除收货地址 */
elseif ($action == 'drop_consignee')
{
    include_once('include/lib_transaction.php');

    $consignee_id = intval($_GET['id']);

    if (drop_consignee($consignee_id))
    {
        ecs_header("Location: user.php?act=address_list\n");
        exit;
    }
    else
    {
        show_message($_LANG['del_address_false']);
    }
}

/* 显示收藏商品列表 */
elseif ($action == 'collection_list')
{
    include_once(ROOT_PATH . 'include/lib_clips.php');

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$ecs->table('collect_goods').
                                " WHERE user_id='$user_id' ORDER BY add_time DESC");

    $pager = get_pager('user.php', array('act' => $action), $record_count, $page);
    $smarty->assign('pager', $pager);
    $smarty->assign('goods_list', get_collection_goods($user_id, $pager['size'], $pager['start']));
    $smarty->assign('url',        $ecs->url());
    $lang_list = array(
        'UTF8'   => $_LANG['charset']['utf8'],
        'GB2312' => $_LANG['charset']['zh_cn'],
        'BIG5'   => $_LANG['charset']['zh_tw'],
    );
    $smarty->assign('lang_list',  $lang_list);
    $smarty->assign('user_id',  $user_id);
    $smarty->display('user_clips.dwt');
}

/* 异步获取收藏 by wang */
elseif ($action == 'async_collection_list'){
    include_once(ROOT_PATH . 'include/lib_clips.php');

    $start = $_POST['last'];
    $limit = $_POST['amount'];
    
    $collections = get_collection_goods($user_id, $limit, $start);
    if(is_array($collections)){
        foreach($collections as $vo){
            $img = $db->getOne("SELECT goods_thumb FROM " .$ecs->table('goods'). " WHERE goods_id = ".$vo['goods_id']);
            $t_price = (empty($vo['promote_price']))? $_LANG['shop_price'].$vo['shop_price']:$_LANG['promote_price'].$vo['promote_price'];
            
            $asyList[] = array(
                'collection' => '<a href="'.$vo['url'].'"><table width="100%" border="0" cellpadding="5" cellspacing="0" class="ectouch_table_no_border">
            <tr>
                <td><img src="'.$config['site_url'].$img.'" width="50" height="50" /></td>
                <td>'.$vo['goods_name'].'<br>'.$t_price.'</td>
                <td align="right"><a href="'.$vo['url'].'" style="color:#1CA2E1">'.$_LANG['add_to_cart'].'</a><br><a href="javascript:if (confirm(\''.$_LANG['remove_collection_confirm'].'\')) location.href=\'user.php?act=delete_collection&collection_id='.$vo['rec_id'].'\'" style="color:#1CA2E1">'.$_LANG['drop'].'</a></td>
            </tr>
          </table></a>'
            );
        }
    }
    echo json_encode($asyList);
}

/* 删除收藏的商品 */
elseif ($action == 'delete_collection')
{
    include_once(ROOT_PATH . 'include/lib_clips.php');

    $collection_id = isset($_GET['collection_id']) ? intval($_GET['collection_id']) : 0;

    if ($collection_id > 0)
    {
        $db->query('DELETE FROM ' .$ecs->table('collect_goods'). " WHERE rec_id='$collection_id' AND user_id ='$user_id'" );
    }

    ecs_header("Location: user.php?act=collection_list\n");
    exit;
}

/* 添加关注商品 */
elseif ($action == 'add_to_attention')
{
    $rec_id = (int)$_GET['rec_id'];
    if ($rec_id)
    {
        $db->query('UPDATE ' .$ecs->table('collect_goods'). "SET is_attention = 1 WHERE rec_id='$rec_id' AND user_id ='$user_id'" );
    }
    ecs_header("Location: user.php?act=collection_list\n");
    exit;
}
/* 取消关注商品 */
elseif ($action == 'del_attention')
{
    $rec_id = (int)$_GET['rec_id'];
    if ($rec_id)
    {
        $db->query('UPDATE ' .$ecs->table('collect_goods'). "SET is_attention = 0 WHERE rec_id='$rec_id' AND user_id ='$user_id'" );
    }
    ecs_header("Location: user.php?act=collection_list\n");
    exit;
}
/* 显示留言列表 */
elseif ($action == 'message_list')
{
    include_once(ROOT_PATH . 'include/lib_clips.php');

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

    $order_id = empty($_GET['order_id']) ? 0 : intval($_GET['order_id']);
    $order_info = array();

    /* 获取用户留言的数量 */
    if ($order_id)
    {
        $sql = "SELECT COUNT(*) FROM " .$ecs->table('feedback').
                " WHERE parent_id = 0 AND order_id = '$order_id' AND user_id = '$user_id'";
        $order_info = $db->getRow("SELECT * FROM " . $ecs->table('order_info') . " WHERE order_id = '$order_id' AND user_id = '$user_id'");
        $order_info['url'] = 'user.php?act=order_detail&order_id=' . $order_id;
    }
    else
    {
        $sql = "SELECT COUNT(*) FROM " .$ecs->table('feedback').
           " WHERE parent_id = 0 AND user_id = '$user_id' AND user_name = '" . $_SESSION['user_name'] . "' AND order_id=0";
    }

    $record_count = $db->getOne($sql);
    $act = array('act' => $action);

    if ($order_id != '')
    {
        $act['order_id'] = $order_id;
    }

    $pager = get_pager('user.php', $act, $record_count, $page, 5);

    $smarty->assign('message_list', get_message_list($user_id, $_SESSION['user_name'], $pager['size'], $pager['start'], $order_id));
    $smarty->assign('pager',        $pager);
    $smarty->assign('order_info',   $order_info);
    $smarty->display('user_clips.dwt');
}

/* 异步获取留言 */
elseif ($action == 'async_message_list'){
    include_once(ROOT_PATH . 'include/lib_clips.php');

    $order_id = empty($_GET['order_id']) ? 0 : intval($_GET['order_id']);
    $start = $_POST['last'];
    $limit = $_POST['amount'];
    
    $message_list = get_message_list($user_id, $_SESSION['user_name'], $limit, $start, $order_id);
    if(is_array($message_list)){
        foreach($message_list as $key=>$vo){
            $re_message = $vo['re_msg_content'] ? '<tr><td>'.$_LANG['shopman_reply'].' ('.$vo['re_msg_time'].')<br>'.$vo['re_msg_content'].'</td></tr>':'';
            $asyList[] = array(
                'message' => '<table width="100%" border="0" cellpadding="5" cellspacing="0" class="ectouch_table_no_border">
            <tr>
                <td><span style="float:right"><a href="user.php?act=del_msg&id='.$key.'&order_id='.$vo['order_id'].'" onclick="if (!confirm(\''.$_LANG['confirm_remove_msg'].'\')) return false;" style="color:#1CA2E1">删除</a></span>'.$vo['msg_type'].'：'.$vo['msg_title'].' - '.$vo['msg_time'].' </td>
            </tr>
            <tr>
                <td>'.$vo['msg_content'].'</td>
            </tr>'.$re_message.'
            
          </table>'
            );
        }
    }
    echo json_encode($asyList);
}

/* 显示评论列表 */
elseif ($action == 'comment_list')
{
    include_once(ROOT_PATH . 'include/lib_clips.php');

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

    /* 获取用户留言的数量 */
    $sql = "SELECT COUNT(*) FROM " .$ecs->table('comment').
           " WHERE parent_id = 0 AND user_id = '$user_id'";
    $record_count = $db->getOne($sql);
    $pager = get_pager('user.php', array('act' => $action), $record_count, $page, 5);

    $smarty->assign('comment_list', get_comment_list($user_id, $pager['size'], $pager['start']));
    $smarty->assign('pager',        $pager);
    $smarty->display('user_clips.dwt');
}


/* 异步获取评论 */
elseif ($action == 'async_comment_list'){
    include_once(ROOT_PATH . 'include/lib_clips.php');

    $start = $_POST['last'];
    $limit = $_POST['amount'];
    
    $comment_list = get_comment_list($user_id, $limit, $start);
    if(is_array($comment_list)){
        foreach($comment_list as $key=>$vo){
            $re_message = $vo['reply_content'] ? '<tr><td>'.$_LANG['reply_comment'].'<br>'.$vo['reply_content'].'</td></tr>':'';
            $asyList[] = array(
                'comment' => '<table width="100%" border="0" cellpadding="5" cellspacing="0" class="ectouch_table_no_border">
            <tr>
                <td><span style="float:right"><a href="user.php?act=del_cmt&id='.$vo['comment_id'].'" onclick="if (!confirm(\''.$_LANG['confirm_remove_msg'].'\')) return false;" style="color:#1CA2E1">删除</a></span>评论：'.$vo['cmt_name'].' - '.$vo['formated_add_time'].' </td>
            </tr>
            <tr>
                <td>'.$vo['content'].'</td>
            </tr>'.$re_message.'
          </table>'
            );
        }
    }
    echo json_encode($asyList);
}

/* 添加我的留言 */
elseif ($action == 'act_add_message')
{
    include_once(ROOT_PATH . 'include/lib_clips.php');

    $message = array(
        'user_id'     => $user_id,
        'user_name'   => $_SESSION['user_name'],
        'user_email'  => $_SESSION['email'],
        'msg_type'    => isset($_POST['msg_type']) ? intval($_POST['msg_type'])     : 0,
        'msg_title'   => isset($_POST['msg_title']) ? trim($_POST['msg_title'])     : '',
        'msg_content' => isset($_POST['msg_content']) ? trim($_POST['msg_content']) : '',
        'order_id'=>empty($_POST['order_id']) ? 0 : intval($_POST['order_id']),
        'upload'      => (isset($_FILES['message_img']['error']) && $_FILES['message_img']['error'] == 0) || (!isset($_FILES['message_img']['error']) && isset($_FILES['message_img']['tmp_name']) && $_FILES['message_img']['tmp_name'] != 'none')
         ? $_FILES['message_img'] : array()
     );

    if (add_message($message))
    {
        show_message($_LANG['add_message_success'], $_LANG['message_list_lnk'], 'user.php?act=message_list&order_id=' . $message['order_id'],'info');
    }
    else
    {
        $err->show($_LANG['message_list_lnk'], 'user.php?act=message_list');
    }
}

/* 标签云列表 */
elseif ($action == 'tag_list')
{
    include_once(ROOT_PATH . 'include/lib_clips.php');

    $good_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    $smarty->assign('tags',      get_user_tags($user_id));
    $smarty->assign('tags_from', 'user');
    $smarty->display('user_clips.dwt');
}

/* 删除标签云的处理 */
elseif ($action == 'act_del_tag')
{
    include_once(ROOT_PATH . 'include/lib_clips.php');

    $tag_words = isset($_GET['tag_words']) ? trim($_GET['tag_words']) : '';
    delete_tag($tag_words, $user_id);

    ecs_header("Location: user.php?act=tag_list\n");
    exit;

}

/* 显示缺货登记列表 */
elseif ($action == 'booking_list')
{
    include_once(ROOT_PATH . 'include/lib_clips.php');

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

    /* 获取缺货登记的数量 */
    $sql = "SELECT COUNT(*) " .
            "FROM " .$ecs->table('booking_goods'). " AS bg, " .
                     $ecs->table('goods') . " AS g " .
            "WHERE bg.goods_id = g.goods_id AND user_id = '$user_id'";
    $record_count = $db->getOne($sql);
    $pager = get_pager('user.php', array('act' => $action), $record_count, $page);

    $smarty->assign('booking_list', get_booking_list($user_id, $pager['size'], $pager['start']));
    $smarty->assign('pager',        $pager);
    $smarty->display('user_clips.dwt');
}
/* 添加缺货登记页面 */
elseif ($action == 'add_booking')
{
    include_once(ROOT_PATH . 'include/lib_clips.php');

    $goods_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($goods_id == 0)
    {
        show_message($_LANG['no_goods_id'], $_LANG['back_page_up'], '', 'error');
    }

    /* 根据规格属性获取货品规格信息 */
    $goods_attr = '';
    if ($_GET['spec'] != '')
    {
        $goods_attr_id = $_GET['spec'];

        $attr_list = array();
        $sql = "SELECT a.attr_name, g.attr_value " .
                "FROM " . $ecs->table('goods_attr') . " AS g, " .
                    $ecs->table('attribute') . " AS a " .
                "WHERE g.attr_id = a.attr_id " .
                "AND g.goods_attr_id " . db_create_in($goods_attr_id);
        $res = $db->query($sql);
        while ($row = $db->fetchRow($res))
        {
            $attr_list[] = $row['attr_name'] . ': ' . $row['attr_value'];
        }
        $goods_attr = join(chr(13) . chr(10), $attr_list);
    }
    $smarty->assign('goods_attr', $goods_attr);

    $smarty->assign('info', get_goodsinfo($goods_id));
    $smarty->display('user_clips.dwt');

}

/* 添加缺货登记的处理 */
elseif ($action == 'act_add_booking')
{
    include_once(ROOT_PATH . 'include/lib_clips.php');

    $booking = array(
        'goods_id'     => isset($_POST['id'])      ? intval($_POST['id'])     : 0,
        'goods_amount' => isset($_POST['number'])  ? intval($_POST['number']) : 0,
        'desc'         => isset($_POST['desc'])    ? trim($_POST['desc'])     : '',
        'linkman'      => isset($_POST['linkman']) ? trim($_POST['linkman'])  : '',
        'email'        => isset($_POST['email'])   ? trim($_POST['email'])    : '',
        'tel'          => isset($_POST['tel'])     ? trim($_POST['tel'])      : '',
        'booking_id'   => isset($_POST['rec_id'])  ? intval($_POST['rec_id']) : 0
    );

    // 查看此商品是否已经登记过
    $rec_id = get_booking_rec($user_id, $booking['goods_id']);
    if ($rec_id > 0)
    {
        show_message($_LANG['booking_rec_exist'], $_LANG['back_page_up'], '', 'error');
    }

    if (add_booking($booking))
    {
        show_message($_LANG['booking_success'], $_LANG['back_booking_list'], 'user.php?act=booking_list',
        'info');
    }
    else
    {
        $err->show($_LANG['booking_list_lnk'], 'user.php?act=booking_list');
    }
}

/* 删除缺货登记 */
elseif ($action == 'act_del_booking')
{
    include_once(ROOT_PATH . 'include/lib_clips.php');

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id == 0 || $user_id == 0)
    {
        ecs_header("Location: user.php?act=booking_list\n");
        exit;
    }

    $result = delete_booking($id, $user_id);
    if ($result)
    {
        ecs_header("Location: user.php?act=booking_list\n");
        exit;
    }
}

/* 确认收货 */
elseif ($action == 'affirm_received')
{
    include_once(ROOT_PATH . 'include/lib_transaction.php');

    $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

    if (affirm_received($order_id, $user_id))
    {
        ecs_header("Location: user.php?act=order_list\n");
        exit;
    }
    else
    {
        $err->show($_LANG['order_list_lnk'], 'user.php?act=order_list');
    }
}

/* 会员退款申请界面 */
elseif ($action == 'account_raply')
{
	/*- add 显示可体现佣金金额*/
	include_once(ROOT_PATH . 'include/lib_clips.php');
	$surplus_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $account    = get_surplus_info($surplus_id);

    $surplus_amount = get_user_surplus($user_id);
      //  print_r($surplus_amount);exit;
    if (empty($surplus_amount))
    {
        $surplus_amount = 0;
    }
	
	//实实  微分销 add 读取用户已保存的提现银行卡信息
	$user_msg = "SELECT * FROM `ecs_users` WHERE `user_id`=". $user_id;
	$userbank_msg = $db -> getRow($user_msg);
	$smarty->assign('real_name',       $userbank_msg['real_name']);
	$smarty->assign('bank_name',       $userbank_msg['bank_name']);
	$smarty->assign('bank_account',    $userbank_msg['bank_account']);
	$smarty->assign('mobile_phone',    $userbank_msg['mobile_phone']);
	//实实 add end
	
	$smarty->assign('surplus_amount',       $surplus_amount);
    $smarty->display('user_transaction1.dwt');
}

/* 会员预付款界面 */
elseif ($action == 'account_deposit')
{
    include_once(ROOT_PATH . 'include/lib_clips.php');

    $surplus_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $account    = get_surplus_info($surplus_id);

    $smarty->assign('payment', get_online_payment_list(false));
    $smarty->assign('order',   $account);
    $smarty->display('user_transaction.dwt');
}

/* 会员账目明细界面 */
elseif ($action == 'account_detail')
{
    include_once(ROOT_PATH . 'include/lib_clips.php');

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

    $account_type = 'user_money';

    /* 获取记录条数 */
    $sql = "SELECT COUNT(*) FROM " .$ecs->table('account_log').
           " WHERE user_id = '$user_id'" .
           " AND $account_type <> 0 ";
    $record_count = $db->getOne($sql);

    //分页函数
    $pager = get_pager('user.php', array('act' => $action), $record_count, $page);

    //获取剩余余额
    $surplus_amount = get_user_surplus($user_id);
    if (empty($surplus_amount))
    {
        $surplus_amount = 0;
    }

    //获取余额记录
    $account_log = array();
    $sql = "SELECT * FROM " . $ecs->table('account_log') .
           " WHERE user_id = '$user_id'" .
           " AND $account_type <> 0 " .
           " ORDER BY log_id DESC";
    $res = $GLOBALS['db']->selectLimit($sql, $pager['size'], $pager['start']);
    while ($row = $db->fetchRow($res))
    {
        $row['change_time'] = local_date($_CFG['date_format'], $row['change_time']);
        $row['type'] = $row[$account_type] > 0 ? $_LANG['account_inc'] : $_LANG['account_dec'];
        $row['user_money'] = price_format(abs($row['user_money']), false);
        $row['frozen_money'] = price_format(abs($row['frozen_money']), false);
        $row['rank_points'] = abs($row['rank_points']);
        $row['pay_points'] = abs($row['pay_points']);
        $row['short_change_desc'] = sub_str($row['change_desc'], 60);
        $row['amount'] = $row[$account_type];
        $account_log[] = $row;
    }

    //模板赋值
    $smarty->assign('surplus_amount', price_format($surplus_amount, false));
    $smarty->assign('account_log',    $account_log);
    $smarty->assign('pager',          $pager);
    $smarty->display('user_transaction1.dwt');
}

/* 会员充值和提现申请记录 */
elseif ($action == 'account_log')
{
    include_once(ROOT_PATH . 'include/lib_clips.php');

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

    /* 获取记录条数 */
    $sql = "SELECT COUNT(*) FROM " .$ecs->table('user_account').
           " WHERE user_id = '$user_id'" .
           " AND process_type " . db_create_in(array(SURPLUS_SAVE, SURPLUS_RETURN));
    $record_count = $db->getOne($sql);

    //分页函数
    $pager = get_pager('user.php', array('act' => $action), $record_count, $page);

    //获取剩余余额
    $surplus_amount = get_user_surplus($user_id);
    if (empty($surplus_amount))
    {
        $surplus_amount = 0;
    }

    //获取余额记录
    $account_log = get_account_log($user_id, $pager['size'], $pager['start']);

    //模板赋值
    $smarty->assign('surplus_amount', price_format($surplus_amount, false));
    $smarty->assign('account_log',    $account_log);
    $smarty->assign('pager',          $pager);
    $smarty->display('user_transaction1.dwt');
}

/* 对会员余额申请的处理 */
elseif ($action == 'act_account')
{
    include_once(ROOT_PATH . 'include/lib_clips.php');
    include_once(ROOT_PATH . 'include/lib_order.php');
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
    if ($amount <= 0)
    {
        show_message($_LANG['amount_gt_zero']);
    }

	
	/*实实分销微分销优化提现流程*/
	$real_name=$_POST['real_name'];//真实姓名
	$bank_name=$_POST['bank_name'];//开户行
	$bank_account=$_POST['bank_account'];//银行账户
	$mobile_phone=$_POST['mobile_phone'];//手机号码
	$user_note=$_POST['user_note'];//留言
	$user_msg="UPDATE `ecs_users` SET `real_name` = '" . $real_name . "', `bank_name` = '" . $bank_name . "', `bank_account` = '" . $bank_account . "', `mobile_phone` = '" . $mobile_phone . "' WHERE `user_id` =" .$user_id;//实-实  微  分销  系统 add 实现保存用户提现账号信息
	$GLOBALS['db']->query($user_msg);//实-实  微  分销  系统 add
	
	$shishi="真实姓名:【".$real_name."】开户行:【".$bank_name."】银行卡号:【".$bank_account."】手机:【".$mobile_phone."】留言:【".$user_note."】";
	
	
	/*实-实  微  分销系统分销微分销优化提现流程*/
    /* 变量初始化 */
    $surplus = array(
            'user_id'      => $user_id,
            'rec_id'       => !empty($_POST['rec_id'])      ? intval($_POST['rec_id'])       : 0,
            'process_type' => isset($_POST['surplus_type']) ? intval($_POST['surplus_type']) : 0,
            'payment_id'   => isset($_POST['payment_id'])   ? intval($_POST['payment_id'])   : 0,
            'user_note'    => $shishi,
            'amount'       => $amount
    );

    /* 退款申请的处理 */
    if ($surplus['process_type'] == 1)
    {
        /* 判断是否有足够的余额的进行退款的操作 */
        $sur_amount = get_user_surplus($user_id);
        if ($amount > $sur_amount)
        {
            $content = $_LANG['surplus_amount_error'];
            show_message($content, $_LANG['back_page_up'], '', 'info');
        }

        //插入会员账目明细
        $amount = '-'.$amount;
        $surplus['payment'] = '';
        $surplus['rec_id']  = insert_user_account($surplus, $amount);

        /* 如果成功提交 */
        if ($surplus['rec_id'] > 0)
        {
            $content = $_LANG['surplus_appl_submit'];
            show_message($content, $_LANG['back_account_log'], 'user.php?act=account_log', 'info');
        }
        else
        {
            $content = $_LANG['process_false'];
            show_message($content, $_LANG['back_page_up'], '', 'info');
        }
    }
    /* 如果是会员预付款，跳转到下一步，进行线上支付的操作 */
    else
    {
        if ($surplus['payment_id'] <= 0)
        {
            show_message($_LANG['select_payment_pls']);
        }

        include_once(ROOT_PATH .'include/lib_payment.php');

        //获取支付方式名称
        $payment_info = array();
        $payment_info = payment_info($surplus['payment_id']);
        $surplus['payment'] = $payment_info['pay_name'];

        if ($surplus['rec_id'] > 0)
        {
            //更新会员账目明细
            $surplus['rec_id'] = update_user_account($surplus);
        }
        else
        {
            //插入会员账目明细
            $surplus['rec_id'] = insert_user_account($surplus, $amount);
        }

        //取得支付信息，生成支付代码
        $payment = unserialize_config($payment_info['pay_config']);

        //生成伪订单号, 不足的时候补0
        $order = array();
        $order['order_sn']       = $surplus['rec_id'];
        $order['user_name']      = $_SESSION['user_name'];
        $order['surplus_amount'] = $amount;

        //计算支付手续费用
        $payment_info['pay_fee'] = pay_fee($surplus['payment_id'], $order['surplus_amount'], 0);

        //计算此次预付款需要支付的总金额
        $order['order_amount']   = $amount + $payment_info['pay_fee'];

        //记录支付log
        $order['log_id'] = insert_pay_log($surplus['rec_id'], $order['order_amount'], $type=PAY_SURPLUS, 0);

        /* 调用相应的支付方式文件 */
        include_once(ROOT_PATH . 'include/modules/payment/' . $payment_info['pay_code'] . '.php');

        /* 取得在线支付方式的支付按钮 */
        $pay_obj = new $payment_info['pay_code'];
				//开始实-实  微分销系统分销开发，兼容微信支付
		$order['pay_id']=4;
		$GLOBALS['db']->autoExecute($ecs->table('order_info'), $order, 'INSERT');
		$new_order_id = $db->insert_id();	
		$amount	=	$order['order_amount'];
		$type=PAY_SURPLUS;
		
		$is_paid=0;
		$sql = 'INSERT INTO ' .$GLOBALS['ecs']->table('pay_log')." (order_id, order_amount, order_type, is_paid)".
            " VALUES  ('$new_order_id', '$amount', '$type', '$is_paid')";
		$GLOBALS['db']->query($sql);		
		//结束
        $payment_info['pay_button'] = $pay_obj->get_code($order, $payment);

        /* 模板赋值 */
        $smarty->assign('payment', $payment_info);
        $smarty->assign('pay_fee', price_format($payment_info['pay_fee'], false));
        $smarty->assign('amount',  price_format($amount, false));
        $smarty->assign('order',   $order);
        $smarty->display('user_transaction.dwt');
    }
}

/* 删除会员余额 */
elseif ($action == 'cancel')
{
    include_once(ROOT_PATH . 'include/lib_clips.php');

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id == 0 || $user_id == 0)
    {
        ecs_header("Location: user.php?act=account_log\n");
        exit;
    }

    $result = del_user_account($id, $user_id);
    if ($result)
    {
        ecs_header("Location: user.php?act=account_log\n");
        exit;
    }
}

/* 会员通过帐目明细列表进行再付款的操作 */
elseif ($action == 'pay')
{
    include_once(ROOT_PATH . 'include/lib_clips.php');
    include_once(ROOT_PATH . 'include/lib_payment.php');
    include_once(ROOT_PATH . 'include/lib_order.php');

    //变量初始化
    $surplus_id = isset($_GET['id'])  ? intval($_GET['id'])  : 0;
    $payment_id = isset($_GET['pid']) ? intval($_GET['pid']) : 0;

    if ($surplus_id == 0)
    {
        ecs_header("Location: user.php?act=account_log\n");
        exit;
    }

    //如果原来的支付方式已禁用或者已删除, 重新选择支付方式
    if ($payment_id == 0)
    {
        ecs_header("Location: user.php?act=account_deposit&id=".$surplus_id."\n");
        exit;
    }

    //获取单条会员帐目信息
    $order = array();
    $order = get_surplus_info($surplus_id);

    //支付方式的信息
    $payment_info = array();
    $payment_info = payment_info($payment_id);

    /* 如果当前支付方式没有被禁用，进行支付的操作 */
    if (!empty($payment_info))
    {
        //取得支付信息，生成支付代码
        $payment = unserialize_config($payment_info['pay_config']);

        //生成伪订单号
        $order['order_sn'] = $surplus_id;

        //获取需要支付的log_id
        $order['log_id'] = get_paylog_id($surplus_id, $pay_type = PAY_SURPLUS);

        $order['user_name']      = $_SESSION['user_name'];
        $order['surplus_amount'] = $order['amount'];

        //计算支付手续费用
        $payment_info['pay_fee'] = pay_fee($payment_id, $order['surplus_amount'], 0);

        //计算此次预付款需要支付的总金额
        $order['order_amount']   = $order['surplus_amount'] + $payment_info['pay_fee'];

        //如果支付费用改变了，也要相应的更改pay_log表的order_amount
        $order_amount = $db->getOne("SELECT order_amount FROM " .$ecs->table('pay_log')." WHERE log_id = '$order[log_id]'");
        if ($order_amount <> $order['order_amount'])
        {
            $db->query("UPDATE " .$ecs->table('pay_log').
                       " SET order_amount = '$order[order_amount]' WHERE log_id = '$order[log_id]'");
        }

        /* 调用相应的支付方式文件 */
        include_once(ROOT_PATH . 'include/modules/payment/' . $payment_info['pay_code'] . '.php');

        /* 取得在线支付方式的支付按钮 */
        $pay_obj = new $payment_info['pay_code'];
        $payment_info['pay_button'] = $pay_obj->get_code($order, $payment);

        /* 模板赋值 */
        $smarty->assign('payment', $payment_info);
        $smarty->assign('order',   $order);
        $smarty->assign('pay_fee', price_format($payment_info['pay_fee'], false));
        $smarty->assign('amount',  price_format($order['surplus_amount'], false));
        $smarty->assign('action',  'act_account');
        $smarty->display('user_transaction.dwt');
    }
    /* 重新选择支付方式 */
    else
    {
        include_once(ROOT_PATH . 'include/lib_clips.php');

        $smarty->assign('payment', get_online_payment_list());
        $smarty->assign('order',   $order);
        $smarty->assign('action',  'account_deposit');
        $smarty->display('user_transaction.dwt');
    }
}

/* 添加标签(ajax) */
elseif ($action == 'add_tag')
{
    include_once('include/cls_json.php');
    include_once('include/lib_clips.php');

    $result = array('error' => 0, 'message' => '', 'content' => '');
    $id     = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $tag    = isset($_POST['tag']) ? json_str_iconv(trim($_POST['tag'])) : '';

    if ($user_id == 0)
    {
        /* 用户没有登录 */
        $result['error']   = 1;
        $result['message'] = $_LANG['tag_anonymous'];
    }
    else
    {
        add_tag($id, $tag); // 添加tag
        clear_cache_files('goods'); // 删除缓存

        /* 重新获得该商品的所有缓存 */
        $arr = get_tags($id);

        foreach ($arr AS $row)
        {
            $result['content'][] = array('word' => htmlspecialchars($row['tag_words']), 'count' => $row['tag_count']);
        }
    }

    $json = new JSON;

    echo $json->encode($result);
    exit;
}

/* 添加收藏商品(ajax) */
elseif ($action == 'collect')
{
    include_once(ROOT_PATH .'include/cls_json.php');
    $json = new JSON();
    $result = array('error' => 0, 'message' => '');
    $goods_id = $_GET['id'];

    if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == 0)
    {
        $result['error'] = 1;
        $result['message'] = $_LANG['login_please'];
        die($json->encode($result));
    }
    else
    {
        /* 检查是否已经存在于用户的收藏夹 */
        $sql = "SELECT COUNT(*) FROM " .$GLOBALS['ecs']->table('collect_goods') .
            " WHERE user_id='$_SESSION[user_id]' AND goods_id = '$goods_id'";
        if ($GLOBALS['db']->GetOne($sql) > 0)
        {
            $result['error'] = 1;
            $result['message'] = $GLOBALS['_LANG']['collect_existed'];
            die($json->encode($result));
        }
        else
        {
            $time = gmtime();
            $sql = "INSERT INTO " .$GLOBALS['ecs']->table('collect_goods'). " (user_id, goods_id, add_time)" .
                    "VALUES ('$_SESSION[user_id]', '$goods_id', '$time')";

            if ($GLOBALS['db']->query($sql) === false)
            {
                $result['error'] = 1;
                $result['message'] = $GLOBALS['db']->errorMsg();
                die($json->encode($result));
            }
            else
            {
                $result['error'] = 0;
                $result['message'] = $GLOBALS['_LANG']['collect_success'];
                die($json->encode($result));
            }
        }
    }
}

/* 删除留言 */
elseif ($action == 'del_msg')
{
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $order_id = empty($_GET['order_id']) ? 0 : intval($_GET['order_id']);

    if ($id > 0)
    {
        $sql = 'SELECT user_id, message_img FROM ' .$ecs->table('feedback'). " WHERE msg_id = '$id' LIMIT 1";
        $row = $db->getRow($sql);
        if ($row && $row['user_id'] == $user_id)
        {
            /* 验证通过，删除留言，回复，及相应文件 */
            if ($row['message_img'])
            {
                @unlink(ROOT_PATH . DATA_DIR . '/feedbackimg/'. $row['message_img']);
            }
            $sql = "DELETE FROM " .$ecs->table('feedback'). " WHERE msg_id = '$id' OR parent_id = '$id'";
            $db->query($sql);
        }
    }
    ecs_header("Location: user.php?act=message_list&order_id=$order_id\n");
    exit;
}

/* 删除评论 */
elseif ($action == 'del_cmt')
{
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id > 0)
    {
        $sql = "DELETE FROM " .$ecs->table('comment'). " WHERE comment_id = '$id' AND user_id = '$user_id'";
        $db->query($sql);
    }
    ecs_header("Location: user.php?act=comment_list\n");
    exit;
}

/* 合并订单 */
elseif ($action == 'merge_order')
{
    include_once(ROOT_PATH .'include/lib_transaction.php');
    include_once(ROOT_PATH .'include/lib_order.php');
    $from_order = isset($_POST['from_order']) ? trim($_POST['from_order']) : '';
    $to_order   = isset($_POST['to_order']) ? trim($_POST['to_order']) : '';
    if (merge_user_order($from_order, $to_order, $user_id))
    {
        show_message($_LANG['merge_order_success'],$_LANG['order_list_lnk'],'user.php?act=order_list', 'info');
    }
    else
    {
        $err->show($_LANG['order_list_lnk']);
    }
}
/* 将指定订单中商品添加到购物车 */
elseif ($action == 'return_to_cart')
{
    include_once(ROOT_PATH .'include/cls_json.php');
    include_once(ROOT_PATH .'include/lib_transaction.php');
    $json = new JSON();

    $result = array('error' => 0, 'message' => '', 'content' => '');
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    if ($order_id == 0)
    {
        $result['error']   = 1;
        $result['message'] = $_LANG['order_id_empty'];
        die($json->encode($result));
    }

    if ($user_id == 0)
    {
        /* 用户没有登录 */
        $result['error']   = 1;
        $result['message'] = $_LANG['login_please'];
        die($json->encode($result));
    }

    /* 检查订单是否属于该用户 */
    $order_user = $db->getOne("SELECT user_id FROM " .$ecs->table('order_info'). " WHERE order_id = '$order_id'");
    if (empty($order_user))
    {
        $result['error'] = 1;
        $result['message'] = $_LANG['order_exist'];
        die($json->encode($result));
    }
    else
    {
        if ($order_user != $user_id)
        {
            $result['error'] = 1;
            $result['message'] = $_LANG['no_priv'];
            die($json->encode($result));
        }
    }

    $message = return_to_cart($order_id);

    if ($message === true)
    {
        $result['error'] = 0;
        $result['message'] = $_LANG['return_to_cart_success'];
        die($json->encode($result));
    }
    else
    {
        $result['error'] = 1;
        $result['message'] = $_LANG['order_exist'];
        die($json->encode($result));
    }

}
elseif ($action == 'user_card')
{
	if($_POST['bind'])
	{
	   $card_no =trim($_POST['card_no']);
	   $card_pass =trim($_POST['card_pass']);
	   if(empty($card_no))
	   {
	      show_message("卡号为空");	   
	   }
	   
	   $sql = "select * from " . $ecs->table('user_card') . " where card_no='$card_no' ";
	   $card_info = $db->getRow($sql);
	   if($card_info)
	   {
	      $user_card_num = $db->getOne("select count(*) from "  . $ecs->table('user_card') . " where card_no='$card_no'  and user_id='$_SESSION[user_id]' " );
	      if($user_card_num>=1)
		  {
		     show_message("您已绑定过一个会员止，由于一个会员最多绑定一个会员卡，无法绑定其它卡"); 
		  }
		  
		  if($card_info['user_id'] ==$_SESSION['user_id'])
		  {
		      show_message("您已绑定了此卡"); 	  
		  }
		  if(!$card_info['is_show'])
		  {
		      show_message("此卡已被禁用"); 	  
		  }
		  elseif($card_info['user_id'] >0)
		  {
		      show_message("此卡已被其它会员绑定");
		  }
		  elseif($card_info['card_pass'] ==$card_pass['card_pass'])
		  {
		      show_message("卡密错误！");
		  }
		  else
		  {
		     $db->query("update " . $ecs->table('user_card') . " set user_id='$_SESSION[user_id]', bind_time ='". gmtime() ."', card_status=1 where card_no='$card_no' limit 1 ");
			 $arr['user_money'] = floatval($card_info['user_money']);
			 $arr['pay_points'] = $card_info['pay_points'];
			 $arr['rank_points'] = $card_info['rank_points'];
			 if($card_info['card_level'])
			 {
				 $card_rank = $db->getOne(" select rank_id from " . $ecs->table('user_rank') . " where rank_name='$card_info[card_level]' ");
				 if($card_rank)  $arr['user_rank'] = $card_rank;
				 $sql = 'UPDATE ' . $ecs->table('users') . " SET `user_rank`='$card_rank'  WHERE `user_id`='" . $_SESSION['user_id'] . "'";
                 $db->query($sql);
			 }
			 log_account_change($_SESSION['user_id'], $arr['user_money'], 0, $arr['rank_points'], $arr['pay_points'], '绑定会卡'.$card_no.'充值等级积分:'.$arr['rank_points'].',消费积分'.$arr['pay_points']);
			 $sql = 'UPDATE ' . $ecs->table('user_card') . " SET `user_money`='0', `pay_points`='0', `rank_points`='0'  WHERE `card_no`='" . $card_no . "'";
			 $db->query($sql);
			  
			 
             show_message("绑定成功", '用户信息', 'user.php?act=user_card', 'info');
		  
		  }
	   }
	   else
	   {
	     show_message("卡号不存在", '重新绑定', 'user.php?act=user_card', 'info');	 
	   }
	   
	   exit;
	}

	if($_POST['unbind'])
	{
	   $card_no =trim($_POST['card_no']);
	   $card_pass =trim($_POST['card_pass']);
	   if(empty($card_no))
	   {
	      show_message("卡号为空", '重新解绑', 'user.php?act=user_card', 'info');	   
	   }
	   $num = $db->getOne("select count(*) from " . $ecs->table('user_card') . " where card_no='$card_no' and card_pass='$card_pass' and user_id='$_SESSION[user_id]' and is_show =1 ");
	   if($num>0)
	   {
	      $db->query("update " . $ecs->table('user_card') . " set user_id='', bind_time ='', card_status=0  where card_no='$card_no' and user_id='$_SESSION[user_id]' and is_show =1");
		  show_message("解绑成功", '会员信息', 'user.php?act=user_card', 'info');
	   }
	   else
	   {
	     show_message("密码错误或未查到您绑定的卡号信息，无法解绑", '重新解绑', 'user.php?act=user_card', 'info');	 
	   }
	   exit;
	}
	
	if($_POST['chgcardpass'])
	{
	   $card_no =trim($_POST['card_no']);
	   $card_pass =trim($_POST['card_pass']);
	   if(empty($card_no) || empty($card_pass))
	   {
	      show_message("卡号卡密不能为空");	   
	   }
	   $num = $db->getOne("select count(*) from " . $ecs->table('user_card') . " where card_no='$card_no' and user_id='$_SESSION[user_id]' and is_show =1 ");
	   //echo $num;
	   if($num>0)
	   {
	      $db->query("update " . $ecs->table('user_card') . " set card_pass='$card_pass' where card_no='$card_no' and user_id='$_SESSION[user_id]' and is_show =1 ");
		  show_message("修改密码成功");
	   }
	   else
	   {
	     show_message("未查到您绑定的卡号信息，无法修改密码");	 
	   }
	   exit;
	}
	
	
	$sql = "select * from " . $ecs->table('user_card') . " where user_id='$_SESSION[user_id]' ";
	$res =$db->query($sql);
	$card_list = array();
	while($row=$db->fetchRow($res))
    {
	    $row['str_bind_time'] = local_date('Y-m-d H:I:s',$row['bind_time']);
		$card_list[] = $row;
	}
    $smarty->assign('card_list', $card_list);
	$smarty->display('user_transaction.dwt');
}
/* 编辑使用余额支付的处理 */
elseif ($action == 'act_edit_surplus')
{
    /* 检查是否登录 */
    if ($_SESSION['user_id'] <= 0)
    {
        ecs_header("Location: ./\n");
        exit;
    }

    /* 检查订单号 */
    $order_id = intval($_POST['order_id']);
    if ($order_id <= 0)
    {
        ecs_header("Location: ./\n");
        exit;
    }

    /* 检查余额 */
    $surplus = floatval($_POST['surplus']);
    if ($surplus <= 0)
    {
        $err->add($_LANG['error_surplus_invalid']);
        $err->show($_LANG['order_detail'], 'user.php?act=order_detail&order_id=' . $order_id);
    }

    include_once(ROOT_PATH . 'include/lib_order.php');

    /* 取得订单 */
    $order = order_info($order_id);
    if (empty($order))
    {
        ecs_header("Location: ./\n");
        exit;
    }

    /* 检查订单用户跟当前用户是否一致 */
    if ($_SESSION['user_id'] != $order['user_id'])
    {
        ecs_header("Location: ./\n");
        exit;
    }

    /* 检查订单是否未付款，检查应付款金额是否大于0 */
    if ($order['pay_status'] != PS_UNPAYED || $order['order_amount'] <= 0)
    {
        $err->add($_LANG['error_order_is_paid']);
        $err->show($_LANG['order_detail'], 'user.php?act=order_detail&order_id=' . $order_id);
    }

    /* 计算应付款金额（减去支付费用） */
    $order['order_amount'] -= $order['pay_fee'];

    /* 余额是否超过了应付款金额，改为应付款金额 */
    if ($surplus > $order['order_amount'])
    {
        $surplus = $order['order_amount'];
    }

    /* 取得用户信息 */
    $user = user_info($_SESSION['user_id']);

    /* 用户帐户余额是否足够 */
    if ($surplus > $user['user_money'] + $user['credit_line'])
    {
        $err->add($_LANG['error_surplus_not_enough']);
        $err->show($_LANG['order_detail'], 'user.php?act=order_detail&order_id=' . $order_id);
    }

    /* 修改订单，重新计算支付费用 */
    $order['surplus'] += $surplus;
    $order['order_amount'] -= $surplus;
    if ($order['order_amount'] > 0)
    {
        $cod_fee = 0;
        if ($order['shipping_id'] > 0)
        {
            $regions  = array($order['country'], $order['province'], $order['city'], $order['district']);
            $shipping = shipping_area_info($order['shipping_id'], $regions);
            if ($shipping['support_cod'] == '1')
            {
                $cod_fee = $shipping['pay_fee'];
            }
        }

        $pay_fee = 0;
        if ($order['pay_id'] > 0)
        {
            $pay_fee = pay_fee($order['pay_id'], $order['order_amount'], $cod_fee);
        }

        $order['pay_fee'] = $pay_fee;
        $order['order_amount'] += $pay_fee;
    }

    /* 如果全部支付，设为已确认、已付款 */
    if ($order['order_amount'] == 0)
    {
        if ($order['order_status'] == OS_UNCONFIRMED)
        {
            $order['order_status'] = OS_CONFIRMED;
            $order['confirm_time'] = gmtime();
        }
        $order['pay_status'] = PS_PAYED;
        $order['pay_time'] = gmtime();
    }
    $order = addslashes_deep($order);
    update_order($order_id, $order);

    /* 更新用户余额 */
    $change_desc = sprintf($_LANG['pay_order_by_surplus'], $order['order_sn']);
    log_account_change($user['user_id'], (-1) * $surplus, 0, 0, 0, $change_desc);

    /* 跳转 */
    ecs_header('Location: user.php?act=order_detail&order_id=' . $order_id . "\n");
    exit;
}

/* 编辑使用余额支付的处理 */
elseif ($action == 'act_edit_payment')
{
    /* 检查是否登录 */
    if ($_SESSION['user_id'] <= 0)
    {
        ecs_header("Location: ./\n");
        exit;
    }

    /* 检查支付方式 */
    $pay_id = intval($_POST['pay_id']);
    if ($pay_id <= 0)
    {
        ecs_header("Location: ./\n");
        exit;
    }

    include_once(ROOT_PATH . 'include/lib_order.php');
    $payment_info = payment_info($pay_id);
    if (empty($payment_info))
    {
        ecs_header("Location: ./\n");
        exit;
    }

    /* 检查订单号 */
    $order_id = intval($_POST['order_id']);
    if ($order_id <= 0)
    {
        ecs_header("Location: ./\n");
        exit;
    }

    /* 取得订单 */
    $order = order_info($order_id);
    if (empty($order))
    {
        ecs_header("Location: ./\n");
        exit;
    }

    /* 检查订单用户跟当前用户是否一致 */
    if ($_SESSION['user_id'] != $order['user_id'])
    {
        ecs_header("Location: ./\n");
        exit;
    }

    /* 检查订单是否未付款和未发货 以及订单金额是否为0 和支付id是否为改变*/
    if ($order['pay_status'] != PS_UNPAYED || $order['shipping_status'] != SS_UNSHIPPED || $order['goods_amount'] <= 0 || $order['pay_id'] == $pay_id)
    {
        ecs_header("Location: user.php?act=order_detail&order_id=$order_id\n");
        exit;
    }

    $order_amount = $order['order_amount'] - $order['pay_fee'];
    $pay_fee = pay_fee($pay_id, $order_amount);
    $order_amount += $pay_fee;

    $sql = "UPDATE " . $ecs->table('order_info') .
           " SET pay_id='$pay_id', pay_name='$payment_info[pay_name]', pay_fee='$pay_fee', order_amount='$order_amount'".
           " WHERE order_id = '$order_id'";
    $db->query($sql);

    /* 跳转 */
    ecs_header("Location: user.php?act=order_detail&order_id=$order_id\n");
    exit;
}

/* 保存订单详情收货地址 */
elseif ($action == 'save_order_address')
{
    include_once(ROOT_PATH .'include/lib_transaction.php');
    
    $address = array(
        'consignee' => isset($_POST['consignee']) ? compile_str(trim($_POST['consignee']))  : '',
        'email'     => isset($_POST['email'])     ? compile_str(trim($_POST['email']))      : '',
        'address'   => isset($_POST['address'])   ? compile_str(trim($_POST['address']))    : '',
        'zipcode'   => isset($_POST['zipcode'])   ? compile_str(make_semiangle(trim($_POST['zipcode']))) : '',
        'tel'       => isset($_POST['tel'])       ? compile_str(trim($_POST['tel']))        : '',
        'mobile'    => isset($_POST['mobile'])    ? compile_str(trim($_POST['mobile']))     : '',
        'sign_building' => isset($_POST['sign_building']) ? compile_str(trim($_POST['sign_building'])) : '',
        'best_time' => isset($_POST['best_time']) ? compile_str(trim($_POST['best_time']))  : '',
        'order_id'  => isset($_POST['order_id'])  ? intval($_POST['order_id']) : 0
        );
    if (save_order_address($address, $user_id))
    {
        ecs_header('Location: user.php?act=order_detail&order_id=' .$address['order_id']. "\n");
        exit;
    }
    else
    {
        $err->show($_LANG['order_list_lnk'], 'user.php?act=order_list');
    }
}

/* 我的红包列表 */
elseif ($action == 'bonus')
{
    include_once(ROOT_PATH .'include/lib_transaction.php');

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$ecs->table('user_bonus'). " WHERE user_id = '$user_id'");

    $pager = get_pager('user.php', array('act' => $action), $record_count, $page);
    $bonus = get_user_bouns_list($user_id, $pager['size'], $pager['start']);

    $smarty->assign('pager', $pager);
    $smarty->assign('bonus', $bonus);
    $smarty->display('user_transaction.dwt');
}

/* 我的团购列表 */
elseif ($action == 'group_buy')
{
    include_once(ROOT_PATH .'include/lib_transaction.php');

    //待议
    $smarty->display('user_transaction.dwt');
}

/* 团购订单详情 */
elseif ($action == 'group_buy_detail')
{
    include_once(ROOT_PATH .'include/lib_transaction.php');

    //待议
    $smarty->display('user_transaction.dwt');
}

// 用户推荐页面
elseif ($action == 'affiliate')
{
    $goodsid = intval(isset($_REQUEST['goodsid']) ? $_REQUEST['goodsid'] : 0);
    $smarty->assign('shopname', $_CFG['shop_name']);
    $smarty->assign('userid', $user_id);
    $smarty->assign('shopurl', $ecs->url());
    $smarty->assign('logosrc', 'themes/' . $_CFG['template'] . '/images/logo.gif');
	//实-实  微分销系统分销显示会员等级和下面人数
	$affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
    $smarty->assign('affiliate', $affiliate);

    empty($affiliate) && $affiliate = array();

    if(empty($affiliate['config']['separate_by']))
    {
        //推荐注册分成
        $affdb = array();
        $num = count($affiliate['item']);
        $up_uid = $_SESSION['user_id'];
        for ($i = 1 ; $i <=$num ;$i++)
        {
            $count = 0;
            if ($up_uid)
            {
                $sql = "SELECT user_id FROM " . $ecs->table('users') . " WHERE parent_id IN($up_uid)";
                $query = $db->query($sql);
                $up_uid = '';
                while ($rt = $db->fetch_array($query))
                {
                    $up_uid .= $up_uid ? ",'$rt[user_id]'" : "'$rt[user_id]'";
                    $count++;
                }
            }
            $affdb[$i]['num'] = $count;
        }
        if ($affdb[1]['num'] > 0)
        {
            $smarty->assign('affdb', $affdb);
        }
    }
	//结束
	//显示详细下线会员
	$auid = $_SESSION['user_id'];
    $user_list['user_list'] = array();

    $affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
    $smarty->assign('affiliate', $affiliate);

    empty($affiliate) && $affiliate = array();

    $num = count($affiliate['item']);
    $up_uid = "'$auid'";
    $all_count = 0;
    for ($i = 1; $i<=$num; $i++)
    {
        $count = 0;
        if ($up_uid)
        {
            $sql = "SELECT user_id FROM " . $ecs->table('users') . " WHERE parent_id IN($up_uid)";
            $query = $db->query($sql);
            $up_uid = '';
            while ($rt = $db->fetch_array($query))
            {
                $up_uid .= $up_uid ? ",'$rt[user_id]'" : "'$rt[user_id]'";
                $count++;
            }
        }
        $all_count += $count;
	
        if ($count)
        {
            $sql = "SELECT user_id, user_name, '$i' AS level, email, is_validated, user_money, frozen_money, rank_points, pay_points, reg_time ".
                    " FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id IN($up_uid)" .
                    " ORDER by level, user_id";
					//echo $sql;
            $user_list['user_list'] = array_merge($user_list['user_list'], $db->getAll($sql));
        }
    }
	
	$smarty->assign('user_list',    $user_list['user_list']);
	//显示详细下线会员结束
	
	//显示分成记录
	$logdb = get_affiliate_ck();
	
	$smarty->assign('logdb',        $logdb['logdb']);
	//显示分成记录结束
	/*新增显示用户的上下级关系  by 实实 微分销 Q1141881310 */
	$user_id=$_SESSION['user_id'];
	$row[user_id]=$user_id;
	$num=2;//需要查看到第几级的上级
	$children=array();
	for($i=0; $i < $num; $i++){
		if($row[user_id]){
		$row = $db->getAll("SELECT * FROM " . $GLOBALS['ecs']->table('users').
                        " WHERE parent_id = '$row[user_id]'"
                    );
		if(!empty($row)){			
			$children[$i]=$row;	
			}
	}		
	}
	$smarty->assign('userid', $user_id);
    $smarty->assign('shopurl', $ecs->url());
	$smarty->assign('children', $children);
	/*新增显示用户的上下级关系*/
    $smarty->display('user_clips.dwt');
}
//显示一级分销代理
elseif ($action == 'fenxiao1')
{
	//推荐注册分成
	$affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
	$user_list['user_list'] = array();
	$auid = $_SESSION['user_id'];
    $num = 1;
    $up_uid = "'$auid'";
    $all_count = 0;
    for ($i = 1; $i<=$num; $i++)
    {
        $count = 0;
        if ($up_uid)
        {
            $sql = "SELECT user_id FROM " . $ecs->table('users') . " WHERE parent_id IN($up_uid)";
            $query = $db->query($sql);
            $up_uid = '';
            while ($rt = $db->fetch_array($query))
            {
                $up_uid .= $up_uid ? ",'$rt[user_id]'" : "'$rt[user_id]'";
                $count++;
            }
        }
        $all_count += $count;
	
        if ($count)
        {
            $sql = "SELECT user_id, user_name, '$i' AS level, email, is_validated, user_money, frozen_money, rank_points, pay_points, reg_time,wxid ".
                    " FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id IN($up_uid)" .
                    " ORDER by level, user_id";
			$user_info=$db->getAll($sql);
			foreach($user_info as $key=>$value){
			
				$sql="SELECT count(*) as order_num ,sum(fencheng)  as order_amount FROM ".$GLOBALS['ecs']->table('order_info')."WHERE user_id=".$value['user_id'];
				$order_info=$db->getRow($sql);
				$k=$i-1;
				$affiliate['item'][$k]['level_money'] = (float)$affiliate['item'][$k]['level_money'];
				if($key==0){
                if ($affiliate['item'][$k]['level_money'])
                {
                    $affiliate['item'][$k]['level_money'] /= 100;
                }
				}
				$setmoney = round($order_info['order_amount'] * $affiliate['item'][$k]['level_money'], 2);
				$user_info[$key]['order_num']=$order_info['order_num'];
				$user_info[$key]['order_amount']=$order_info['order_amount'];
				$user_info[$key]['setmoney']=$setmoney;
				
			}
            $user_list['user_list'] = array_merge($user_list['user_list'], $user_info);	
        }
    }
	$new_arr=array();
	foreach($user_list['user_list'] as $key =>$value){
		
		if($value['level']==1){
			
			$wxid=$value['wxid'];
			$value['head_url']=$GLOBALS['db']->getOne("SELECT  headimgurl FROM wxch_user WHERE wxid = '$wxid'");
			$value['nickname']=$GLOBALS['db']->getOne("SELECT nickname FROM wxch_user WHERE wxid = '$wxid'");
			$new_arr[]=$value;
		}
	}
	$count=count($new_arr);
	$smarty->assign('count',    $count);
	$smarty->assign('user_list',    $new_arr);
	$smarty->display('distribute.dwt');
}
//显示二级分销代理
elseif ($action == 'fenxiao2')
{
	//推荐注册分成
	$affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
	$user_list['user_list'] = array();
	$auid = $_SESSION['user_id'];
    $num = 2;
    $up_uid = "'$auid'";
    $all_count = 0;
    for ($i = 1; $i<=$num; $i++)
    {
        $count = 0;
        if ($up_uid)
        {
            $sql = "SELECT user_id FROM " . $ecs->table('users') . " WHERE parent_id IN($up_uid)";
            $query = $db->query($sql);
            $up_uid = '';
            while ($rt = $db->fetch_array($query))
            {
                $up_uid .= $up_uid ? ",'$rt[user_id]'" : "'$rt[user_id]'";
                $count++;
            }
        }
        $all_count += $count;
	
        if ($count)
        {
            $sql = "SELECT user_id, user_name, '$i' AS level, email, is_validated, user_money, frozen_money, rank_points, pay_points, reg_time,wxid ".
                    " FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id IN($up_uid)" .
                    " ORDER by level, user_id";
			$user_info=$db->getAll($sql);
			foreach($user_info as $key=>$value){
			
				$sql="SELECT count(*) as order_num ,sum(fencheng)  as order_amount FROM ".$GLOBALS['ecs']->table('order_info')."WHERE user_id=".$value['user_id'];
				$order_info=$db->getRow($sql);
				$k=$i-1;
				$affiliate['item'][$k]['level_money'] = (float)$affiliate['item'][$k]['level_money'];
				if($key==0){
                if ($affiliate['item'][$k]['level_money'])
                {
                    $affiliate['item'][$k]['level_money'] /= 100;
                }
				}
				$setmoney = round($order_info['order_amount'] * $affiliate['item'][$k]['level_money'], 2);
				$user_info[$key]['order_num']=$order_info['order_num'];
				$user_info[$key]['order_amount']=$order_info['order_amount'];
				$user_info[$key]['setmoney']=$setmoney;
				
			}
            $user_list['user_list'] = array_merge($user_list['user_list'], $user_info);	
        }
    }
	$new_arr=array();
	foreach($user_list['user_list'] as $key =>$value){
		
		if($value['level']==2){
			
			$wxid=$value['wxid'];
			$value['head_url']=$GLOBALS['db']->getOne("SELECT  headimgurl FROM wxch_user WHERE wxid = '$wxid'");
			$value['nickname']=$GLOBALS['db']->getOne("SELECT nickname FROM wxch_user WHERE wxid = '$wxid'");
			$new_arr[]=$value;
		}
	}
	$count=count($new_arr);
	$smarty->assign('count',    $count);
	$smarty->assign('user_list',    $new_arr);
	$smarty->display('distribute.dwt');
}
//显示三级分销代理
elseif ($action == 'fenxiao3')
{
	//推荐注册分成
	$affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
	$user_list['user_list'] = array();
	$auid = $_SESSION['user_id'];
    $num = 3;
    $up_uid = "'$auid'";
    $all_count = 0;
    for ($i = 1; $i<=$num; $i++)
    {
        $count = 0;
        if ($up_uid)
        {
            $sql = "SELECT user_id FROM " . $ecs->table('users') . " WHERE parent_id IN($up_uid)";
            $query = $db->query($sql);
            $up_uid = '';
            while ($rt = $db->fetch_array($query))
            {
                $up_uid .= $up_uid ? ",'$rt[user_id]'" : "'$rt[user_id]'";
                $count++;
            }
        }
        $all_count += $count;
	
        if ($count)
        {
            $sql = "SELECT user_id, user_name, '$i' AS level, email, is_validated, user_money, frozen_money, rank_points, pay_points, reg_time,wxid ".
                    " FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id IN($up_uid)" .
                    " ORDER by level, user_id";
			$user_info=$db->getAll($sql);
			foreach($user_info as $key=>$value){
			
				$sql="SELECT count(*) as order_num ,sum(fencheng)  as order_amount FROM ".$GLOBALS['ecs']->table('order_info')."WHERE user_id=".$value['user_id'];
				$order_info=$db->getRow($sql);
				$k=$i-1;
				$affiliate['item'][$k]['level_money'] = (float)$affiliate['item'][$k]['level_money'];
				if($key==0){
                if ($affiliate['item'][$k]['level_money'])
                {
                    $affiliate['item'][$k]['level_money'] /= 100;
                }
				}
				/*Y I ZI修复 */
				$shishi_level_money=$affiliate['item'][$k]['level_money'];
				$setmoney = round($order_info['order_amount'] * $shishi_level_money, 2);
				
				$user_info[$key]['order_num']=$order_info['order_num'];
				$user_info[$key]['order_amount']=$order_info['order_amount'];
				$user_info[$key]['setmoney']=$setmoney;
				
			}
            $user_list['user_list'] = array_merge($user_list['user_list'], $user_info);	
        }
    }
	
	//print_r($user_list['user_list']);
	$new_arr=array();
	foreach($user_list['user_list'] as $key =>$value){
		
		if($value['level']==3){
			
			$wxid=$value['wxid'];
			$value['head_url']=$GLOBALS['db']->getOne("SELECT  headimgurl FROM wxch_user WHERE wxid = '$wxid'");
			$value['nickname']=$GLOBALS['db']->getOne("SELECT nickname FROM wxch_user WHERE wxid = '$wxid'");
			$new_arr[]=$value;
		}
	}
	$count=count($new_arr);
	$smarty->assign('count',    $count);
	$smarty->assign('user_list',    $new_arr);
	$smarty->display('distribute.dwt');
}
//实实  微分销系统分销微分销开发结束
//显示四级分销代理
elseif ($action == 'fenxiao4')
{
	//推荐注册分成
	$affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
	$user_list['user_list'] = array();
	$auid = $_SESSION['user_id'];
    $num = 4;
    $up_uid = "'$auid'";
    $all_count = 0;
    for ($i = 1; $i<=$num; $i++)
    {
        $count = 0;
        if ($up_uid)
        {
            $sql = "SELECT user_id FROM " . $ecs->table('users') . " WHERE parent_id IN($up_uid)";
            $query = $db->query($sql);
            $up_uid = '';
            while ($rt = $db->fetch_array($query))
            {
                $up_uid .= $up_uid ? ",'$rt[user_id]'" : "'$rt[user_id]'";
                $count++;
            }
        }
        $all_count += $count;
	
        if ($count)
        {
            $sql = "SELECT user_id, user_name, '$i' AS level, email, is_validated, user_money, frozen_money, rank_points, pay_points, reg_time,wxid ".
                    " FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id IN($up_uid)" .
                    " ORDER by level, user_id";
			$user_info=$db->getAll($sql);
			foreach($user_info as $key=>$value){
			
				$sql="SELECT count(*) as order_num ,sum(fencheng)  as order_amount FROM ".$GLOBALS['ecs']->table('order_info')."WHERE user_id=".$value['user_id'];
				$order_info=$db->getRow($sql);
				$k=$i-1;
				if($key==0){
                if ($affiliate['item'][$k]['level_money'])
                {
                    $affiliate['item'][$k]['level_money'] /= 100;
                }
				}
				/*Yi Z i修复 */
				$shishi_level_money=$affiliate['item'][$k]['level_money'];
				$setmoney = round($order_info['order_amount'] * $shishi_level_money, 2);
				$user_info[$key]['order_num']=$order_info['order_num'];
				$user_info[$key]['order_amount']=$order_info['order_amount'];
				$user_info[$key]['setmoney']=$setmoney;
				
			}
            $user_list['user_list'] = array_merge($user_list['user_list'], $user_info);	
        }
    }
	$new_arr=array();
	foreach($user_list['user_list'] as $key =>$value){
		
		if($value['level']==4){
			
			$wxid=$value['wxid'];
			$value['head_url']=$GLOBALS['db']->getOne("SELECT  headimgurl FROM wxch_user WHERE wxid = '$wxid'");
			$value['nickname']=$GLOBALS['db']->getOne("SELECT nickname FROM wxch_user WHERE wxid = '$wxid'");
			$new_arr[]=$value;
		}
	}
	$count=count($new_arr);
	$smarty->assign('count',    $count);
	$smarty->assign('user_list',    $new_arr);
	$smarty->display('distribute.dwt');
}
//实实  微分销系统分销微分销开发结束
//显示五级分销代理
elseif ($action == 'fenxiao5')
{
	//推荐注册分成
	$affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
	$user_list['user_list'] = array();
	$auid = $_SESSION['user_id'];
    $num = 5;
    $up_uid = "'$auid'";
    $all_count = 0;
    for ($i = 1; $i<=$num; $i++)
    {
        $count = 0;
        if ($up_uid)
        {
            $sql = "SELECT user_id FROM " . $ecs->table('users') . " WHERE parent_id IN($up_uid)";
            $query = $db->query($sql);
            $up_uid = '';
            while ($rt = $db->fetch_array($query))
            {
                $up_uid .= $up_uid ? ",'$rt[user_id]'" : "'$rt[user_id]'";
                $count++;
            }
        }
        $all_count += $count;
	
        if ($count)
        {
            $sql = "SELECT user_id, user_name, '$i' AS level, email, is_validated, user_money, frozen_money, rank_points, pay_points, reg_time,wxid ".
                    " FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id IN($up_uid)" .
                    " ORDER by level, user_id";
			$user_info=$db->getAll($sql);
			foreach($user_info as $key=>$value){
			
				$sql="SELECT count(*) as order_num ,sum(fencheng)  as order_amount FROM ".$GLOBALS['ecs']->table('order_info')."WHERE user_id=".$value['user_id'];
				$order_info=$db->getRow($sql);
				$k=$i-1;
				if($key==0){
                if ($affiliate['item'][$k]['level_money'])
                {
                    $affiliate['item'][$k]['level_money'] /= 100;
                }
				}
				/*yi zi修复 */
				$shishi_level_money=$affiliate['item'][$k]['level_money'];
				$setmoney = round($order_info['order_amount'] * $shishi_level_money, 2);
				$user_info[$key]['order_num']=$order_info['order_num'];
				$user_info[$key]['order_amount']=$order_info['order_amount'];
				$user_info[$key]['setmoney']=$setmoney;
				
			}
            $user_list['user_list'] = array_merge($user_list['user_list'], $user_info);	
        }
    }
	$new_arr=array();
	foreach($user_list['user_list'] as $key =>$value){
		
		if($value['level']==5){
			
			$wxid=$value['wxid'];
			$value['head_url']=$GLOBALS['db']->getOne("SELECT  headimgurl FROM wxch_user WHERE wxid = '$wxid'");
			$value['nickname']=$GLOBALS['db']->getOne("SELECT nickname FROM wxch_user WHERE wxid = '$wxid'");
			$new_arr[]=$value;
		}
	}
	$count=count($new_arr);
	$smarty->assign('count',    $count);
	$smarty->assign('user_list',    $new_arr);
	$smarty->display('distribute.dwt');
}
//实实  微分销系统分销微分销开发结束
//显示六级分销代理
elseif ($action == 'fenxiao6')
{
	//推荐注册分成
	$affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
	$user_list['user_list'] = array();
	$auid = $_SESSION['user_id'];
    $num = 6;
    $up_uid = "'$auid'";
    $all_count = 0;
    for ($i = 1; $i<=$num; $i++)
    {
        $count = 0;
        if ($up_uid)
        {
            $sql = "SELECT user_id FROM " . $ecs->table('users') . " WHERE parent_id IN($up_uid)";
            $query = $db->query($sql);
            $up_uid = '';
            while ($rt = $db->fetch_array($query))
            {
                $up_uid .= $up_uid ? ",'$rt[user_id]'" : "'$rt[user_id]'";
                $count++;
            }
        }
        $all_count += $count;
	
        if ($count)
        {
            $sql = "SELECT user_id, user_name, '$i' AS level, email, is_validated, user_money, frozen_money, rank_points, pay_points, reg_time,wxid ".
                    " FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id IN($up_uid)" .
                    " ORDER by level, user_id";
			$user_info=$db->getAll($sql);
			foreach($user_info as $key=>$value){
			
				$sql="SELECT count(*) as order_num ,sum(fencheng)  as order_amount FROM ".$GLOBALS['ecs']->table('order_info')."WHERE user_id=".$value['user_id'];
				$order_info=$db->getRow($sql);
				$k=$i-1;
				if($key==0){
                if ($affiliate['item'][$k]['level_money'])
                {
                    $affiliate['item'][$k]['level_money'] /= 100;
                }
				}
				/*yi zi修复 */
				$shishi_level_money=$affiliate['item'][$k]['level_money'];
				$setmoney = round($order_info['order_amount'] * $shishi_level_money, 2);
				$user_info[$key]['order_num']=$order_info['order_num'];
				$user_info[$key]['order_amount']=$order_info['order_amount'];
				$user_info[$key]['setmoney']=$setmoney;
				
			}
            $user_list['user_list'] = array_merge($user_list['user_list'], $user_info);	
        }
    }
	$new_arr=array();
	foreach($user_list['user_list'] as $key =>$value){
		if($value['level']==6){
			$wxid=$value['wxid'];
			$value['head_url']=$GLOBALS['db']->getOne("SELECT  headimgurl FROM wxch_user WHERE wxid = '$wxid'");
			$value['nickname']=$GLOBALS['db']->getOne("SELECT nickname FROM wxch_user WHERE wxid = '$wxid'");
			$new_arr[]=$value;
		}
	}
	//echo $count;
	//print_r($new_arr);
	$count=count($new_arr);
	$smarty->assign('count',    $count);
	$smarty->assign('user_list',    $new_arr);
	$smarty->display('distribute.dwt');
}
//实实  微分销系统分销微分销开发结束
//显示七级分销代理
elseif ($action == 'fenxiao7')
{
	//推荐注册分成
	$affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
	$user_list['user_list'] = array();
	$auid = $_SESSION['user_id'];
    $num = 7;
    $up_uid = "'$auid'";
    $all_count = 0;
    for ($i = 1; $i<=$num; $i++)
    {
        $count = 0;
        if ($up_uid)
        {
            $sql = "SELECT user_id FROM " . $ecs->table('users') . " WHERE parent_id IN($up_uid)";
            $query = $db->query($sql);
            $up_uid = '';
            while ($rt = $db->fetch_array($query))
            {
                $up_uid .= $up_uid ? ",'$rt[user_id]'" : "'$rt[user_id]'";
                $count++;
            }
        }
        $all_count += $count;
	
        if ($count)
        {
            $sql = "SELECT user_id, user_name, '$i' AS level, email, is_validated, user_money, frozen_money, rank_points, pay_points, reg_time,wxid ".
                    " FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id IN($up_uid)" .
                    " ORDER by level, user_id";
			$user_info=$db->getAll($sql);
			foreach($user_info as $key=>$value){
			
				$sql="SELECT count(*) as order_num ,sum(fencheng)  as order_amount FROM ".$GLOBALS['ecs']->table('order_info')."WHERE user_id=".$value['user_id'];
				$order_info=$db->getRow($sql);
				$k=$i-1;
				if($key==0){
                if ($affiliate['item'][$k]['level_money'])
                {
                    $affiliate['item'][$k]['level_money'] /= 100;
                }
				}
				/*yi zi修复 */
				$shishi_level_money=$affiliate['item'][$k]['level_money'];
				$setmoney = round($order_info['order_amount'] * $shishi_level_money, 2);
				$user_info[$key]['order_num']=$order_info['order_num'];
				$user_info[$key]['order_amount']=$order_info['order_amount'];
				$user_info[$key]['setmoney']=$setmoney;
				
			}
            $user_list['user_list'] = array_merge($user_list['user_list'], $user_info);	
        }
    }
	$new_arr=array();
	foreach($user_list['user_list'] as $key =>$value){
		
		if($value['level']==7){
			
			$wxid=$value['wxid'];
			$value['head_url']=$GLOBALS['db']->getOne("SELECT  headimgurl FROM wxch_user WHERE wxid = '$wxid'");
			$value['nickname']=$GLOBALS['db']->getOne("SELECT nickname FROM wxch_user WHERE wxid = '$wxid'");
			$new_arr[]=$value;
		}
	}
	$count=count($new_arr);
	$smarty->assign('count',    $count);
	$smarty->assign('user_list',    $new_arr);
	$smarty->display('distribute.dwt');
}
//实实  微分销系统分销微分销开发结束
//显示八级分销代理
elseif ($action == 'fenxiao8')
{
	//推荐注册分成
	$affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
	$user_list['user_list'] = array();
	$auid = $_SESSION['user_id'];
    $num = 8;
    $up_uid = "'$auid'";
    $all_count = 0;
    for ($i = 1; $i<=$num; $i++)
    {
        $count = 0;
        if ($up_uid)
        {
            $sql = "SELECT user_id FROM " . $ecs->table('users') . " WHERE parent_id IN($up_uid)";
            $query = $db->query($sql);
            $up_uid = '';
            while ($rt = $db->fetch_array($query))
            {
                $up_uid .= $up_uid ? ",'$rt[user_id]'" : "'$rt[user_id]'";
                $count++;
            }
        }
        $all_count += $count;
	
        if ($count)
        {
            $sql = "SELECT user_id, user_name, '$i' AS level, email, is_validated, user_money, frozen_money, rank_points, pay_points, reg_time,wxid ".
                    " FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id IN($up_uid)" .
                    " ORDER by level, user_id";
			$user_info=$db->getAll($sql);
			foreach($user_info as $key=>$value){
			
				$sql="SELECT count(*) as order_num ,sum(fencheng)  as order_amount FROM ".$GLOBALS['ecs']->table('order_info')."WHERE user_id=".$value['user_id'];
				$order_info=$db->getRow($sql);
				$k=$i-1;
				if($key==0){
                if ($affiliate['item'][$k]['level_money'])
                {
                    $affiliate['item'][$k]['level_money'] /= 100;
                }
				}
				/*yi zi修复 */
				$shishi_level_money=$affiliate['item'][$k]['level_money'];
				$setmoney = round($order_info['order_amount'] * $shishi_level_money, 2);
				$user_info[$key]['order_num']=$order_info['order_num'];
				$user_info[$key]['order_amount']=$order_info['order_amount'];
				$user_info[$key]['setmoney']=$setmoney;
				
			}
            $user_list['user_list'] = array_merge($user_list['user_list'], $user_info);	
        }
    }
	$new_arr=array();
	foreach($user_list['user_list'] as $key =>$value){
		
		if($value['level']==8){
			
			$wxid=$value['wxid'];
			$value['head_url']=$GLOBALS['db']->getOne("SELECT  headimgurl FROM wxch_user WHERE wxid = '$wxid'");
			$value['nickname']=$GLOBALS['db']->getOne("SELECT nickname FROM wxch_user WHERE wxid = '$wxid'");
			$new_arr[]=$value;
		}
	}
	$count=count($new_arr);
	$smarty->assign('count',    $count);
	$smarty->assign('user_list',    $new_arr);
	$smarty->display('distribute.dwt');
}

//显示分销会员的分层订单
elseif ($action =='myorder')
{
	//显示分成记录
	$user_id=$_GET['user_id'];
	$level=$_GET['level'];
	$logdb = get_affiliate_ck($user_id,$level);
	$smarty->assign('logdb',        $logdb['logdb']);
	$smarty->assign('level',        $level);
	//显示分成记录结束
	$smarty->display('user_clips.dwt');
}
//显示分销商的下线
/* 查看订单详情 */
elseif ($action == 'myorder_detail')
{
    include_once(ROOT_PATH . 'include/lib_transaction.php');
    include_once(ROOT_PATH . 'include/lib_payment.php');
    include_once(ROOT_PATH . 'include/lib_order.php');
    include_once(ROOT_PATH . 'include/lib_clips.php');

    $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

    /* 订单详情 */
    $order = get_order_detail_new($order_id, $user_id);
	
	
    if ($order === false)
    {
        $err->show($_LANG['back_home_lnk'], './');

        exit;
    }

    /* 是否显示添加到购物车 */
    if ($order['extension_code'] != 'group_buy' && $order['extension_code'] != 'exchange_goods')
    {
        $smarty->assign('allow_to_cart', 1);
    }

    /* 订单商品 */
    $goods_list = order_goods($order_id);
    foreach ($goods_list AS $key => $value)
    {
        $goods_list[$key]['market_price'] = price_format($value['market_price'], false);
        $goods_list[$key]['goods_price']  = price_format($value['goods_price'], false);
        $goods_list[$key]['subtotal']     = price_format($value['subtotal'], false);
    }

     /* 设置能否修改使用余额数 */
    if ($order['order_amount'] > 0)
    {
        if ($order['order_status'] == OS_UNCONFIRMED || $order['order_status'] == OS_CONFIRMED)
        {
            $user = user_info($order['user_id']);
            if ($user['user_money'] + $user['credit_line'] > 0)
            {
                $smarty->assign('allow_edit_surplus', 1);
                $smarty->assign('max_surplus', sprintf($_LANG['max_surplus'], $user['user_money']));
            }
        }
    }

    /* 未发货，未付款时允许更换支付方式 */
    if ($order['order_amount'] > 0 && $order['pay_status'] == PS_UNPAYED && $order['shipping_status'] == SS_UNSHIPPED)
    {
        $payment_list = available_payment_list(false, 0, true);

        /* 过滤掉当前支付方式和余额支付方式 */
        if(is_array($payment_list))
        {
            foreach ($payment_list as $key => $payment)
            {
                if ($payment['pay_id'] == $order['pay_id'] || $payment['pay_code'] == 'balance')
                {
                    unset($payment_list[$key]);
                }
            }
        }
        $smarty->assign('payment_list', $payment_list);
    }

    /* 订单 支付 配送 状态语言项 */
    $order['order_status'] = $_LANG['os'][$order['order_status']];
    $order['pay_status'] = $_LANG['ps'][$order['pay_status']];
    $order['shipping_status'] = $_LANG['ss'][$order['shipping_status']];
	//读取分成比列
	$affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
	$level=$_GET['level'];
	$k=$level-1;
	$affiliate['item'][$k]['level_money'] = (float)$affiliate['item'][$k]['level_money'];
    if ($affiliate['item'][$k]['level_money'])
    {
       $affiliate['item'][$k]['level_money'] /= 100;
    }
	$sql="SELECT fencheng  FROM ".$GLOBALS['ecs']->table('order_info')."WHERE order_id = '$order_id'";
	$order['order_amount'] = $GLOBALS['db']->getOne($sql);
	$setmoney = round($order['order_amount'] * $affiliate['item'][$k]['level_money'], 2);
	$order['setmoney']=$setmoney ;
	$order['level_money_all']=$affiliate['item'][$k]['level_money'];
    $smarty->assign('order',      $order);
    $smarty->assign('goods_list', $goods_list);
    $smarty->display('user_transaction.dwt');
}
//首页邮件订阅ajax操做和验证操作
elseif ($action =='email_list')
{
    $job = $_GET['job'];

    if($job == 'add' || $job == 'del')
    {
        if(isset($_SESSION['last_email_query']))
        {
            if(time() - $_SESSION['last_email_query'] <= 30)
            {
                die($_LANG['order_query_toofast']);
            }
        }
        $_SESSION['last_email_query'] = time();
    }

    $email = trim($_GET['email']);
    $email = htmlspecialchars($email);

    if (!is_email($email))
    {
        $info = sprintf($_LANG['email_invalid'], $email);
        die($info);
    }
    $ck = $db->getRow("SELECT * FROM " . $ecs->table('email_list') . " WHERE email = '$email'");
    if ($job == 'add')
    {
        if (empty($ck))
        {
            $hash = substr(md5(time()), 1, 10);
            $sql = "INSERT INTO " . $ecs->table('email_list') . " (email, stat, hash) VALUES ('$email', 0, '$hash')";
            $db->query($sql);
            $info = $_LANG['email_check'];
            $url = $ecs->url() . "user.php?act=email_list&job=add_check&hash=$hash&email=$email";
            send_mail('', $email, $_LANG['check_mail'], sprintf($_LANG['check_mail_content'], $email, $_CFG['shop_name'], $url, $url, $_CFG['shop_name'], local_date('Y-m-d')), 1);
        }
        elseif ($ck['stat'] == 1)
        {
            $info = sprintf($_LANG['email_alreadyin_list'], $email);
        }
        else
        {
            $hash = substr(md5(time()),1 , 10);
            $sql = "UPDATE " . $ecs->table('email_list') . "SET hash = '$hash' WHERE email = '$email'";
            $db->query($sql);
            $info = $_LANG['email_re_check'];
            $url = $ecs->url() . "user.php?act=email_list&job=add_check&hash=$hash&email=$email";
            send_mail('', $email, $_LANG['check_mail'], sprintf($_LANG['check_mail_content'], $email, $_CFG['shop_name'], $url, $url, $_CFG['shop_name'], local_date('Y-m-d')), 1);
        }
        die($info);
    }
    elseif ($job == 'del')
    {
        if (empty($ck))
        {
            $info = sprintf($_LANG['email_notin_list'], $email);
        }
        elseif ($ck['stat'] == 1)
        {
            $hash = substr(md5(time()),1,10);
            $sql = "UPDATE " . $ecs->table('email_list') . "SET hash = '$hash' WHERE email = '$email'";
            $db->query($sql);
            $info = $_LANG['email_check'];
            $url = $ecs->url() . "user.php?act=email_list&job=del_check&hash=$hash&email=$email";
            send_mail('', $email, $_LANG['check_mail'], sprintf($_LANG['check_mail_content'], $email, $_CFG['shop_name'], $url, $url, $_CFG['shop_name'], local_date('Y-m-d')), 1);
        }
        else
        {
            $info = $_LANG['email_not_alive'];
        }
        die($info);
    }
    elseif ($job == 'add_check')
    {
        if (empty($ck))
        {
            $info = sprintf($_LANG['email_notin_list'], $email);
        }
        elseif ($ck['stat'] == 1)
        {
            $info = $_LANG['email_checked'];
        }
        else
        {
            if ($_GET['hash'] == $ck['hash'])
            {
                $sql = "UPDATE " . $ecs->table('email_list') . "SET stat = 1 WHERE email = '$email'";
                $db->query($sql);
                $info = $_LANG['email_checked'];
            }
            else
            {
                $info = $_LANG['hash_wrong'];
            }
        }
        show_message($info, $_LANG['back_home_lnk'], 'index.php');
    }
    elseif ($job == 'del_check')
    {
        if (empty($ck))
        {
            $info = sprintf($_LANG['email_invalid'], $email);
        }
        elseif ($ck['stat'] == 1)
        {
            if ($_GET['hash'] == $ck['hash'])
            {
                $sql = "DELETE FROM " . $ecs->table('email_list') . "WHERE email = '$email'";
                $db->query($sql);
                $info = $_LANG['email_canceled'];
            }
            else
            {
                $info = $_LANG['hash_wrong'];
            }
        }
        else
        {
            $info = $_LANG['email_not_alive'];
        }
        show_message($info, $_LANG['back_home_lnk'], 'index.php');
    }
}

/* ajax 发送验证邮件 */
elseif ($action == 'send_hash_mail')
{
    include_once(ROOT_PATH .'include/cls_json.php');
    include_once(ROOT_PATH .'include/lib_passport.php');
    $json = new JSON();

    $result = array('error' => 0, 'message' => '', 'content' => '');

    if ($user_id == 0)
    {
        /* 用户没有登录 */
        $result['error']   = 1;
        $result['message'] = $_LANG['login_please'];
        die($json->encode($result));
    }

    if (send_regiter_hash($user_id))
    {
        $result['message'] = $_LANG['validate_mail_ok'];
        die($json->encode($result));
    }
    else
    {
        $result['error'] = 1;
        $result['message'] = $GLOBALS['err']->last_message();
    }

    die($json->encode($result));
}
else if ($action == 'track_packages')
{
    include_once(ROOT_PATH . 'include/lib_transaction.php');
    include_once(ROOT_PATH .'include/lib_order.php');

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

    $orders = array();

    $sql = "SELECT order_id,order_sn,invoice_no,shipping_id FROM " .$ecs->table('order_info').
            " WHERE user_id = '$user_id' AND shipping_status = '" . SS_SHIPPED . "'";
    $res = $db->query($sql);
    $record_count = 0;
    while ($item = $db->fetch_array($res))
    {
        $shipping   = get_shipping_object($item['shipping_id']);

        if (method_exists ($shipping, 'query'))
        {
            $query_link = $shipping->query($item['invoice_no']);
        }
        else
        {
            $query_link = $item['invoice_no'];
        }

        if ($query_link != $item['invoice_no'])
        {
            $item['query_link'] = $query_link;
            $orders[]  = $item;
            $record_count += 1;
        }
    }
    $pager  = get_pager('user.php', array('act' => $action), $record_count, $page);
    $smarty->assign('pager',  $pager);
    $smarty->assign('orders', $orders);
    $smarty->display('user_transaction.dwt');
}
else if ($action == 'order_query')
{
    $_GET['order_sn'] = trim(substr($_GET['order_sn'], 1));
    $order_sn = empty($_GET['order_sn']) ? '' : addslashes($_GET['order_sn']);
    include_once(ROOT_PATH .'include/cls_json.php');
    $json = new JSON();

    $result = array('error'=>0, 'message'=>'', 'content'=>'');

    if(isset($_SESSION['last_order_query']))
    {
        if(time() - $_SESSION['last_order_query'] <= 10)
        {
            $result['error'] = 1;
            $result['message'] = $_LANG['order_query_toofast'];
            die($json->encode($result));
        }
    }
    $_SESSION['last_order_query'] = time();

    if (empty($order_sn))
    {
        $result['error'] = 1;
        $result['message'] = $_LANG['invalid_order_sn'];
        die($json->encode($result));
    }

    $sql = "SELECT order_id, order_status, shipping_status, pay_status, ".
           " shipping_time, shipping_id, invoice_no, user_id ".
           " FROM " . $ecs->table('order_info').
           " WHERE order_sn = '$order_sn' LIMIT 1";

    $row = $db->getRow($sql);
    if (empty($row))
    {
        $result['error'] = 1;
        $result['message'] = $_LANG['invalid_order_sn'];
        die($json->encode($result));
    }

    $order_query = array();
    $order_query['order_sn'] = $order_sn;
    $order_query['order_id'] = $row['order_id'];
    $order_query['order_status'] = $_LANG['os'][$row['order_status']] . ',' . $_LANG['ps'][$row['pay_status']] . ',' . $_LANG['ss'][$row['shipping_status']];

    if ($row['invoice_no'] && $row['shipping_id'] > 0)
    {
        $sql = "SELECT shipping_code FROM " . $ecs->table('touch_shipping') . " WHERE shipping_id = '$row[shipping_id]'";
        $shipping_code = $db->getOne($sql);
        $plugin = ROOT_PATH . 'include/modules/shipping/' . $shipping_code . '.php';
        if (file_exists($plugin))
        {
            include_once($plugin);
            $shipping = new $shipping_code;
            $order_query['invoice_no'] = $shipping->query((string)$row['invoice_no']);
        }
        else
        {
            $order_query['invoice_no'] = (string)$row['invoice_no'];
        }
    }

    $order_query['user_id'] = $row['user_id'];
    /* 如果是匿名用户显示发货时间 */
    if ($row['user_id'] == 0 && $row['shipping_time'] > 0)
    {
        $order_query['shipping_date'] = local_date($GLOBALS['_CFG']['date_format'], $row['shipping_time']);
    }
    $smarty->assign('order_query',    $order_query);
    $result['content'] = $smarty->fetch('library/order_query.lbi');
    die($json->encode($result));
}
elseif ($action == 'transform_points')
{
    $rule = array();
    if (!empty($_CFG['points_rule']))
    {
        $rule = unserialize($_CFG['points_rule']);
    }
    $cfg = array();
    if (!empty($_CFG['integrate_config']))
    {
        $cfg = unserialize($_CFG['integrate_config']);
        $_LANG['exchange_points'][0] = empty($cfg['uc_lang']['credits'][0][0])? $_LANG['exchange_points'][0] : $cfg['uc_lang']['credits'][0][0];
        $_LANG['exchange_points'][1] = empty($cfg['uc_lang']['credits'][1][0])? $_LANG['exchange_points'][1] : $cfg['uc_lang']['credits'][1][0];
    }
    $sql = "SELECT user_id, user_name, pay_points, rank_points FROM " . $ecs->table('users')  . " WHERE user_id='$user_id'";
    $row = $db->getRow($sql);
    if ($_CFG['integrate_code'] == 'ucenter')
    {
        $exchange_type = 'ucenter';
        $to_credits_options = array();
        $out_exchange_allow = array();
        foreach ($rule as $credit)
        {
            $out_exchange_allow[$credit['appiddesc'] . '|' . $credit['creditdesc'] . '|' . $credit['creditsrc']] = $credit['ratio'];
            if (!array_key_exists($credit['appiddesc']. '|' .$credit['creditdesc'], $to_credits_options))
            {
                $to_credits_options[$credit['appiddesc']. '|' .$credit['creditdesc']] = $credit['title'];
            }
        }
        $smarty->assign('selected_org', $rule[0]['creditsrc']);
        $smarty->assign('selected_dst', $rule[0]['appiddesc']. '|' .$rule[0]['creditdesc']);
        $smarty->assign('descreditunit', $rule[0]['unit']);
        $smarty->assign('orgcredittitle', $_LANG['exchange_points'][$rule[0]['creditsrc']]);
        $smarty->assign('descredittitle', $rule[0]['title']);
        $smarty->assign('descreditamount', round((1 / $rule[0]['ratio']), 2));
        $smarty->assign('to_credits_options', $to_credits_options);
        $smarty->assign('out_exchange_allow', $out_exchange_allow);
    }
    else
    {
        $exchange_type = 'other';

        $bbs_points_name = $user->get_points_name();
        $total_bbs_points = $user->get_points($row['user_name']);

        /* 论坛积分 */
        $bbs_points = array();
        foreach ($bbs_points_name as $key=>$val)
        {
            $bbs_points[$key] = array('title'=>$_LANG['bbs'] . $val['title'], 'value'=>$total_bbs_points[$key]);
        }

        /* 兑换规则 */
        $rule_list = array();
        foreach ($rule as $key=>$val)
        {
            $rule_key = substr($key, 0, 1);
            $bbs_key = substr($key, 1);
            $rule_list[$key]['rate'] = $val;
            switch ($rule_key)
            {
                case TO_P :
                    $rule_list[$key]['from'] = $_LANG['bbs'] . $bbs_points_name[$bbs_key]['title'];
                    $rule_list[$key]['to'] = $_LANG['pay_points'];
                    break;
                case TO_R :
                    $rule_list[$key]['from'] = $_LANG['bbs'] . $bbs_points_name[$bbs_key]['title'];
                    $rule_list[$key]['to'] = $_LANG['rank_points'];
                    break;
                case FROM_P :
                    $rule_list[$key]['from'] = $_LANG['pay_points'];$_LANG['bbs'] . $bbs_points_name[$bbs_key]['title'];
                    $rule_list[$key]['to'] =$_LANG['bbs'] . $bbs_points_name[$bbs_key]['title'];
                    break;
                case FROM_R :
                    $rule_list[$key]['from'] = $_LANG['rank_points'];
                    $rule_list[$key]['to'] = $_LANG['bbs'] . $bbs_points_name[$bbs_key]['title'];
                    break;
            }
        }
        $smarty->assign('bbs_points', $bbs_points);
        $smarty->assign('rule_list',  $rule_list);
    }
    $smarty->assign('shop_points', $row);
    $smarty->assign('exchange_type',     $exchange_type);
    $smarty->assign('action',     $action);
    $smarty->assign('lang',       $_LANG);
    $smarty->display('user_transaction.dwt');
}
elseif ($action == 'act_transform_points')
{
    $rule_index = empty($_POST['rule_index']) ? '' : trim($_POST['rule_index']);
    $num = empty($_POST['num']) ? 0 : intval($_POST['num']);


    if ($num <= 0 || $num != floor($num))
    {
        show_message($_LANG['invalid_points'], $_LANG['transform_points'], 'user.php?act=transform_points');
    }

    $num = floor($num); //格式化为整数

    $bbs_key = substr($rule_index, 1);
    $rule_key = substr($rule_index, 0, 1);

    $max_num = 0;

    /* 取出用户数据 */
    $sql = "SELECT user_name, user_id, pay_points, rank_points FROM " . $ecs->table('users') . " WHERE user_id='$user_id'";
    $row = $db->getRow($sql);
    $bbs_points = $user->get_points($row['user_name']);
    $points_name = $user->get_points_name();

    $rule = array();
    if ($_CFG['points_rule'])
    {
        $rule = unserialize($_CFG['points_rule']);
    }
    list($from, $to) = explode(':', $rule[$rule_index]);

    $max_points = 0;
    switch ($rule_key)
    {
        case TO_P :
            $max_points = $bbs_points[$bbs_key];
            break;
        case TO_R :
            $max_points = $bbs_points[$bbs_key];
            break;
        case FROM_P :
            $max_points = $row['pay_points'];
            break;
        case FROM_R :
            $max_points = $row['rank_points'];
    }

    /* 检查积分是否超过最大值 */
    if ($max_points <=0 || $num > $max_points)
    {
        show_message($_LANG['overflow_points'], $_LANG['transform_points'], 'user.php?act=transform_points' );
    }

    switch ($rule_key)
    {
        case TO_P :
            $result_points = floor($num * $to / $from);
            $user->set_points($row['user_name'], array($bbs_key=>0 - $num)); //调整论坛积分
            log_account_change($row['user_id'], 0, 0, 0, $result_points, $_LANG['transform_points'], ACT_OTHER);
            show_message(sprintf($_LANG['to_pay_points'],  $num, $points_name[$bbs_key]['title'], $result_points), $_LANG['transform_points'], 'user.php?act=transform_points');

        case TO_R :
            $result_points = floor($num * $to / $from);
            $user->set_points($row['user_name'], array($bbs_key=>0 - $num)); //调整论坛积分
            log_account_change($row['user_id'], 0, 0, $result_points, 0, $_LANG['transform_points'], ACT_OTHER);
            show_message(sprintf($_LANG['to_rank_points'], $num, $points_name[$bbs_key]['title'], $result_points), $_LANG['transform_points'], 'user.php?act=transform_points');

        case FROM_P :
            $result_points = floor($num * $to / $from);
            log_account_change($row['user_id'], 0, 0, 0, 0-$num, $_LANG['transform_points'], ACT_OTHER); //调整商城积分
            $user->set_points($row['user_name'], array($bbs_key=>$result_points)); //调整论坛积分
            show_message(sprintf($_LANG['from_pay_points'], $num, $result_points,  $points_name[$bbs_key]['title']), $_LANG['transform_points'], 'user.php?act=transform_points');

        case FROM_R :
            $result_points = floor($num * $to / $from);
            log_account_change($row['user_id'], 0, 0, 0-$num, 0, $_LANG['transform_points'], ACT_OTHER); //调整商城积分
            $user->set_points($row['user_name'], array($bbs_key=>$result_points)); //调整论坛积分
            show_message(sprintf($_LANG['from_rank_points'], $num, $result_points, $points_name[$bbs_key]['title']), $_LANG['transform_points'], 'user.php?act=transform_points');
    }
}
elseif ($action == 'act_transform_ucenter_points')
{
    $rule = array();
    if ($_CFG['points_rule'])
    {
        $rule = unserialize($_CFG['points_rule']);
    }
    $shop_points = array(0 => 'rank_points', 1 => 'pay_points');
    $sql = "SELECT user_id, user_name, pay_points, rank_points FROM " . $ecs->table('users')  . " WHERE user_id='$user_id'";
    $row = $db->getRow($sql);
    $exchange_amount = intval($_POST['amount']);
    $fromcredits = intval($_POST['fromcredits']);
    $tocredits = trim($_POST['tocredits']);
    $cfg = unserialize($_CFG['integrate_config']);
    if (!empty($cfg))
    {
        $_LANG['exchange_points'][0] = empty($cfg['uc_lang']['credits'][0][0])? $_LANG['exchange_points'][0] : $cfg['uc_lang']['credits'][0][0];
        $_LANG['exchange_points'][1] = empty($cfg['uc_lang']['credits'][1][0])? $_LANG['exchange_points'][1] : $cfg['uc_lang']['credits'][1][0];
    }
    list($appiddesc, $creditdesc) = explode('|', $tocredits);
    $ratio = 0;

    if ($exchange_amount <= 0)
    {
        show_message($_LANG['invalid_points'], $_LANG['transform_points'], 'user.php?act=transform_points');
    }
    if ($exchange_amount > $row[$shop_points[$fromcredits]])
    {
        show_message($_LANG['overflow_points'], $_LANG['transform_points'], 'user.php?act=transform_points');
    }
    foreach ($rule as $credit)
    {
        if ($credit['appiddesc'] == $appiddesc && $credit['creditdesc'] == $creditdesc && $credit['creditsrc'] == $fromcredits)
        {
            $ratio = $credit['ratio'];
            break;
        }
    }
    if ($ratio == 0)
    {
        show_message($_LANG['exchange_deny'], $_LANG['transform_points'], 'user.php?act=transform_points');
    }
    $netamount = floor($exchange_amount / $ratio);
    include_once(ROOT_PATH . './include/lib_uc.php');
    $result = exchange_points($row['user_id'], $fromcredits, $creditdesc, $appiddesc, $netamount);
    if ($result === true)
    {
        $sql = "UPDATE " . $ecs->table('users') . " SET {$shop_points[$fromcredits]}={$shop_points[$fromcredits]}-'$exchange_amount' WHERE user_id='{$row['user_id']}'";
        $db->query($sql);
        $sql = "INSERT INTO " . $ecs->table('account_log') . "(user_id, {$shop_points[$fromcredits]}, change_time, change_desc, change_type)" . " VALUES ('{$row['user_id']}', '-$exchange_amount', '". gmtime() ."', '" . $cfg['uc_lang']['exchange'] . "', '98')";
        $db->query($sql);
        show_message(sprintf($_LANG['exchange_success'], $exchange_amount, $_LANG['exchange_points'][$fromcredits], $netamount, $credit['title']), $_LANG['transform_points'], 'user.php?act=transform_points');
    }
    else
    {
        show_message($_LANG['exchange_error_1'], $_LANG['transform_points'], 'user.php?act=transform_points');
    }
}
/* 清除商品浏览历史 */
elseif ($action == 'clear_history')
{
    setcookie('ECS[history]',   '', 1);
}
//新增by   实实  微分销系统 Q 1141881310 积分记录
elseif ($action == 'point')
{	
	
	$user_id=$_SESSION['user_id'];
	$account_type = '';
    $account_list = get_accountlist($user_id, $account_type);
	//print_r($account_list['account']);
    $smarty->assign('account_list', $account_list['account']);
	//查找用户的积分变化记录
	/*
	$sql = "SELECT * FROM " . $ecs->table('account_log') . " WHERE user_id = ".$_SESSION['user_id'];
	$log = $GLOBALS['db']->getAll($sql);
	
	foreach($log  as $k=>$v){
		
		$v['change_time']=local_date("Y-m-d H:i:s",$v['change_time']);
		
		$log[$k]['change_time']=$v['change_time'];
	}*/
	$smarty->assign('log', $log ); 
	$smarty->display('user_clips.dwt');
}
//生成随机数 by wang
function random($length = 6, $numeric = 0) {
    PHP_VERSION < '4.2.0' && mt_srand((double) microtime() * 1000000);
    if ($numeric) {
        $hash = sprintf('%0' . $length . 'd', mt_rand(0, pow(10, $length) - 1));
    } else {
        $hash = '';
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }
    }
    return $hash;
}



//定义，显示某个会员下面的分成订单情况
function get_affiliate_ck($user_id,$level)
{

    $affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
    empty($affiliate) && $affiliate = array();
    $separate_by = $affiliate['config']['separate_by'];

    $sqladd = '';
    if (isset($_REQUEST['status']))
    {
        $sqladd = ' AND o.is_separate = ' . (int)$_REQUEST['status'];
        $filter['status'] = (int)$_REQUEST['status'];
    }
    if (isset($_REQUEST['order_sn']))
    {
        $sqladd = ' AND o.order_sn LIKE \'%' . trim($_REQUEST['order_sn']) . '%\'';
        $filter['order_sn'] = $_REQUEST['order_sn'];
    }
		
		
    //$sqladd = ' AND a.user_id=' . $_SESSION['user_id'];
   

    if(!empty($affiliate['on']))
    {
        if(empty($separate_by))
        {
            //推荐注册分成
            $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('order_info') . " o".
                    " LEFT JOIN".$GLOBALS['ecs']->table('users')." u ON o.user_id = u.user_id".
                    " LEFT JOIN " . $GLOBALS['ecs']->table('affiliate_log') . " a ON o.order_id = a.order_id" .
                    " WHERE o.user_id > 0 AND (u.parent_id > 0 AND o.is_separate = 0 OR o.is_separate > 0) $sqladd";
        }
        else
        {
            //推荐订单分成
            $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('order_info') . " o".
                    " LEFT JOIN".$GLOBALS['ecs']->table('users')." u ON o.user_id = u.user_id".
                    " LEFT JOIN " . $GLOBALS['ecs']->table('affiliate_log') . " a ON o.order_id = a.order_id" .
                    " WHERE o.user_id > 0 AND (o.parent_id > 0 AND o.is_separate = 0 OR o.is_separate > 0) $sqladd";
        }
    }
    else
    {
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('order_info') . " o".
                " LEFT JOIN".$GLOBALS['ecs']->table('users')." u ON o.user_id = u.user_id".
                " LEFT JOIN " . $GLOBALS['ecs']->table('affiliate_log') . " a ON o.order_id = a.order_id" .
                " WHERE o.user_id > 0 AND o.is_separate > 0 $sqladd";
    }


    $filter['record_count'] = $GLOBALS['db']->getOne($sql);
    $logdb = array();
    /* 分页大小 */
    $filter = page_and_size($filter);

    if(!empty($affiliate['on']))
    {
        if(empty($separate_by))
        {
            //推荐注册分成
			
            $sql = "SELECT o.*, a.log_id, a.user_id as suid,  a.user_name as auser, a.money, a.point, a.separate_type,u.parent_id as up FROM " . $GLOBALS['ecs']->table('order_info') . " o".
                    " LEFT JOIN".$GLOBALS['ecs']->table('users')." u ON o.user_id = u.user_id".
                    " LEFT JOIN " . $GLOBALS['ecs']->table('affiliate_log') . " a ON o.order_id = a.order_id" .
                    " WHERE o.user_id > 0 AND (u.parent_id > 0 AND o.is_separate = 0 OR o.is_separate > 0) $sqladd".
                    " ORDER BY order_id DESC";

            /*
                SQL解释：

                列出同时满足以下条件的订单分成情况：
                1、有效订单o.user_id > 0
                2、满足以下情况之一：
                    a.有用户注册上线的未分成订单 u.parent_id > 0 AND o.is_separate = 0
                    b.已分成订单 o.is_separate > 0

            */
        }
        else
        {
            //推荐订单分成
            $sql = "SELECT o.*, a.log_id,a.user_id as suid, a.user_name as auser, a.money, a.point, a.separate_type,u.parent_id as up FROM " . $GLOBALS['ecs']->table('order_info') . " o".
                    " LEFT JOIN".$GLOBALS['ecs']->table('users')." u ON o.user_id = u.user_id".
                    " LEFT JOIN " . $GLOBALS['ecs']->table('affiliate_log') . " a ON o.order_id = a.order_id" .
                    " WHERE o.user_id > 0 AND (o.parent_id > 0 AND o.is_separate = 0 OR o.is_separate > 0) $sqladd" .
                    " ORDER BY order_id DESC" .
                    " LIMIT " . $filter['start'] . ",$filter[page_size]";

            /*
                SQL解释：

                列出同时满足以下条件的订单分成情况：
                1、有效订单o.user_id > 0
                2、满足以下情况之一：
                    a.有订单推荐上线的未分成订单 o.parent_id > 0 AND o.is_separate = 0
                    b.已分成订单 o.is_separate > 0

            */
        }
    }
    else
    {
        //关闭
        $sql = "SELECT o.*, a.log_id,a.user_id as suid, a.user_name as auser, a.money, a.point, a.separate_type,u.parent_id as up FROM " . $GLOBALS['ecs']->table('order_info') . " o".
                " LEFT JOIN".$GLOBALS['ecs']->table('users')." u ON o.user_id = u.user_id".
                " LEFT JOIN " . $GLOBALS['ecs']->table('affiliate_log') . " a ON o.order_id = a.order_id" .
                " WHERE o.user_id > 0 AND o.is_separate > 0 $sqladd" .
                " ORDER BY order_id DESC" .
                " LIMIT " . $filter['start'] . ",$filter[page_size]";
    }

	//echo $sql;
    $query = $GLOBALS['db']->query($sql);
	//print_r($GLOBALS['db']->getALL($sql));
    while ($rt = $GLOBALS['db']->fetch_array($query))
    {
        if(empty($separate_by) && $rt['up'] > 0)
        {
            //按推荐注册分成
            $rt['separate_able'] = 1;
        }
        elseif(!empty($separate_by) && $rt['parent_id'] > 0)
        {
            //按推荐订单分成
            $rt['separate_able'] = 1;
        }
        if(!empty($rt['suid']))
        {
            //在affiliate_log有记录
            $rt['info'] = sprintf($GLOBALS['_LANG']['separate_info2'], $rt['suid'], $rt['auser'], $rt['money'], $rt['point']);
            if($rt['separate_type'] == -1 || $rt['separate_type'] == -2)
            {
                //已被撤销
                $rt['is_separate'] = 3;
                $rt['info'] = "<s>" . $rt['info'] . "</s>";
            }
        }
        $logdb[] = $rt;
    }

	$logdbnew=array();
	foreach($logdb  as $key=>$value){
		
		//print_r($value);
		
			$if_shishi=1;
			if($value['is_separate']==1){
				$if_shishi=$value['user_id']==$user_id&&$value['suid']==$_SESSION['user_id'];
			}else{
				$if_shishi=$value['user_id']==$user_id;
			}
		if($if_shishi){
			$logdbnew[$key]=$value;
			$user_id=$value['user_id'];
			$sql = "SELECT wxid FROM " .$GLOBALS['ecs']->table('users'). " WHERE user_id = '$user_id'";
			$wxid = $GLOBALS['db']->getOne($sql);
			if(!empty($wxid)){
				$weixinInfo = $GLOBALS['db']->getRow("SELECT nickname, headimgurl FROM wxch_user WHERE wxid = '$wxid'");
				$logdbnew[$key]['avatar'] = empty($weixinInfo['headimgurl']) ? '':$weixinInfo['headimgurl'];
				$logdbnew[$key]['username'] = empty($weixinInfo['nickname']) ? '':$weixinInfo['nickname'];
			}	
				$affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
				$k=$level-1;
				$affiliate['item'][$k]['level_money'] = (float)$affiliate['item'][$k]['level_money'];
                if ($affiliate['item'][$k]['level_money'])
                {
                    $affiliate['item'][$k]['level_money'] /= 100;
                }
				$setmoney = round($value['fencheng'] * $affiliate['item'][$k]['level_money'], 2);
				$logdbnew[$key]['set_money']=$setmoney;
				$logdbnew[$key]['level_money']=$affiliate['item'][$k]['level_money'];
				$logdbnew[$key]['order_amount']=$value['fencheng'];
				
			
		}
	}
	//print_r($logdbnew);
    $arr = array('logdb' => $logdbnew, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}	
function page_and_size($filter)
{
    if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0)
    {
        $filter['page_size'] = intval($_REQUEST['page_size']);
    }
    elseif (isset($_COOKIE['ECSCP']['page_size']) && intval($_COOKIE['ECSCP']['page_size']) > 0)
    {
        $filter['page_size'] = intval($_COOKIE['ECSCP']['page_size']);
    }
    else
    {
        $filter['page_size'] = 15;
    }

    /* 每页显示 */
    $filter['page'] = (empty($_REQUEST['page']) || intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);

    /* page 总数 */
    $filter['page_count'] = (!empty($filter['record_count']) && $filter['record_count'] > 0) ? ceil($filter['record_count'] / $filter['page_size']) : 1;

    /* 边界处理 */
    if ($filter['page'] > $filter['page_count'])
    {
        $filter['page'] = $filter['page_count'];
    }

    $filter['start'] = ($filter['page'] - 1) * $filter['page_size'];

    return $filter;
}
/**
 * 取得帐户明细
 * @param   int     $user_id    用户id
 * @param   string  $account_type   帐户类型：空表示所有帐户，user_money表示可用资金，
 *                  frozen_money表示冻结资金，rank_points表示等级积分，pay_points表示消费积分
 * @return  array
 */
function get_accountlist($user_id, $account_type = '')
{
    /* 检查参数 */
    $where = " WHERE user_id = '$user_id' ";
    if (in_array($account_type, array('user_money', 'frozen_money', 'rank_points', 'pay_points')))
    {
        $where .= " AND $account_type <> 0 ";
    }

    /* 初始化分页参数 */
    $filter = array(
        'user_id'       => $user_id,
        'account_type'  => $account_type
    );

    /* 查询记录总数，计算分页数 */
    $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('account_log') . $where;
    $filter['record_count'] = $GLOBALS['db']->getOne($sql);
    $filter = page_and_size($filter);

    /* 查询记录 */
    $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('account_log') . $where .
            " ORDER BY log_id DESC";
    $res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

    $arr = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $row['change_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['change_time']);
        $arr[] = $row;
    }

    return array('account' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}


?>
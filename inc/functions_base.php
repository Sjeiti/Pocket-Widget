<?php

// dump
if (!function_exists("dump")) {
	function dump($s) {
		echo "<pre>";
		print_r($s);
		echo "</pre>";
	}
}

// trace
if (!function_exists("trace")) {
	function trace($s) {
		$oFile = fopen("log.txt", "a");
		$sDump  = $s."\n";
		fputs ($oFile, $sDump );
		fclose($oFile);
	}
}

// fileIsNewer
if (!function_exists("fileIsNewer")) {
	function fileIsNewer($file,$newerThan) {
		return filemtime($file)>filemtime($newerThan);
	}
}


// isPage
if (!function_exists("isPage")) {
	function isPage($pageName) {
		return (isset($_GET['post'])&&get_post_type($_GET['post'])==$pageName)
			|| (isset($_GET['post_type'])&&$_GET['post_type']==$pageName);
	}
}


// isSettings
if (!function_exists("isSettings")) {
	function isSettings($pageName) {
		$aSlashed = explode('/',$_SERVER['PHP_SELF']);
		return array_pop($aSlashed)=='options-general.php'&&isset($_GET['page'])&&$_GET['page']==$pageName;
	}
}

// atLogin
if (!function_exists("atLogin")) {
	function atLogin() {
		$sSelf = $_SERVER['PHP_SELF'];
		$aSelf = explode('/',$sSelf);
		$iSelf = count($aSelf);
		//echo '<!-- '.$aSelf[$iSelf-2].' '.$aSelf[$iSelf-1].' -->';
		return ($aSelf[$iSelf-2]=='wp-admin'&&$aSelf[$iSelf-1]=='index.php')||$aSelf[$iSelf-1]=='wp-login.php';
	}
}
// atScript
if (!function_exists("atScript")) {
	function atScript($name) {
		$sSelf = $_SERVER['SCRIPT_NAME'];
		$aSelf = explode('/',$sSelf);
		$sScript = array_pop($aSelf);
		return $sScript==$name;
	}
}
// atLocalhost
if (!function_exists("atLocalhost")) {
	function atLocalhost() {
		return $_SERVER['SERVER_NAME']=='localhost';
	}
}


// echoLogo
if (!function_exists("echoLogo")) {
	function echoLogo(){
		echoTag(is_home()?'h1':'h4','<a href="'.get_bloginfo('url').'/"><span class="name">'.get_bloginfo('name').'</span><span class="payoff">'.get_bloginfo('description').'</span></a>',' id="logo"');
	}
}

// placeHolder // todo: absolet
if (!function_exists("placeHolder")) {
	function placeHolder($file='index.html') {
		if (!atLocalhost()&&!is_user_logged_in()&&!atLogin()&&!atScript('admin-ajax.php')) {
			$sThemeBase = get_theme_root().'/'.get_template().'/';
			$sThemeUri = get_template_directory_uri().'/';
			$sRgx = '/(?<=\(|\(\')(style\/)/';
			$sCss = file_get_contents($sThemeBase.$file);
			echo preg_replace($sRgx,$sThemeUri.'style/',$sCss);
			die('<!-- IDDQD -->');
		}
	}
}

// get_featured
if (!function_exists("get_featured")) {
	function get_featured() {
		global $post;
		$header_image = get_header_image();
		if (has_post_thumbnail( $post->ID )) {
			$oFeatured = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );
			$header_image = $oFeatured[0];
		}
		return $header_image;
	}
}

// getTagId
if (!function_exists("getTagId")) {
function getTagId($name) {
	global $wpdb;
	$tag_ID = $wpdb->get_var("SELECT * FROM ".$wpdb->terms." WHERE  `name` =  '".$name."'");
	return $tag_ID;
}
}

// getTagId
if (!function_exists("outputBuffer")) {
function outputBuffer($name) {
	ob_start();
	$sHtml = ob_get_contents();
	ob_end_flush();
	return $sHtml;
}
}

/*
function getUser($user_id){
	return get_userdata($user_id);
}

function getUserRole($user){
	//if (!empty($user->roles)&& is_array($user->roles)) foreach ($user->roles as$role) return $role;
	global $wpdb;
	$sCap = $wpdb->prefix.'capabilities';
	$oCap = $user->$sCap;
	if (!empty($oCap)&& is_array($oCap)) foreach ($oCap as $role=>$id) return $role;
}

function getRoleName($usertype){
	switch ($usertype) {
		case 'pers':		return __("press"); break;
		case 'exposant':	return __("exhibitor"); break;
		case 'bezoeker':	return __("visitor"); break;
		return __("nothing");
	}
}

function echoTag($tag,$contents,$attributes){
	echo '<'.$tag.' '.$attributes.'>'.$contents.'</'.$tag.'>';
}

function getPassword($iLen,$bCut=false) {
	$iSnm = $bCut?2*$iLen:$iLen;
	$lLtr = array(array('a','e','i','o','u','y'),array('aa','ai','au','ea','ee','ei','eu','ia','ie','io','iu','oa','oe','oi','ua','ui'),array('b','c','d','f','g','h','j','k','l','m','n','p','q','r','s','t','v','w','x','z'),array('bb','br','bs','cc','ch','cl','cr','db','dd','df','dg','dh','dj','dk','dl','dm','dn','dp','dq','dr','ds','dt','dv','dw','dz','fb','fd','ff','fg','fh','fj','fk','fl','fm','fn','fp','fq','fr','fs','ft','fv','fw','fz','gb','gd','gf','gg','gh','gj','gk','gl','gm','gn','gp','gq','gr','gs','gt','gv','gw','gz','kb','kd','kf','kg','kh','kj','kk','kl','km','kn','kp','kq','kr','ks','kt','kv','kw','kz','lb','ld','lf','lg','lh','lj','lk','ll','lm','ln','lp','lq','lr','ls','lt','lv','lw','lz','mb','md','mf','mg','mh','mj','mk','ml','mm','mn','mp','mq','mr','ms','mt','mv','mw','mz','nb','nd','nf','ng','nh','nj','nk','nl','nm','nn','np','nq','nr','ns','nt','nv','nw','nz','pb','pd','pf','pg','ph','pj','pk','pl','pm','pn','pp','pq','pr','ps','pt','pv','pw','pz','qb','qd','qf','qg','qh','qj','qk','ql','qm','qn','qp','qq','qr','qs','qt','qv','qw','qz','rb','rd','rf','rg','rh','rj','rk','rl','rm','rn','rp','rq','rr','rs','rt','rv','rw','rz','sb','sc','sd','sf','sg','sh','sj','sk','sl','sm','sn','sp','sq','sr','ss','st','sv','sw','sz','tb','td','tf','tg','th','tj','tk','tl','tm','tn','tp','tq','tr','ts','tt','tv','tw','tz','vb','vd','vf','vg','vh','vj','vk','vl','vm','vn','vp','vq','vr','vs','vt','vv','vw','vz','xb','xd','xf','xg','xh','xj','xk','xl','xm','xn','xp','xq','xr','xs','xt','xv','xw','xx','xz'));
	$iSnm = 6;
	$sPsw = "";
	$iNum = 0;
	for ($i=0;$i<$iSnm;$i++) {
		if ($i==0) {
			$iNum = rand(0,2);
		} else if ($i==$iSnm-1) {
			$iNum = ($iNum<2)?2:rand(0,1);
		} else {
			$iNum = ($iNum<2)?rand(0,1)+2:rand(0,1);
		}
		$lLst = $lLtr[$iNum];
		$sPsw .= $lLst[ rand(0,count($lLst)-1) ];
	}
	return $bCut?substr($sPsw,0,$iLen):$sPsw;
}*/

?>
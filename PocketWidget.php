<?php
require_once(__DIR__.'/inc/FormElement.php');
class PocketWidget extends WP_Widget {

	private $aValues;

	public function __construct() {
		$this->aValues = array(
			'title' => array('label' => __('Title:','pocketwidget'))
			// state: unread|archive|all
			// favorite: 0|1
			// tag: [tag_name]|_untagged_
			// contentType: article|video|image
			// sort: newest|oldest|title|site
			// detailType: simple|complete
			// search {string}
			// domain {string}
			// since {timestamp}
			// count {integer}
			// offset {offset}
			,'state' => array('label' => __('State','pocketwidget'), 'type'=>'select', 'values'=>$this->optionArray('all|unread|archive'), 'default'=>'all')
			,'favorite' => array('label' => __('Favorite','pocketwidget'), 'type'=>'checkbox')
			,'tag' => array('label' => __('Tag','pocketwidget'), 'type'=>'text')
			,'contentType' => array('label' => __('Tag','pocketwidget'), 'type'=>'select', 'values'=>$this->optionArray('any|article|video|image'), 'default'=>'any')
			,'sort' => array('label' => __('Tag','pocketwidget'), 'type'=>'select', 'values'=>$this->optionArray('newest|oldest|title|site'), 'default'=>'newest')
			,'search' => array('label' => __('Search','pocketwidget'), 'type'=>'text')
			,'domain' => array('label' => __('Domain','pocketwidget'), 'type'=>'text')
			// since
			,'count' => array('label' => __('Count','pocketwidget'), 'type'=>'number', 'default'=>3)
			// offset
		);
		parent::__construct( false, 'Pocket Widget' );
		$this->parseValues();
	}

	private function optionArray($string) {
		$a = array();
		foreach (explode('|',$string) as $k) $a[$k] = $k;
		return $a;
	}

	private function parseValues() {
		foreach ($this->aValues as $name=>$aValue) {
			$this->aValues[$name]['attr'] = array('class'=>'widefat');
		}
	}

	public function widget( $args, $instance ) {
        extract( $args );
        $sTitle = isset($instance['title'])?apply_filters('widget_title', $instance['title']):'';
		//
		$aOptions = array(
			'detailType' => 'simple'
			,'state' => $instance['state']
			,'favorite' => $instance['favorite']==='on'?1:0
			,'sort' => $instance['sort']
			,'count' => $instance['count']
		);
		if ($instance['contentType']!=='any') $aOptions['contentType'] = $instance['contentType'];
		if ($instance['tag']!=='') $aOptions['tag'] = $instance['tag'];
		if ($instance['search']!=='') $aOptions['search'] = $instance['search'];
		if ($instance['domain']!=='') $aOptions['domain'] = $instance['domain'];
		//
		$sOptionsQuery = http_build_query($aOptions);
		$sUrl = 'https://getpocket.com/v3/get?'.$sOptionsQuery;
		//$sUrl = 'https://getpocket.com/v3/get?count=6&favorite=1';
		//
		$sQuery = http_build_query(array(
			'consumer_key' => get_option('pocketwidget_consumer_key')
			,'access_token' => get_option('pocketwidget_access_token')
		));
		$oContext  = stream_context_create(array(
			'http' => array(
				'header' => "Content-Type: application/x-www-form-urlencoded\r\n".
						"Content-Length: ".strlen($sQuery)."\r\n".
						"User-Agent:MyAgent/1.0\r\n"
				,'method'  => 'POST'
				,'content' => $sQuery
			)
		));

		//
		// make request
		$sResult = file_get_contents($sUrl, false, $oContext);
		$bError = $sResult===false;
		if (!$bError) $oResult = json_decode($sResult);
		//
//		$oCurl = curl_init();
//		curl_setopt($oCurl, CURLOPT_URL, $sUrl);
////		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
//		//////////////////////////////////////////////////////
//		curl_setopt($oCurl, CURLOPT_POST, true);
//		curl_setopt($oCurl, CURLOPT_POSTFIELDS, $sQuery);
//		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, true);
//		curl_setopt($oCurl, CURLOPT_FOLLOWLOCATION, true);
////		curl_setopt($oCurl, CURLOPT_HEADER, $this->_config['debug']);
//		curl_setopt($oCurl, CURLINFO_HEADER_OUT, true);
//		curl_setopt($oCurl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'X-Accept: application/json'));
//		curl_setopt($oCurl, CURLOPT_USERAGENT, 'php-pocket 0.2');
//		curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, 5);
//		curl_setopt($oCurl, CURLOPT_TIMEOUT, 15);
//		////////////$oCurl////////////////////////////////////////
//		$sResult = curl_exec($oCurl);
//		$oResult = json_decode($sResult);
//		// handle request error
//		$sHTTPCode = curl_getinfo($oCurl, CURLINFO_HTTP_CODE);
//		$bError = $sHTTPCode!==200;
//		curl_close($oCurl);
		//
		if ($bError) {
			echo $args['before_widget']
				.($sTitle?elm('h3','widget-title',$sTitle):'')
				.elm('p','error',__('Something went wrong while connecting to Pocket. Try re-authorizing on <a href="options-general.php?page=pocketwidget">the settings page</a>.','pocketwidget'))
				.$args['after_widget']
			;
		} else {
			$sLi = '';
			foreach ($oResult->list as $id=>$item) {
	            /*
	            [item_id] => 394324160
	            [resolved_id] => 394324160
	            [given_url] => http://shouldiuseacarousel.com/
	            [given_title] =>
	            [favorite] => 1
	            [status] => 1
	            [time_added] => 1399653521
	            [time_updated] => 1399669934
	            [time_read] => 1399669939
	            [time_favorited] => 1399669927
	            [sort_id] => 0
	            [resolved_title] => should i use a carousel?
	            [resolved_url] => http://shouldiuseacarousel.com/
	            [excerpt] => 1% clicked a feature. Of those, 89% were the first position. 1% of clicks for the most significant object on the home page?  The target was the biggest item on the homepage - the first carousel item. “Nonetheless, the user failed the task.”
	            [is_article] => 0
	            [is_index] => 1
	            [has_video] => 0
	            [has_image] => 0
	            [word_count] => 197
	            */
				$sLi .= elm('li',null,elm('a',array('href'=>$item->resolved_url,'target'=>'_blank'),$item->resolved_title));
			}
			echo $args['before_widget']
				.($sTitle?elm('h3','widget-title',$sTitle):'')
				.elm('ul',null,$sLi)
				.$args['after_widget']
			;
		}
	}

 	public function form( $instance ) {

		$sConsumer_key = get_option('pocketwidget_consumer_key');
		$sAccess_token = get_option('pocketwidget_access_token');
		if (empty($sConsumer_key)||empty($sAccess_token)) {
			echo elm('p','error',
				__('Before using this widget you have to authenticate with Pocket. Go to <a href="options-general.php?page=pocketwidget">the settings page</a> and click authorize.','pocketwidget')
			);
		}
		foreach ($this->aValues as $name=>$aValue) {
			$aValue['id'] = $this->get_field_id($name);
			$aValue['name'] = $this->get_field_name($name);
			$aValue['value'] = empty( $instance[$name] ) ? (isset($aValue['default'])?$aValue['default']:'') : esc_attr( $instance[$name] );
			echo FormElement::getElement($aValue);
		}
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		foreach ($this->aValues as $name=>$value) {
			$instance[$name] = strip_tags($new_instance[$name]);
		}
        return $instance;
	}
}
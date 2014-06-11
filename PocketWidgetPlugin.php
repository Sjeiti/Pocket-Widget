<?php
/*
Plugin Name:	Pocket Widget
Plugin URI:		http://pocketwidget.sjeiti.com/
Version:		0.1.3
WordPress Version: 3.0.3
Author:			Ron Valstar
Author URI:		http://sjeiti.com/
Author email:	pw@sjeiti.com
Description:	A Wordpress widget to show your Pocket collection.
*/
if (!class_exists('PocketWidgetPlugin')) {
require_once 'PocketWidgetPluginBase.php';
include('inc/FormElement.php');
class PocketWidgetPlugin extends PocketWidgetPluginBase {

	protected $sPluginName = 'PocketWidget';
	protected $sPluginId = 'pocketwidget'; // strtolower($sPluginName);
	protected $sPluginHomeUri = 'http://pocketwidget.sjeiti.com/';
	protected $sPluginWpUri = 'http://wordpress.org/extend/plugins/pocketwidget/';
	protected $sPluginFlattrUri = '';
	protected $sConstantId = 'SPW';
	protected $sVersion = '0.1.0';

	private $sConsumerKey = '19156-8cc4e5f72fd2e401f29b6826';

	/**
	 * The constructor constructing stuff.
	 */
	function __construct() {
		parent::__construct();
		$this->sPluginRootUri = plugin_dir_url(__FILE__);
		$this->sPluginRootDir = plugin_dir_path(__FILE__);
		$this->sPluginBaseName = plugin_basename(__FILE__);
		$this->sPluginDirName = dirname($this->sPluginBaseName);
		add_action('plugins_loaded',array(&$this,'addHooks'));
	}

	/**
	* Add all the hooks when the plugins are loaded.
	*/
	function addHooks() {
		//
		// set locale
 		load_plugin_textdomain( $this->sPluginId, false, $this->sPluginDirName.'/lang/' );
		$this->getFormdata(true); // force form data with locale
		//
		// admin
		add_action('admin_menu',			array(&$this,'initSettings'));
		add_action('admin_init',			array(&$this,'initSettingsForm'));
		add_action('widgets_init',			array(&$this,'initWidget'));
		add_action('admin_enqueue_scripts',	array(&$this,'initScripts'));

		add_filter('plugin_action_links_'.$this->sPluginBaseName, array(&$this,'plugin_page_settings_link') );
	}

	function initSettings() {
		add_options_page(__('Pocket widget options','pocketwidget'),__('Pocket widget','pocketwidget'),'manage_options','pocketwidget',array(&$this,'settingsPage'));
	}

	function initWidget() {
		//$sAccessToken = get_option('pocketwidget_access_token');
		//if (!empty($sAccessToken)) {
		require_once $this->sPluginRootDir.'PocketWidget.php';
		register_widget('PocketWidget');
	}

	function initScripts($hook) {
		if ($hook==='settings_page_pocketwidget') {
			wp_enqueue_script('spw_admin',$this->sPluginRootUri.'js/pocketwidget.js');
		}
	}

	function plugin_page_settings_link($links) {
	  $settings_link = '<a href="options-general.php?page=pocketwidget">Settings</a>';
	  array_unshift($links, $settings_link);
	  return $links;
	}

	function settingsPage() {

		ob_start();
		settings_fields($this->sIdSettings);
		do_settings_sections($this->sIdPage);
		$sHtml = ob_get_contents();
		ob_end_clean();

		echo elm('div','wrap wp-'.$this->sPluginId.'-settings',
			elm('div',array('id'=>'icon-options-general','class'=>'icon32'))
			.elm('h2',null,__('Pocket widget options','pocketwidget'))
			.$this->showErrors()
			.elm('div','postbox-container main',
				elm('div','metabox-holder',
					elm('div','meta-box-sortables ui-sortable',
						elm('p',null,__('_explainPocketwidget','pocketwidget'))
						.elm('form',array('method'=>'post','action'=>'options.php'),
							$sHtml
							.elm('p',null,elm('input',array('type'=>'submit','name'=>'submit','class'=>'button-primary','value'=>__('Save changes','pocketwidget'))))
						)
						.elm('p',null,__('_explainPocketwidgetApi','pocketwidget'))
					)
				)
			)
		);
	}

	function getFormdata($force=false) {
		if (!$force||isset($this->aForm)) return $this->aForm;
		//
		$sGetQuery = http_build_query(array(
			'callback_uri'=>$this->sPluginRootUri.'callback.php'
			,'consumer_key'=>get_option('pocketwidget_consumer_key')
		));
		$sAccessToken = get_option('pocketwidget_access_token');
		$bAccessToken = !empty($sAccessToken);
		//
		$aForm = array(
			 'label1'=>array(
				'type'=>'label'
//				,'label'=>__('Basic settings','pocketwidget')
			 )
			,'pocketwidget_consumer_key'=>array(
				'default'=>$this->sConsumerKey
				,'data-default'=>$this->sConsumerKey
				,'label'=>__('Consumer key','pocketwidget')
				,'type'=>'text'
				,'text'=>
					elm('a',array(
						'href'=>$this->sPluginRootUri.'authenticate.php?'.$sGetQuery
						,'class'=>'button'.($bAccessToken?'':' button-primary')
					),__('Authorize','pocketwidget'))
					.elm('a',array('href'=>'#','id'=>'reset_consumer_key'),__('Reset to default','pocketwidget'))
					.($bAccessToken?elm('div',array('id'=>'show_access_token'),
						elm('span',null,__('Access token:','pocketwidget')).' '
						.elm('strong',null,$sAccessToken).' '
						.elm('a',array('href'=>'#','id'=>'revoke_access_token'),__('Revoke','pocketwidget'))
					):'')

				,'size'=>'30'
			)
			,'pocketwidget_access_token'=>array(
				'type'=>'hidden'
			)
		);
		$this->aForm = $this->setDefaultOptions($aForm);
		return $this->aForm;
	}

	function drawFormField($data) {
		parent::drawFormField($data);
	}

//	function getInputName($sId){
//		return $this->sPluginId.'-'.$sId.'-input';
//	}
}
}
new PocketWidgetPlugin();
?>

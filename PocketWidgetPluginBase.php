<?php
if (!class_exists('PocketWidgetPluginBase')) {
	include_once('inc/functions_base.php');
	class PocketWidgetPluginBase {
		//
		protected $sPluginName;
		protected $sPluginId;
		protected $sPluginHomeUri;
		protected $sPluginWpUri;
		protected $sPluginFlattrUri;

		protected $sPluginRootUri;
		protected $sPluginRootDir;
		protected $sPluginBaseName;
		protected $sPluginDirName;

		protected $sIdSettings;
		protected $sIdPage;
		protected $sIdField;

		protected $sConstantId;
		protected $sVersion;
		protected $aForm;
		//
		private $aError = array();
		//
		// PocketWidgetPluginBase
		function __construct() {
			$this->sIdSettings = $this->sPluginId.'_settings';
			$this->sIdPage = $this->sPluginId.'_page';
			$this->sIdField = $this->sPluginId.'_field';
		}
		//
		// getValue
		protected function getValue($s){
			$aForm = $this->getFormdata();
			$o = $aForm[$s];
			$value = $o['value'];
			$sType = $o['type'];
			// if 'values' is set then the value should be an array unless it's type is dropdown
			if (isset($o['values'])&&!is_array($value)&&!$sType=='select'&&!$sType=='dropdown') $value = array();
			// if the type is 'checkbox' and 'values' is not set then the value should be a boolean
			if (isset($o['type'])&&$o['type']=='checkbox'&&!isset($o['values'])) $value = $value=='on';
			return $value;
		}
		//
		// getObject
		protected function getObject($s){
			$aForm = $this->getFormdata();
			return $aForm[$s];
		}
		//
		// section_text
		public function section_text($for){
			$aForm = $this->getFormdata();
			return isset($aForm[$for['id']]['text'])?elm('p',null,$aForm[$for['id']]['text']):'';
		}
		//
		// getFormdata
		protected function getFormdata($force=false) {
			if (!$force||isset($this->aForm)) return $this->aForm;
			return $this->aForm;
		}
		//
		// setDefaultOptions
		protected function setDefaultOptions($form) {
			foreach ($form as $sId=>$aField) {
//				if (!in_array($aField['type'],array('label','script'))) {
				if (!isset($aField['type'])||$aField['type']!='label') {
//				if ($aField['type']!='label') {
					$sDefault = isset($aField['default'])?$aField['default']:'';
					$sVal = get_option($sId);
					if ($sVal===false) update_option($sId, $sDefault);
					$form[$sId]['value'] = $sVal!==false?$sVal:$sDefault;
					$form[$sId]['id'] = $sId;
				}
			}
			return $form;
		}
		//
		// initSettingsForm
		public function initSettingsForm() {
			$sSection = 'default';
			$aForm = $this->getFormdata();
			foreach ($aForm as $sId=>$aField) {
				$sLabel = isset($aField['label'])?$aField['label']:'';
				$bHasType = isset($aField['type']);
				if ($bHasType) {
					if ($aField['type']==='label') {
						$sSection = $sId;
						add_settings_section($sId, $sLabel, array(&$this,'section_text'), $this->sIdPage);
					} else {
						register_setting( $this->sIdSettings, $sId, array(&$this,'optionsSanatize') ); // todo: validation?
						add_settings_field( $sId, $sLabel, array(&$this,'drawFormField'), $this->sIdPage, $sSection, $aField);
					}
				}
			}
		}
		//
		// drawFormField
		protected function drawFormField($data) {
			//
			$sId = $data['id'];
			$sType = isset($data['type'])?$data['type']:'text';
			$sValue = $data['value'];
			$aValues = isset($data['values'])?$data['values']:array();
			$bValues = count($aValues)>0;
			//
			$sElement = $sType==='textarea'?'textarea':'input';
			$sContent = $sType==='textarea'?$sValue:'';
			$sText = isset($data['text'])?elm('span','description',$data['text']):'';
			//
			$aAttributes = array(
				'value' =>	is_array($sValue)?json_encode($sValue):$sValue
				,'type' =>	isset($data['type'])?$data['type']:'text'
				,'name' =>	$sId
				,'id' =>	$sId
			);
//			foreach (array('required','disabled','size') as $k) {
//				if (isset($data[$k])) $aAttributes[$k] = $data[$k];
//			}
			foreach ($data as $k=>$v) {
//				dump($k.': '.$v);
//				dump(substr($k,0,4));
				if (in_array($k,array('required','disabled','size','placeholder'))||substr($k,0,5)==='data-') {
					$aAttributes[$k] = $v;
				}
			}
			//
			$sHTML = '';
			if ($bValues&&$sType==='text') {
				foreach ($aValues as $sValueId=>$sValueLabel) {
					$sSubId = $sId.$sValueId;
					$aAttributes['name'] = $sId.'['.$sValueId.']';
					$aAttributes['id'] = $sSubId;
					$aAttributes['value'] = isset($sValue[$sValueId])?$sValue[$sValueId]:'';
					$sHTML .= elm('label',array('for'=>$sSubId),$sValueLabel)
							.elm('input',$aAttributes);
				}
				$sHTML .= $sText;

			} else if ($bValues&&$sType==='select') {
				$sContent = '';
				unset($aAttributes['value']);
				unset($aAttributes['type']);
				foreach ($aValues as $sValueId=>$sValueLabel) {
					$aOptionAttr = array('value'=>$sValueId);
					if ($sValueId===$sValue) $aOptionAttr['selected'] = 'selected';
					$sContent .= elm('option',$aOptionAttr,$sValueLabel);
				}
				$sHTML .= elm('select',$aAttributes,$sContent).$sText;

			} else if ($bValues&&$sType==='checkbox') {
				foreach ($aValues as $sValueId=>$sValueLabel) {
					$sSubName = $sId.'['.$sValueId.']';
					$sSubId = $sId.$sValueId;
					$aAttributes['name'] = $sSubName;
					$aAttributes['id'] = $sSubId;
					unset($aAttributes['value']);
					if (isset($sValue[$sValueId])&&$sValue[$sValueId]=='on') $aAttributes['checked'] = 'checked';
					$sHTML .= elm('input',$aAttributes).elm('label',array('for'=>$sSubId),$sValueLabel);
				}
				$sHTML .= $sText;

			} else if ($sType==='array') {
				// todo: implement elm
				//$oData = json_decode($sValue);
				//dump($oData);
				$sHTML .= '<div class="settings-array">';
				$sHTML .= 	'<input name="'.$sId.'" id="'.$sId.'" type="hidden" value="'.str_replace('"','&quot;',$sValue).'" />';

				$sHTML .= '<script type="text/html" class="tmpl">';
				$sHTML .= '<tr><td><%=key%></td><td><%=label%></td><td>';
				$sHTML .= '<button class="dashicons dashicons-no"></button>';
				$sHTML .= '</td></tr>';
				$sHTML .= '</script>';

				$sHTML .= 	'<table>';
				$sHTML .= 		'<thead><tr><th data-key="key">key</th><th data-key="label">value</th><th>remove</th></tr></thead>';
				$sHTML .= 		'<tbody></tbody>';
				$sHTML .= 		'<tfoot><tr><td><input type="text" data-key="key" placeholder="key" /></td><td><input type="text" data-key="label" placeholder="value" /></td><td><input type="button" class="add-button" value="'.__('Add value').'" /></td></tr></tfoot>';
				$sHTML .= 	'</table>';
				$sHTML .= '</div>';

			} else if ($sType==='meta') {
				// todo: implement elm
				// todo: override for meta in child class
				// todo: check 'array' and 'meta' for position of data-key (is in header as well as footer)
				// todo: automate rows by looping array instead of writing them all out
				//$oData = json_decode($sValue);
				//dump($oData);
				$sHTML .= '<div class="settings-array">';
				$sHTML .= 	'<input name="'.$sId.'" id="'.$sId.'" type="hidden" value="'.str_replace('"','&quot;',$sValue).'" />';

				$sHTML .= '<script type="text/html" class="tmpl">';
				$sHTML .= '<tr>';
				$sHTML .= 		'<td><%=key%></td>';
				$sHTML .= 		'<td><input type="text" value="<%=label%>"/></td>';
				$sHTML .=		'<td>'.$this->getSelectInput('<%=type%>').'</td>';
				$sHTML .= 		'<td><input type="checkbox"<%=!!incol?" checked":""%>/></td>';
				$sHTML .= 		'<td><input type="checkbox"<%=!!inquick?" checked":""%>/></td>';
				$sHTML .= '<td>';
				$sHTML .= '<button class="dashicons dashicons-arrow-up"></button>';
				$sHTML .= '<button class="dashicons dashicons-arrow-down"></button>';
				$sHTML .= '<button class="dashicons dashicons-no"></button>';
				$sHTML .= '</td>';
				$sHTML .= '</tr>';
				$sHTML .= '</script>';

				$sHTML .= 	'<table>';
				$sHTML .= 		'<thead><tr><th data-key="key">key</th><th data-key="label">label</th><th data-key="type">value</th><th data-key="incol">col</th><th data-key="inquick">quick</th><th>actions</th></tr></thead>';
				$sHTML .= 		'<tbody></tbody>';
				$sHTML .= 		'<tfoot><tr>';
				$sHTML .= 		'<td><input type="text" data-key="key" placeholder="key" /></td>';
				$sHTML .= 		'<td><input type="text" data-key="label" placeholder="value" /></td>';
				$sHTML .= 		'<td>'.$this->getSelectInput(false).'</td>';
				$sHTML .= 		'<td><input type="checkbox" data-key="incol" /></td>';
				$sHTML .= 		'<td><input type="checkbox" data-key="inquick" /></td>';
				$sHTML .= 		'<td><input type="button" class="add-button" value="'.__('Add value').'" /></td>';
				$sHTML .= 		'</tr></tfoot>';
				$sHTML .= 	'</table>';
				$sHTML .= '</div>';

			} else {
				if ($sType==='checkbox') {
					unset($aAttributes['value']);
					if ($sValue=='on') $aAttributes['checked'] = 'checked';
				}
				//dump($data['type']);
				$sHTML .= elm($sElement,$aAttributes,$sContent).$sText;
			}
			echo $sHTML;
		}
		//
		function optionsSanatize($a){
			return $a;
		}
		//
		private function getSelectInput($asTemplate=true) {
			$aOptions = array('text','textarea','checkbox','date');
			$sReturn = '<select data-key="type">';
			foreach ($aOptions as $option) $sReturn .= '<option value="'.$option.'"'.($asTemplate?' <%=(type=="'.$option.'"?"selected":"")%>':'').'>'.$option.'</option>';
			$sReturn .= '</select>';
			return $sReturn;
		}
		//
		private function nonceName($id) {
			return $this->sPluginId.'_'.$id.'_nonce';
		}
		protected function addNonce($id) {
        	wp_nonce_field( basename( __FILE__ ), $this->nonceName($id) );
		}
		protected function checkNonce($id) {
			$sNonce = $this->nonceName($id);
			return isset( $_POST[$sNonce])&&wp_verify_nonce($_POST[$sNonce],basename( __FILE__ ));
		}
		//
		// postbox
		protected function postbox($id, $title, $content) {
			return elm('div',array('id'=>$id,'class'=>'postbox'),
				elm('h3','hndle',elm('span',null,$title))
				.elm('div','inside',$content)
			);
		}
		//
		// addError
		protected function addError($warning,$message='') {
			$this->aError[] = array($warning,$message);
		}
		//
		// showErrors
		protected function showErrors() {
			$errors = '';
			foreach ($this->aError as $i=>$error) {
				$errors .= $this->errorBox($error[0],$error[1]);
			}
			return $errors;
		}
		//
		// errorBox
		protected function errorBox($warning,$message) {
			return elm('div','sfb-debug error settings-error',elm('p',null,
				elm('strong',null,$warning)
				.$message
			));
		}
		//
		// like plugin?
		protected function plugin_like() {
			return $this->postbox(
				 'donate'
				,'<strong class="red">'.$this->sPluginName.' '.$this->sVersion.'</strong>'
				//,'<form action="https://www.paypal.com/cgi-bin/webscr" method="post"><input type="hidden" name="cmd" value="_donations"><input type="hidden" name="business" value="FFDDQVHENGNXG"><input type="hidden" name="lc" value="NL"><input type="hidden" name="item_name" value="sfbrowser"><input type="hidden" name="currency_code" value="EUR"><input type="hidden" name="bn" value="PP-DonationsBF:btn_donateCC_LG.gif:NonHosted"><input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal, de veilige en complete manier van online betalen."><img alt="" border="0" src="https://www.paypal.com/nl_NL/i/scr/pixel.gif" width="1" height="1"></form>'
				,elm('hr')
				.elm('strong',null,'If you like '.$this->sPluginName)
				.elm('p',null,elm('a',array('href'=>$this->sPluginFlattrUri),'Please rate it'))
			);
		}
	}
}
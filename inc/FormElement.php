<?php
if (!class_exists('FormElement')) {
	require_once('HTMLElement.php');
	class FormElement extends HTMLElement {
		/**
		 * @static
		 * @param $data array()
		 * @return string
		 */
		// todo: check default value
		// todo: check possible refactor to get (see HTMLElement)
		public static function getElement($data) {
			$sId = isset($data['id'])?$data['id']:$data['name'];
			$sName = isset($data['name'])?$data['name']:$data['id'];
			$sType = isset($data['type'])?$data['type']:'text';
			$sValue = isset($data['value'])?$data['value']:'';
			$aValues = isset($data['values'])?$data['values']:array();
			$sHTML = isset($data['label'])?'<label for="'.$sId.'">'.$data['label'].'</label>':'';
			$aAttr = isset($data['attr'])?$data['attr']:array();
			switch ($sType) {
				case 'text':		$sHTML .= self::getInputText($sId,$sName,$sValue,$aValues,$aAttr); break;
				case 'select':		$sHTML .= self::getSelect($sId,$sName,$sValue,$aValues,$aAttr); break;
				case 'checkbox':	$sHTML .= self::getCheckbox($sId,$sName,$sValue,$aValues,$aAttr); break;
				case 'radio':		$sHTML .= self::getRadios($sId,$sName,$sValue,$aValues,$aAttr); break;
				case 'textarea':	$sHTML .= self::getTextArea($sId,$sName,$sValue,$aAttr); break;
				case 'hidden':		$sHTML .= self::getHidden($sId,$sName,$sValue,$aAttr); break;
				case 'array':		$sHTML .= self::getArray($sId,$sName,$sValue); break;
				default:			$sHTML .= self::getInput($sType,$sId,$sName,$sValue,$aAttr);
			}
			/*
			 *  todo: add all types:
			 * 		progress
			 * 		meter
			 * 		datalist
			 * 		keygen
			 * 		output
			 *
			 * 		tel
			 * 		search
			 * 		url
			 * 		email
			 * 		datetime
			 * 		date
			 * 		month
			 * 		week
			 * 		time
			 * 		datetime-local
			 * 		number
			 * 		range
			 * 		color
			 *
			 * and remember attr
			 *		autofocus
			 *		placeholder
			 *		form
			 *		required
			 *		autocomplete
			 *		pattern
			 *		dirname
			 *		novalidate
			 *		formaction
			 *		formenctype
			 *		formmethod
			 *		formnovalidate
			 *		formtarget
			 */
			return $sHTML;
		}

		public static function getInput($type,$id,$name,$value='',$attr=array()) {
			return self::get('input',array_merge($attr,array(
				'id'=>$id
				,'name'=>$name
				,'type'=>$type
				,'value'=>$value
			)));
		}

		public static function getInputText($id,$name,$value,$values,$attr) {
			$sHTML = '';
			$attr['type'] = 'text';
			if (count($values)==0) {
				$sHTML .= self::get('input',array_merge($attr,array(
					'id'=>$id
					,'name'=>$name
					,'value'=>$value
				)));
			} else {
				foreach ($values as $sValueId=>$sValueLabel) {
					$aSubAttr = $attr;
					$aSubAttr['id'] = $id.$sValueId;
					$aSubAttr['name'] = $name;//$id.'['.$sValueId.']';
					$sHTML .= self::get('label',array('for'=>$aSubAttr['id']),$sValueLabel);
					$sHTML .= self::get('input',$aSubAttr);
				}
			}
			if (isset($data['text'])) $sHTML .= '<span class="description">'.$data['text'].'</span>';
			return $sHTML;
		}

		public static function getSelect($id,$name,$value,$values,$attr=array()) {
			$sHTML = '';
			$bMultiple = in_array('multiple',$attr);
			$aValue = json_decode($value);
			$bValueIsArray = is_array($aValue);
			foreach ($values as $sValueId=>$sValueLabel) {
				$aAttr = array('value'=>$sValueId);
				if ($bMultiple&&$bValueIsArray?in_array($sValueId,$aValue):$sValueId==$value) $aAttr[] = 'selected';
				if (is_array($sValueLabel)) {
					foreach ($sValueLabel as $key=>$val) if ($key!='title') $aAttr['data-'.$key] = preg_replace('/\"/','',$val);
					$sValueLabel = $sValueLabel['title'];
				}
				$sHTML .= self::get('option',$aAttr,$sValueLabel);
			}
			return self::get('select',array_merge(array(
				'id'=>$id
				,'name'=>$name//$id.($bMultiple?'[]':'')
				,'value'=>$value
			),$attr),$sHTML);
			/*return self::wrapWithElement('select',array_merge($attr,array(
				'id'=>$id
				,'name'=>$id.($bMultiple?'[]':'')
				,'value'=>$value
			)),$sHTML);*/
		}

		public static function getRadios($id,$name,$value,$values,$attr=array()) {
			$sHTML = '';
			$aAttr = array_merge(array(
				'type'=>'radio'
				,'id'=>$id
				,'name'=>$name
			),$attr);
			foreach ($values as $sValueId=>$sValueLabel) {
				/*if (is_array($sValueLabel)) {
					foreach ($sValueLabel as $key=>$val) if ($key!='title') $aAttr['data-'.$key] = preg_replace('/\"/','',$val);
					$sValueLabel = $sValueLabel['title'];
				}*/
				$aAttrSingle = array_merge($aAttr,array(
					'value'=>$sValueId
				));
				$aAttrSingle['id'] .= '-'.$sValueId;
				if ($sValueId==$value) $aAttrSingle[] = 'checked';
				$sHTML .= self::get('label',array('for'=>$aAttrSingle['id']),
					self::get('input',$aAttrSingle)
					.$sValueLabel
				);
				/*$sHTML .= self::get('input',$aAttrSingle,
					self::get('label',array('for'=>$aAttrSingle['id']),$sValueLabel)
				);*/
			}
			return $sHTML;
		}

		public static function getCheckbox($id,$name,$value='',$values=array(),$attr=array()){ // todo: set checked status if true
			$sHTML = '';
			$attr['type'] = 'checkbox';
			if (count($values)==0) {
				$attr['id'] = $id;
				$attr['name'] = $name;
				if ($value=='on') $attr[] = 'checked';
				$sHTML .= self::get('input',$attr);
			} else {
				foreach ($values as $sValueId=>$sValueLabel) {
					$aSubAttr = array_merge($attr,array(
						'id'=>$id.$sValueId
						,'name'=>$id.'['.$sValueId.']'
					));
					if (isset($value[$sValueId])&&$value[$sValueId]=='on') $aSubAttr[] = 'checked';
					$sHTML .= self::get('input',$aSubAttr);
					$sHTML .= '<label for="'.$aSubAttr['id'].'">'.$sValueLabel.'</label>';
				}
			}
			if (isset($data['text'])) $sHTML .= '<span class="description">'.$data['text'].'</span>';
			return $sHTML;
		}

		public static function getTextArea($id,$name,$value,$attr){
			return self::get('textarea',array_merge($attr,array(
				'id'=>$id
				,'name'=>$name
			)),$value);//'<textarea name="'.$id.'" id="'.$id.'" class="form_textarea" type="textarea"'.self::getAttr($attr).'>'.$value.'</textarea>';
		}

		public static function getHidden($id,$name,$value,$attr=array()){
			return self::get('input',array_merge($attr,array(
				'type'=>'hidden'
				,'id'=>$id
				,'name'=>$name
				,'value'=>$value
			)));
		}

		public static function getArray($id,$name,$value){
			$sHTML = '<div class="settings-array">';
			$sHTML .= 	'<input name="'.$name.'" id="'.$id.'" type="hidden" value="'.str_replace('"','&quot;',$value).'" />';
			$sHTML .= 	'<table>';
			$sHTML .= 		'<thead><tr><td>key</td><td>value</td><td>remove</td></tr></thead>';
			$sHTML .= 		'<tbody></tbody>';
			$sHTML .= 		'<tfoot><tr><td><input type="text" class="key" placeholder="key" /></td><td><input type="text" class="value" placeholder="value" /></td><td><input type="button" class="add-button" value="'.__('Add value').'" /></td></tr></tfoot>';
			$sHTML .= 	'</table>';
			$sHTML .= '</div>';
			return $sHTML;
		}

	//	// todo: create base class HTMLElement with following method and extend FormElement
	//	public static function get($type,$attr=array(),$html=null){
	//		return $html!==null?'<'.$type.self::getAttrString($attr).'>'.$html.'</'.$type.'>':'<'.$type.self::getAttrString($attr).' />';
	//	}
	//
	//	private static function getAttrString($attr=array()) {
	//		$bAttr = count($attr)>0;
	//		$sAttr = $bAttr?' ':'';
	//		if ($bAttr) {
	//			foreach ($attr as $k=>$v) {
	//				$sAttr .= is_numeric($k)?$v.' ':$k.'="'.$v.'" ';
	//			}
	//		}
	//		return $sAttr;
	//	}
	}
}

<?php
if (!class_exists('HTMLElement')) {
	class HTMLElement {
		private static $aSelfClose = array('area','base','br','col','command','embed','hr','inpu','imgt','keygen','link','meta','param','source','track','wbr');
		public static function get($type,$attr=array(),$html=''){
			$hasHTML = !empty($html);
			if (gettype($attr)==='string') {
				$attr = array('class'=>$attr);
			}
			$sReturn = $hasHTML||!in_array($type,self::$aSelfClose)
				?'<'.$type.self::getAttrString($attr).'>'.$html.'</'.$type.'>'
				:'<'.$type.self::getAttrString($attr).' />';
			return $sReturn;
		}
		private static function getAttrString($attr=array()) {
			$bAttr = count($attr)>0;
			$sAttr = $bAttr?' ':'';
			if ($bAttr) {
				foreach ($attr as $k=>$v) {
					@$sAttr .= is_numeric($k)?$v.' ':$k.'="'.$v.'" ';
				}
			}
			return $sAttr;
		}
	}
}
if (!function_exists('elm')) {
	function elm($type,$attr=array(),$html=''){
		return HTMLElement::get($type,$attr,$html);
	}
}
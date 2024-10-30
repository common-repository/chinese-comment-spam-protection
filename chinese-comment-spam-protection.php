<?php
/*
Plugin Name: Chinese Comment Spam Protection
Plugin URI: http://iibetter.com/12198.html
Description: Asks the visitor making the comment to key in the disapeared chinese word. This is intended to prove that the visitor is a human being but a spam robot. Example: <em>成长之？ 请输入“路”字</em>
Version: 1.0
Author: Joe Jiang
Author URI: http://iibetter.com/
*/

/*	----------------------------------------------------------------------------
 	    ____________________________________________________
       |                                                    |
       |           Chinese Comment Spam Protection          |
       |                  © Joe Jiang                       |
       |____________________________________________________|

	© Copyright 2010 Joe Jiang (joe031102 at gmail dot com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

	----------------------------------------------------------------------------

	ACKNOWLEDGEMENTS:
	- Thanks to 燕儿 (http://www.y2sky.com/) I took her idea and worked out it by writing a plugin on my own. 
	- Thanks to Michael Woehrer (http://sw-guide.de/) for his plugin
	  "Math Comment Spam Protection". I took his idea and extended/improved it by
	  writing a plugin on my own.
	- Thanks to my wife(http://shabo.org/)&daughter(http://baobaowan.net/)

	----------------------------------------------------------------------------

	INSTALLATION, USAGE:
	Visit the plugin's homepage.
	----------------------------------------------------------------------------
	
*/

require_once ( dirname(__FILE__) . '/inc.swg-plugin-framework.php');

class ChineseCommentSpamProtection extends ChineseCommentSpamProtection_SWGPluginFramework {

	/**
	 * Apply Spam Protection
	 */
	function ApplyChineseCommentSpamProtection() {

		// We add the math comment field to the theme
		if ($this->g_opt['regina_opt_apply'] == '1') {
			add_action('comment_form_after_fields', array(&$this, 'EchoInputField'));
		}

		// We check the user input
		add_filter('preprocess_comment', array(&$this, 'CheckInput'), 0);
	
	}


	function GetInputField() {

		// Include math class, create object and generate numbers
		if ( !class_exists('ChineseCheck') ) include_once ( dirname(__FILE__) . '/chinese-comment-spam-protection.classes.php');
		$ChineseCheckObject = new ChineseCheck;
		$GeneratedNumbersArray = $ChineseCheckObject->ChineseCheck_GenerateValues($this->g_opt['regina_opt_words']);
	
		// Replace placeholders
		$result = $this->g_opt['regina_opt_html'];
		$result = str_replace('[chinesewords]', $GeneratedNumbersArray['chinesewords'], $result);
		$result = str_replace('[accurateword]', $GeneratedNumbersArray['accurateword'], $result);
		$result = str_replace('[result]', $GeneratedNumbersArray['result'], $result);
		$result = str_replace('[fieldname_answer]', $this->g_opt['regina_opt_fieldname_useranswer'], $result);
		$result = str_replace('[fieldname_hash]', $this->g_opt['regina_opt_fieldname_result'], $result);
		return $result;

	}

	function EchoInputField() {
		echo  $this->GetInputField();
	}




	/**
	 * Input validation. 
	 */
	function CheckInput($comment_data) {
	
	    if (  ( !is_user_logged_in() ) && ( $comment_data['comment_type'] == '' ) ) { // Do not check if the user is logged in & do not check trackbacks/pingbacks
		
			// Get actual result
			$actual_result = $_POST[ $this->g_opt['regina_opt_fieldname_result'] ];
			// Get value user has entered
			$value_entered = $_POST[ $this->g_opt['regina_opt_fieldname_useranswer'] ];
			
			// Get input validation result
			if ( !class_exists('ChineseCheck') ) include_once ( dirname(__FILE__) . '/chinese-comment-spam-protection.classes.php');
			$ChineseCheckObject = new ChineseCheck;
			$result = $ChineseCheckObject->ChineseCheck_InputValidation($actual_result, $value_entered);
	
			// DIE if there was an error. Apply filter for security reasons (strip JS code, etc.)
			switch ($result) {
				case 'No answer': 
					wp_die( apply_filters('pre_comment_content', stripslashes($this->g_opt['regina_opt_msg_no_answer'])) );
					break;
				case 'Wrong answer': 
					wp_die( apply_filters('pre_comment_content', stripslashes($this->g_opt['regina_opt_msg_wrong_answer'])) );
					break;
			}
	
		}
	
		return $comment_data;
	
	}


	/**
	 * Plugin Options
	 */
	function PluginOptionsPage() {

		$this->AddContentMain(__('Add chinese inspection automatically',$this->g_info['ShortName']), '
			<p>
				'.__('If this option is activated, the HTML code for the chinese inspection will be added automatically to your theme.',$this->g_info['ShortName']). '
				'.__('Please note that this may not work with all themes. If it does not work with your theme, then deactivate this option and add the template tag',$this->g_info['ShortName']). ' <code>&lt;&#63;php regina_html(); &#63;&gt;</code> '.__('manually to the according theme file.',$this->g_info['ShortName']). '
			</p>
			<input name="regina_opt_apply" type="checkbox" id="regina_opt_apply" value="1" ' . ($this->COPTHTML('regina_opt_apply')=='1'?'checked="checked"':'') . ' />
			<label for="regina_opt_apply">'.__('Add chinese inspection automatically',$this->g_info['ShortName']).'</label>
			');

		$this->AddContentMain(__('HTML code for chinese inspection',$this->g_info['ShortName']), '
			<p>
				'.__('Here we have the HTML code for chinese inspection (will be used when activating the option',$this->g_info['ShortName']).' "<em>'.__('Add HTML output automatically',$this->g_info['ShortName']).'</em>" ' . __('or when using the template tag',$this->g_info['ShortName']) . ' <code>&lt;&#63;php regina_html(); &#63;&gt;</code> '.__('in your theme).',$this->g_info['ShortName']) . ' 				
			</p>
			<textarea name="regina_opt_html" id="regina_opt_html" cols="100%" rows="7">'
			. $this->COPTHTML('regina_opt_html') . '</textarea>

			<p class="swginfo">'
			.__('Use HTML only, no PHP allowed. You can use the following placeholders:',$this->g_info['ShortName']). ' <strong>[chinesewords]</strong> '.__('(chinese words)',$this->g_info['ShortName']). ', <strong>[accurateword]</strong> ' . __('(disappeared chinese word)',$this->g_info['ShortName']). ', <strong>[fieldname_answer]</strong> '.__('(name of field for user\'s answer)',$this->g_info['ShortName']). ', <strong>[fieldname_hash]</strong> '.__('(name of the hidden field)',$this->g_info['ShortName']). ', <strong>[result]</strong> '.__('(the correct answer)',$this->g_info['ShortName'])
			.'<br /><br />
			</p>
			');

		$this->AddContentMain(__('Chinese Inspection Words',$this->g_info['ShortName']), '
			<p>
				'.__('Enter the words to be used. Examples:(Chinese words only)',$this->g_info['ShortName']). ' 
				<ul>
				<li style="padding:0;margin:0 0 0 20px;list-style-type:disc;"><em>成长之路</em></li>
				<li style="padding:0;margin:0 0 0 20px;list-style-type:disc;"><em>雨阳美眉</em></li>
				</ul>
			</p>
			<br />
			<textarea name="regina_opt_words" id="regina_opt_words" cols="100%" rows="2">'
			. $this->COPTHTML('regina_opt_words') . '</textarea>
			');


		$this->AddContentMain(__('Error Messages',$this->g_info['ShortName']), '
			<p>
				'.__('Error message being displayed in case of no answer (empty field):',$this->g_info['ShortName']). '
				<br />
				<textarea style="margin-left: 0px" name="regina_opt_msg_no_answer" id="regina_opt_msg_no_answer" cols="100%" rows="3">'
				. $this->COPTHTML('regina_opt_msg_no_answer') . '</textarea>
			</p>

			<p>
				'.__('Error message being displayed in case of a wrong answer:',$this->g_info['ShortName']). '
				<br />
				<textarea style="margin-left: 0px" name="regina_opt_msg_wrong_answer" id="regina_opt_msg_wrong_answer" cols="100%" rows="3">'
				. $this->COPTHTML('regina_opt_msg_wrong_answer') . '</textarea>
			</p>
			');

		$this->AddContentMain(__('Field Names',$this->g_info['ShortName']), '
			<p>
				'.__('Here you can change the default HTML field names to make it more difficult for spam bots.',$this->g_info['ShortName']). ' <br /><br />
				<label for="regina_opt_fieldname_useranswer">'.__('Name of field for user\'s answer:',$this->g_info['ShortName']). '</label>
				<br /><input name="regina_opt_fieldname_useranswer" type="text" id="regina_opt_fieldname_useranswer" value="'
				. $this->COPTHTML('regina_opt_fieldname_useranswer') . '" size="30" />
				<br /><br />
				<label for="regina_opt_fieldname_result">'.__('Name of hidden field that contains the hash:',$this->g_info['ShortName']). '</label>
				<br /><input name="regina_opt_fieldname_result" type="text" id="regina_opt_fieldname_result" value="'
				. $this->COPTHTML('regina_opt_fieldname_result') . '" size="30" />
			</p>
			');



		// Sidebar, we can also add individual items...
		$this->PrepareStandardSidebar();
		
		$this->GetGeneratedOptionsPage();



	} // function PluginOptionsPage()


	/**
	 * Formats the input of the numbers...
	 */
	function ConvertOptions_Numbers($input) {
		$result = str_replace(' ', '', $input);	// Strip whitespace
		$result = preg_replace('/,/', ', ', $result); // Add whitespace after comma
		$result = preg_replace('/(\r\n|\n|\r)/', '', $result); // Strip line breaks
		return $result;
	}

	/**
	 * Clean the input values for the field names...
	 */
	function ConvertOptions_Fieldname($input) {
		$return = preg_replace('/[^a-zA-Z0-9_\-]/', '', $input);
		return $return;
	}


	/**
	 * Convert option prior to save ("COPTSave"). 
	 * !!!! This function is used by the framework class !!!!
	 */
	function COPTSave($optname) {
		switch ($optname) {
			case 'regina_opt_words':
				return $this->ConvertOptions_Numbers($_POST[$optname]);
			case 'regina_opt_fieldname_useranswer':
				return $this->ConvertOptions_Fieldname($_POST[$optname]);
			case 'regina_opt_fieldname_result':
				return $this->ConvertOptions_Fieldname($_POST[$optname]);
			default:
				if (isset($_POST[$optname])) {			
					return $_POST[$optname];
				} else {
					return;
				}
		} // switch
	}

	/**
	 * Convert option before HTML output ("COPTHTML"). 
	 * *NOT* used by the framework class
	 */
	function COPTHTML($optname) {
		$optval = $this->g_opt[$optname];
		switch ($optname) {
			case 'regina_opt_words':
				return htmlspecialchars(stripslashes($this->ConvertOptions_Numbers($optval)));
			case 'regina_opt_msg_no_answer':
				return htmlspecialchars(stripslashes($optval));
			case 'regina_opt_msg_wrong_answer':
				return htmlspecialchars(stripslashes($optval));
			case 'regina_opt_html':
				return htmlspecialchars(stripslashes($optval));
			default:
				return $optval;
		} // switch
	}

} // Class


if( !isset($myRegina)  ) {
	// Create a new instance of your plugin that utilizes the WordpressPluginFramework and initialize the instance.
	$myRegina = new ChineseCommentSpamProtection();

	$myRegina->Initialize( 
		// 1. We define the plugin information now and do not use get_plugin_data() due to performance.
		array(	 
			# Plugin name
				'Name' => 			'Chinese Comment Spam Protection',
			# Author of the plugin
				'Author' => 		'Joe Jiang',
			# Authot URI
				'AuthorURI' => 		'http://iibetter.com/',
			# Plugin URI
				'PluginURI' => 		'http://iibetter.com/12198.html',
			# Support URI: E.g. WP or plugin forum, wordpress.org tags, etc.
				'SupportURI' => 	'http://iibetter.com/12198.html',
			# Name of the options for the options database table
				'OptionName' => 	'plugin_chinesecommentspamprotection',
			# Old option names to delete from the options table; newest last please
				#'DeleteOldOpt' =>	array('plugin_chinesecommentspamprotection'),
			# Plugin version
				'Version' => 		'1.0',
			# First plugin version of which we do not reset the plugin options to default;
			# Normally we reset the plugin's options after an update; but if we for example
			# update the plugin from version 2.3 to 2.4 und did only do minor changes and
			# not any option modifications, we should enter '2.3' here. In this example
			# options are being reset to default only if the old plugin version was < 2.3.
				'UseOldOpt' => 		'1.0',
			# Copyright year(s)
				'CopyrightYear' => 	'2010',
			# Minimum WordPress version
				'MinWP' => 			'3.0.1',
			# Do not change; full path and filename of the plugin
				'PluginFile' => 	__FILE__,
			# Used for language file, nonce field security, etc.				
				'ShortName' =>		'chinese-comment-spam-protection',
			),

		// 2. We define the plugin option names and the initial options
		array(
			'regina_opt_words'				=> '成长之路',
			'regina_opt_msg_no_answer' 		=> 'Error: Please key in the required chinese word',
			'regina_opt_msg_wrong_answer' 	=> 'Error: Please key in the correct chinese word',
			'regina_opt_fieldname_useranswer' => 'reginavalue',
			'regina_opt_fieldname_result' => 'reginainfo',
			'regina_opt_html'					=> '<p>'."\n".'<input id="[fieldname_answer]" name="[fieldname_answer]" type="text" value="" size="30" aria-required="true" />'."\n".'<label for="[fieldname_answer]">[chinesewords] (请输入[accurateword]字)</label> <span class="required">*</span>'."\n".'<input type="hidden" name="[fieldname_hash]" value="[result]" />'."\n".'</p>',
			'regina_opt_apply'				=> '1',
		));

	$myRegina->ApplyChineseCommentSpamProtection();


	############################################################################
	# Template Tags for using in themes
	############################################################################
	function regina_html() {
		global $myRegina;
		echo $myRegina->GetInputField();
	}

	function chinese_comment_spam_protection() {
		global $myRegina;

		// Include math class, create object and generate numbers
		if ( !class_exists('ChineseCheck') ) include_once ( dirname(__FILE__) . '/chinese-comment-spam-protection.classes.php');
		$ChineseCheckObject = new ChineseCheck;
		$GeneratedNumbersArray = $ChineseCheckObject->ChineseCheck_GenerateValues($myRegina->g_opt['regina_opt_words']);
	
		$mcsp_info['chinesewords'] = $GeneratedNumbersArray['chinesewords'];
		$mcsp_info['accurateword'] = $GeneratedNumbersArray['accurateword'];
		$mcsp_info['result']   = $GeneratedNumbersArray['result'];
		$mcsp_info['fieldname_answer']   = $myRegina->g_opt['regina_opt_fieldname_useranswer'];
		$mcsp_info['fieldname_hash']   = $myRegina->g_opt['regina_opt_fieldname_result'];
		
		return $mcsp_info;

	}

} // if( !$myRegina



?>
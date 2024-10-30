<?php

/*      ____________________________________________________
       |                                                    |
       |             		ChineseCheck                    |
       |                                                    |
       |            - a PHP class for web forms -           |
       |                                                    |
       |                    © Joe Jiang                     |
       |____________________________________________________|

    Author: Joe Jiang <joe031102 [at] gmail dot com>
	Author URI: http://iibetter.com
    Version: 0.1
    Copyright © 2010, all rights reserved

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

*/

class ChineseCheck {

	/**
	 * GenerateValues
	 *
	 */
	function ChineseCheck_GenerateValues($input_cn_words = '成长之路') {

		$cn_array = str_split($input_cn_words, 3);

		$verifyArrayA = array ();
		while (list ($key) = each($cn_array)) {
			for ($k = 0; $k < count($cn_array); $k++) {
				$color = $this->rand_color();
				if ($key == $k) {
					$verifyArray[$key] = $verifyArray[$key] . "<font color=red>？</font>";
				} else {
					$verifyArray[$key] = $verifyArray[$key] . "<font color=$color>" . $cn_array[$k] . "</font>";
				}
			}
			array_push($verifyArrayA, $verifyArray[$key]);
		}
		$result = array_combine($cn_array, $verifyArrayA);
		$randNum = rand(0, count($cn_array) - 1);

		$resultArray = array (
			'chinesewords' => $result[$cn_array[$randNum]],
			'accurateword' => "<font color=red>“" . $cn_array[$randNum] . "”</font>",
			'result' => $cn_array[$randNum]
		);
		
		return $resultArray;
		
	}

	/**
	 * InputValidation
	 *
	 * Input validation. Returns an empty string if validation passed or an
	 * error string if not passed.	 
	 */
	function ChineseCheck_InputValidation($actualResult, $userEntered) {
		
		$error = '';

		if ($error == '' && $userEntered == '') {
			$error = 'No answer';
		}
		if ($error == '' && $actualResult != $userEntered) {
			$error = 'Wrong answer';
		}

		return $error;

	}

	/**
	 * To generate a random color
	 */
	function rand_color() {
		for ($a = 0; $a < 6; $a++) {
			$d .= dechex(rand(0, 15));
		}
		return '#' . $d;
	}


} 

?>
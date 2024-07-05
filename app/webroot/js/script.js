/**
 * Common Javascript
 *
 * @author
 */

//const SYSTEM_URL= "/chemical/";
// let langConfig = "<?php echo $this->Session->read('Config.language'); ?>";
/**
 * Validate HasMixedCase
 *
 * @param passwd
 * @reutrn True (valid) | False (not valid)
 */
function HasMixedCase(passwd) {
	if (
		passwd.match(
			/([a-z].*[A-Z].*[0-9].*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?].*)|([a-z].*[A-Z].*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?].*[0-9].*)|([a-z].*[0-9].*[A-Z].*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?].*)|([a-z].*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?].*[A-Z].*[0-9].*)|([a-z].*[0-9].*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?].*[A-Z].*)|([a-z].*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?].*[0-9].*[A-Z].*)|([A-Z].*[a-z].*[0-9].*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?].*)|([A-Z].*[a-z].*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?].*[0-9].*)|([A-Z].*[0-9].*[a-z].*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?].*)|([A-Z].*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?].*[a-z].*[0-9].*)|([A-Z].*[0-9].*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?].*[a-z].*)|([A-Z].*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?].*[0-9].*[a-z].*)|([0-9].*[a-z].*[A-Z].*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?].*)|([0-9].*[a-z].*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?].*[A-Z].*)|([0-9].*[A-Z].*[a-z].*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?].*)|([0-9].*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?].*[a-z].*[A-Z].*)|([0-9].*[A-Z].*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?].*[a-z].*)|([0-9].*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?].*[A-Z].*[a-z].*)|([!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?].*[a-z].*[A-Z].*[0-9].*)|([!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?].*[a-z].*[0-9].*[A-Z].*)|([!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?].*[A-Z].*[a-z].*[0-9].*)|([!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?].*[0-9].*[a-z].*[A-Z].*)|([!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?].*[A-Z].*[0-9].*[a-z].*)|([!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?].*[0-9].*[A-Z].*[a-z].*)/
		)
	)
		return true;
	else return false;
}

/**
 * Validate Email
 *
 * @param email
 * @reutrn True (valid) | False (not valid)
 */
function validateEmail(email) {
	var re =
		/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	return re.test(email);
}

/**
 * Validate Number Only
 *
 * @param num
 * @reutrn True (valid) | False (not valid)
 */
function validateNumberOnly(num) {
	var re = /^\d+$/;
	return re.test(num);
}

/**
 * Check Number Between Two Value
 *
 * @param number, check value1, check value2
 * @reutrn True (valid) | False (not valid)
 */
function isBetween(n, a, b) {
	return (n - a) * (n - b) <= 0;
}

/**
 * Check null or blank
 *
 * @param num
 * @reutrn True (has value) | False (null or blank)
 */
function checkNullOrBlank(value) {
	if (value == "" || value == null) {
		return false;
	}
	return true;
}

/**
 * Left Padding
 *
 * @param original string
 * @param count of padding
 * @param padding character (default '0')
 * @reutrn padding string
 */
function paddy(n, p, c) {
	var pad_char = typeof c !== "undefined" ? c : "0";
	var pad = new Array(1 + p).join(pad_char);
	return (pad + n).slice(-pad.length);
}

/**
 * Validate Singal Byte Character
 *
 * @param string
 * @reutrn True (valid) | False (not valid)
 */
function isHan(str) {
	for (var i = 0; i < str.length; i++) {
		var len = escape(str.charAt(i)).length;
		if (len < 4) {
		} else {
			return false;
		}
	}
	return true;
}

/**
 * Validate Singal Byte Alpha Character
 *
 * @param Html Object
 * @reutrn True (valid) | False (not valid)
 */
function isHanAlpha(obj) {
	var str = obj.value;
	for (var i = 0; i < str.length; i++) {
		var code = str.charCodeAt(i);
		if (
			(65 <= code && code <= 90) ||
			(97 <= code && code <= 122) ||
			str.substr(i, 1) == " "
		) {
		} else {
			return false;
		}
	}
	return true;
}

/**
 * Validate Formart (15 Digit and 2 Decimal Point)
 *
 * @param value
 * @reutrn True (valid) | False (not valid)
 */
function isDecimal(value) {
	var decimalOnly = /^\s*-?(\d{1,15})(\.\d{0,})?\s*$/;
	if (decimalOnly.test(value)) {
		return true;
	}
	return false;
}

/**
 * Validate Formart (8 Digit and 2 Decimal Point)
 *
 * @param value
 * @reutrn True (valid) | False (not valid)
 */
function is2Decimal(value) {
	var decimalOnly = /^\s*-?(\d{1,9})(\.\d{0,2})?\s*$/;
	if (decimalOnly.test(value)) {
		return true;
	}
	return false;
}

/**
 * Change Number format with commas
 *
 * @param value
 * @reutrn string with commas
 */
function numberWithCommas(x) {
	return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

/**
 * Change Number format without commas
 *
 * @param value
 * @reutrn string without commas
 */
function removeCommas(str) {
	return str.replace(/,/g, "");
}

/**
 * Round Method
 *
 * @param value
 * @param decimal ponit (default '0')
 * @reutrn rounded value
 */
function round(value, decimals) {
	return parseFloat(Math.round(value + "e" + decimals) + "e-" + decimals);
}

/**
 * Prevent callback perivous page using backspace key
 *
 * @param -
 */
$(function () {
	var rx = /INPUT|SELECT|TEXTAREA/i;

	$(document).bind("keydown keypress", function (e) {
		if (e.which == 8) {
			if (
				!rx.test(e.target.tagName) ||
				e.target.disabled ||
				e.target.readOnly
			) {
				e.preventDefault();
			}
		}
	});
});

/**
 * Change Language Setting
 *
 * @param language
 *
 * @return
 */
function changeLanguage(language) {
	var path = location.pathname.split("/");
	var project = "/" + path[1] + "/";
	if (project != "/chemical/") {
		project = "/";
	}
	if (jQuery.inArray("ssoLogin", path) !== -1) {
		location.pathname = project + "Logins/ssoLogin/?lang=" + language;
	} else {
		$.ajax({
			type: "post",
			url: project + "app/changeLanguage",
			data: {
				language: language,
			},
			dataType: "json",
			success: function (response) {
				console.log(response);
				if (response.content) {
					location.reload();
				}
			},
			error: function (e) {},
		});
	}
}
/**
 * ErrorMessage Method
 *
 * @param formartErrMsg
 * @param dynamicValues
 * @param msgShowDivName
 *
 * @reutrn Error Message
 */
function errMsg(formartErrMsg, dynamicValues, showDivName) {
	var msg = "";
	var divName = "#error";

	if (showDivName !== undefined) {
		divName = "#" + showDivName;
	}
	msg += vsprintf(formartErrMsg, dynamicValues);
	return msg;
}

/**
 * Check max length
 *
 * @param num
 * @reutrn True (has value) | False (length exceed)
 */
function checkMaxLength(value, num) {
	if (value.length > num) {
		return false;
	}
	return true;
}
/**
 * Validate Formart (English Character)
 *
 * @param value
 * @reutrn True (valid) | False (not valid)
 */
function englishCharacterOnly(value) {
	var engstr = /^[a-zA-Z-_ ]+$/;
	if (engstr.test(value)) {
		return true;
	}
	return false;
}
/**
 * Validation white space
 *
 * @param value
 * @reutrn
 */
function validationWhiteSpace(value) {
	reWhiteSpace = new RegExp(/^\s+$/);
	// Check for white space
	if (reWhiteSpace.test(value)) {
		return false;
	}
	return true;
}

function checkSpecialChar(value) {
	var regex_symbols = /[-!$%^&*()_+|~=`{}\[\]:\/;<>?,.@#]/;
	if (regex_symbols.test(value)) {
		return true;
	}
	return false;
}

/**
 * DateTimePicker for calendar
 *
 *
 */
$(function () {
	$(function () {
		$(".monthsPicker").datetimepicker({
			ignoreReadonly: true,
			format: "YYYY-MM",
			autoclose: true,
		});
	});
});

function englishCharacterNumberOnly(value) {
	var engstr = /^[\r\na-zA-Z0-9-()=.'"\/\\:;& _]*$/;
	if (engstr.test(value)) {
		return true;
	}
	return false;
}

/**
 * Validate Formart(old)
 * allow 8 digit and 2 decimal point for positive number
 * allow 7 digit and 2 decimal point for negative number
 * Validate Formart(new-13.12.1993/Fri) by khin hnin myo
 * allow 8 digit and 2 decimal point for positive number
 * allow 7 digit and 2 decimal point for negative number
 * @param value
 * @reutrn True (valid) | False (not valid)
 */
function isDecimalPosNeg(value) {
	var decimalOnly = /^\s*(-\d{1,9}|\d{1,10})(\.\d{0,2})?\s*$/;
	if (decimalOnly.test(value)) {
		return true;
	}
	return false;
}

/**
 * Language button active style
 * @author Zeyar Min
 * @param value
 * @reutrn
 */
document.addEventListener("DOMContentLoaded", function () {
	if (langConfig == "jpn") {
		$(".lang-jpn").addClass("active");
	} else {
		$(".lang-eng").addClass("active");
	}
});

function isNumberDots(value) {
	var pattern = /^[0-9\.]+$/;

	if (pattern.test(value)) {
		return true;
	}
	return false;
}
function Clear(){
	$('.amsify-list li').removeClass('active');
	localStorage.setItem("SELECTED_LAYER", "");
	return false;
}


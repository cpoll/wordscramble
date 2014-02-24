<?php

class InputSanitizer {
	
	# Regular Expressions
	public $email = '/^[A-Z0-9._-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i';
	public $password = '/^[a-zA-Z0-9]{5,20}$/';
	
	public $displayname = '/^[a-z0-9]{4,20}$/';
	public $firstname = '/^[A-Za-z]{1,20}$/';
	public $lastname = '/^[A-Za-z]{1,20}$/';
	public $birthday = '/^[0-9]{4}(-[0-9]{2}){2}$/';
	public $avatar = '/^[0-3]$/';

	# Allowed chars
	public $whitelist = '/[^a-zA-Z0-9@\.\/_-\s]/';

	# error
	public $last_error_msg = '';

	# error messages
	public $error_msg = array(
		'email' => 'Invalid email address.',
		'password' => '5-20 alphanumeric characters',
		'displayname' => '4-20 numbers or lower case letters',
		'firstname' => 'Letters only(max. 20)',
		'lastname' => 'Letters only(max. 20)',
		'birthday' => 'Format: ####-##-##',
		'avatar' => 'Invalid avatar selection...hacker',
		'badchars' => 'We\'ve removed invalid characters.',
		'emptyinput' => 'This field is required.',
	);

	function sanitize($regex, &$input, $error_msg){
		if ($input == ""){
			$this->last_error_msg = $this->error_msg['emptyinput'];
			return false;
		}

		if(preg_match($this->whitelist, $input)){
			# strip bad chars
			$input = preg_replace($this->whitelist, "", $input);
	
			$this->last_error_msg = $this->error_msg['badchars'];
			return false;
		}	

		if(preg_match($regex, $input)){
			$this->last_error_msg = '';
			return true;
		}
		$this->last_error_msg = $error_msg;
		return false;
	}
}

?>

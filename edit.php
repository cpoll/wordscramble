<?php

include 'lib/controller.php';


# form content
echo '<form method="post" action="edit.php">';

$ERRORS = array(
	'displayname' => '',
	'firstname' => '',
	'lastname' => '',
	'birthday' => '',
	'avatar' => '',
	'password' => '',
);

$db = pg_connect($APP_CONFIG['db_conn_string']) or die('Couldn\'t connect to database');

$result = pg_prepare($db, 'db_user_info', 'SELECT displayname, fname, lname, birthday, avatar FROM profile WHERE email = $1');
$result = pg_execute($db, 'db_user_info', array( $_SESSION['email'] ));

$user_info = pg_fetch_assoc($result);

if (isset($_POST['displayname'])){
	$user_info = $_POST;

	# error
	$error = false;

	# display name
	if (!$input_sanitizer->sanitize($input_sanitizer->displayname, $user_info['displayname'], $input_sanitizer->error_msg['displayname'])){
		$error = true;
		$ERRORS['displayname'] = $input_sanitizer->last_error_msg;
	}

	# first name
	if (!$input_sanitizer->sanitize($input_sanitizer->firstname, $user_info['fname'], $input_sanitizer->error_msg['firstname'])){
		$error = true;
		$ERRORS['firstname'] = $input_sanitizer->last_error_msg;
	}

	# last name
	if (!$input_sanitizer->sanitize($input_sanitizer->lastname, $user_info['lname'], $input_sanitizer->error_msg['lastname'])){
		$error = true;
		$ERRORS['lastname'] = $input_sanitizer->last_error_msg;
	}

	# birthday
	if (!$input_sanitizer->sanitize($input_sanitizer->birthday, $user_info['birthday'], $input_sanitizer->error_msg['birthday'])){
		$error = true;
		$ERRORS['birthday'] = $input_sanitizer->last_error_msg;
	}
	
	# avatar
	if (!$input_sanitizer->sanitize($input_sanitizer->avatar, $user_info['avatar'], $input_sanitizer->error_msg['avatar'])){
		$error = true;
		$ERRORS['avatar'] = $input_sanitizer->last_error_msg;
	}

	if (!$error){
		# check if display name already exists
		$result = pg_prepare($db, 'displayname_check', 'SELECT email FROM profile WHERE displayname = $1');
		$result = pg_execute($db, 'displayname_check', array( $user_info['displayname'] ));	

		if (!$result){
			die('Something went wrong...' . pg_last_error($db));
		}
		
		$error = false;

		# check that we're the one with this username, else it's someone else's
		if (pg_num_rows($result) == 1){
			$row = pg_fetch_row($result);
			if ($row[0] != $_SESSION['email']){
				$error = true;
				$ERRORS['displayname'] = 'Another user has this displayname!';
			}	
		}

		if (!$error){
			# Now check password
			if ($_POST['password'] != ""){
				# check that newpassword matched the confirmed one
				if ($_POST['password'] != $_POST['confirmnewpw']){
					$error = true;
					$ERRORS['password'] = 'New passwords don\'t match';
				}

				if (!$error){
					# sanitize passwords
					if (!$input_sanitizer->sanitize($input_sanitizer->password, $_POST['password'], '') ||
						!$input_sanitizer->sanitize($input_sanitizer->password, $_POST['confirmnewpw'], '') ) {
						$error = true;
						$ERRORS['password'] = 'Passwords contain invalid characters.';
					}
				}

				if (!$error){
		            # generate salt
    		        $salt = hash('sha256', md5(uniqid(rand(), TRUE)));

		            # hash password
		            $hash = $salt . $_POST['password'];

		            # bunch of times
		            for( $i = 0; $i < 100000; $i++){
		                    $hash = hash('sha256', $hash);
		            }

		            $user_info['password'] = $salt . $hash;

					$result = pg_prepare($db, 'update_pw', 'UPDATE profile SET password = $1 WHERE email = $2');
					$result = pg_execute($db, 'update_pw', array( $user_info['password'], $_SESSION['email']));

					if (!$result){
						$error = true;
						$ERRORS['password'] = 'Couldn\'t update password! ' . pg_last_error($db);
					}	
				}
			}
		}

		if (!$error){
			# all is ok

			$result = pg_prepare($db, 'update_profile', 'UPDATE profile SET displayname = $1, fname = $2, lname = $3, birthday = $4, avatar = $5 WHERE email = $6');
			$result = pg_execute($db, 'update_profile', array(
							$user_info['displayname'],
							$user_info['fname'],
							$user_info['lname'],
							$user_info['birthday'],
							$user_info['avatar'],
							$_SESSION['email'],
						)
					);
				
			if (!$result){
				die('Couldn\'t update your profile!' . pg_last_error($db));
			}
			
			header('Location: edit.php?success=true');
		}
	}
}

# Submit
echo '<h2>Profile</h2>';
echo '<label>Display Name:</label><input type="text" name="displayname" value="' . $user_info['displayname'] . '"><label class="error">' . $ERRORS['displayname'] . '</label><hr>';
echo '<label>First Name:</label><input type="text" name="fname" value="' . $user_info['fname'] . '"><label class="error">' . $ERRORS['firstname'] . '</label><hr>';
echo '<label>Last Name:</label><input type="text" name="lname" value="' . $user_info['lname'] . '"><label class="error">' . $ERRORS['lastname'] . '</label><hr>';
echo '<label>Birthday:</label><input type="date" name="birthday" value="' . $user_info['birthday'] . '"><label class="error">' . $ERRORS['birthday'] . '</label><hr>';
echo '<label>Avatar:</label><br>';
foreach( $APP_CONTENT['avatar'] as $id => $href){
    $selected = '';
    if ($user_info['avatar'] == $id){
        $selected = 'checked';
    }
    echo '<input type="radio" class="avatar_radio"  name="avatar" value="' . $id . '" ' . $selected . '><img class="avatar" src="' . $href . '">';
}

echo '<h2>Password (leave blank to leave unchanged)</h2>';
echo '<label class="pwerror">' . $ERRORS['password'] . '</label><br>';
echo '<label>New Password:</label><input type="password" name="password">';
echo '<label>Confirm:</label><input type="password" name="confirmnewpw"><hr>';

echo '<input type="submit" class="submit" value="UPDATE" >';
echo '<label class="success">';
# cheap success msg
if (isset($_GET['success']) && $_GET['success'] == 'true'){
	echo 'Profile successfully updated!';
}
echo '</label>';
echo '</form>';

?>

</body></html>

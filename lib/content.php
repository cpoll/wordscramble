<?php

# Store everything in here
$APP_CONTENT = array();

# Global Content for dynamic fetching

#Footer
$APP_CONTENT['footer'] = '<footer>Word Scramble &copy; 2013 Cristian Poll & Lance Blais</footer>';

# Titles
$APP_CONTENT['title'] = array(
	'register' 	=> 'Registration',
	'profile' 	=> 'Profile Information',
	'index'		=> 'News',
	'login' 	=> 'Login Page',
	'edit'		=> 'Edit Your Profile',
	'about'		=> 'About Us',
	'leaderboard' => 'Leaderboard',
	'game'		=> 'Word Scramble',
);

# CSS -- css/ prepended and .css appended by controller
$APP_CONTENT['css'] = array(
	'register' 	=> 'register',
	'profile' 	=> 'profile',
	'index' 	=> 'index',
	'login' 	=> 'login',
	'leaderboard' => 'leaderboard',
	'game' => 'game',
	'edit' => 'edit',
);


# Nav HREFs
$APP_CONTENT['nav'] = array(
	'index.php' => 'NEWS',
	'leaderboard.php' => 'LEADERBOARD',
	'game.php' => 'PLAY',
	'about.php' => 'ABOUT',
);

# Avatars
$APP_CONTENT['avatar'] = array(
	'0' => 'img/avatar0.jpg',
	'1' => 'img/avatar1.jpg',
	'2' => 'img/avatar2.jpg',
	'3' => 'img/avatar3.jpg',
);

# News page, blog style
$APP_CONTENT['news'] = array(
	array(
		'date' => 'Feb 10th, 2013',
		'title' => 'We\'re on Twitter!',
		'post' =>'<p><a href="https://twitter.com/wordscramble" class="twitter-follow-button" data-show-count="false" data-size="large" data-dnt="true">Follow @wordscramble</a></p>
			<script>
				!function(d,s,id){
					var js,fjs=d.getElementsByTagName(s)[0];
					if(!d.getElementById(id)){
							js=d.createElement(s);
							js.id=id;
							js.src="//platform.twitter.com/widgets.js";
							fjs.parentNode.insertBefore(js,fjs);
					}
				}
			(	document,"script","twitter-wjs");
			</script>',
	),
	array(
		'date' => 'Feb 9th, 2013',
		'title' => 'Follow Us on Facebook!',
		'post' => '
		<div id="fb-root"></div>
		<script>(function(d, s, id) {
		  var js, fjs = d.getElementsByTagName(s)[0];
		  if (d.getElementById(id)) return;
		  js = d.createElement(s); js.id = id;
		  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
		  fjs.parentNode.insertBefore(js, fjs);
		}(document, "script", "facebook-jssdk"));</script>

		<p class="fb-follow" data-href="https://www.facebook.com/pages/Wordscramble/334173233367684" data-show-faces="false"></p>'
	),

	array(
		'date' => 'Feb 6th, 2013',
		'title' => 'The Game is Live!',
		'post' => '<p>Word Scramble is now playable.</p>' .
				'<p>Check it out by clicking <b>Play</b> in the top bar!</p>' .
				'<p>Post your high score, check your rank on the Leaderboard, and challenge your friends!</p>',
	),
	array(
		'date' => 'Feb 4th, 2013',
		'title' => 'Check out the Leaderboard!',
		'post' => '<p>Hey all,</p>' . 
				'<p>We\'ve added a new feature to the site: Leaderboard!</p>' .
				'<p>Check it out by clicking <b>Leaderboard</b> in the nav!</p>',
	),
	array(
		'date' => 'Jan 20th, 2013',
		'title' => 'Accepting new users!',
		'post' => '<p>We\'re no longer an invite-only platform!</p>' . 
				'<p>Come one, come all! Register your profile today!</p>',
	),
);

$APP_CONTENT['about'] = array(
	array(
		'title' => 'About the Game',
		'post'	=> '<p>Word Scramble was made for CSC309 at UTM.</p>' . 
					'<p>Features: A fully working game, accounts, and score tracking</p>' .
					'<p> </p>' .
					'<p>Created from scratch in php, with a backend psql database.</p>' .
					'<p></p>',
	),
	array(
		'title' => 'About Us',
		'post' => '<p>Cristian Poll and Lance Blais are two students at University of Toronto at Mississauga.</p>' .
	'<p>&nbsp;</p>' . 
	'<p>Contact us at fake-email@wordscramble.com</p>',
	),
);


?>

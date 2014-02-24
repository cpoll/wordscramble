<?php

include 'lib/controller.php';

echo '<header><h1>About</h1></header>';

# print out the about content
foreach ( $APP_CONTENT['about'] as $about ){
	echo '<section>';
	echo '<h1>' . $about['title'] . '</h1>';
	echo $about['post'];
	echo '</section>';	
}

?>

</body></html>

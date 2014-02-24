<?php

include 'lib/controller.php';

echo '<header><h1>News</h1></header>';
# print out the news
foreach ( $APP_CONTENT['news'] as $news ){
	echo '<section>';
	echo '<h1>' . $news['title'] . '</h1>';
	echo $news['post'];
	echo '<div class="postdate">Posted on ' . $news['date'] . '</div>';
	echo '</section>';	
}

?>

</body></html>

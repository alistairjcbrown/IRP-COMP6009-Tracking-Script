<?php
require_once 'tracker.class.php';
$tracker = new Tracker(FALSE);

if (isset($_GET['remove']))
   $tracker->remove_category($_GET['remove']);

if (isset($_GET['delete_cookie'])) {
   $tracker->remove_cookie();
   header('Location: '.basename($_SERVER['SCRIPT_FILENAME']));
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
   <head>
      <title>
         Your Preferences
      </title>

      <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
      <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
      <script type="text/javascript">
         $(document).ready(function() {
            $('.remove').click(function() {
               $.ajax({
                  url: $(this).attr('href'),
                  context: $(this),
                  success: function(){
                     $(this).parent().slideUp(400, function() {
                        if ($('.remove:visible').size() == 0)
                           $(this).after("<div id=\"empty_cat\" style=\"display: none;\"><i>You have no associated categories</i></div>");
                           $('#empty_cat').fadeIn();
                     });
                  }
               });
               return false;
            })
            
            $('span.show_urls a').click(function() {
               $(this).parent().parent().parent().next().slideToggle();
               return false;
            });
         });
      </script>
      <link rel="stylesheet" type="text/css" href="style.css" />
   </head>
   <body>
      <h1>Your Preferences</h1>
      <div>
         This prototype script replicates similar functionality as that of Google's "Ads Preferences" which generate a profile of interests based on your browsing history.
      </div>

      <table id="data">
         <tr>
            <td><h4>Your Categories</h4></td>
            <td colspan="2">Below you can edit the interests that have been associated with your cookie:</td>
         </tr>
         <tr>
            <td></td>
            <th colspan="2">Category</th>
         </tr>
<?php
	$categories = $tracker->get_categories();
	$i = 0;
	foreach($categories as $category => $urls) {
		if (empty($category))
			continue;
		echo '         <tr>'."\n"
		   . '            <td></td>'."\n"
		   . '            <td class="'.($i % 2 == 0 ? 'even' : 'odd').'">'.$category.'<span class="cat_weight">('.count($urls).')</span></td>'."\n"
		   . '            <td class="'.($i % 2 == 0 ? 'even' : 'odd').'">'."\n"
		   . '               <a href="?remove='.$category.'" class="remove">Remove</a>'."\n"
		   . '               <span class="show_urls"><a href="">[+]</a></span>'."\n"
		   .'             </td>'."\n"
		   . '         </tr>'."\n"
		   . '         <tr class="url_list">'."\n"
		   . '            <td></td>'."\n"
		   . '            <td colspan="2">'."\n"
		   . '               <ul>'."\n";
		foreach ($urls as $url)
			echo '                  <li>'.$url.'</li>'."\n";
		echo '               </ul>'."\n";
		echo '            </td>'."\n"
		   . '         </tr>'."\n";
		$i++;
	}

	if ($i == 0) {
		echo '         <tr class="'.($i % 2 == 0 ? 'even' : 'odd').'">'."\n"
		   . '            <td></td>'."\n"
		   . '            <td colspan="2">You have no associated categories</td>'."\n"
		   . '          </tr>'."\n";
	}
?>
      </table>

      <h3>Your cookie</h3>
      <div>
               <?php
				if (isset($_COOKIE[$tracker->get_cookie_name()]))
					echo '         <div class="cookie_id">'."\n".'         '.$_COOKIE[$tracker->get_cookie_name()]."\n".'         </div>'."\n".' - <a href="?delete_cookie">Delete Cookie</a>'."\n";
				else
					echo '      <div id="empty_cookie"><i>You have no associated cookie</i></div>';
               ?>
      </div>
   </body>
</html>
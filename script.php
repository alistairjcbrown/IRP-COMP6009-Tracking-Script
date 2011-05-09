<?php require_once 'tracker.class.php'; ?>

$(document).ready(function() {
   var returned_data = <?php
$tracker = new Tracker();
// Validate the input
if (!$tracker->validate())
	echo $tracker->error('retrieving either your current URL or tracking id');
// Retrieve categories from open directory
if (!$tracker->retrieve_categories())
	echo $tracker->error('searching for the associated categories');
// Log categories in database
if (!$tracker->log_categories())
	echo $tracker->error('logging the associated categories');
// Output success JSON
echo $tracker->output();
?>;

   var categories_notice = "";

   if (returned_data["success"]) {
      if (returned_data["categories"].length == 0) {
         categories_notice = "There were no associated categories found.";
      } else {
         categories_notice = "The following categories have been logged: ";
         $.each(returned_data["categories"], function(index, value) {
            if (index != 0)
               categories_notice += ", "
            categories_notice += value;
         });
      }
   } else {
      categories_notice = "However, it was not possible to track this page due to an error when "+returned_data["message"]+".";
   }
   $('body').prepend('<div id="tracker" style="width: 99%; border: 1px solid #FFD324; font-family: arial, sans-serif; size; 1em; background-color: #FFF6BF; text-align: center; font-family: Helvetica, Arial, sans-serif; font-size: 1.1em; padding: 0.3em; color: #514721;"><b>This page is currently being tracked.</b><br />'+categories_notice+'<br /></div>');
   $("#tracker").click(function() { $(this).slideUp(); });
});

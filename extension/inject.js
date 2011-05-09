// Script fragments from:
// Erik Vold, http://r.evold.ca/jquery4us [https://gist.github.com/437513]
// Rey Bango, http://blog.reybango.com/2010/09/02/how-to-easily-inject-jquery-into-any-web-page/

function addJQuery(callback) {
   var otherlib = false;

   // another javascript library loaded
   if (typeof $=='function') {
      otherlib=true;
   }

   var script = document.createElement("script");
   script.setAttribute("src", "http://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js");
   script.addEventListener('load', function() {
      var script = document.createElement("script");
      script.textContent = "(" + callback.toString() + ")();";
      document.body.appendChild(script);
   }, false);
   document.body.appendChild(script);

   if (otherlib) {
      $jq=jQuery.noConflict();
   }
}

// Main function executed on callback
// Functionalty changed for use in this extension
function main() {
   $('head').prepend('<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script><script type="text/javascript" src="http://localhost/irp_tracker/script.php?url='+window.location+'"></script>');
}

// load jQuery and execute the main function
addJQuery(main);
# Research Project Tracking Script #
A combination browser extension and Javascript/PHP script which emulates similar behavior to Google's online advertising profiles.

![Overview diagram](http://diggering.com/holding/github/tracker_diagram.png)

### Details ###
This prototype script replicates similar functionality to that of Google's "_Ads Preferences_", which generate a profile of interests based on your browsing history as tracked by the Google advertising. 
You can see your Google generated profile on their [Ads Preferences page](http://www.google.com/ads/preferences/).

Unlike Google, this script does not have a vast advertising network with which to track users. Therefore the use of a [Google Chome](http://chrome.google.com) browser plugin simulates this by embedding the tracking script in to every page you view. Don't worry, it also adds a large yellow banner to the top of every page to remind you you're being tracked and to let you know what categories it has logged for that page!

Additionally, the script does not have Google's vast category database for each web page. Therefore, the superb open directory source at [dmoz.org](http://www.dmoz.org/) is used to attempt to retrieve a general category associated with the current _domain_ you are on.

And of course, where there's Javascript, there's jQuery. This script relies on [jQuery](http://www.jquery.com), as hosted by [Google Libraries APIs](http://code.google.com/apis/libraries/devguide.html#jquery).

### To Do ###
 * Chrome browser extension background pages cannot access the extension settings. To do is look for a way around this so settings can be set easily.
 * Find an API for website categories rather than scraping dmoz.org

### Support ###

For more information, please feel free to [get in contact](http://www.diggering.co.uk/?page=contact).

### License and Attribution ###

This work is licensed under the Creative Commons Attribution-Share Alike 3.0 Unported.  
To view a copy of this license, visit [http://creativecommons.org/licenses/by-sa/3.0/](http://creativecommons.org/licenses/by-sa/3.0/)
or send a letter to `Creative Commons, 171 Second Street, Suite 300, San Francisco, California, 94105, USA`

__No Warranty__  
As this program is licensed free of charge, there is no warranty for the program, to the extent permitted by applicable law. Except when otherwise stated in writing the copyright holders and/or other parties provide the program "as is" without warranty of any kind, either expressed or implied, including, but not limited to, the implied warranties of merchantability and fitness for a particular purpose. The entire risk as to the quality and performance of the program is with you. should the program prove defective, you assume the cost of all necessary servicing, repair or correction.
In no event unless required by applicable law or agreed to in writing will any copyright holder, or any other party who may modify and/or redistribute the program as permitted above, be liable to you for damages, including any general, special, incidental or consequential damages arising out of the use or inability to use the program (including but not limited to loss of data or data being rendered inaccurate or losses sustained by you or third parties or a failure of the program to operate with any other programs), even if such holder or other party has been advised of the possibility of such damages.
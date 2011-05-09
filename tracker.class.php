<?php 
error_reporting(0);

class Tracker {

	// Class Variables
	private $url = FALSE;
	private $id = 0;
	private $categories = array();
	private $connection = FALSE;

	// General Settings
	private $cookie_name = 'tracking_test';
	private $cookie_expire = 2592000; // one month
	private $open_directory_source = 'http://www.dmoz.org/search?q=';
	private $delimiter = '|';
	private $cache_expires = 86400; // 24 hours
	private $log_granularity = 600; // 10 minutes

	// Database Settings
	private $database_server = 'localhost';
	private $database_user = 'root';
	private $database_password = '';
	private $database_name = 'test';
	private $database_log_table = 'tracker_log';
	private $database_cache_table = 'tracker_cache';

	// Constructor
	function Tracker($set_cookie = TRUE) {
		// Set current URL (domain only)
		if (isset($_GET['url'])) {
			$url_pieces = parse_url($_GET['url']);
			$this->url =  $url_pieces['scheme'].'://'.$url_pieces['host'];
		}

		// Set current cookie id
		if (!isset($_COOKIE[$this->cookie_name]) && $set_cookie) {
			$this->id = sha1($_SERVER['REMOTE_ADDR'].time());
			setcookie($this->cookie_name,
			          $this->id,
			          time()+$this->cookie_expire);
		} else if (isset($_COOKIE[$this->cookie_name])) {
			$this->id = $_COOKIE[$this->cookie_name];
		}

		// Set cache limit (if set)
		if (isset($_GET['cac_e']) && is_numeric($_GET['cac_e']))
			$ths->cache_expires = (int) $_GET['cac_e'];

		// Set logging throttle (if set)
		if (isset($_GET['log_g']) && is_numeric($_GET['log_g']))
			$ths->log_granularity = (int) $_GET['log_g'];

		$this->open_db() or die ($tracker->error('connecting to the database'));
	}

	// Validate input
	public function validate() {
		return $this->url !== FALSE && $this->id !== 0;
	}

	// Getter - cookie id
	public function get_id() {
		return $this->id;
	}

	// Getter - current URL
	public function get_url() {
		return $this->url;
	}

	// Getter - cookie name
	public function get_cookie_name() {
		return $this->cookie_name;
	}

	// Return array of category indexes mapped to array of associated urls
	public function get_categories() {
		$current_categories = array();
		$result = mysql_query('SELECT url, category, count(time) as weight
		                       FROM '.$this->database_log_table.'
		                       WHERE id = \''.mysql_real_escape_string($this->id).'\'
		                       GROUP BY url, category
		                       ORDER BY category ASC');
		while($row = mysql_fetch_array($result)) {
			if (!isset($current_categories[$row['category']]))
				$current_categories[$row['category']] = array();
			$current_categories[$row['category']][] = $row['url'];
		}
		return $current_categories;
	}

	// Remove user associated categoriy entries
	public function remove_category($category) {
		return mysql_query('DELETE FROM '.$this->database_log_table.'
		                     WHERE category = \''.mysql_real_escape_string($category).'\'
		                       AND id       = \''.mysql_real_escape_string($this->id).'\'');
	}

	// Remove cookie associated with calling computer
	public function remove_cookie() {
		mysql_query('DELETE FROM '.$this->database_log_table.'
		             WHERE id = \''.mysql_real_escape_string($this->id).'\'');
		$this->id = 0;
		return setcookie($this->cookie_name,
		        '',
		        1);
	}

	// Output error message
	public function error($message) {
		$this->close_db();
		return json_encode(array('success' => FALSE, 'message' => $message));
	}

	// output success message
	public function output() {
		if ($this->connection === FALSE)
			return '';
		$this->close_db();
		return json_encode(array('success' => TRUE, 'categories' => $this->categories));
	}

	// Retrieve category/ies associated with URL from open directory 
	public function retrieve_categories() {
		if ($this->connection === FALSE)
			return TRUE;

		// Check that this should be retrieved - look up db and see when this page was last retrieved
		$result = mysql_query('SELECT time, categories 
		                       FROM '.$this->database_cache_table.'
		                       WHERE url = \''.mysql_real_escape_string($this->url).'\'');
		$row = mysql_fetch_array($result);
		if (isset($row['time']) && strtotime($row['time']) > time()-$this->cache_expires) {
			if (!empty($row['categories']))
				$this->categories = explode($this->delimiter, $row['categories']);
			else
				$this->categories = array();
			return TRUE;
		}

		$ping = $this->get_data($this->open_directory_source.urlencode($this->url));
		if ($ping['status'] !== 200)
			return FALSE;
		$this->categories = $this->extract_categories($ping['data']);
		mysql_query('DELETE FROM '.$this->database_cache_table.' WHERE url=\''.mysql_real_escape_string($this->url).'\'');
		mysql_query('INSERT INTO '.$this->database_cache_table.' (url, categories)
		             VALUES (\''.mysql_real_escape_string($this->url).'\',
		                     \''.implode($this->delimiter, $this->categories).'\')');
		return TRUE;
	}

	// Return webpage associated with URL
	private function get_data($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec($ch);
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		return array('status' => $status, 'data' => $data);
	}

	// Extract categories from "http://www.dmoz.org/search?q=" page response
	// If variable $open_directory_source changed, this function may need to be rewritten
	private function extract_categories($data) {
		preg_match_all('/<div class="ref">-- ([^&]*)&nbsp;[^<]*<[^>]*>(.*?)<[^>]*>/', $data, $categories_string, PREG_SET_ORDER);
		$categories = array();
		foreach($categories_string as $category) {
			if (stripos($category[1], $this->url) !== FALSE) {
				$category_pieces = explode(':', $category[2]);
				if (!empty($category_pieces))
					$categories[$category_pieces[0]] = TRUE;
			}
		}
		return array_keys($categories);
	}

	// Log user categories to database
	// For table structure, please see end of file
	public function log_categories() {
		if ($this->connection === FALSE)
			return TRUE;

		// Check that this should be logged - look up db and see when this page was last logged for this user
		$result = mysql_query('SELECT DISTINCT time
		                       FROM '.$this->database_log_table.'
		                       WHERE id  = \''.mysql_real_escape_string($this->id).'\'
		                         AND url = \''.mysql_real_escape_string($this->url).'\'
		                       ORDER BY time DESC');
		$row = mysql_fetch_array($result);
		if (strtotime($row['time']) > time()-$this->log_granularity)
			return TRUE;

		foreach ($this->categories as $category) {
			mysql_query('INSERT INTO '.$this->database_log_table.' (id, url, category)
			             VALUES (\''.mysql_real_escape_string($this->id).'\',
			                     \''.mysql_real_escape_string($this->url).'\',
			                     \''.mysql_real_escape_string($category).'\')');
		}
		return TRUE;
	}

	private function open_db() {
		$this->connection = mysql_connect($this->database_server, $this->database_user, $this->database_password);
		if ($this->connection === FALSE)
			return FALSE;

		$select_db = mysql_select_db($this->database_name);
		if ($select_db === FALSE)
			return FALSE;

		return TRUE;
	}

	private function close_db() {
		mysql_close($this->connection);
	}

}

// End tracker class


// ----------------------------------------------


// LOG TABLE STRUCTURE MySQL
/*
CREATE TABLE IF NOT EXISTS `tracker_log` (
  `id` varchar(40) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `url` varchar(256) NOT NULL,
  `category` varchar(256) NOT NULL,
  PRIMARY KEY (`id`,`time`,`url`,`category`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
*/

// CACHE TABLE STRUCTURE MySQL
/*
CREATE TABLE IF NOT EXISTS `tracker_cache` (
  `url` varchar(256) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `categories` varchar(256) NOT NULL,
  PRIMARY KEY (`url`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
*/


/* End tracker.class.php */

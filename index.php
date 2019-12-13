<?PHP
	define('DOC_ROOT', realpath(dirname(__FILE__)) . '/');

	$url = $_SERVER['REDIRECT_URL'];

	$start_date = determine_start_date($url);
	if($start_date === false) {
		$start_date = determine_start_date($_SERVER['HTTP_REFERER']);
		if($start_date === false) {
			do404();
		}
		$url = $_SERVER['HTTP_REFERER'];

		$path = match("/\\/(current|[0-9]{8,14})\\/(.*)/", $url, 2);
		$domain = explode('/', $path)[0];
		$url_parts = parse_url('http://' . $domain . '/' . $_SERVER['REDIRECT_URL']);
	} else {
		$path = match("/\\/(current|[0-9]{8,14})\\/(.*)/", $url, 2);
		$url_parts = parse_url('http://' . $path);
	}

	if(!isset($url_parts['host'])) {
		do404();
	}

	define('WEB_ROOT', DOC_ROOT . $url_parts['host'] . '/');
	if(!is_dir(WEB_ROOT)) {
		do404();
	}

	list($smaller, $bigger) = generate_smaller_bigger($start_date);

	attempt_read_file($start_date, $url_parts);
	
	for($i = count($smaller) - 1; $i >= 0; $i--) {
		attempt_read_file($smaller[$i], $url_parts);
	}

	for($i = 0; $i < count($bigger); $i++) {
		attempt_read_file($bigger[$i], $url_parts);
	}
	
	do404();

	// #############################################
	
	function determine_start_date($url) {
		$match = match('/\/(current)\//', $url, 1);
		if($match !== false) {
			return date('YmdHis');
		} else {
			$match = match('/\/([0-9]{8})\//', $url, 1);
			if($match !== false) {
				return $match . '000000';
			} else {
				$match = match('/\/([0-9]{14})\//', $url, 1);
				if($match !== false) {
					return $match;
				} else {
					return false;
				}
			}
		}
	}
	
	function generate_smaller_bigger($start_date) {
		$ls = scandir(WEB_ROOT);	
		$smaller = array();
		$bigger = array();
		foreach($ls as $dir) {
			if(substr($dir, 0, 1) === '.') {
				continue;
			}
			if(intval($dir) <= intval($start_date)) {
				$smaller[] = $dir;
			} else {
				$bigger[] = $dir;
			}
		}
		return array($smaller, $bigger);
	}
	
	function attempt_read_file($start_date, $url_parts) {
		$target = WEB_ROOT . $start_date . @$url_parts['path'];

		if(is_dir($target)) {
			if(file_exists($target . '/index.php')) {
				error_log($target . '/index.php');
				readfile($target . '/index.php');
				exit;
			}
			if(file_exists($target . '/index.html')) {
				error_log($target . '/index.html');
				readfile($target . '/index.html');
				exit;
			}
			if(file_exists($target . '/index.htm')) {
				error_log($target . '/index.htm');
				readfile($target . '/index.htm');
				exit;
			}

			return false;
		}
		
		if(is_readable($target)) {
			header('Content-Length: ' . filesize($target));
		    readfile($target);
		    exit;
		}

		return false;
	}
	
	function do404() {
		http_response_code(404);
		exit;
	}

	function match($regex, $str, $i = 0)
	{
	    if(preg_match($regex, $str, $match) == 1)
	        return $match[$i];
	    else
	        return false;
	}

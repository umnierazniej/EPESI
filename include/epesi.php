<?php
/**
 * @author Paul Bukowski <pbukowski@telaxus.com>
 * @version 1.0
 * @copyright Copyright &copy; 2007, Telaxus LLC
 * @license SPL
 * @package epesi-base
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Epesi {
	private static $jses = array();
	private static $load_jses = array();
	private static $load_csses = array();
	private static $txts = '';

	/**
	 * @deprecated
	 * @throws Exception
     */
	public final static function send_output() {
		throw new Exception('This method is deprecated.');
	}
	
	public final static function prepare_minified_files($arr) {
		$out = array();
		require_once('libs/minify/Minify/Build.php');
		foreach($arr as $loader=>$css) {
			if($loader=='') {
			    foreach($css as $c2)
    			    $out[] = $c2;
			} else  {
			    if(DEBUG_CSS) {
			        foreach($css as $c2) {
		            	$out[] = $c2;
			        }
			    } else {
        			$csses_build = new Minify_Build($css);
	        		$f = $csses_build->uri($loader.'?'.http_build_query(array('f'=>array_values($css))));
		        	$out[] = $f;
		        }
		    }
		}
		return $out;
	}
	
	public final static function get_csses() {
		return self::prepare_minified_files(self::$load_csses);
	}

	public final static function get_jses() {
		return self::prepare_minified_files(self::$load_jses);
	}
	
	public final static function get_eval_jses() {
		$jjj = '';
		foreach(self::$jses as $cc) {
			$x = rtrim($cc[0],';');
			if ($x) {
                if (DEBUG_JS) {
                    $debug_info = isset($cc[2]) ? "/* {$cc[2]} */\n" : '';
                    $jjj .= $debug_info . $x . ";\n";
                } else {
                    $jjj .= $x . ';';
                }
            }
		}
		return $jjj;
	}

	public final static function get_content() {
		return self::$txts;
	}

	/**
	 * @deprecated
	 * @throws Exception
     */
	public final static function get_output() {
		throw new Exception('This method is deprecated');
	}

	public final static function clean() {
		self::$txts = '';
		self::$jses = array();
		self::$load_jses = array();
	}
	
	public final static function load_js($u,$loader=null) {
		if(!is_string($u) || strlen($u)==0) return false;
		if (!isset($_SESSION['client']['__loaded_jses__'][$u])) {
    		if (!isset($loader)) $loader = 'serve.php';
			if(!isset(self::$load_jses[$loader])) self::$load_jses[$loader] = array();
			self::$load_jses[$loader][] = $u;
			$_SESSION['client']['__loaded_jses__'][$u] = true;
			return true;
		}
		return false;
	}

	public final static function load_css($u,$loader=null) {
		if(!is_string($u) || strlen($u)==0) return false;
		if (!isset($_SESSION['client']['__loaded_csses__'][$u])) {
    		if (!isset($loader)) $loader = 'serve.php';
			if(!isset(self::$load_csses[$loader])) self::$load_csses[$loader] = array();
			self::$load_csses[$loader][] = $u;
			$_SESSION['client']['__loaded_csses__'][$u] = true;
			return true;
		}
		return false;
	}

	public final static function text($txt,$id,$type='instead') {
		self::$txts .= 'Epesi.text(\''.self::escapeJS($txt,false).'\',\''.self::escapeJS($id,false).'\',\''.self::escapeJS($type{0},false).'\');';
	}

	public final static function alert($txt,$del = false) {
		self::js('alert(\''.self::escapeJS($txt,false).'\')',$del);
	}

	public final static function redirect($addr='') {
		self::js('document.location=\''.self::escapeJS($addr,false).'\'');
	}

	/**
	 * Extends list of javascript commands to execute
	 *
	 * @param string javascript code
	 */
	public final static function js($js,$del_on_loc=true) {
		if(!is_string($js) || strlen($js)==0) return false;
		$js = rtrim($js,';');
        $js_def = array($js,$del_on_loc);
        if (DEBUG_JS && function_exists('debug_backtrace')) {
            $arg = false;
            if (version_compare(PHP_VERSION, '5.3.6', '>=')) {
                $arg = DEBUG_BACKTRACE_IGNORE_ARGS;
            }
            $bt = debug_backtrace($arg);
            array_shift($bt); // remove first, because it's this function
            $debug_str = '';
            $limit = (int) DEBUG_JS;
            while ($limit--) {
                $x = array_shift($bt);
                if (!$x) break;
                $file = & $x['file'];
                $line = & $x['line'];
                $func = & $x['function'];
                $debug_str .= "$func ($file:$line)";
                if ($limit) $debug_str .= ' <-- ';
            }
            $js_def[] = $debug_str;
        }
		self::$jses[] = $js_def;
		return true;
	}

	/**
	 * Escapes special characters in js code.
	 *
	 * @param string $str js code to escape
     * @param bool $double escape double quotes
     * @param bool $single escape single quotes
	 * @return string escaped js code
	 */
	public final static function escapeJS($str,$double=true,$single=true) {
		$arr = array (
			'\\' => '\\\\',
			"\r" => '\\r',
			"\n" => '\\n',
			'</' => '<\/',
			"\xe2\x80\xa8" => '\\u2028',
			"\xe2\x80\xA9" => '\\u2029'
		);
		if($single)
			$arr["'"] = "\\'";
		if($double)
			$arr['"'] = '\\"';
		// borrowed from smarty
		return strtr($str, $arr);
	}

	//============================================
	/**
	 * @var Module[]
     */
	public static $instances = array();

	public static $content;
	public static $debug;
	public static $times;

	private static function check_firstrun() {
		$first_run = false;

		foreach(ModuleManager::$modules as $name=>$version) {
			if($name==FIRST_RUN) $first_run=true;
		}
		ob_start();
		if(!$first_run) {
            if (ModuleManager :: install(FIRST_RUN)) {
                $processed_modules = ModuleManager::get_processed_modules();
                $_SESSION['first-run_post-install'] = $processed_modules['install'];                
            } else {
                $x = ob_get_contents();
                ob_end_clean();
                trigger_error('Unable to install default module: '.$x,E_USER_ERROR);
            }
		}
		ob_end_clean();
	}

	public static function process($url, $history_call=false,$refresh=false) {
		if(MODULE_TIMES)
			$time = microtime(true);

		$url = str_replace('&amp;','&',$url); //do we need this if we set arg_separator.output to &?

		if($url) {
			$_POST = array();
			parse_str($url, $_POST);
			if (get_magic_quotes_gpc())
			        $_POST = undoMagicQuotes($_POST);
			$_GET = $_REQUEST = & $_POST;
		}

		ModuleManager::load_modules();
		self::check_firstrun();

		if($history_call==='0')
		    History::clear();
		elseif($history_call)
		    History::set_id($history_call);

		//on init call methods...
		$ret = on_init(null,null,null,true);
		foreach($ret as $k) {
			call_user_func_array($k['func'],$k['args']);
		}

		self::$content = ModuleManager::create_root()->get_html();

		//go somewhere else?
		$loc = location(null,true);

		//on exit call methods...
		$ret = on_exit(null,null,null,true,$loc===false);
		foreach($ret as $k)
			call_user_func_array($k['func'],$k['args']);

		if($loc!==false) {
			if(isset($_REQUEST['__action_module__']))
				$loc['__action_module__'] = $_REQUEST['__action_module__'];

			//clean up
			foreach(self::$jses as $k=>$v)
				if($v[1]) unset(self::$jses[$k]);

			//go
			$loc['__location'] = microtime(true);
			return self::process(http_build_query($loc),false,true);
		}

		foreach(self::$instances as $instance) {
			if(method_exists($instance,'reloaded')) $instance->reloaded();
			foreach($instance->get_jses() as $js) self::js($js);
		}

		if(DEBUG) {
			self::$debug .= 'vars '.CID.': '.print_r($_SESSION['client']['__module_vars__'],true).'<br>';
			self::$debug .= 'user='.Base_AclCommon::get_user().'<br>';
			if(isset($_REQUEST['__action_module__']))
				self::$debug .= 'action module='.$_REQUEST['__action_module__'].'<br>';
		}

		if(MODULE_TIMES) {
			foreach (self::$times as $k => $v) {
				$style='color:red;font-weight:bold';
				if ($v<0.5) $style = 'color:orange;font-weight:bold';
				if ($v<0.05) $style = 'color:green;font-weight:bold';
				self::$debug .= 'Time of loading module <b>'.$k.'</b>: <i>'.'<span style="'.$style.';">'.number_format($v,4).'</span>'.'</i><br>';
			}
			self::$debug .= 'Page renderered in '.(microtime(true)-$time).'s<hr>';
		}

		if(SQL_TIMES) {
			self::$debug .= '<font size="+1">QUERIES</font><br>';
			$queries = DB::GetQueries();
			$sum = 0;
			$qty = 0;
			foreach($queries as $kk=>$q) {
				$style='color:red;font-weight:bold';
				if ($q['time']<0.5) $style = 'color:orange;font-weight:bold';
				if ($q['time']<0.05) $style = 'color:green';
				for($kkk=0; $kkk<$kk; $kkk++)
					if($queries[$kkk]['args']==$q['args']) {
						$style .= ';text-decoration:underline';
					}
				self::$debug .= '<span style="'.$style.';">'.'<b>'.$q['func'].'</b> '.htmlspecialchars(var_export($q['args'],true)).' <i><b>'.number_format($q['time'],4).'</b></i><br>'.'</span>';
				$sum+=$q['time'];
				$qty++;
			}
			self::$debug .= '<b>Number of queries:</b> '.$qty.'<br>';
			self::$debug .= '<b>Queries times:</b> '.$sum.'<br>';
		}
		if(!isset($_SESSION['client']['custom_debug']) || self::$debug!=$_SESSION['client']['custom_debug']) {
			if (self::$debug) Epesi::js("$('debug_content').style.display='block';");
			$_SESSION['client']['custom_debug'] = self::$debug;
		}

		if(!$history_call && !History::soft_call()) {
		        History::set();
		}

		if(!$history_call) {
			self::js('Epesi.history_add('.History::get_id().')');
		}

	}
}

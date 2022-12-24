<?php
define('USER', 'oldpc');
define('N', "\n");
function main($argc, $argv) {
	echo 'AFA' . "\n";
	$list = getOpenFilesList($argv[2] ? $argv[2] : '');
	// lg(print_r($list, true));
	$s = '';
	foreach ($list as $file) {
		$s .= parseFile($file) . N;
	}
	file_put_contents('/home/' . USER . '/.config/geany/tags/geanyjsparser.js.tags', $s);
	exec('killall geany; geany');
}


function parseFile($file) {
	$content = file_get_contents($file);
	$c = explode(N, $content);
	$sz = count($c);
	$objects = [];
	$lastReturn = '';
	for ($i = 0; $i < $sz; $i++) {
		$s = $c[$i];
		$lr = parseRetyrnType($s);
		if ($lr) {
			$lastReturn = $lr;
		}
		$o = parseLineAsFunctionHeader($s, $content);
		if ($o->name) {
			$objects[] = createPostScripLine($o, $lastReturn);
			$lastReturn = '';
		}
	}
	
	return implode(N, $objects);
}

function parseRetyrnType($s)
{
	if (strpos($s, '@return') !== false) {
		$a = explode('@return', $s);
		$s = trim($a[1]);
		$r = '';
		$allow = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_';
		$sz = strlen($s);
		for ($i = 0; $i < $sz; $i++) {
			$ch = $s[$i];
			if (strpos($allow, $ch) !== false) {
				$r .= $ch;
			} else {
				break;
			}
		}
		
		return $r;
	}
	
	return '';
}

function createPostScripLine($o, $return)
{
	$args = implode(', ', $o->args);
	$s = "{$o->name}|{$return} {$o->className}.|({$args})|";
	
	return $s;
}

function parseLineAsFunctionHeader($s, $content)
{
	$o = new StdClass();
	$o->name = '';
	$o->className = '';
	$o->args = [];
	parseLineAsFunction($o, $s);
	if ($o->name) {
		return $o;
	}
	parseLineAsClassMember($o, $s);
	if ($o->name) {
		return $o;
	}
	parseLineAsObjectMember($o, $s, $content);
	return $o;
	
}


function parseLineAsObjectMember(&$o, $s, $content)
{
	if (strpos($s, 'function') !== false) {
		$a = explode('function', $s);
		if (strpos($a[1], '(' ) !== false) {
			$b = explode('(', $a[1]);
			if (trim($b[0]) == '') {
				$o->args = getArgs($b[1]);
				$b = explode(':', $a[0]);
				if (count($b) == 2) {
					$o->className = getObjectName($content);
					$o->name = trim($b[0]);
				}
			}
		}
	}
	
	if (!$o->className) {
		$o->name = '';
	}
}

function getObjectName($content)
{
	$s = explode('=', $content)[0];
	$s = trim(str_replace('var', '', $s));
	$s = trim(str_replace('let', '', $s));
	
	return $s;
}

function parseLineAsClassMember(&$o, $s)
{
	if (strpos($s, 'function') !== false) {
		$a = explode('function', $s);
		if (strpos($a[1], '(' ) !== false) {
			$b = explode('(', $a[1]);
			if (trim($b[0]) == '') {
				$o->args = getArgs($b[1]);
				$b = explode('.prototype.', $a[0]);
				if (count($b) > 1) {
					lg($a[0]);
					lg(print_r($b, 1));
					$o->className = trim($b[0]);
					$o->name = $b[1];
					$o->name = trim(str_replace('=', '', $o->name));
				}
			}
		}
	}
	
	if (!$o->className) {
		$o->name = '';
	}
}

function parseLineAsFunction(&$o, $s)
{
	if (strpos($s, 'function') !== false) {
		$a = explode('function', $s);
		if (strpos($a[1], '(' ) !== false) {
			$b = explode('(', $a[1]);
			if (trim($b[0])) {
				$o->name = trim($b[0]);
				$o->args = getArgs($b[1]);
				$o->className = 'global';
			}
		}
	}
}

/**
 * @param string $s like 'abs, kopp, mode) {...'
*/
function getArgs($s)
{
	$s = explode(')', $s)[0];
	$a = explode(',', $s);
	array_map(function($s) {
		return trim($s);
	}, $a);
	
	return $a;
}

function getOpenFilesList($projectDir)
{
	$projectDir = preg_replace("#/$#", '', $projectDir);
	$file = $projectDir . '.geany';
	$r = [];
	if (file_exists($file)) {
		$ls = explode(N, file_get_contents($file));
		foreach ($ls as $line) {
			if (strpos($line, 'FILE_NAME_') === 0) {
				$fileInfo = explode(';', $line);
				$fileName = $fileInfo[7];
				$fileName = str_replace('%2F', '/', $fileName);
				if (file_exists($fileName)) {
					$pathInfo = pathinfo($fileName);
					if ($pathInfo['extension'] == 'js') {
						$r[] = $fileName;
					}
				}
			}
		}
		
	}
	return $r;
}

function lg($s) {
	file_put_contents(__DIR__ . '/log.log', $s . N, FILE_APPEND);
}

main($argc, $argv);

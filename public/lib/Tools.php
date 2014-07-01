<?php
/**
 * array_column_sort
 *
 * function to sort an "arrow of rows" by its columns
 * exracts the columns to be sorted and then
 * uses eval to flexibly apply the standard
 * array_multisort function
 *
 * uses a temporary copy of the array whith "_" prefixed to  the keys
 * this makes sure that array_multisort is working with an associative
 * array with string type keys, which in turn ensures that the keys
 * will be preserved.
 *
 * TODO: find a way of modifying the keys of $array directly, without using
 * a copy of the array.
 *
 * flexible syntax:
 * $new_array = array_column_sort($array [, 'col1' [, SORT_FLAG [, SORT_FLAG]]]...);
 *
 * original code credited to Ichier (www.ichier.de) here:
 * http://uk.php.net/manual/en/function.array-multisort.php
 *
 * prefixing array indeces with "_" idea credit to steve at mg-rover dot org, also here:
 * http://uk.php.net/manual/en/function.array-multisort.php
 *
 */
if (!function_exists('array_column_sort')){
	function array_column_sort()
	{
		$args = func_get_args();
		$array = array_shift($args);

		// make a temporary copy of array for which will fix the
		// keys to be strings, so that array_multisort() doesn't
		// destroy them
		$array_mod = array();
		foreach ($array as $key => $value)
		$array_mod['_' . $key] = $value;

		$i = 0;
		$multi_sort_line = "return array_multisort( ";
		foreach ($args as $arg)
		{
			$i++;
			if ( is_string($arg) )
			{
				foreach ($array_mod as $row_key => $row)
				{
					$sort_array[$i][] = strtolower($row[$arg]);
				}
			}
			else
			{
				$sort_array[$i] = $arg;
			}
			$multi_sort_line .= "\$sort_array[" . $i . "], ";
		}
		$multi_sort_line .= "\$array_mod );";

		eval($multi_sort_line);

		// now copy $array_mod back into $array, stripping off the "_"
		// that we added earlier.
		$array = array();
		foreach ($array_mod as $key => $value)
		$array[ substr($key, 1) ] = $value;

		return $array;
	}
}

function rmdirr($dir) {
	if(is_dir($dir) && !is_link($dir))
	{
		if($objs = glob($dir."/*")){
			foreach($objs as $obj) {
				is_dir($obj)? rmdirr($obj) : unlink($obj);
			}
		}
		rmdir($dir);
	}
	elseif(is_link($dir))
	{
		unlink($dir);
	}
}

function get_basename($file_name)
{
	$newfile = basename($file_name);
	if (strpos($newfile,'\\') !== false)
	{
		$tmp = preg_split("[\\\]",$newfile);
		$newfile = $tmp[count($tmp) - 1];
		return($newfile);
	}
	else
	{
		return($newfile);
	}
}

function mkdirr($working_directory)
{
	do
	{
		$dir = $working_directory;
		while (!@mkdir($dir,0777))
		{
			$dir = dirname($dir);

			if ($dir == '/' || is_dir($dir))
			break;
		}
	} while ($dir != $working_directory);

}

function markSelected($array,$key,$value)
{
	if(is_array($array) && !is_null($key) && !is_null($value))
	{
		foreach($array as $item=>$entry)
		{
			if($entry[$key] == $value)
			{
				$array[$item]['selected'] = true;
			}
			else
			{
				$array[$item]['selected'] = false;
			}
		}
	}

	return $array;
}

function markSelectedSet($array,$key,$values)
{
	if(is_array($array) && !is_null($key) && is_array($values))
	{
		for($i = 0; $i < sizeof($array); $i++)
		{
			if(in_array($array[$i][$key],$values))
			{
				$array[$i]['selected'] = true;
			}
			else
			{
				$array[$i]['selected'] = false;
			}
		}
	}

	return $array;
}

function print_a( $TheArray )
{ // Note: the function is recursive
	echo "<table border=1>\n";

	$Keys = array_keys( $TheArray );
	foreach( $Keys as $OneKey )
	{
		echo "<tr>\n";

		echo "<td bgcolor='#727450'>";
		echo "<B>" . $OneKey . "</B>";
		echo "</td>\n";

		echo "<td bgcolor='#C4C2A6'>";
		if ( is_array($TheArray[$OneKey]) )
		print_a($TheArray[$OneKey]);
		else
		echo $TheArray[$OneKey];
		echo "</td>\n";

		echo "</tr>\n";
	}
	echo "</table>\n";
}

//RSS Parser Functions

$rss_channel = "";
$currently_writing = "";
$main = "";
$item_counter = "";

function startElement($parser, $name, $attrs) {
	global $rss_channel, $currently_writing, $main;
	switch($name) {
		case "RSS":
		case "RDF:RDF":
		case "ITEMS":
			$currently_writing = "";
			break;
		case "CHANNEL":
			$main = "CHANNEL";
			break;
		case "IMAGE":
			$main = "IMAGE";
			$rss_channel["IMAGE"] = array();
			break;
		case "ITEM":
			$main = "ITEMS";
			break;
		default:
			$currently_writing = $name;
			break;
	}
}

function endElement($parser, $name) {
	global $rss_channel, $currently_writing, $item_counter;
	$currently_writing = "";
	if ($name == "ITEM") {
		$item_counter++;
	}
}

function characterData($parser, $data) {
	global $rss_channel, $currently_writing, $main, $item_counter;
	if ($currently_writing != "") {
		switch($main) {
			case "CHANNEL":
				if (isset($rss_channel[$currently_writing])) {
					$rss_channel[$currently_writing] .= $data;
				} else {
					$rss_channel[$currently_writing] = $data;
				}
				break;
			case "IMAGE":
				if (isset($rss_channel[$main][$currently_writing])) {
					$rss_channel[$main][$currently_writing] .= $data;
				} else {
					$rss_channel[$main][$currently_writing] = $data;
				}
				break;
			case "ITEMS":
				if (isset($rss_channel[$main][$item_counter][$currently_writing])) {
					$rss_channel[$main][$item_counter][$currently_writing] .= $data;
				} else {
					//print ("rss_channel[$main][$item_counter][$currently_writing] = $data<br>");
					$rss_channel[$main][$item_counter][$currently_writing] = $data;
				}
				break;
		}
	}
}

function unhtmlentities($string)
{
	$trans_tbl = get_html_translation_table(HTML_ENTITIES);
	$trans_tbl = array_flip($trans_tbl);
	return strtr($string, $trans_tbl);
}

function validateEmail($email)
{
	if(strpos($email,"@") === false)
		return false;
	
	list($local, $domain) = explode("@", $email);

	$pattern_local = '^([0-9a-z]*([-|_]?[0-9a-z]+)*)(([-|_]?)\.([-|_]?)';
	$pattern_local .= '[0-9a-z]*([-|_]?[0-9a-z]+)+)*([-|_]?)$';

	$pattern_domain = '^([0-9a-z]+([-]?[0-9a-z]+)*)(([-]?)\.([-]?)';
	$pattern_domain .= '[0-9a-z]*([-]?[0-9a-z]+)+)*\.[a-z]{2,4}$';

	$match_local = eregi($pattern_local, $local);
	$match_domain = eregi($pattern_domain, $domain);

	if ($match_local && $match_domain)
	return TRUE;
	else
	return FALSE;
}

function pagedResults($fullResults,$pageNumber=0,$pageSize=20)
{
	$emptyResultSet = array("pagedResults"=>array(),
							"pageCount"=>0,
							"nextPage"=>0,
							"prevPage"=>0,
							"min"=>0,
							"max"=>0,
							"total"=>0);

	if(!is_array($fullResults) || sizeof($fullResults) == 0)
	{
		return $emptyResultSet;
	}

	$pagedResults = array();

	$startIndex = $pageSize*$pageNumber;
	$endIndex = $startIndex + $pageSize;

	if($startIndex >= sizeof($fullResults))
	{
		return $emptyResultSet;
	}

	if($endIndex >= sizeof($fullResults))
	{
		$endIndex = sizeof($fullResults);
	}

	for($i = $startIndex; $i < $endIndex; $i++)
	{
		$pagedResults[] = $fullResults[$i];
	}

	$pageCount = ceil(sizeof($fullResults)/$pageSize);

	$pageList = array();

	for($i = 0; $i < $pageCount; $i++)
	{
		$pageList[] = array("id"=>$i+1,"page"=>$i);
	}


	$nextPage = $pageNumber+1;
	$prevPage = $pageNumber-1;

	if($prevPage < 0)
	$prevPage = 0;

	if($nextPage >= $pageCount)
	$nextPage = $pageCount - 1;

	if($nextPage < 0)
	$nextPage = 0;

	$min = $startIndex + 1;
	$max = $endIndex;

	return array("pagedResults"=>$pagedResults,
				"pageCount"=>$pageCount,
				"pageList"=>$pageList,
				"nextPage"=>$nextPage,
				"prevPage"=>$prevPage,
				"min"=>$min,
				"max"=>$max,
				"total"=>sizeof($fullResults));

}

function pagedResults2($fullResults,$pageNumber=0,$count)
{
	$pageSize = 20;
	$emptyResultSet = array("pagedResults"=>array(),
							"pageCount"=>0,
							"nextPage"=>0,
							"prevPage"=>0,
							"min"=>0,
							"max"=>0,
							"total"=>0);

	if(!is_array($fullResults) || sizeof($fullResults) == 0)
	{
		return $emptyResultSet;
	}

	$pagedResults = $fullResults;

	$startIndex = $pageSize*$pageNumber;
	$endIndex = $startIndex + $pageSize;

	if($startIndex >= $count)
	{
		return $emptyResultSet;
	}

	if($endIndex >= $count)
	{
		$endIndex = $count;
	}

	$pageCount = ceil($count/$pageSize);

	$nextPage = $pageNumber+1;
	$prevPage = $pageNumber-1;

	if($prevPage < 0)
	$prevPage = 0;

	if($nextPage >= $pageCount)
	$nextPage = $pageCount - 1;

	if($nextPage < 0)
	$nextPage = 0;

	$min = $startIndex + 1;
	$max = $endIndex;

	return array("pagedResults"=>$pagedResults,
	"pageCount"=>$pageCount,
	"nextPage"=>$nextPage,
	"prevPage"=>$prevPage,
	"min"=>$min,
	"max"=>$max,
	"total"=>$count);

}


function buildSelect($name,$options,$valueColumn,$nameColumn,$includeDefault=false)
{

	if(!is_array($options) || sizeof($options) == 0)
	{
		return "No Options to Select";
	}

	$select = "<select name=\"$name\">";

	if($includeDefault)
	{
		$select .= "<option value=\"0\">-Select One-</option>";
	}

	$options = array_values($options);

	for($i = 0; $i < sizeof($options); $i++)
	{
		$optionValue = $options[$i][$valueColumn];
		$optionName = $options[$i][$nameColumn];

		if(array_key_exists("selected",$options[$i]) && $options[$i]['selected'])
		{
			$select .= "<option value=\"$optionValue\" selected>$optionName</option>";
		}
		else
		{
			$select .= "<option value=\"$optionValue\">$optionName</option>";
		}
	}

	$select .= "</select>";

	return $select;
}

function maskCCNumber($number)
{
	if(is_string($number))
	{
		for($i = 0; $i < (strlen($number) - 4); $i++)
		{
			$number[$i] = "*";
		}
	}

	return $number;
}

function getNameValuePairs($info)
{
	foreach($info as $name => $value){
		$array[] = "$name=$value";
	}

	$pairs = @join("&", $array);

	return $pairs;
}

function PasswordGenerator($max_len)
{
	$min_len = 6;
	$pass_lenght= $max_len;

	$password = "";

	if ($min_len > $max_len)
	{
		$pass_lenght = $min_len;
	}

	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	$chr = strlen($chars)-1;

	for($i=0;$i<$pass_lenght;$i++)
	{
		mt_srand((double)microtime()*1000000);
		$password.=$chars[mt_rand(0, $chr)];
	}

	return $password;
}

function spamTest($messageText)
{
	$fHandle = fopen("._temp_message","w");
	fwrite($fHandle,$messageText);
	fclose($fHandle);

	$results = shell_exec("spamassassin -t -L < ._temp_message");
	$lines = split("\n",$results);
	$score = 0.0;
	$scoreRetrieved = false;
	$report = "";
	for($i = sizeof($lines)-1; $i >= 0; $i--)
	{
		if(strstr($lines[$i],"Content analysis details:"))
		{
			list($junk,$info) = explode(":",$lines[$i]);
			$info = trim($info);
			$infoParts = split(",",$info);
			$info = $infoParts[0];
			$info = str_replace("(","",$info);
			$score = str_replace(" points","",$info);

			$i++;
			$scoreRetrieved = true;
		}

		if($scoreRetrieved)
		{
			for($k = $i; $k < sizeof($lines); $k++)
			{
				if(strstr($lines[$k],"ALL_TRUSTED"))
				{
					$badRuleLine = $lines[$k];

					list($badScore,$badDesc) = explode("ALL_TRUSTED",$badRuleLine);
					$badScore = trim($badScore);

					$score -= $badScore;
					continue;
				}

				if(strstr($lines[$k],"MIME_HTML_ONLY"))
				{
					$badRuleLine = $lines[$k];

					list($badScore,$badDesc) = explode("MIME_HTML_ONLY",$badRuleLine);
					$badScore = trim($badScore);

					$score -= $badScore;
					continue;
				}


				if(strlen(trim($lines[$k])))
				{
					$report .= $lines[$k]."\n";
				}
			}

			break;
		}
	}

	unlink("._temp_message");
	$spamInfo = array("score"=>$score,"report"=>$report);
	return $spamInfo;
}

function myrename($src,$dest)
{
	if(file_exists($dest))
	unlink($dest);

	$result = copy($src,$dest);

	if($result)
	{
		unlink($src);
	}

	return true;
}

function dirmv($source, $dest, $overwrite = false, $funcloc = NULL)
{
	if(is_null($funcloc))
	{
		$dest .= '/' . strrev(substr(strrev($source), 0, strpos(strrev($source), '/')));
		$funcloc = '/';
	}


	if(!is_dir($dest . $funcloc))
	mkdir($dest . $funcloc); // make subdirectory before subdirectory is copied

	if($handle = opendir($source . $funcloc))
	{ // if the folder exploration is sucsessful, continue
		while(false !== ($file = readdir($handle)))
		{ // as long as storing the next file to $file is successful, continue
			if($file != '.' && $file != '..')
			{
				$path  = $source . $funcloc . $file;
				$path2 = $dest . $funcloc . $file;

				if(file_exists($path) && is_file($path))
				{
					if(!file_exists($path2) && !is_file($path2))
					{
						myrename($path, $path2);
					}
					elseif($overwrite)
					{
						if(file_exists($path2))
						unlink($path2);

						myrename($path, $path2);
					}
				}
				elseif(file_exists($path) && is_dir($path))
				{
					dirmv($source, $dest, $overwrite, $funcloc . $file . '/'); //recurse!
					rmdirr($path);
				}
			}
		}
		closedir($handle);

		rmdirr($source.$funcloc);
	}
} // end of dirmv()

function isArrayEmpty($array)
{
	if(is_array($array) && sizeof($array) > 0)
	{
		return false;
	}
	
	return true;
}

function isState($state)
{
$US_STATES = array(
               array("id"=>"1","long_name"=>"Alaska","short_name"=>"AK"),
               array("id"=>"2","long_name"=>"Alabama","short_name"=>"AL"),
               array("id"=>"4","long_name"=>"Arkansas","short_name"=>"AR"),
               array("id"=>"3","long_name"=>"Arizona","short_name"=>"AZ"),
               array("id"=>"5","long_name"=>"California","short_name"=>"CA"),
               array("id"=>"6","long_name"=>"Colorado","short_name"=>"CO"),
               array("id"=>"7","long_name"=>"Connecticut","short_name"=>"CT"),
               array("id"=>"9","long_name"=>"District of Columbia","short_name"=>"DC"),
               array("id"=>"8","long_name"=>"Deleware","short_name"=>"DE"),
               array("id"=>"10","long_name"=>"Florida","short_name"=>"FL"),
               array("id"=>"11","long_name"=>"Georgia","short_name"=>"GA"),
               array("id"=>"12","long_name"=>"Hawaii","short_name"=>"HI"),
               array("id"=>"16","long_name"=>"Iowa","short_name"=>"IA"),
               array("id"=>"13","long_name"=>"Idaho","short_name"=>"ID"),
               array("id"=>"14","long_name"=>"Illinois","short_name"=>"IL"),
               array("id"=>"15","long_name"=>"Indiana","short_name"=>"IN"),
               array("id"=>"17","long_name"=>"Kansas","short_name"=>"KS"),
               array("id"=>"18","long_name"=>"Kentucky","short_name"=>"KY"),
               array("id"=>"19","long_name"=>"Louisiana","short_name"=>"LA"),
               array("id"=>"22","long_name"=>"Massachusetts","short_name"=>"MA"),
               array("id"=>"21","long_name"=>"Maryland","short_name"=>"MD"),
               array("id"=>"20","long_name"=>"Maine","short_name"=>"ME"),
               array("id"=>"23","long_name"=>"Michigan","short_name"=>"MI"),
               array("id"=>"24","long_name"=>"Minnesota","short_name"=>"MN"),
               array("id"=>"26","long_name"=>"Missouri","short_name"=>"MO"),
               array("id"=>"25","long_name"=>"Mississippi","short_name"=>"MS"),
               array("id"=>"27","long_name"=>"Montana","short_name"=>"MT"),
               array("id"=>"34","long_name"=>"North Carolina","short_name"=>"NC"),
               array("id"=>"35","long_name"=>"North Dakota","short_name"=>"ND"),
               array("id"=>"28","long_name"=>"Nebraska","short_name"=>"NE"),
               array("id"=>"30","long_name"=>"New Hampshire","short_name"=>"NH"),
               array("id"=>"31","long_name"=>"New Jersey","short_name"=>"NJ"),
               array("id"=>"32","long_name"=>"New Mexico","short_name"=>"NM"),
               array("id"=>"29","long_name"=>"Nevada","short_name"=>"NV"),
               array("id"=>"33","long_name"=>"New York","short_name"=>"NY"),
               array("id"=>"36","long_name"=>"Ohio","short_name"=>"OH"),
               array("id"=>"37","long_name"=>"Oklahoma","short_name"=>"OK"),
               array("id"=>"38","long_name"=>"Oregon","short_name"=>"OR"),
               array("id"=>"39","long_name"=>"Pennsylvania","short_name"=>"PA"),
               array("id"=>"40","long_name"=>"Rhode Island","short_name"=>"RI"),
               array("id"=>"41","long_name"=>"South Carolina","short_name"=>"SC"),
               array("id"=>"42","long_name"=>"South Dakota","short_name"=>"SD"),
               array("id"=>"42","long_name"=>"South Dakota","short_name"=>"SD"),
               array("id"=>"43","long_name"=>"Tennessee","short_name"=>"TN"),
               array("id"=>"44","long_name"=>"Texas","short_name"=>"TX"),
               array("id"=>"45","long_name"=>"Utah","short_name"=>"UT"),
               array("id"=>"47","long_name"=>"Virginia","short_name"=>"VA"),
               array("id"=>"46","long_name"=>"Vermont","short_name"=>"VT"),
               array("id"=>"48","long_name"=>"Washington","short_name"=>"WA"),
               array("id"=>"50","long_name"=>"Wisconsin","short_name"=>"WI"),
               array("id"=>"49","long_name"=>"West Virginia","short_name"=>"WV"),
               array("id"=>"51","long_name"=>"Wyoming","short_name"=>"WY"),
               array("id"=>"52","long_name"=>"Other","short_name"=>"Other")
               );
               

	for($i = 0; $i < sizeof($US_STATES); $i++)
	{
		if(stricmp($US_STATES[$i]['short_name'],$state) == 0)
		{
			return true;
		}
	}
	
	return false;
}
?>

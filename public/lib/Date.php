<?php
//Date formats
define("DATE_SQL",1);
define("DATE_AMERICA",2);
define("DATE_EUROPE",3);

//Error codes
define("DATE_ERROR_INVALID_FORMAT",1);
define("DATE_ERROR_INVALID_DATE",2);
define("DATE_SUCCESS",100);

class Date
{
	var $_month;
	var $_day;
	var $_year;

	function Date($value="",$format=DATE_SQL)
	{
		$this->_Date($value,$format);
	}

	function _Date($value="",$format=DATE_SQL)
	{
		if("" != $value)
		{
			$error = DATE_SUCCESS;

			if(DATE_SQL == $format)
			{
				list($year,$month,$day) = explode("-",$value);
			}
			elseif(DATE_AMERICA == $format)
			{
				list($month,$day,$year) = explode("/",$value);
			}
			elseif(DATE_EUROPE == $format)
			{
				list($day,$month,$year) = explode("/",$value);
			}
			else
			{
				$error = DATE_ERROR_INVALID_FORMAT;
			}

			if(DATE_SUCCESS == $error)
			{
				$this->_year = $year;
				$this->_month = $month;
				$this->_day = $day;
			}
		}
		else
		{
			$this->_month = date("m",strtotime("now"));
			$this->_day = date("d",strtotime("now"));
			$this->_year = date("Y",strtotime("now"));

		}
	}

	function getMonth()
	{
		return $this->_month;
	}

	function getDay()
	{
		return $this->_day;
	}

	function getYear()
	{
		return $this->_year;
	}

	function setMonth($month)
	{
		$this->_month = $month;
	}

	function setDay($day)
	{
		$this->_day = $day;
	}

	function setYear($year)
	{
		$this->_year = $year;
	}

	function isValid()
	{
		if(checkdate($this->_month,$this->_day,$this->_year))
		{
			return DATE_SUCCESS;
		}

		return DATE_ERROR_INVALID_DATE;
	}

	function format($format=DATE_SQL)
	{
		switch($format)
		{
			case DATE_SQL:
				$month = sprintf("%02s",$this->_month);
				$day = sprintf("%02s",$this->_day);
				$value = $this->_year . "-" . $month . "-" . $day;
				break;
			case DATE_AMERICA:
				$value = $this->_month . "/" . $this->_day . "/" . $this->_year;
				break;
			case DATE_EUROPE:
				$value = $this->_day . "/" . $this->_month . "/" . $this->_year;
				break;
			default:
				$value = DATE_ERROR_INVALID_FORMAT;
				break;
		}

		return $value;
	}

	function getDayOfWeek()
	{
		return date("w",strtotime($this->format()));
	}

	function getMonthName()
	{
		return date("F",strtotime($this->format()));
	}

	/**
   * Retrieve a description for a given error code.
   *
   * @param int errorCode The error code to be translated into english.
   * @return string A string with an explanation of the error code.
   */
	function getErrorMessage($errorCode)
	{
		$error = "Invalid Error Code.";
		//Determine the proper message for a given error
		switch($errorCode)
		{
			case DATE_ERROR_INVALID_FORMAT:
				$error = "The Date format you have chosen is invalid.";
				break;
			case DATE_ERROR_INVALID_DATE:
				$error = "The Date you have specified is invalid.";
				break;
			default:
				$error = "Invalid Error Code.";
				break;
		}

		return $error;
	}

	function equalTo($other)
	{
		$thisDay = number_format($this->_day,0,".","");
		$thisMonth = number_format($this->_month,0,".","");
		$thisYear = number_format($this->_year,0,".","");

		$otherDay = number_format($other->getDay(),0,".","");
		$otherMonth = number_format($other->getMonth(),0,".","");
		$otherYear = number_format($other->getYear(),0,".","");

		if($thisDay == $otherDay &&
		$thisMonth == $otherMonth &&
		$thisYear == $otherYear)
		{
			return true;
		}

		return false;
	}

	function lessThan($other)
	{
		$thisDay = number_format($this->_day,0,".","");
		$thisMonth = number_format($this->_month,0,".","");
		$thisYear = number_format($this->_year,0,".","");

		$otherDay = number_format($other->getDay(),0,".","");
		$otherMonth = number_format($other->getMonth(),0,".","");
		$otherYear = number_format($other->getYear(),0,".","");

		if($thisYear < $otherYear)
		{
			return true;
		}
		elseif($thisYear == $otherYear)
		{
			if($thisMonth < $otherMonth)
			{
				return true;
			}
			elseif($thisMonth == $otherMonth)
			{
				if($thisDay < $otherDay)
				{
					return true;
				}
				return false;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}

	}

	function getDropDownArrays($numYears=5,$useFutureDates=true,$yearAnchor=null)
	{
		//If there is no year anchor specified, use the current year.
		if(null == $yearAnchor)
		{
			$yearAnchor = date("Y");
		}

		$months = array();
		for($i = 0; $i < 12; $i++)
		{
			$months[$i]['id'] = $i+1;
			if(($i+1) == $this->_month)
			{
				$months[$i]['selected'] = true;
			}
			else
			{
				$months[$i]['selected'] = false;
			}
		}

		$days = array();
		for($i = 0; $i < 31; $i++)
		{
			$days[$i]['id'] = $i+1;
			if(($i+1) == $this->_day)
			{
				$days[$i]['selected'] = true;
			}
			else
			{
				$days[$i]['selected'] = false;
			}

		}

		$years = array();
		$currentYear = $yearAnchor;
		$maxYear = $currentYear + $numYears;

		if(!$useFutureDates)
		{
			$currentYear = $yearAnchor - $numYears;
			$maxYear = $yearAnchor+1;
		}

		for($i = $currentYear; $i < $maxYear; $i++)
		{
			$years[$i]['id'] = $i;
			if($i == $this->_year)
			{
				$years[$i]['selected'] = true;
			}
			else
			{
				$years[$i]['selected'] = false;
			}

		}

		$dateInfo = array("months"=>$months,"days"=>$days,"years"=>$years);
		return $dateInfo;
	}
	
	function getRawDropDownArrays($numYears=5,$useFutureDates=true,$yearAnchor=null)
	{
		//If there is no year anchor specified, use the current year.
		if(null == $yearAnchor)
		{
			$yearAnchor = date("Y");
		}

		$months = array();
		for($i = 0; $i < 12; $i++)
		{
			$months[$i]['id'] = $i+1;
			$months[$i]['selected'] = false;
		}

		$days = array();
		for($i = 0; $i < 31; $i++)
		{
			$days[$i]['id'] = $i+1;
			$days[$i]['selected'] = false;
		}

		$years = array();
		$currentYear = $yearAnchor;
		$maxYear = $currentYear + $numYears;

		if(!$useFutureDates)
		{
			$currentYear = $yearAnchor - $numYears;
			$maxYear = $yearAnchor+1;
		}

		for($i = $currentYear; $i < $maxYear; $i++)
		{
			$years[$i]['id'] = $i;
			$years[$i]['selected'] = false;
		}

		$dateInfo = array("months"=>$months,"days"=>$days,"years"=>$years);
		return $dateInfo;
	}
}



function getSQLDate($americanDate)
{
	$date = new Date($americanDate,DATE_AMERICA);
	return $date->format(DATE_SQL);
}

function getAmericanDate($sqlDate)
{
	$date = new Date($sqlDate,DATE_SQL);
	return $date->format(DATE_AMERICA);
}

function getLastMonthRange()
{
	$currentDate = new Date();
	$currentMonth = $currentDate->getMonth();
	$currentYear  = $currentDate->getYear();

	$lastMonth = $currentMonth - 1;
	$lastYear = $currentYear;
	if($lastMonth == 0)
	{
		$lastMonth = 12;
		$lastYear = $currentYear - 1;
	}

	$startDate = new Date(date("Y-m-d",mktime(0,0,0,$lastMonth,1,$lastYear)));
	$endDate = new Date(date("Y-m-d",mktime(0,0,0,$currentMonth,0,$currentYear)));

	$result = array("startDate"=>$startDate,"endDate"=>$endDate);
	return $result;
}

function getThisMonthRange()
{
	$currentDate = new Date();
	$currentMonth = $currentDate->getMonth();
	$currentYear  = $currentDate->getYear();

	$nextMonth = $currentMonth + 1;
	$nextYear = $currentYear;
	if($nextMonth == 13)
	{
		$nextMonth = 1;
		$nextYear = $currentYear + 1;
	}

	$startDate = new Date(date("Y-m-d",mktime(0,0,0,$currentMonth,1,$currentYear)));
	$endDate = new Date(date("Y-m-d",mktime(0,0,0,$nextMonth,0,$nextYear)));

	$result = array("startDate"=>$startDate,"endDate"=>$endDate);
	return $result;
}

function getMonthRange($date)
{
	$currentMonth = $date->getMonth();
	$currentYear  = $date->getYear();

	$nextMonth = $currentMonth + 1;
	$nextYear = $currentYear;
	if($nextMonth == 13)
	{
		$nextMonth = 1;
		$nextYear = $currentYear + 1;
	}

	$startDate = new Date(date("Y-m-d",mktime(0,0,0,$currentMonth,1,$currentYear)));
	$endDate = new Date(date("Y-m-d",mktime(0,0,0,$nextMonth,0,$nextYear)));

	$result = array("startDate"=>$startDate,"endDate"=>$endDate);
	return $result;
}

?>

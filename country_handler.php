<?php
// CONFIG type stuff
$MAX_RESULTS = 50;

$searchInput = isset($_REQUEST['searchInput']) ? $_REQUEST['searchInput'] : "";

$aResultSet = [];

if (strlen($searchInput) > 0)
{
	//	The relative importance of the searches are :
	//	1). Full name search
	//	2). 2/3 letter code search
	//	3). Partial name search

	if (strlen($searchInput) > 3)
	{
		$aTempFullNameResults = __sendRequestToRestCountries('name/', urlencode($searchInput), true);
		if (count($aTempFullNameResults) > 0)
		{
			if (isset($aTempFullNameResults['languages'])) $aResultSet[] = $aTempFullNameResults;
			else $aResultSet = $aTempFullNameResults;
		}
	}

	if (strlen($searchInput) < 4)
	{
		$aTempCodeResults = __sendRequestToRestCountries('alpha/', urlencode($searchInput));
		if (count($aTempCodeResults) > 0)
		{
			if (isset($aTempCodeResults['languages'])) $aCodeResults[] = $aTempCodeResults;
			else $aCodeResults = $aTempCodeResults;

			//	Merge the Code results with the Full results, if they exist.
			//	Due to the way the array_merge() works is that records in the second array that 
			//	are not in the first array will be added at the end.
			//	In this way Full name results which are more relevant will float to the top.		
			if (count($aResultSet) > 0)
			{
				$aResultSet = array_unique(array_merge($aResultSet, $aCodeResults), SORT_REGULAR);
			}
			else
			{
				$aResultSet = $aCodeResults;
			}
		}
	}

	$aTempNameResults = __sendRequestToRestCountries('name/', urlencode($searchInput));
	if (count($aTempNameResults) > 0)
	{
		if (isset($aTempNameResults['languages'])) $aCodeResults[] = $aTempNameResults;
		else $aNameResults = $aTempNameResults;

		if (count($aResultSet) > 0)
		{
			$aResultSet = array_unique(array_merge($aResultSet, $aNameResults), SORT_REGULAR);
		}
		else
		{
			$aResultSet = $aNameResults;
		}
	}
}

function __sendRequestToRestCountries ($type, $search, $bIsFull = false)
{	//	$type : alpha/	name/	name/{name}?fullText=true&
	$ch = curl_init('https://restcountries.eu/rest/v2/'. $type . $search . ($bIsFull == true ?  '?fullText=true&' : '?') .'fields=name;alpha2Code;alpha3Code;flag;region;subregion;population;languages');
	curl_setopt($ch, CURLOPT_VERBOSE, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

	$jsonResults = curl_exec($ch);
	curl_close($ch);

	$aResults = json_decode($jsonResults, true);
	if (!isset($aResults['status']))
	{
		if (isset($aResults['languages'])) return [$aResults];
		else return $aResults;
	}

	return [];
}

if (isset($_REQUEST['ajax']) && count($aResultSet) < 1)
{
	die("var error='No results found.';");
}

$aOut = [];
foreach ($aResultSet AS $aResult)
{
	$strLanguages = "";
	if (isset($aResult['languages']))
	{
		foreach($aResult['languages'] AS $aLanguage)
		{
			$strLanguages .= $strLanguages != "" ? ', '. $strLanguages : "";
			$strLanguages .= $aLanguage['name'];
		}
	}

	$aOut[] = ['name' => $aResult['name'], 'code_2' => $aResult['alpha2Code'], 'code_3' => $aResult['alpha3Code'], 'flag' => $aResult['flag'], 'region' => $aResult['region'], 'subregion' => $aResult['subregion'], 'population' => $aResult['population'], 'languages' => $strLanguages];
}

$options = "var nameSort='up'";
if (isset($_REQUEST['nameSort']))
{
	$_REQUEST['nameSort'] == 'up' ? usort($aOut, "__sortNameUp") : usort($aOut, "__sortNameDown");
	$options = "var populationSort='';var nameSort='". ($_REQUEST['nameSort'] == 'up' ? 'up' : 'down') ."'";
}
elseif (isset($_REQUEST['populationSort']))
{
	$_REQUEST['populationSort'] == 'up' ? usort($aOut, "__sortPopulationUp") : usort($aOut, "__sortPopulationDown");
	$options = "var nameSort='';var populationSort='". ($_REQUEST['populationSort'] == 'up' ? 'up' : 'down') ."'";;
}
else
{
	$options = "var nameSort='';var populationSort=''";;
}

$iLimiter = 0;
$countryCount = 0;
$aResponse = [];
$aRegions = [];
$aSubregions = [];
foreach ($aOut AS $out)
{
	$iLimiter++;
	$countryCount++;

	if ($out['region'] != "" && isset($aRegions[$out['region']])) $aRegions[$out['region']]++;
	elseif ($out['region'] != "") $aRegions[$out['region']] = 1;

	if ($out['subregion'] != "" && isset($aSubregions[$out['subregion']])) $aSubregions[$out['subregion']]++;
	elseif ($out['subregion'] != "") $aSubregions[$out['subregion']] = 1;

	$aResponse[] = $out;

	if ($iLimiter >= $MAX_RESULTS)
		break;
}
arsort($aRegions);
arsort($aSubregions);
//die("<pre>". print_r($aSubregions, true) ."</pre>");

if (isset($_REQUEST['ajax']))
{
	echo "var error='';var searchString='". htmlentities($searchInput) ."';$options;var countryCount=$countryCount;var regions=". json_encode($aRegions) .";var subregions=". json_encode($aSubregions) .";var result=". print_r(json_encode($aResponse), true) .";";
}

function __sortNameUp ($a, $b)
{
	return $a['name'] > $b['name'];
}

function __sortNameDown ($a, $b)
{
	return $a['name'] < $b['name'];
}

function __sortPopulationUp ($a, $b)
{
	return $a['population'] > $b['population'];
}

function __sortPopulationDown ($a, $b)
{
	return $a['population'] < $b['population'];
}
?>
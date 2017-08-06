<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>

<style type="text/css">
#searchInput {
    width: 200px;
}
#searchString {
	font-style: italic;
	font-weight:500;
}
#resultSet {
	display:table;
	border:1px solid;
	border-collapse: collapse;
}
#headerRow, .resultRow {
	display:table-row;
}
#headerRow {
	font-weight:bold;
}
.headerField, .columnField {
	display:table-cell;
	padding:5px 8px;
	border:1px solid;
	vertical-align:middle;
}
.headerField {
	white-space:nowrap;
	text-align:center;
}
.codeField {
	text-align:center;
}
.populationField {
	text-align:right;
}
.subregion {
	white-space:nowrap;
}
.upArrow, .downArrow {
	display:inline-block;
    position:relative;
    width:0px;
    height:0px;
    border-style:solid;
    border-width:6px;
    border-color:#fff;
}
.upArrow {
    top:-7px;
    border-bottom-color:#CECACA;
	left:6px;
}
.upArrow:hover {
    border-bottom-color:#80FC80;
}
.downArrow {
    top:7px;
    border-top-color:#CECACA;
	right:6px;
}
.downArrow:hover {
    border-top-color:#80FC80;
}
.sortUp .upArrow {
	border-bottom-color:#80FC80;
}
.sortUp .upArrow:hover {
    border-bottom-color:#CECACA;
}
.sortDown .downArrow {
	border-top-color:#80FC80;
}
.sortDown .downArrow:hover {
    border-top-color:#CECACA;
}

.upArrow:hover, .downArrow:hover {
	cursor:pointer;
}


#nameSort {
	width:14%;
}
#code2 {
	width:6%;
}
#code3 {
	width:6%;
}
#flag {
	width:7%;
}
#region {
	width:7%;
}
#subregion {
	width:12%;
}
#populationSort {
	width:7%;
}
#languages {
	width:44%;
} img {
	max-width:100%;
}

.spinner {
	height: 60px;
	width: 60px;
	margin: 94px auto 0 auto;
	position: absolute;
	top:-24px;
	left:40%;
	animation: rotation .6s infinite linear;
	border-left: 6px solid rgba(0, 174, 239, .15);
	border-right: 6px solid rgba(0, 174, 239, .15);
	border-bottom: 6px solid rgba(0, 174, 239, .15);
	border-top: 6px solid rgba(0, 174, 239, .8);
	border-radius: 100%;
}

@keyframes rotation {
	from {
		transform: rotate(0deg);
	}
	to {
		transform: rotate(359deg);
	}
}
</style>
</head>
<body>
<form id="search" action="/">
	<input type="text" name="searchInput" id="searchInput" placeholder="Name or 2 or 3 letter code" value="<?php echo (isset($_REQUEST['searchInput']) ? htmlentities($_REQUEST['searchInput']) : ""); ?>" />
	<input type="submit" />
</form>
<h1>Result set for : <span id="searchString"><?php echo htmlentities((isset($_REQUEST['searchInput']) ? $_REQUEST['searchInput'] : ""));?></span></h1>
<div id="resultSet">
	<div id="headerRow">
		<div id="nameSort" class="headerField">Name<span id="nameSortUp" class="upArrow" title="Click to sort in ascending order."></span><span id="nameSortDown" class="downArrow" title="Click to sort in descending order."></span></div>
		<div id="code2" class="headerField">Alpha Code 2</div>
		<div id="code3" class="headerField">Alpha Code 3</div>
		<div id="flag" class="headerField">Flag</div>
		<div id="region" class="headerField">Region</div>
		<div id="subregion" class="headerField">Subregion</div>
		<div id="populationSort" class="headerField">Population<span id="populationSortUp" class="upArrow" title="Click to sort in ascending order."></span><span id="populationSortDown" class="downArrow" title="Click to sort in descending order."></span></div>
		<div id="languages" class="headerField">Language(s)</div>
	</div>
<?php
if (isset($_REQUEST['searchInput']))
{
	require_once 'country_handler.php'; // Creates and populates $aResponse based on input string
	foreach ($aResponse AS $aLine)
	{
?>
	<div class="resultRow">
		<div class="columnField"><?php echo $aLine['name']; ?></div>
		<div class="columnField codeField"><?php echo $aLine['code_2']; ?></div>
		<div class="columnField codeField"><?php echo $aLine['code_3']; ?></div>
		<div class="columnField"><?php echo $aLine['flag']; ?></div>
		<div class="columnField"><?php echo $aLine['region']; ?></div>
		<div class="columnField"><?php echo $aLine['subregion']; ?></div>
		<div class="columnField populationField"><?php echo number_format($aLine['population'], 0); ?></div>
		<div class="columnField"><?php echo $aLine['languages']; ?></div>
	</div>
<?php
	}
}
?>
</div>
<h1>Summary : <?php echo htmlentities((isset($_REQUEST['searchInput']) ? $_REQUEST['searchInput'] : ""));?></h1>
<ul id="summary">
	<li id="summCountryCount">Number of Countries : <?php echo (isset($countryCount) ? $countryCount : ""); //created/populated in 'country_handler.php'?></li>
	<li>Regions (name : instances)
		<ul id="summRegions">
<?php
if (isset($aRegions))
{
	foreach ($aRegions AS $name => $count)
	{
?>
			<li><?php echo $name .' : '. $count; ?></li>
<?php
	}
}
?>
		</ul>
	</li>
	<li>Subregions (name : instances)
		<ul id="summSubregions">
<?php
if (isset($aSubregions))
{
	foreach ($aSubregions AS $name => $count)
	{
?>
			<li><?php echo $name .' : '. $count; ?></li>
<?php
	}
}
?>
		</ul>
	</li>
</ul>

<script type="text/javascript">
//	Enable extended functionality when Javascript is available.
//	JS-enabled : AJAX search
//	JS-disabled (accessibility/SEO) : page reloads
var oSpinner = null;
var oBody = document.getElementsByTagName("body")[0];
document.getElementById('search').onsubmit = function () {
	if ((oInput = document.getElementById('searchInput')) == null) return;

	if (oInput.value.length < 1)
	{
		alert('No input was provided.');
		return false;
	}

	oSpinner = document.createElement('div');
	oSpinner.className = 'spinner';
	oBody.appendChild(oSpinner);

	aParam = [];
	aParam['searchInput'] = oInput.value;
	aParam['ajax'] = 1;
	var oAJAX = new AJAX('country_handler.php', aParam, 'get', 8080, 'dataReply');

	return false;
}

//	Attach an event to the button in result header
//	to sort results by country name, ascending.
document.getElementById('nameSortUp').onclick = function () {
	if (this.parentNode.className == 'headerField sortUp')
	{
		__getStringAndSubmit('none')
	}
	else
	{
		__getStringAndSubmit('nameSort', 'up')
	}
}

//	Attach an event to the button in result header
//	to sort results by country name, descending.
document.getElementById('nameSortDown').onclick = function () {
	if (this.parentNode.className == 'headerField sortDown')
	{
		__getStringAndSubmit('none')
	}
	else
	{
		__getStringAndSubmit('nameSort', 'down')
	}
}

//	Attach an event to the button in result header
//	to sort results by country population, ascending.
document.getElementById('populationSortUp').onclick = function () {
	if (this.parentNode.className == 'headerField sortUp')
	{
		__getStringAndSubmit('none')
	}
	else
	{
		__getStringAndSubmit('populationSort', 'up')
	}
}

//	Attach an event to the button in result header
//	to sort results by country population, descending.
document.getElementById('populationSortDown').onclick = function () {
	if (this.parentNode.className == 'headerField sortDown')
	{
		__getStringAndSubmit('none')
	}
	else
	{
		__getStringAndSubmit('populationSort', 'down')
	}
}

//	Sharing is caring. Doing the heavy lifting for the four
//	previous event handlers.
function __getStringAndSubmit (sortType, direction)
{
	if ((oInput = document.getElementById('searchInput')) == null) return;

	if (oInput.value.length < 1)
	{
		alert('No input was provided.');
		return;
	}

	oSpinner = document.createElement('div');
	oSpinner.className = 'spinner';
	oBody.appendChild(oSpinner);

	aParam = [];
	aParam['searchInput'] = oInput.value;
	if (sortType != 'none')
	{
		if (sortType == 'nameSort' && direction == 'up')
		{
			aParam['nameSort'] = 'up';
		}
		else if (sortType == 'nameSort' && direction == 'down')
		{
			aParam['nameSort'] = 'down';
		}
		else if (sortType == 'populationSort' && direction == 'up')
		{
			aParam['populationSort'] = 'up';
		}
		else if (sortType == 'populationSort' && direction == 'down')
		{
			aParam['populationSort'] = 'down';
		}
	}
	aParam['ajax'] = 1;
	var oAJAX = new AJAX('country_handler.php', aParam, 'get', 8080, 'dataReply');
}

function removeSiblings (node)
{
	while (node.nextSibling)
	{
		node.nextSibling.parentNode.removeChild(node.nextSibling);
	}
}

var objSearchString = null;
function dataReply (reply)
{
	if (oSpinner != null) oBody.removeChild(oSpinner);
	oSpinner = null;

	var newReply = unescape(reply);
console.log(newReply);
	eval(newReply);
	if ( error != '' )
	{
		alert('Message received from server - '+error);
		return;
	}
	
	//	Update displayed search string
	if (objSearchString == null) objSearchString = document.getElementById('searchString');
	if (objSearchString == null)
	{	//	Only possible if the element had actually been removed.
		alert('Processing cannot continue. A missing page element is missing : "searchString".');
		return;
	}
	if (typeof searchString !== 'undefined')
	{
		objSearchString.innerHTML = searchString;
	}

	//	Now comes the fun part, emptying and then refilling the result set.
	//	First the emptying ...
	if ((headerRow = document.getElementById('headerRow')) == null) return;
	removeSiblings(headerRow);

	//	Before filling it back up, set the sort buttons accordingly.
	if (nameSort != "")
	{
		document.getElementById('nameSort').className = nameSort == 'up' ? 'headerField sortUp' : 'headerField sortDown';
		document.getElementById('nameSortUp').title = nameSort == 'up' ? 'Click to remove sort' : 'Click to sort in ascending order.';
		document.getElementById('nameSortDown').title = nameSort == 'up' ? 'Click to sort in descending order.' : 'Click to remove sort';
		document.getElementById('populationSort').className = 'headerField';
		document.getElementById('populationSortUp').title = 'Click to sort in ascending order.';
		document.getElementById('populationSortDown').title = 'Click to sort in descending order.';
	}
	else if (populationSort != "")
	{
		document.getElementById('populationSort').className = populationSort == 'up' ? 'headerField sortUp' : 'headerField sortDown';
		document.getElementById('populationSortUp').title = populationSort == 'up' ? 'Click to remove sort' : 'Click to sort in ascending order.';
		document.getElementById('populationSortDown').title = populationSort == 'up' ? 'Click to sort in descending order.' : 'Click to remove sort';
		document.getElementById('nameSort').className = 'headerField';
		document.getElementById('nameSortUp').title = 'Click to sort in ascending order.';
		document.getElementById('nameSortDown').title = 'Click to sort in descending order.';
	}
	else
	{
		document.getElementById('populationSort').className = 'headerField';
		document.getElementById('nameSort').className = 'headerField';
		document.getElementById('nameSortUp').title = 'Click to sort in ascending order.';
		document.getElementById('nameSortDown').title = 'Click to sort in descending order.';
		document.getElementById('populationSortUp').title = 'Click to sort in ascending order.';
		document.getElementById('populationSortDown').title = 'Click to sort in descending order.';
	}

	//	Now, fill it back up
	if ((resultSet = document.getElementById('resultSet')) == null) return;

	for (x = 0; x < result.length; x++)
	{
		rowDiv = document.createElement('div');
		rowDiv.className = 'resultRow';
		nameDiv = document.createElement('div');
		nameDiv.className = 'columnField';
		nameDiv.innerHTML = result[x]['name'];
		rowDiv.appendChild(nameDiv);
		code2Div = document.createElement('div');
		code2Div.className = 'columnField codeField';
		code2Div.innerHTML = result[x]['code_2'];
		rowDiv.appendChild(code2Div);
		code3Div = document.createElement('div');
		code3Div.className = 'columnField codeField';
		code3Div.innerHTML = result[x]['code_3'];
		rowDiv.appendChild(code3Div);
		flagDiv = document.createElement('div');
		flagDiv.className = 'columnField';
		flagImg = document.createElement('img');
		flagImg.src = result[x]['flag'];
		flagDiv.appendChild(flagImg);
		rowDiv.appendChild(flagDiv);
		regionDiv = document.createElement('div');
		regionDiv.className = 'columnField';
		regionDiv.innerHTML = result[x]['region'];
		rowDiv.appendChild(regionDiv);
		subregion = document.createElement('div');
		subregion.className = 'columnField subregion';
		subregion.innerHTML = result[x]['subregion'];
		rowDiv.appendChild(subregion);
		population = document.createElement('div');
		population.className = 'columnField populationField';
		population.innerHTML = result[x]['population'].toLocaleString();
		rowDiv.appendChild(population);
		languages = document.createElement('div');
		languages.className = 'columnField';
		languages.innerHTML = result[x]['languages'];
		rowDiv.appendChild(languages);
		resultSet.appendChild(rowDiv);
	}
//	'countryCount' 'regions' 'subregions'
	document.getElementById('summCountryCount').innerHTML = 'Number of Countries : '+ countryCount;
	var summRegions = document.getElementById('summRegions');
	while (summRegions.firstChild)
	{
		summRegions.removeChild(summRegions.firstChild);
	}

	for (var key in regions)
	{
		region = document.createElement('li');
		region.innerHTML = key +" : "+ regions[key];
		summRegions.appendChild(region);
	}

	var summSubregions = document.getElementById('summSubregions');
	while (summSubregions.firstChild)
	{
		summSubregions.removeChild(summSubregions.firstChild);
	}

	for (var key in subregions)
	{
		subregion = document.createElement('li');
		subregion.innerHTML = key +" : "+ subregions[key];
		summSubregions.appendChild(subregion);
	}
}

var encodedStr = rawStr.replace(/[\u00A0-\u9999<>\&]/gim, function(i) {
   return '&#'+i.charCodeAt(0)+';';
});

function AJAX ( requestPage, parameters, type, port, reply ) 
{
	var oReply = reply;
	var oRequest;
	var oThis = this;

	this.processReply = processReply;

	if (window.XMLHttpRequest)
		oRequest = new XMLHttpRequest();
	else if (window.ActiveXObject) //     For IE
		oRequest = new ActiveXObject('Microsoft.XMLHTTP');
	var list = '';
	for (key in parameters) 
		list += key+'='+parameters[key]+'&';

	list += 'r='+ new Date().getTime();
	if (oRequest) 
	{
		oRequest.onreadystatechange = processReply;
		try 
		{
			if ( type == 'get' )
			{
				list = '?'+list;
				oRequest.open('GET', window.location.protocol +'//'+ window.location.hostname +':'+ port +'/'+ requestPage + list, true);
				oRequest.send(null);
			}
			else
			{
				oRequest.open('POST', window.location.protocol +'//'+ window.location.hostname +':'+ port +'/'+ requestPage, true);
				oRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
				oRequest.send(list);
			}
		}
		catch (e)
		{
		if ( oReply != '' && oReply != null )
			eval(oReply + '(\'' + escape("error='The URL " + requestPage + " could not be accessed. The server may be down or busy.  Please try again in a few minutes.'") + '\')');
		}
	}
	else
	{
		if ( oReply != '' && oReply != null )
		eval(oReply + '(\'' + escape("error='Could not create request object.") + '\')');
	}

	function processReply () 
	{
		if (oRequest.readyState == 4) 
		{
			if (oRequest.status == 200) 
			{
				var response = oRequest.responseText;
				response = escape(response);
				if ( oReply != '' && oReply != null )
				eval(oReply + '(\'' + response + '\')');
			}
			else
			{
				if ( oReply != '' && oReply != null )
				eval(oReply + '(\'' + escape("var error='An error occured during reply processing.'") + '\')');
			}
		}
	}
};
</script>

</body>
</html>

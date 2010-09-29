// JavaScript Document
var miniChartUrl = "";

function isReady() 
{
	return true;
}
function initMiniChart() 
{
	changeChart(miniChartUrl);
}
function changeChart(url) 
{
	thisMovie("miniChart").changeChart(url);
}

function thisMovie(movieName) 
{
	if (navigator.appName.indexOf("Microsoft") != -1)
	 {
		return window[movieName];
	} 
	else
	 {
		return document[movieName];
	}
}

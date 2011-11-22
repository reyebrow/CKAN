<?php

/**
 * @author Sean Burlington www.practicalweb.co.uk
 * @copyright PracticalWeb Ltd
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * 
 */
class Ckan {
	private  $url = 'http://ca.ckan.net/';
	private $errors = array( 
		'0'  	 =>   'Network Error?',
		'301'  =>   'Moved Permanently',
		'400'  =>   'Bad Request',
		'403'  =>   'Not Authorized',
		'404'  =>   'Not Found',
		'409'  =>   'Conflict (e.g. name already exists)',
		'500'  =>   'Internal Server Error', 
	);
	
	public function __construct($url=null){
		if ($url){
			$this->url=$url;
		}
	}
	
	private function transfer($url){

		$ch = curl_init($this->url . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Cache-Control: no-cache'));
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		$result = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
		if ($info['http_code'] != 200){
			throw new CkanException($info['http_code'] . ' : ' . $this->error_codes[$info[http_code]]);
		}
		if (!$result){
			throw new CkanException("No Result");
		}
		return json_decode($result);
	}

	public function search($keyword){
		$results = $this->transfer('api/1/search/package/?all_fields=1&q=' . urlencode($keyword));
		if (!$results->count){
			throw new CkanException("Search Error");
		}
		return $results;
	}
	
	public function advancedSearch($parameters){
		foreach($parameters as $key => $value) {
			$querystring .= $key .'='. urlencode($value) .'&';
		}
		$results = $this->transfer('api/1/search/package?'. $querystring);
		if (!$results->count){
			throw new CkanException("Search Error");
		}
		return $results;
		
	}

	public function getPackage($package){
		$package = $this->transfer('api/1/rest/package/' . urlencode($package));
		if (!$package->name){
			throw new CkanException("Package Load Error");
		}
		return $package;
	}


	public function getPackageList(){
		$list =  $this->transfer('api/1/rest/package/');
		if (!is_array($list)){
			throw new CkanException("Package List Error");
		}
		return $list;
	}

	public function getGroup($group){
		$group = $this->transfer('api/1/rest/group/' . urlencode($group) );
		if (!$group->name){
			throw new CkanException("Group Error");
		}
		return $group;
	}

	public function getGroupList(){
		$groupList = $this->transfer('api/1/rest/group/');
		if (!is_array($groupList)){
			throw new CkanException("Group List Error");
		}
		return $groupList;
	}
	
	public function getTags(){
		$list =  $this->transfer('api/1/rest/tag/');
		if (!is_array($list)){
			throw new CkanException("Tags Error");
		}
		return $list;
	}
	
	public function getTagList($tag){
		$list =  $this->transfer('api/1/rest/tag/' . urlencode($tag));
		if (!is_array($list)){
			throw new CkanException("Tag List Error");
		}
		return $list;
	}
	
	public function getTagCount(){
		$list =  $this->transfer('api/1/tag_counts');
		if (!is_array($list)){
			throw new CkanException("Tag Count Error");
		}
		return $list;
	}
}

class CkanException extends Exception{}

<?php

global $my_base_url;
global $my_data_server;
global $my_asset_server;
global $my_asset_database_WebId;
global $my_asset_database;
global $my_authorization_string;

$my_base_url = "https://usagstpian/piwebapi/";
$my_data_server = "roasterpi";
$my_asset_server ="USAGSTPIAF";
$my_asset_database = "PIWebAPI";
$my_asset_database_WebId = "F1RDFa_bAB7LwUSn0blPHPLcyQiMEG8BKdZEO1zROxkYUGPwVVNBR1NUUElBRlxQSVdFQkFQSQ";
$my_authorization_string = "cGlfZ2xvYmFsOkcxb2JhTFAh";

ini_set('max_execution_time', 30);

class PIWebAPI
{

	public static function dd($value){
			echo "<pre>";
			var_dump($value);
			echo "</pre>";
			die();
	}

	public static function CheckIfPIServerExists($piServerName)
	{
        global $my_base_url;
        global $my_data_server;
        global $my_asset_database;

		$base_service_url = $my_base_url;
		$url = $base_service_url . "dataservers";
		$obj = PIWebAPI::GetJSONObject($url);
		foreach($obj->Items as $myServer)
		{
			if(strtolower($myServer->Name)==strtolower($piServerName))
			{
				return(true);
			}
		}
		return (false);
	}

	public static function CheckIfPIPointExists($piServerName, $piPointName)
	{
		$base_service_url = "https://cross-platform-lab-uc2017.osisoft.com/piwebapi/";
		$url = $base_service_url . "points?path=\\\\" . $piServerName . "\\" . $piPointName;
		$obj1 = PIWebAPI::GetJSONObject($url);
		try {
            if(($obj1->Name)!=null)
            {
                return (true);
            }
            return (false);
		}
		catch (Exception $e)
		{
            //test
		}
	}

    public static function GetDataServerWebId ($dataServer)
    {
        global $my_base_url;

        $url = $my_base_url . "dataservers?path=\\\\" . $dataServer;
        $obj = PIWebAPI::GetJSONObject($url);

        return($obj->WebId);
    }


    public static function GetPIPointWebID($tagName)
    {
        global $my_base_url;
        global $my_data_server;

        $url = $my_base_url . "points?path=\\\\" . $my_data_server . "\\" . $tagName;
        $obj = PIWebAPI::GetJSONObject($url);

        return($obj->WebId);

    }

    public static function GetElementInfo($elementPath){
        global $my_base_url;
				$elementPath = str_replace(' ','%20', $elementPath);

        $url = $my_base_url . "elements?path=".$elementPath;
        $obj = PIWebAPI::GetJSONObject($url);

        return($obj);
    }

		public static function GetElementInfoById($elementId){
				global $my_base_url;

				$url = $my_base_url . "elements/".$elementId;
				$obj = PIWebAPI::GetJSONObject($url);

				return($obj);
		}

		public static function GetAssetDatabaseElements($WebId =""){
			global $my_base_url;
			global $my_asset_database_WebId;

			if($WebId == ""){
				$useId = $my_asset_database_WebId;
			}else{
				$useId = $WebId;
			}

			$url = $my_base_url . "assetdatabases/".$useId."/elements";

			$obj = PIWebAPI::GetJSONObject($url);

			return($obj);

		}

    public static function GetElementAttributesItems($elementPath){
        global $my_base_url;

				$elementPath = str_replace(' ','%20', $elementPath);

        $elementWebId = PIWebAPI::GetElementInfo($elementPath)->WebId;

        $url = $my_base_url . "elements/" . $elementWebId ."/attributes";

        $obj = PIWebAPI::GetJSONObject($url);

        $j_array= array();

        PIWebAPI::objToArray($obj, $j_array);

        return($j_array['Items']);

    }

    public static function objToArray($obj, &$arr){

        if(!is_object($obj) && !is_array($obj)){
            $arr = $obj;
            return $arr;
        }

        foreach ($obj as $key => $value)
        {
            if (!empty($value))
            {
                $arr[$key] = array();
                PIWebAPI::objToArray($value, $arr[$key]);
            }
            else
            {
                $arr[$key] = $value;
            }
        }
        return $arr;
    }

    public static function ExecuteBatch($postData, $err){
        global $my_base_url;

        $url = $my_base_url . "batch";

        $json_obj = PIWebAPI::ProcessJSONContent($url, 'POST', $postData, $err);

        return($json_obj);
    }

	public static function PostDataForBatchGet($urlArray){

		$postField="";
		for($i=0; $i<count($urlArray); $i++){
		    $index=$i+1;
		    if($i==0){
		        $postField="{";
		    }
		    else{
		        $postField=$postField.",";
		    }

		    $postField = $postField . "\r\n \"".$index."\": {\r\n \"Method\": \"GET\",\r\n \"Resource\": \"".$urlArray[$i]."\"\r\n  }";

		    if($i== (count($urlArray)-1)){
		        $postField = $postField . "\r\n  }";
		    }
			}

		return $postField;
	}

	public static function PostDataForBatchUpdateValue($data){
		$postField="";
		$index=0;
		for($i=0;$i<count($data);$i++){
			for($j=0;$j<count($data[$i]['Name']);$j++){

				$k=0;
				$l=0;

				if(!$data[$i]['ValuesToPost'][$j]==0){
					$index++;
					if($postField==""){
						$postField="{";
					}else{
						$postField=$postField . ",";
					}
					$postField = $postField  . "\r\n \"".$index."\": {\r\n\"Method\": \"POST\",\r\n \"Resource\": \"".$data[$i]['ValueUrl'][$j]."\",\r\n    \"Content\": \"[\r\n\t";
					while($l<$data[$i]['ValuesToPost'][$j]){

						if($data[$i]['Value'][$j][$k]==""){
							$k++;
						}else{
							$postField = $postField . "{\r\n\t \\\"Timestamp\\\": \\\"".$data[$i]['Timestamp'][$j][$k]."\\\",\r\n\t \\\"Value\\\": ".$data[$i]['Value'][$j][$k]."\r\n\t  }";
							$l++;
							$k++;

							if($l<$data[$i]['ValuesToPost'][$j]){
								$postField = $postField . ",\r\n\t ";
							}else{
								$postField = $postField . "\r\n\t  \r\n\t]\",\r\n \"Headers\": {\r\n \"Cache-Control\": \"no-cache\"\r\n }\r\n }";
							}
						}
					}
				}
			}
		}

		if(!$postField==""){
			$postField=$postField."\r\n}";
		}

		return $postField;
	}

	public static function GetJSONObject($url)
	{
        global $my_authorization_string;

		$ch = curl_init();

        curl_setopt_array($ch, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_SSL_VERIFYPEER => false,
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "Authorization: Basic ". $my_authorization_string,
            "Cache-Control: no-cache",
            "Content-Type: application/json",
            "Postman-Token: 3fd54aa5-33e4-c64d-5d7f-6e7c681814d3"
          ),
        ));

        $response = curl_exec($ch);
        $err = curl_error($ch);
		$json_o=json_decode($response);

        curl_close($ch);

        if($err){
            return("cURL Error #:".$err);
        } else{
            return ($json_o);
        }
	}


    public static function ProcessJSONContent($url, $type, $postData,  $errorCallBack)
	{
        global $my_authorization_string;

		$ch = curl_init();

        curl_setopt_array($ch, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_SSL_VERIFYPEER => false,
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_POSTFIELDS => $postData,
          CURLOPT_CUSTOMREQUEST => $type,
          CURLOPT_HTTPHEADER => array(
            "Authorization: Basic ". $my_authorization_string,
            "Cache-Control: no-cache",
            "Content-Type: application/json",
            "Postman-Token: 3fd54aa5-33e4-c64d-5d7f-6e7c681814d3"
          ),
        ));

        $response = curl_exec($ch);
        $err = curl_error($ch);
		$json_o=json_decode($response);

        curl_close($ch);

        $errorCallBack = $err;

        return $json_o;

	}
}
?>

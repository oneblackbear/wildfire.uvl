<?php
class Ebay{
  public static $url = [
    "sandbox" => "https://api.sandbox.ebay.com/wsapi",
    "production" => "https://api.ebay.com/wsapi"
  ];

  public function __callStatic($name, $params){
    $client = new SoapClient("http://developer.ebay.com/webservices/latest/ebaySvc.wsdl", array("trace"=>1));

    $auth = new stdClass();
    $auth->eBayAuthToken = $params[0]["auth_token"];

    //ebay want the auth in the body and headers
    $params[0]["data"]->RequesterCredentials = $auth;
    $client->__setSoapHeaders(new SoapHeader("urn:ebay:apis:eBLBaseComponents", "RequesterCredentials", $auth));

    //some static ebay stuff set on every request
    $params[0]["data"]->Version = 795;
    $params[0]["data"]->ErrorLanguage = "en_US";
    $params[0]["data"]->WarningLevel = "High";

    $client->__setLocation(self::$url[$params[0]["system"]]."?version=795&routing=default&callname=$name&siteid=3&appid=".$params[0]["appid"]);

    try{
      return $client->$name($params[0]["data"]);
    }catch(Exception $e){
      WaxLog::log("error", print_r($e, 1), "ebay");
    }
  }
}
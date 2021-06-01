<?php

namespace JuniorFontenele\QualitorWS;

use JuniorFontenele\QualitorWS\Exceptions\QualitorException;
use SoapClient;

abstract class QualitorWS {

  protected $tokenLogin;
  protected $client;
  protected $user;
  protected $pass;
  protected $company_id;

  public function __construct($url, $user, $pass, $company_id = 1) {
    $this->client = new SoapClient($url);
    $this->user = $user;
    $this->pass = $pass;
    $this->company_id = $company_id;
    $this->login();
  }

  protected function login() {
    try {
      $this->tokenLogin = $this->client->login($this->user, $this->pass, $this->company_id);
      return true;
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  protected function getTokenLogin() {
    return $this->tokenLogin;
  }

  private function getXmlContent(array $data, $root = 'wsqualitor') {
    $xmlArray = [
      'contents' => [
        'data' => $data
      ]
    ];
    $xml = new \SimpleXMLElement('<'.$root.'/>');
    self::addXMLData($xml, $xmlArray);

    $dom = dom_import_simplexml($xml)->ownerDocument;
    //$dom->encoding = "ISO-8859-1";
    $dom->formatOutput = true;
    return $dom->saveXML();
  }

  private static function addXMLData(\SimpleXMLElement $xml, array $data) {
    array_walk($data, function($value, $key) use($xml){
      if (is_array($value)) {
        $child = $xml->addChild($key);
        self::addXMLData($child, $value);
      } else {
        $xml->addChild($key, $value);
      }
    });
  }

  public function execute($function, $arg = null) {
    try {
      return $this->parseResponse($this->client->$function($this->tokenLogin,$this->getXmlContent($arg)));
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  protected function parseResponse(string $xmlString) {
    $xml = simplexml_load_string($xmlString, "SimpleXMLElement", LIBXML_NOCDATA);
    if ($xml->response_status->status != 1) {
      throw new QualitorException("Erro " . $xml->response_status->error_code[0] . ": " . $xml->response_status->msg);
    }
    else {
      $json = json_encode($xml);
      $array = json_decode($json, true);
      return (count($array['response_data']) > 0) ? $array['response_data']['dataitem'] : [];
    }
  }

}
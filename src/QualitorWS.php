<?php

namespace JuniorFontenele\QualitorWS;

use JuniorFontenele\QualitorWS\Exceptions\QualitorLoginException;
use JuniorFontenele\QualitorWS\Exceptions\QualitorException;
use JuniorFontenele\QualitorWS\Exceptions\QualitorResponseException;
use JuniorFontenele\QualitorWS\Exceptions\QualitorSoapException;
use SoapClient;
use SoapFault;

abstract class QualitorWS
{

    protected string $tokenLogin;
    protected SoapClient $client;
    protected string $user;
    protected string $pass;
    protected int $company_id;

    public function __construct(string $url, string $user, string $pass, int $company_id = 1)
    {
        try {
            $this->client = new SoapClient($url);
            $this->user = $user;
            $this->pass = $pass;
            $this->company_id = $company_id;
            $this->login();
        } catch (SoapFault $e) {
            throw new QualitorSoapException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function login(): void
    {
        try {
            $this->tokenLogin = $this->client->login($this->user, $this->pass, $this->company_id);
        } catch (\Exception $e) {
            throw new QualitorLoginException($e->getMessage());
        }
    }

    protected function getTokenLogin(): string
    {
        return $this->tokenLogin;
    }

    private function getXmlContent(array $data, $root = 'wsqualitor'): string
    {
        $xmlArray = [
            'contents' => [
                'data' => $data
            ]
        ];
        $xml = new \SimpleXMLElement('<' . $root . '/>');
        self::addXMLData($xml, $xmlArray);

        $dom = dom_import_simplexml($xml)->ownerDocument;
        //$dom->encoding = "ISO-8859-1";
        $dom->formatOutput = true;
        return $dom->saveXML() ?: throw new QualitorException('getXmlContent: Failed to generate XML');
    }

    private static function addXMLData(\SimpleXMLElement $xml, array $data)
    {
        array_walk($data, function ($value, $key) use ($xml) {
            if (is_array($value)) {
                $child = $xml->addChild($key);
                self::addXMLData($child, $value);
            } else {
                $xml->addChild($key, $value);
            }
        });
    }

    public function execute($function, $arg = null)
    {
        return $this->parseResponse($this->client->$function($this->tokenLogin, $this->getXmlContent($arg)));
    }

    protected function parseResponse(string $xmlString)
    {
        $xml = simplexml_load_string($xmlString, "SimpleXMLElement", LIBXML_NOCDATA);
        if ($xml->response_status->status != 1) {
            throw new QualitorResponseException("Erro " . $xml->response_status->error_code[0] . ": " . $xml->response_status->msg);
        } else {
            $json = json_encode($xml);
            $array = json_decode($json, true);
            return (count($array['response_data']) > 0) ? $array['response_data']['dataitem'] : [];
        }
    }
}

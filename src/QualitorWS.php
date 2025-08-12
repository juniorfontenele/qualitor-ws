<?php

namespace JuniorFontenele\QualitorWS;

use JuniorFontenele\QualitorWS\Exceptions\QualitorLoginException;
use JuniorFontenele\QualitorWS\Exceptions\QualitorResponseException;
use JuniorFontenele\QualitorWS\Exceptions\QualitorSoapException;
use JuniorFontenele\QualitorWS\Exceptions\QualitorXmlException;
use SoapClient;
use SoapFault;

abstract class QualitorWS
{

    protected string $tokenLogin;
    protected SoapClient $client;
    protected string $user;
    protected string $pass;
    protected int $company_id;

    /**
     * QualitorWS constructor.
     *
     * @param string $url WSDL URL for Qualitor SOAP API
     * @param string $user Username for authentication
     * @param string $pass Password for authentication
     * @param int $company_id Company ID for the API
     */
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

    /**
     * Logs in to the Qualitor SOAP service.
     *
     * @return void
     * @throws QualitorLoginException
     */
    public function login(): void
    {
        try {
            $this->tokenLogin = $this->client->login($this->user, $this->pass, $this->company_id);
        } catch (\Exception $e) {
            throw new QualitorLoginException($e->getMessage());
        }
    }

    /**
     * Returns the token used for authentication.
     *
     * @return string Token login string
     */
    protected function getTokenLogin(): string
    {
        return $this->tokenLogin;
    }

    /**
     * Generates XML content for the SOAP request.
     *
     * @param array $data Data to be included in the XML
     * @param string $root Root element name
     * @return string XML content
     * @throws QualitorXmlException
     */
    private function getXmlContent(array $data, string $root = 'wsqualitor'): string
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
        return $dom->saveXML() ?: throw new QualitorXmlException('getXmlContent: Failed to generate XML');
    }

    /**
     * Recursively adds data to the SimpleXMLElement.
     *
     * @param \SimpleXMLElement $xml
     * @param array $data
     */
    private static function addXMLData(\SimpleXMLElement $xml, array $data): void
    {
        array_walk($data, function ($value, $key) use ($xml): void {
            if (is_array($value)) {
                $child = $xml->addChild($key);
                self::addXMLData($child, $value);
            } else {
                $xml->addChild($key, $value);
            }
        });
    }

    /**
     * Executes a SOAP function and returns the parsed response.
     *
     * @param string $function Qualitor SOAP function
     * @param array|null $arg Qualitor SOAP arguments
     * @return array
     * @throws QualitorXmlException
     */
    public function execute(string $function, ?array $arg = null): array
    {
        return $this->parseResponse($this->client->$function($this->tokenLogin, $this->getXmlContent($arg)));
    }

    /**
     * Parses the XML response from the SOAP client.
     *
     * @param string $xmlString XML content
     * @return array
     * @throws QualitorXmlException
     */
    protected function parseResponse(string $xmlString): array
    {
        $xml = simplexml_load_string($xmlString, "SimpleXMLElement", LIBXML_NOCDATA) ?: throw new QualitorXmlException('parseResponse: Failed to parse XML');
        if ($xml->response_status->status != 1) {
            throw new QualitorResponseException("Erro " . $xml->response_status->error_code[0] . ": " . $xml->response_status->msg);
        } else {
            $json = json_encode($xml);
            $array = json_decode($json, true);
            return (count($array['response_data']) > 0) ? $array['response_data']['dataitem'] : [];
        }
    }
}

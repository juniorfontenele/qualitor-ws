<?php
namespace JuniorFontenele\QualitorWS;

/**
 * Class for General Webservice
 */
class ServiceCatalog extends QualitorWS {

  /**
   * Class for ServiceCatalog Webservice
   *
   * @param string $url Qualitor Webservice Base URL
   * @param string $user Qualitor username
   * @param string $pass Qualitor password
   * @param integer $company_id Qualitor company id
   */
  public function __construct(string $url, string $user, string $pass, int $company_id) {
    parent::__construct($url . '/services/ServiceCatalog/WSServiceCatalog.wsdl', $user, $pass, $company_id);
  }

  /**
   * @param int $client_id
   * @param int $contact_id
   * @return array
   */
  public function getServicesByUser(int $client_id, int $contact_id): array {
    $result = $this->execute('getServicesByUser', ['cdcliente' => $client_id, 'cdcontato' => $contact_id]);
    return $result;
  }

  /**
   * @param int $service_id
   * @return array
   */
  public function getServiceData(int $service_id): array {
    $result = $this->execute('getServiceData', ['cdservico' => $service_id]);
    return $result;
  }
}
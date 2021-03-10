<?php
namespace JuniorFontenele\QualitorWS;

/**
 * Class for General Webservice
 */
class General extends QualitorWS {

  /**
   * Class for General Webservice
   *
   * @param string $url Qualitor Webservice Base URL
   * @param string $user Qualitor username
   * @param string $pass Qualitor password
   * @param integer $company_id Qualitor company id
   */
  public function __construct(string $url, string $user, string $pass, int $company_id) {
    parent::__construct($url . '/services/General/WSGeneral.wsdl', $user, $pass, $company_id);
  }

  /**
   * @param integer $ticket_id
   * @param integer $additionalinfo_id
   * @return bool|array
   */
  public function getTicketAdditionalInformation(int $ticket_id, int $additionalinfo_id = null) {
    $query = "SELECT * FROM hd_chamadoinformacaoadicional WHERE cdchamado={$ticket_id}";
    $additionalInfos = $this->execute('getSQLQueryResult', ['dsquery' => $query]);
    if (!count($additionalInfos)) {
      return false;
    }
    $return = [];
    foreach ($additionalInfos as $info) {
      if (!is_array($info)) {
        $return[$additionalInfos['cdtipoinformacaoadicional']] = $additionalInfos['dsinformacao'] != '' ? trim($additionalInfos['dsinformacao']) : trim($additionalInfos['vlinformacaoadicional']);
        break;
      }
      else {
        if (is_array($info['dsinformacao'])) {
          break;
        }
        $return[$info['cdtipoinformacaoadicional']] = $info['dsinformacao'] != '' ? trim($info['dsinformacao']) : trim($info['vlinformacaoadicional']);
      }
    }
    if ($additionalinfo_id) {
      if (!array_key_exists($additionalinfo_id, $return)) {
        return false;
      }
      return $return[$additionalinfo_id];
    }
    return $return;
  }

  /**
   * @param string $query
   * @return array
   */
  public function getSQLQueryResult(string $query): array {
    $result = $this->execute('getSQLQueryResult', ['dsquery' => $query]);
    return $result;
  }
}
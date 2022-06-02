<?php
namespace JuniorFontenele\QualitorWS;

class Ticket extends QualitorWS {

  public function __construct($url, $user, $pass, $company_id) {
    parent::__construct($url . '/services/Ticket/WSTicket.wsdl', $user, $pass, $company_id);
  }

  public function getTickets($filter = null) {
    return $this->execute('getTicket', $filter);
  }

  public function getTicket($ticket_id) {
    $data = [
      'cdchamado' => $ticket_id,
      'campos' => 'cdchamado, nmtitulochamado, nmsituacao, nmtipochamado, nmcategoriacompleta, 
      nmequipe, dspalavrachave, dschamado, nmlocalidade, nmseveridade, nmoperador, nmresponsavel, 
      nmcliente, nmcontato, cdempresa, nmempresa, cdsituacao, cdtipochamado, 
      cdcategoria, nmcategoria, cdequipe, nmequipe, cdcliente, cdcontato'
    ];
    return $this->execute('getTicketData', $data);
  }

  public function getTicketStep($ticket_id) {
    return $this->execute('getTicketStep', ['cdchamado' => $ticket_id]);
  }

  public function getTicketNextSteps($ticket_id) {
    return $this->execute('getTicketNextSteps', ['cdchamado' => $ticket_id]);
  }

  public function cancelTicket($ticket_id, $reason) {
    return $this->execute('cancelTicket', ['cdchamado' => $ticket_id, 'dsacompanhamento' => $reason]);
  }

  public function startTicket($ticket_id) {
    return $this->execute('startTicket', ['cdchamado' => $ticket_id]);
  }

  public function closeTicket($ticket_id, $close_related_id = false) {
    $closeRelated = $close_related_id ? 'Y' : 'N';
    return $this->execute('closeTicket', ['cdchamado' => $ticket_id, 'idfecharrelacionados' => $closeRelated]);
  }

  public function addTicketHistory($ticket_id, $history, $history_type_id = 1) {
    return $this->execute('addTicketHistory', ['cdchamado' => $ticket_id, 'dsacompanhamento' => $history, 'cdtipoacompanhamento' => $history_type_id]);
  }

  public function getTicketAdditionalInfos($ticket_id) {
    return $this->execute('getTicketAdditionalInfos', ['cdchamado' => $ticket_id]);
  }

  public function setTicketNextStep($ticket_id, $step_id) {
    return $this->execute('setTicketNextStep', ['cdchamado' => $ticket_id, 'cdetapa' => $step_id]);
  }

  public function setTeam($ticket_id, $team_id) {
    return $this->execute('transferTicketTeam', ['cdchamado' => $ticket_id, 'cdequipe' => $team_id]);
  }

  public function setAdditionalInfo($ticket_id, $info_id, $info) {
    $data = [
      'cdchamado' => $ticket_id, 
      'informacoesadicionais' => [
        'vlinformacaoadicional'.$info_id => $info
      ]
    ];
    return $this->execute('changeTicketAdditionalInfo', $data);
  }
}
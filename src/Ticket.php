<?php

namespace JuniorFontenele\QualitorWS;

use JuniorFontenele\QualitorWS\Exceptions\QualitorResponseException;

class Ticket extends QualitorWS
{

    /**
     * Ticket constructor.
     *
     * @param string $url Base Webservices URL for Qualitor SOAP API (e.g. http://example.com/qualitor/ws)
     * @param string $user Username for authentication
     * @param string $pass Password for authentication
     * @param int $company_id Company ID for the API
     */
    public function __construct($url, $user, $pass, $company_id)
    {
        parent::__construct($url . '/services/Ticket/WSTicket.wsdl', $user, $pass, $company_id);
    }

    /**
     * Get Qualitor tickets using filter
     *
     * @param array|null $filter Qualitor field filters
     * @return array Tickets array
     * @throws QualitorResponseException
     */
    public function getTickets(?array $filter = null): array
    {
        return $this->execute('getTicket', $filter);
    }

    /**
     * Get a specific Qualitor ticket by ID
     *
     * @param int $ticket_id Ticket ID
     * @return array Ticket data
     * @throws QualitorResponseException
     */
    public function getTicket(int $ticket_id): array
    {
        $data = [
            'cdchamado' => $ticket_id,
            'campos' => 'cdchamado, nmtitulochamado, nmsituacao, nmtipochamado, nmcategoriacompleta, 
      nmequipe, dspalavrachave, dschamado, nmlocalidade, nmseveridade, nmoperador, nmresponsavel, 
      nmcliente, nmcontato, cdempresa, nmempresa, cdsituacao, cdtipochamado, 
      cdcategoria, nmcategoria, cdequipe, nmequipe, cdcliente, cdcontato'
        ];
        return $this->execute('getTicketData', $data);
    }

    /**
     * Get the actual step of a specific Qualitor ticket by ID
     *
     * @param int $ticket_id Ticket ID
     * @return array Ticket step
     * @throws QualitorResponseException
     */
    public function getTicketStep(int $ticket_id): array
    {
        return $this->execute('getTicketStep', ['cdchamado' => $ticket_id]);
    }

    /**
     * Get the next steps of a specific Qualitor ticket by ID
     *
     * @param int $ticket_id Ticket ID
     * @return array Ticket next steps
     * @throws QualitorResponseException
     */
    public function getTicketNextSteps(int $ticket_id): array
    {
        return $this->execute('getTicketNextSteps', ['cdchamado' => $ticket_id]);
    }

    /**
     * Cancel a specific Qualitor ticket by ID
     *
     * @param int $ticket_id Ticket ID
     * @param string $reason Reason for cancellation
     * @return array Cancellation response
     * @throws QualitorResponseException
     */
    public function cancelTicket(int $ticket_id, string $reason): array
    {
        return $this->execute('cancelTicket', ['cdchamado' => $ticket_id, 'dsacompanhamento' => $reason]);
    }

    /**
     * Start a specific Qualitor ticket by ID
     *
     * @param int $ticket_id Ticket ID
     * @return array Start ticket response
     * @throws QualitorResponseException
     */
    public function startTicket(int $ticket_id): array
    {
        return $this->execute('startTicket', ['cdchamado' => $ticket_id]);
    }

    /**
     * Close a specific Qualitor ticket by ID
     *
     * @param int $ticket_id Ticket ID
     * @param bool $close_related_id Whether to close related tickets
     * @return array Close ticket response
     * @throws QualitorResponseException
     */
    public function closeTicket(int $ticket_id, bool $close_related_id = false): array
    {
        $closeRelated = $close_related_id ? 'Y' : 'N';
        return $this->execute('closeTicket', ['cdchamado' => $ticket_id, 'idfecharrelacionados' => $closeRelated]);
    }

    /**
     * Add history to a specific Qualitor ticket by ID
     *
     * @param int $ticket_id Ticket ID
     * @param string $history History text
     * @param int $history_type_id History type ID
     * @return array Add history response
     * @throws QualitorResponseException
     */
    public function addTicketHistory(int $ticket_id, string $history, int $history_type_id = 1): array
    {
        return $this->execute('addTicketHistory', ['cdchamado' => $ticket_id, 'dsacompanhamento' => $history, 'cdtipoacompanhamento' => $history_type_id]);
    }

    /**
     * Get all additional information fields for a specific Qualitor ticket by ID
     *
     * @param int $ticket_id Ticket ID
     * @return array Ticket additional information fields
     * @throws QualitorResponseException
     */
    public function getTicketAdditionalInfos(int $ticket_id): array
    {
        return $this->execute('getTicketAdditionalInfos', ['cdchamado' => $ticket_id]);
    }

    /**
     * Get the details of a specific additional information field for a Qualitor ticket by additional info ID
     *
     * @param int $additional_info_id Additional information ID
     * @return array Additional information details
     * @throws QualitorResponseException
     */
    public function getTicketAdditionalInfoDetail(int $additional_info_id): array
    {
        return $this->execute('getTicketAdditionalInfoDetail', ['cdtipoinformacaoadicional' => $additional_info_id]);
    }

    /**
     * Set the next step for a specific Qualitor ticket by ID
     *
     * @param int $ticket_id Ticket ID
     * @param int $step_id Step ID
     * @return array Set next step response
     * @throws QualitorResponseException
     */
    public function setTicketNextStep(int $ticket_id, int $step_id): array
    {
        return $this->execute('setTicketNextStep', ['cdchamado' => $ticket_id, 'cdetapa' => $step_id]);
    }

    /**
     * Transfer a specific Qualitor ticket to a different team by ID
     *
     * @param int $ticket_id Ticket ID
     * @param int $team_id Team ID
     * @return array Transfer team response
     * @throws QualitorResponseException
     */
    public function setTeam(int $ticket_id, int $team_id): array
    {
        return $this->execute('transferTicketTeam', ['cdchamado' => $ticket_id, 'cdequipe' => $team_id]);
    }

    /**
     * Set additional information for a specific Qualitor ticket by ID
     *
     * @param int $ticket_id Ticket ID
     * @param int $info_id Additional information ID
     * @param string $info Additional information value
     * @return array Set additional information response
     * @throws QualitorResponseException
     */
    public function setAdditionalInfo(int $ticket_id, int $info_id, string $info): array
    {
        $data = [
            'cdchamado' => $ticket_id,
            'informacoesadicionais' => [
                'vlinformacaoadicional' . $info_id => $info
            ]
        ];
        return $this->execute('changeTicketAdditionalInfo', $data);
    }

    /**
     * Add a new ticket by client and contact ID
     *
     * @param int $client_id Client ID
     * @param int $contact_id Contact ID
     * @param array $ticket_data Ticket data
     * @return array Add ticket response
     * @throws QualitorResponseException
     */
    public function addTicketByData(int $client_id, int $contact_id, array $ticket_data): array
    {
        $ticketData = array_merge($ticket_data, [
            'cdcliente' => $client_id,
            'cdcontato' => $contact_id
        ]);
        return $this->execute('addTicketByData', $ticketData);
    }
}

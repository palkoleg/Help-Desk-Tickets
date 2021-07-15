<?php

include_once 'TicketDTO.php';

class CSVService extends TicketDTO
{

    private string $nameOfFile;

    public function __construct()
    {
        $this->nameOfFile = 'tickets.csv';
    }


    public function getNameOfFile(): string
    {
        return $this->nameOfFile;
    }


    public function headersToCSV(): void
    {
        $headers = [
            'Ticket ID',
            'Description',
            'Status',
            'Priority',
            'Agent ID',
            'Agent Name',
            'Agent Email',
            'Contact ID',
            'Contact Name',
            'Contact Email',
            'Group ID',
            'Group Name',
            'Company ID',
            'Company Name',
            'Comments'
        ];

        $fh = fopen($this->nameOfFile, 'w');
        fputcsv($fh, $headers);
        fclose($fh);
    }

    public function saveArrayToCSV(array $ticketDTOs): void
    {
        $fh = fopen($this->nameOfFile, 'a');

        foreach ($ticketDTOs as $ticket) {
            fputcsv($fh, [
                $ticket->getTicketID(),
                $ticket->getDescription(),
                $ticket->getStatus(),
                $ticket->getPriority(),
                $ticket->getAgentID(),
                $ticket->getAgentName(),
                $ticket->getAgentEmail(),
                $ticket->getContactID(),
                $ticket->getContactName(),
                $ticket->getContactEmail(),
                $ticket->getGroupID(),
                $ticket->getThisGroupName(),
                $ticket->getCompanyID(),
                $ticket->getCompanyName(),
                $ticket->getThisComments()
            ]);
        }

        fclose($fh);
    }
}
?>
<?php


require __DIR__ . '/vendor/autoload.php';

class Tickets_to_CSV
{
public $subdomain = 'palkoleg';
public $version = 'v2';
public $username = 'poleg1997@gmail.com' . "/token";
public $password  = '9o8Hc2eLPRtK4k56bjsmtHoHQdMuZ1wPdenI0teB'; //Token

public function connect_to_api_and_save_response ($subdomain, $version, $username, $password)
{
    $client = new GuzzleHttp\Client();

    $response = $client->request('GET', 'https://'.$subdomain.'.zendesk.com/api/'.$version.'/tickets.json',
        [
            'auth'  => [ $username, $password],
        ]);
    return json_decode($response->getBody(), true);
}

    public function save_array_to_csv ($array)
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

        $fh = fopen('tickets.csv', 'w');
        fputcsv($fh, $headers);

        foreach ($array['tickets'] as $ticket) {
            fputcsv($fh, [
                $ticket['id'],
                $ticket['description'],
                $ticket['status'],
                $ticket['priority'],
                $ticket['submitter_id'],
                $ticket['to'],
                $ticket['submitter_email'],
                $ticket['requester_id'],
                $ticket['from'],
                $ticket['requester_email'],
                $ticket['group_id'],
                $ticket['group_name'],
                $ticket['organization_id'],
                $ticket['organization_name'],
                $ticket['comments']
            ]);
        }

        fclose($fh);
        print_r('Успішно записано у CSV-файл');
    }


}

    $my_ticket = new Tickets_to_CSV();

    $jsonDecoded = $my_ticket->connect_to_api_and_save_response($my_ticket->subdomain, $my_ticket->version, $my_ticket->username,$my_ticket->password);
    /*print_r('<pre>');
    print_r($jsonDecoded);
    print_r('</pre>');*/

    $my_ticket->save_array_to_csv($jsonDecoded);
?>
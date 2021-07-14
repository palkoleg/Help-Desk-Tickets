<?php


require __DIR__ . '/vendor/autoload.php';

class Tickets
{
    private $isToken;
    private $subdomain;
    private $version;
    private $username;
    private $password;

    public function __construct($isToken,$subdomain,$version,$username,$password)
    {
        $this->isToken = $isToken;
        $this->subdomain = $subdomain;
        $this->version = $version;
        $this->username = $this->isToken ? $username . "/token" : $username;
        $this->password = $password;
    }

    public function getSubdomain()
    {
    return $this->subdomain;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getUserName()
    {
        return $this->username;
    }

    public function getPassword()
    {
        return $this->password;
    }



    public function connect_to_api_and_save_response ($subdomain, $version, $username, $password)
    {
        $client = new GuzzleHttp\Client();

        try {
            $response = $client->request('GET', 'https://' . $subdomain . '.zendesk.com/api/' . $version . '/tickets.json?page[size]=100',
                [
                    'auth' => [$username, $password],
                ]);

            $result = json_decode($response->getBody(), true);
            $count = 1;
            $hasMore = $result["meta"]["has_more"];
            $next = $result["links"]["next"];
            $this->save_array_to_csv($result, $count);

            while($hasMore)
            {
                $response = $client->request('GET', $next,
                    [
                        'auth' => [$username, $password],
                    ]);

                $result = json_decode($response->getBody(), true);
                $count++;
                $hasMore = $result["meta"]["has_more"];
                $next = $result["links"]["next"];
                $this->save_array_to_csv($result, $count);
            }

        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
        }

    }

    private function getUserNameAndEmail($userID)
    {
        $client = new GuzzleHttp\Client();

        try {
            $response = $client->request('GET', 'https://' . $this->subdomain . '.zendesk.com/api/' . $this->version . '/users/' . $userID . '.json',
                [
                    'auth' => [$this->username, $this->password],
                ]);
            return [json_decode($response->getBody(), true)["user"]["name"], json_decode($response->getBody(), true)["user"]["email"]];
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
        }
    }


    private function getGroupName($groupID)
    {
        $client = new GuzzleHttp\Client();

        try {
            $response = $client->request('GET', 'https://' . $this->subdomain . '.zendesk.com/api/' . $this->version . '/groups/' . $groupID . '.json',
                [
                    'auth' => [$this->username, $this->password],
                ]);
            return json_decode($response->getBody(), true)["group"]["name"];
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
        }
    }

    private function getOrganizationName($organizationID)
    {
        $client = new GuzzleHttp\Client();

        try {
            $response = $client->request('GET', 'https://' . $this->subdomain . '.zendesk.com/api/' . $this->version . '/organizations/' . $organizationID . '.json',
                [
                    'auth' => [$this->username, $this->password],
                ]);
            return json_decode($response->getBody(), true)["organization"]["name"];
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
        }
    }


    private function getComments($ticketID)
    {
        $client = new GuzzleHttp\Client();

        try {
            $response = $client->request('GET', 'https://' . $this->subdomain . '.zendesk.com/api/' . $this->version . '/tickets/' . $ticketID . '/comments.json',
                [
                    'auth' => [$this->username, $this->password],
                ]);
            $comment_str = '';
            foreach(json_decode($response->getBody(), true)["comments"] as $comment) {
                $comment_str .=  $comment["body"] ."\n";
            }
            return substr($comment_str,0,-1);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
        }
    }


    private function save_array_to_csv ($array, $count)
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


        if ($count == 1)
        {
            $fh = fopen('tickets.csv', 'w');
            fputcsv($fh, $headers);
        }
        else
        {
            $fh = fopen('tickets.csv', 'a');
        }

        foreach ($array['tickets'] as $ticket) {
            fputcsv($fh, [
                $ticket['id'],
                $ticket['description'],
                $ticket['status'],
                $ticket['priority'],
                $ticket['submitter_id'],
                $this->getUserNameAndEmail($ticket['submitter_id'])[0],
                $this->getUserNameAndEmail($ticket['submitter_id'])[1],
                $ticket['requester_id'],
                $this->getUserNameAndEmail($ticket['requester_id'])[0],
                $this->getUserNameAndEmail($ticket['requester_id'])[1],
                $ticket['group_id'],
                $this->getGroupName($ticket['group_id']),
                $ticket['organization_id'],
                $this->getOrganizationName($ticket['organization_id']),
                $this->getComments($ticket['id'])
            ]);
        }

        fclose($fh);
        print_r('Успішно записано у CSV-файл');
    }


}

$my_tickets = new Tickets(true, 'palkoleghelp', 'v2', 'palkoleg1997@gmail.com', 'HKkUGVcuaBu0F9psl8YjmMSIIKxpuhGWJEvBjs3o');

$jsonDecoded = $my_tickets->connect_to_api_and_save_response($my_tickets->getSubdomain(), $my_tickets->getVersion(), $my_tickets->getUserName(),$my_tickets->getPassword());
<?php

class APIService
{
    private bool $isToken;
    private string $subdomain;
    private string $version;
    private string $username;
    private string $password;
    private string $protocol;

    public function __construct(bool $isToken, string $subdomain, string $username, string $password)
    {
        $this->isToken = $isToken;
        $this->subdomain = $subdomain;
        $this->version = 'v2';
        $this->username = $this->isToken ? $username . "/token" : $username;
        $this->password = $password;
        $this->protocol = 'https://';
    }

    public function getSubdomain(): string
    {
        return $this->subdomain;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getUserName(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getProtocol(): string
    {
        return $this->protocol;
    }


    private function getUserNameAndEmail($userID): array
    {
        $client = new GuzzleHttp\Client();
        $response = [];
        try {
            $response = $client->request('GET', $this->protocol . $this->subdomain . '.zendesk.com/api/' . $this->version . '/users/' . $userID . '.json',
                [
                    'auth' => [$this->username, $this->password],
                ]);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            echo $e;
        }

        $name = json_decode($response->getBody(), true)["user"]["name"] == NULL ? '' : json_decode($response->getBody(), true)["user"]["name"] ;
        $email = json_decode($response->getBody(), true)["user"]["email"] == NULL ? '' : json_decode($response->getBody(), true)["user"]["email"];
        return [$name, $email];
    }

    private function getGroupName($groupID): string
    {
        $client = new GuzzleHttp\Client();
        $response = [];
        try {
            $response = $client->request('GET', 'https://' . $this->subdomain . '.zendesk.com/api/' . $this->version . '/groups/' . $groupID . '.json',
                [
                    'auth' => [$this->username, $this->password],
                ]);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            echo $e;
        }

        $name = json_decode($response->getBody(), true)["group"]["name"] == NULL ? '' : json_decode($response->getBody(), true)["group"]["name"];
        return $name;
    }

    private function getOrganizationName($organizationID): string
    {
        $client = new GuzzleHttp\Client();
        $response = [];
        try {
            $response = $client->request('GET', 'https://' . $this->subdomain . '.zendesk.com/api/' . $this->version . '/organizations/' . $organizationID . '.json',
                [
                    'auth' => [$this->username, $this->password],
                ]);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            echo $e;
        }

        $name = json_decode($response->getBody(), true)["organization"]["name"] == NULL ? '' : json_decode($response->getBody(), true)["organization"]["name"];
        return $name;
    }

    private function getComments($ticketID): string
    {
        $client = new GuzzleHttp\Client();
        $response = [];
        try {
            $response = $client->request('GET', 'https://' . $this->subdomain . '.zendesk.com/api/' . $this->version . '/tickets/' . $ticketID . '/comments.json',
                [
                    'auth' => [$this->username, $this->password],
                ]);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            echo $e;
        }
        $comment_str = '';
        foreach(json_decode($response->getBody(), true)["comments"] as $comment) {
            $comment_str .=  $comment["body"] ."\n";
        }
        return substr($comment_str,0,-1);
    }


    public function firstResponse(): object
    {
        $client = new GuzzleHttp\Client();
        $response = [];
        try {
            $response = $client->request('GET', $this->protocol . $this->subdomain . '.zendesk.com/api/' . $this->version . '/tickets.json?page[size]=100',
                [
                    'auth' => [$this->username, $this->password],
                ]);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            echo $e;
        }
        return $response;
    }

    public function nextResponse($next): object
    {
        $client = new GuzzleHttp\Client();
        $response = [];
        try {
            $response = $client->request('GET', $next,
                [
                    'auth' => [$this->username, $this->password],
                ]);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            echo $e;
        }
        return $response;
    }

    public function SaveResponse($response): array
    {
        $data = json_decode($response->getBody(), true);

        $tickets = array();

        foreach ($data['tickets'] as $ticket) {
           $oneTicket = new TicketDTO(
               $ticket['id'],
               $ticket['description'] == NULL ? '' : $ticket['description'],
               $ticket['status'],
               $ticket['priority'] == NULL ? '' : $ticket['priority'],
               $ticket['submitter_id'],
               $this->getUserNameAndEmail($ticket['submitter_id'])[0],
               $this->getUserNameAndEmail($ticket['submitter_id'])[1],
               $ticket['requester_id'],
               $this->getUserNameAndEmail($ticket['requester_id'])[0],
               $this->getUserNameAndEmail($ticket['requester_id'])[1],
               $ticket['group_id'],
               $this->getGroupName($ticket['group_id']),
               $ticket['organization_id'] == NULL ? 0 : $ticket['organization_id'],
               $this->getOrganizationName($ticket['organization_id']),
               $this->getComments($ticket['id'])
           );
                $tickets[] = $oneTicket;
        }

        return [$tickets, $data["meta"]["has_more"], $data["links"]["next"]];
    }
}
?>
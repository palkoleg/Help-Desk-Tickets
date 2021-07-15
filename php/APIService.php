<?php

use GuzzleHttp\Exception\GuzzleException;

include_once 'TicketDTO.php';

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
        $this->username = $this->isToken ? $username . '/token' : $username;
        $this->password = $password;
        $this->protocol = 'https://';
    }

    public function firstResponse(): object
    {
        $client = new GuzzleHttp\Client();
        $response = [];
        try {
            $response = $client->request(
                'GET', $this->protocol . $this->subdomain . '.zendesk.com/api/' . $this->version . '/tickets.json?page[size]=100',
                ['auth' => [$this->username, $this->password]]
            );
        } catch (GuzzleException $e) {
            echo $e;
        }
        return $response;
    }

    public function nextResponse($next): object
    {
        $client = new GuzzleHttp\Client();
        $response = [];
        try {
            $response = $client->request(
                'GET',
                $next,
                ['auth' => [$this->username, $this->password]]
            );
        } catch (GuzzleException $e) {
            echo $e;
        }
        return $response;
    }

    public function getResponse($response): array
    {
        $data = json_decode($response->getBody(), true);

        $tickets = [];
        foreach ($data['tickets'] as $ticket) {
            $oneTicket = new TicketDTO(
                $ticket['id'],
                $ticket['description'] ?? '',
                $ticket['status'],
                $ticket['priority'] ?? '',
                $ticket['submitter_id'],
                $this->getUserNameAndEmail($ticket['submitter_id'])[0],
                $this->getUserNameAndEmail($ticket['submitter_id'])[1],
                $ticket['requester_id'],
                $this->getUserNameAndEmail($ticket['requester_id'])[0],
                $this->getUserNameAndEmail($ticket['requester_id'])[1],
                $ticket['group_id'],
                $this->getGroupName($ticket['group_id']),
                $ticket['organization_id'] ?? 0,
                $this->getOrganizationName($ticket['organization_id']),
                $this->getComments($ticket['id'])
            );
            $tickets[] = $oneTicket;
        }

        return [$tickets, $data['meta']['has_more'], $data['links']['next']];
    }

    private function getUserNameAndEmail($userID): array
    {
        $client = new GuzzleHttp\Client();
        $response = [];
        try {
            $response = $client->request(
                'GET', $this->protocol . $this->subdomain . '.zendesk.com/api/' . $this->version . '/users/' . $userID . '.json',
                ['auth' => [$this->username, $this->password]]
            );
        } catch (GuzzleException $e) {
            echo $e;
        }

        $data = json_decode($response->getBody(), true);
        $name = $data['user']['name'] ?? '';
        $email = $data['user']['email'] ?? '';

        return [$name, $email];
    }

    private function getGroupName($groupID): string
    {
        $client = new GuzzleHttp\Client();
        $response = [];
        try {
            $response = $client->request(
                'GET', 'https://' . $this->subdomain . '.zendesk.com/api/' . $this->version . '/groups/' . $groupID . '.json',
                ['auth' => [$this->username, $this->password]]
            );
        } catch (GuzzleException $e) {
            echo $e;
        }

        $data = json_decode($response->getBody(), true);
        return $data['group']['name'] ?? '';
    }

    private function getOrganizationName($organizationID): string
    {
        $client = new GuzzleHttp\Client();
        $response = [];
        try {
            $response = $client->request(
                'GET', 'https://' . $this->subdomain . '.zendesk.com/api/' . $this->version . '/organizations/' . $organizationID . '.json',
                ['auth' => [$this->username, $this->password]]
            );
        } catch (GuzzleException $e) {
            echo $e;
        }

        $data = json_decode($response->getBody(), true);
        return $data['organization']['name'] ?? '';
    }

    private function getComments($ticketID): string
    {
        $client = new GuzzleHttp\Client();
        $response = [];
        try {
            $response = $client->request(
                'GET', 'https://' . $this->subdomain . '.zendesk.com/api/' . $this->version . '/tickets/' . $ticketID . '/comments.json',
                ['auth' => [$this->username, $this->password]]
            );
        } catch (GuzzleException $e) {
            echo $e;
        }
        $fullComment = '';
        foreach(json_decode($response->getBody(), true)['comments'] as $comment) {
            $fullComment .=  $comment['body'] . PHP_EOL;
        }
        return substr($fullComment, 0, -1);
    }
}

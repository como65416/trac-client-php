<?php

namespace Comoco\TracClientPhp;

use Comoco\TracClientPhp\TracApiHandler;

class Client
{
    /**
     * @var Comoco\TracClientPhp\TracApiHandler
     */
    protected $tracApiHandler = null;

    /**
     * @param string $api_url json rpc api url. Ex: http://trac.local/login/jsonrpc
     * @param string $username login username
     * @param string $password login password
     */
    public function __construct($api_url, $username, $password)
    {
        $this->tracApiHandler = new TracApiHandler($api_url, $username, $password);
    }

    /**
     * get user own ticket id
     *
     * @param string $username target username
     * @param array $statuses array of status
     * @param integer $limit max ticket amount
     * @return array array of ticket id
     */
    public function getUserTicketIds($username, array $statuses, $limit = 100)
    {
        $query_params = [];
        $query_params[] = "owner=" . $username;
        $query_params[] = "max=" . $limit;
        foreach ($statuses as $status) {
            $query_params[] = "status=" . $status;
        }

        return $this->tracApiHandler->call("ticket.query", [
            implode("&", $query_params)
        ]);
    }

    /**
     * get ticket information
     *
     * @param int $ticket_id ticket id
     * @return array ticket infomation (it is different based on installed module)
     */
    public function getTicketInfo($ticket_id)
    {
        $response = $this->tracApiHandler->call("ticket.get", [
            $ticket_id
        ]);

        $ticket_data = [];
        $ticket_data['time_created'] = $response[1]['__jsonclass__'][1];
        $ticket_data['time_changed'] = $response[2]['__jsonclass__'][1];
        if (isset($response[3]['changetime'])) {
            unset($response[3]['changetime']);
        }
        if (isset($response[3]['time'])) {
            unset($response[3]['time']);
        }
        $ticket_data = array_merge($ticket_data, $response[3]);
        return $ticket_data;
    }

    /**
     * create a new ticket
     *
     * @param string $summary ticket summary
     * @param string $description ticket description
     * @param array  $attributes ticket attributes (it is different based on installed module)
     * Ex: [
     *      'owner' => 'jack',
     *      'cc' => 'alice, bob',
     *      'priority' => 'minor'
     * ]
     * @return int created ticket id
     */
    public function createTicket($summary, $description = '', array $attributes = [])
    {
        return $this->tracApiHandler->call("ticket.create", [
            $summary,
            $description,
            $attributes
        ]);
    }

    /**
     * update ticket infomation
     *
     * @param int $ticket_id ticket id
     * @param string $comment comment
     * @param array  $attributes ticket attributes (it is different based on installed module)
     * Ex: [
     *      'owner' => 'jack',
     *      'cc' => 'alice, bob',
     *      'priority' => 'minor'
     * ]
     */
    public function updateTicket($ticket_id, $comment = '', array $attributes = [])
    {
        $this->tracApiHandler->call("ticket.update", [
            $ticket_id,
            $comment,
            $attributes
        ]);
    }

    /**
     * accept the ticket
     *
     * @param int $ticket_id ticket id
     * @param string $comment comment
     */
    public function acceptTicket($ticket_id, $comment = '')
    {
        $this->tracApiHandler->call("ticket.update", [
            $ticket_id,
            $comment,
            [
                'action' => 'accept'
            ]
        ]);
    }

    /**
     * reassign ticket to the user
     *
     * @param int $ticket_id ticket id
     * @param string $username username
     * @param string $comment comment
     */
    public function reassignOwner($ticket_id, $username, $comment = '')
    {
        $this->tracApiHandler->call("ticket.update", [
            $ticket_id,
            $comment,
            [
                'action' => 'reassign',
                'action_reassign_reassign_owner' => $username
            ]
        ]);
    }

    /**
     * resolve ticket
     *
     * @param int $ticket_id ticket id
     * @param string $comment comment
     * @param string $option resolve option (it is different based on trac setting)
     */
    public function resolveTicket($ticket_id, $comment = '', $option = 'fixed')
    {
        $this->tracApiHandler->call("ticket.update", [
            $ticket_id,
            $comment,
            [
                'action' => 'resolve',
                'action_resolve_resolve_resolution' => $option
            ]
        ]);
    }

    /**
     * reopen ticket
     *
     * @param int $ticket_id ticket id
     * @param string $comment comment
     */
    public function reopenTicket($ticket_id, $comment = '')
    {
        $this->tracApiHandler->call("ticket.update", [
            $ticket_id,
            $comment,
            [
                'action' => 'reopen'
            ]
        ]);
    }

    /**
     * delete ticket
     *
     * @param int $ticket_id ticket id
     */
    public function deleteTicket($ticket_id)
    {
        $this->tracApiHandler->call("ticket.delete", [
            $ticket_id
        ]);
    }

    /**
     * add comment to the ticket
     *
     * @param string $username username
     * @param string $comment comment
     */
    public function addComment($ticket_id, $comment)
    {
        $this->tracApiHandler->call("ticket.update", [
            $ticket_id,
            $comment
        ]);
    }

    /**
     * get the ticket's comments
     *
     * @param string $username username
     * @param string $comment comment
     * @return array array of comment datas
     * structure : [
     *      [
     *          'comment_id' => id,
     *          'updated_time' => datetime,
     *          'user' => username,
     *          'comment' => comment,
     *          'action' => [
     *              [
     *                  'type' => type_name,
     *                  ...
     *              ]
     *          ]
     *      ],
     *      ...
     * ]
     */
    public function getComments($ticket_id)
    {
        $response = $this->tracApiHandler->call("ticket.changeLog", [
            $ticket_id
        ]);

        $comments = [];
        foreach ($response as $info) {
            if ($info[2] == 'comment' && !empty($info[3])) {
                $comments[] = [
                    'comment_id' => $info[3],
                    'updated_time' => $info[0]['__jsonclass__'][1],
                    'user' => $info[1],
                    'comment' => $info[4],
                    'action' => []
                ];
            } elseif (preg_match('/_.*?/', $info[2])) {
                // do nothing
            } elseif (!in_array($info[2], ['comment', 'attachment', 'resolution'])) {
                $comments[count($comments) - 1]['action'][] = [
                    'type' => 'change_' . $info[2],
                    'old' => $info[3],
                    'new' => $info[4]
                ];
            }
        }
        return $comments;
    }

    /**
     * list ticket's attachments
     *
     * @param int $ticket_id ticket id
     * @return array array of attachment
     * structure : [
     *      [
     *          'filename' => filename,
     *          'description' => description,
     *          'size' => size,
     *          'updated_time' => updated_time,
     *          'user' => username
     *      ],
     *      ...
     * ]
     */
    public function listAttachments($ticket_id)
    {
        $attachments = [];
        $response = $this->tracApiHandler->call("ticket.listAttachments", [
            $ticket_id
        ]);
        foreach ($response as $info) {
            $attachments[] = [
                'filename' => $info[0],
                'description' => $info[1],
                'size' => $info[2],
                'updated_time' => $info[3]['__jsonclass__'][1],
                'user' => $info[4]
            ];
        }
        return $attachments;
    }

    /**
     * upload a attachment to ticket
     *
     * @param int $ticket_id ticket id
     * @param string $filename file name
     * @param string $description file description
     * @param string $upload_fila_path file path
     */
    public function uploadAttachment($ticket_id, $filename, $description, $upload_fila_path)
    {
        $content = file_get_contents($upload_fila_path);
        $binary_content = base64_encode($content);
        $data = [
            '__jsonclass__' => [
                'binary',
                $binary_content
            ]
        ];

        $response = $this->tracApiHandler->call("ticket.putAttachment", [
            $ticket_id,
            $filename,
            $description,
            $data
        ]);
    }

    /**
     * download the attachment from ticket
     *
     * @param int $ticket_id ticket id
     * @param string $filename file name
     * @param string $save_path file save path
     */
    public function downloadAttachment($ticket_id, $filename, $save_path)
    {
        $response = $this->tracApiHandler->call("ticket.getAttachment", [
            $ticket_id,
            $filename
        ]);
        $data = base64_decode($response['__jsonclass__'][1]);
        file_put_contents($save_path, $data);
    }
}

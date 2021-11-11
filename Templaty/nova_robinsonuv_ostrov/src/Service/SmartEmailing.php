<?php

namespace App\Service;

/**
 * Class SmartEmailing
 * @package App\Service
 */
class SmartEmailing
{
    // smart mailing API requests
    const
        REQUEST_CHECK_AUTH = 'check-credentials',
        REQUEST_CONTACT_LISTS = 'contactlists',
        REQUEST_CONTACTS = 'contacts',
        REQUEST_IMPORT = 'import',
        REQUEST_EMAIL = 'send/custom-emails';

    /** @var string */
    private $apiUser;

    /** @var string */
    private $apiKey;

    /** @var string */
    private $apiUrl = 'https://app.smartemailing.cz/api/v3';

    /** @var string */
    private $request = '';

    /** @var array */
    private $requestBody = [];

    /** @var array */
    private $response = [];

    /**
     * @param string $apiUser
     * @param string $apiKey
     * @param null|string $apiUrl
     */
    public function __construct(string $apiUser, string $apiKey, $apiUrl = null)
    {
        if (empty($apiUser) || empty($apiKey))
            throw new \InvalidArgumentException('Invalid argument has been entered.');

        $this->apiUser = $apiUser;
        $this->apiKey = $apiKey;

        if (!empty($apiUrl)) {
            $this->apiUrl = $apiUrl;
        }
    }

    /**
     * @return bool
     */
    public function checkAuthorization(): bool
    {
        try {
            $this->call(self::REQUEST_CHECK_AUTH);
            return true;
        } catch (\UnexpectedValueException $e) {
            return false;
        }
    }

    /**
     * @return array
     */
    public function getContactListsList() {
        $response = $this->call(self::REQUEST_CONTACT_LISTS);
        $contactList = [];

        if (!isset($response['data']) && !is_array($response['data']))
            return [];

        foreach ($response['data'] as $dataItem) {
            $contactList[$dataItem['id']] = $dataItem['name'];
        }

        return $contactList;
    }

    /**
     * @param string $email
     * @param int|array $group
     * @param array $additional
     */
    public function addToContactList($email, $group, $additional = []) {
        if (empty($email) || empty($group))
            throw new \InvalidArgumentException('Invalid argument has been entered.');

        if (($list = $this->getContactLists($email)) === null) {
            // new
            $addList = [];
            $group = (array)$group;

            foreach ($group as $groupId) {
                $addList[] = [
                    'id' => $groupId,
                    'status' => 'confirmed',
                ];
            }

            $this->call(self::REQUEST_IMPORT, [
                'settings' => [
                    'update' => true,
                ],
                'data' => [
                    [
                        'emailaddress' => $email,
                        'contactlists' => $addList,
                        'name' => isset($additional['name']) ? $additional['name'] : null,
                        'surname' => isset($additional['surname']) ? $additional['surname'] : null,
                    ],
                ],
            ]);

        } else {
            // update
            $addList = [];
            $group = (array)$group;

            foreach ($group as $groupId) {
                if (!isset($list[$groupId])) {
                    $addList[] = [
                        'id' => $groupId,
                        'status' => 'confirmed',
                    ];
                }
            }

            if (empty($addList))
                return;

            $this->call(self::REQUEST_IMPORT, [
                'settings' => [
                    'update' => false,
                ],
                'data' => [
                    [
                        'emailaddress' => $email,
                        'contactlists' => $addList,
                    ],
                ],
            ]);
        }
    }

    /**
     * @param string $email
     * @param int|array $group
     */
    public function deleteFromContactList($email, $group) {
        if (empty($email) || empty($group))
            throw new \InvalidArgumentException('Invalid argument has been entered.');

        if (($list = $this->getContactLists($email)) === null)
            throw new \UnexpectedValueException("Can not find $email email.");

        $group = (array)$group;
        $removeList = [];

        foreach ($group as $groupId) {
            if (isset($list[$groupId])) {
                $removeList[] = [
                    'id' => $groupId,
                    'status' => 'removed',
                ];
            }
        }

        if (empty($removeList))
            return;

        $this->call(self::REQUEST_IMPORT, [
            'settings' => [
                'update' => false,
            ],
            'data' => [
                [
                    'emailaddress' => $email,
                    'contactlists' => $removeList,
                ],
            ],
        ]);
    }

    /**
     * @param string $email
     * @return array|null
     */
    public function getContactLists($email) {
        if (empty($email))
            throw new \InvalidArgumentException('Invalid argument has been entered.');

        $getData = [
            'emailaddress' => $email,
        ];

        $data = $this->call(self::REQUEST_CONTACTS . '?' . http_build_query($getData));

        // user not found
        if (empty($data['data']))
            return null;
        // we expect only one parameter, so index is 0
        if (!isset($data['data'][0]['contactlists']))
            return [];

        $contactList = [];

        foreach ($data['data'][0]['contactlists'] as $item) {
            $contactList[$item['contactlist_id']] = $item['status'];
        }

        return $contactList;
    }

    /**
     * @param array $data
     * <code>
     * $data = [
     *     [
     *         'sender' => 'Martin <martin@smartemailing.cz>',
     *         'recipient' => 'Václav <vaclav@smartemailing.cz>',
     *         'tag' => 'custom_tag',
     *         'subject' => 'new registration',
     *         'body' => '<h1>email<h1>',
     *     ],
     *     ...
     * ];
     * </code>
     */
    public function sendCustomEmail(array $data)
    {
        $emails = [];

        if (!isset($data[0]) || !is_array($data[0]))
            $data = [$data];

        foreach ($data as $dataItem) {
            if (empty($dataItem['sender']) || empty($dataItem['recipient']) || empty($dataItem['tag']))
                throw new \InvalidArgumentException('Missing required item.');

            $sender = $this->formatEmails($dataItem['sender']);
            $recipient = $this->formatEmails($dataItem['recipient']);

            try {
                $emails[] = [
                    'custom_id' => \App\Utils\Random::generate(20),
                    'sendername' => $sender['name'],
                    'senderemail' => $sender['email'],
                    'recipientname' => $recipient['name'],
                    'recipientemail' => $recipient['email'],
                    'tag' => $dataItem['tag'],
                    'email' => [
                        'subject' => $dataItem['subject'],
                        'htmlbody' => $dataItem['body'],
                    ],
                ];
            } catch (\InvalidArgumentException $e) {
                throw $e;
            }
        }

        if (count($emails) > 0) {
            $this->call(self::REQUEST_EMAIL, ['batch' => $emails]);
        }
    }

    // debugging purpose -----------------------------------------------------------------------------------------------

    /**
     * @return string
     */
    public function getRequest(): string
    {
        return $this->request;
    }

    /**
     * @return array
     */
    public function getRequestBody(): array
    {
        return $this->requestBody;
    }

    /**
     * @return array
     */
    public function getResponse(): array
    {
        return $this->response;
    }

    // private ---------------------------------------------------------------------------------------------------------

    /**
     * @param string $request
     * @param array $data
     * @return array
     */
    private function call($request, array $data = []) {
        if (empty($request))
            throw new \InvalidArgumentException('Invalid argument has been entered.');

        $this->request = "$this->apiUrl/$request";
        $this->requestBody = $data;

        $r = curl_init();
        curl_setopt($r, CURLOPT_URL, $this->request);
        curl_setopt($r, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($r, CURLOPT_HEADER, false);
        if (!empty($data)) {
            curl_setopt($r, CURLOPT_POST, true);
            curl_setopt($r, CURLOPT_POSTFIELDS, json_encode($data));
        }
        curl_setopt($r,CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . base64_encode("$this->apiUser:$this->apiKey"),
            'Content-Type: application/json',
        ]);
        $response = curl_exec($r);
        curl_close($r);

        $this->response = $response = json_decode($response, true);

        if (!isset($response['status'])
            || (($response['status'] != 'ok') && $response['status'] != 'created')) {
            $data = serialize($data);
            $response = serialize($response);
            throw new \UnexpectedValueException("Authentication failed or bad request. Request=$request, data=$data, response=$response.");
        }

        return $response;
    }

    /**
     * @param string $email
     * @return array
     */
    private function formatEmails(string $email): array
    {
        if (preg_match('#^(.+) +<(.*)>\z#', $email, $matches)) {
            return [
                'name' => trim($matches[1]),
                'email' => trim($matches[2]),
            ];
        } else {
            return [
                'name' => $email,
                'email' => $email,
            ];
        }
    }

    /**
     * @param int $length
     * @param string $charlist
     * @return string
     */
    private function generateId($length = 20, $charlist = '0-9a-z'): string
    {
        if ($length < 1)
            throw new \InvalidArgumentException('Length must be greater than zero.');

        $charlist = count_chars(preg_replace_callback('#.-.#', function (array $m) {
            return implode('', range($m[0][0], $m[0][2]));
        }, $charlist), 3);

        $chLen = strlen($charlist);

        if ($chLen < 2)
            throw new \InvalidArgumentException('Character list must contain as least two chars.');

        $res = '';

        for ($i = 0; $i < $length; $i++) {
            $res .= $charlist[random_int(0, $chLen - 1)];
        }

        return $res;
    }
}
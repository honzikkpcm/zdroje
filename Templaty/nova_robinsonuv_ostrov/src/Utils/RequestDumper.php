<?php

namespace App\Utils;
use Symfony\Component\HttpFoundation\Request;

/**
 * Transforms various request object properties into associative array.
 */
class RequestDumper
{
    /**
     * @var string
     */
    const URL = 'url';

    /**
     * @var string
     */
    const USER_INFO = 'user_info';

    /**
     * @var string
     */
    const FORWARD_HEADERS = 'forward_headers';

    /**
     *
     * @param Request $request
     * @param array $propGroups
     * @return array
     */
    public static function dump(Request $request, array $propGroups=[])
    {
        $data = [];
        foreach($propGroups as $group) {
            switch ($group) {
                case self::URL:
                    $data['url'] = $request->getSchemeAndHttpHost() . $request->getRequestUri();
                    break;
                case self::USER_INFO:
                    $data['ip'] = $request->getClientIp();
                    $data['user_agent'] = $request->headers->get('User-Agent');
                    $data['SID'] = $request->getSession()->getId();
                    break;
                case self::FORWARD_HEADERS:
                    array_merge($data, $request->headers->get(Request::HEADER_X_FORWARDED_ALL));
                    break;
            }
        }
        return $data;
    }
}
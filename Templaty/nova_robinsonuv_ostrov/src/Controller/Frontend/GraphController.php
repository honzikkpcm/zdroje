<?php

namespace App\Controller\Frontend;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class GraphController
 * @package App\Controller
 */
class GraphController extends Controller
{

    /**
     * @Route(
     *     "/graphs/top-players/{offset}/{limit}/{guestId}",
     *     name="graphs-top-players",
     *     requirements={"offset"="\d+", "limit"="\d+", "guestId"="\d+"},
     *     defaults={"guestId"=0}
     * )
     * @param int $offset
     * @param int $limit
     * @return Response
     * @todo: add cache
     */
    public function topPlayers(int $offset = 0, int $limit = 10, int $guestId = 0): Response
    {
        $conn = $this->getDoctrine()->getConnection();
        $stmt = $conn->prepare('SELECT ROW_NUMBER() OVER (ORDER BY SUM(score) DESC) AS no,'
            .'guest_id,name,SUM(score) AS points '
            .'FROM guest_challenge gc '
            .'LEFT JOIN guest g ON gc.guest_id=g.id '
            .'GROUP BY guest_id,name '
            .'ORDER BY points DESC');
        $stmt->execute();
        $records = $stmt->fetchAll();

        $data = array_slice($records, $offset, $limit);

        // test if guest id is part of data,
        // if not, find it in the records and add it to the last position
        if ($guestId) {
            $found = false;

            foreach ($data as $item) {
                if ($guestId == $item['guest_id']) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                foreach ($records as $item) {
                    if ($guestId == $item['guest_id']) {
                        $data[] = $item;
                        break;
                    }
                }
            }
        }

        return $this->json([
            'status' => true,
            'data' => $data,
        ]);
    }
}

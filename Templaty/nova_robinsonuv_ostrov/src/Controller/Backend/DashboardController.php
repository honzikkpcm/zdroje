<?php

namespace App\Controller\Backend;

use App\Entity\Challenge;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DashboardController
 * @package App\Controller
 */
class DashboardController extends BackendController
{

    /**
     * @Route("/", name="dashboard")
     */
    public function index()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getEntityManager();
        $qb = $em->createQueryBuilder();

        // get information about challenges
        /** @var Challenge[] $response */
        $activeChallenges = $qb->select('c')
            ->from(Challenge::class, 'c')
            ->where('c.validFrom < :today')
            ->andWhere('c.validTo > :today')
            ->andwhere('c.active = :status')
            ->setParameters([
                'today' => new \DateTime(),
                'status' => true,
            ])
            ->orderBy('c.validTo')
            ->getQuery()
            ->getResult();

        // get information about new content
        $conn = $this->getDoctrine()->getConnection();
        $stmt = $conn->prepare('SELECT COUNT(id) AS count FROM guest_file WHERE status=0 AND type=\'image/jpeg\'');
        $stmt->execute();
        $imagesNew = $stmt->fetch()['count'];
        $stmt = $conn->prepare('SELECT COUNT(id) AS count FROM guest_file WHERE status=0 AND type=\'video/mpeg\'');
        $stmt->execute();
        $videosNew = $stmt->fetch()['count'];

        // get information about guests
        $guestGraph = [];

        $stmt = $conn->prepare('SELECT COUNT(*),created_at::date FROM guest GROUP BY created_at::date ORDER BY created_at');
        $stmt->execute();
        $guestPerDay = $stmt->fetchAll();

        if ($guestPerDay) {
            $previousCount = 0;

            foreach ($guestPerDay as $guestPerDayItem) {
                $guestGraph[] = [
                    $guestPerDayItem['created_at'],
                    $guestPerDayItem['count'] + $previousCount,
                ];

                $previousCount += $guestPerDayItem['count'];
            }
        }

        // get information about guest challenges
        $guestChallengesGraph = [
            'total' => [],
            'success' => [],
            'challenges' => [],
        ];

        $stmt = $conn->prepare('SELECT number AS challenge,COUNT(*) FROM guest_challenge gc '
            . 'LEFT JOIN challenge c ON gc.challenge_id=c.id '
            . 'GROUP BY number '
            . 'UNION '
            . 'SELECT number AS challenge,COUNT(*) FROM guest_challenge gc '
            . 'LEFT JOIN challenge c ON gc.challenge_id=c.id '
            . 'WHERE score > 0 '
            . 'GROUP BY number '
            . 'ORDER BY challenge');
        $stmt->execute();
        $totalGcPerC = $stmt->fetchAll();

        if ($totalGcPerC) {
            $totalGcPerCData = [];

            foreach ($totalGcPerC as $totalGcPerCItem) {
                $chId = $totalGcPerCItem['challenge'];

                if (isset($totalGcPerCData[$chId])) {
                    $totalGcPerCData[$chId][] = $totalGcPerCItem['count'];
                } else {
                    $totalGcPerCData[$chId] = [$totalGcPerCItem['count']];
                }
            }

            foreach ($totalGcPerCData as $challenge => $counts) {
                if (count($counts) === 1) {
                    $guestChallengesGraph['total'][] = $counts[0];
                    $guestChallengesGraph['success'][] = 0;
                } else {
                    $guestChallengesGraph['total'][] = max($counts);
                    $guestChallengesGraph['success'][] = min($counts);
                }
                $guestChallengesGraph['challenges'][] = "#$challenge";
            }
        }

        return $this->render('Backend/dashboard.html.twig', [
            'activeChallenges' => $activeChallenges,
            'imagesForCheck' => $imagesNew,
            'videosForCheck' => $videosNew,
            'guestGraph' => $guestGraph,
            'guestChallengesGraph' => $guestChallengesGraph,
            'today' => new \DateTime(),
            'h1' => 'Dashboard',
        ]);
    }
}

<?php

namespace App\Controller;

use DateTime;
use App\Entity\Timelog;
use App\Entity\Project;
use App\Form\TimelogType;
use App\Form\SearchfilterType;
use App\Service\MyDateInterval;
use App\Repository\TimelogRepository;
use App\Repository\ProjectRepository;
use App\Twig\DateDifferenceExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TimelogController extends AbstractController
{

    public const TODAY = 'today';
    public const WEEK = 'week';
    public const MONTH = 'month';
    private TimelogRepository $timelogRepository;

    public function __construct(TimelogRepository $timelogRepository)
    {
        $this->timelogRepository = $timelogRepository;
    }

    /**
     * @Route("/", name="timelog")
     */
    public function index(): Response
    {
        return $this->render('timelog/overview.html.twig', [
            'controller_name' => 'TimelogController',
        ]);
    }

    /**
     * @Route("/create", name="timelog-create", methods={"GET","POST"})
     */
    public function create(Request $request, ProjectRepository $projectRepository): Response
    {
        // We need existing projects first
        $entries = $projectRepository->findAll();
        if (empty($entries)) {
            $this->redirectToRoute('project_new');
        }

        $timelog = new Timelog();
        if ($request->query->has('startTime') && is_numeric($request->query->get('startTime'))) {
            $startTime = substr($request->query->get('startTime'), 0, -3);
            $timelog->setStart((new \DateTime())->setTimestamp($startTime));
        }

        if ($request->query->has('endTime') && is_numeric($request->query->get('endTime'))) {
            $endTime = substr($request->query->get('endTime'), 0, -3);
            $timelog->setEnd((new \DateTime())->setTimestamp($endTime));
        }

        $form = $this->createForm(TimelogType::class, $timelog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($timelog);
            $entityManager->flush();

            return $this->redirectToRoute('timelog_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('timelog/create.html.twig', [
            'timelog' => $timelog,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/list", name="timelog_list", methods={"GET","POST"})
     * @throws \Exception
     */
    public function list(Request $request, TimelogRepository $timelogRepository): Response
    {
        $form = $this->createForm(SearchfilterType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $timelogs = $this->queryAnalyzer($request->request->all()['searchfilter']);
        } else {
            $timelogs = $timelogRepository->entriesForToday();
        }

        $sumTime = !empty($timelogs) ? $this->calcWorkingTime($timelogs) : '';

        if ($form->get('download')->isClicked()) {
            $this->downloadList($timelogs, $sumTime);
        }

        return $this->render('timelog/list.html.twig', [
            'controller_name' => 'TimelogController',
            'timelogs' => $timelogs,
            'form' => $form->createView(),
            'sumTime' => $sumTime,
        ]);
    }

    /**
     * @Route("/edit/{id}", name="timelog-edit", methods={"GET","POST"})
     */
    public function editTimelog(Request $request, Timelog $timelog): Response
    {
        $form = $this->createForm(TimelogType::class, $timelog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($timelog);
            $entityManager->flush();

            return $this->redirectToRoute('timelog_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('timelog/edit.html.twig', [
            'timelog' => $timelog,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/statistics", name="timelog_statistics", methods={"GET","POST"})
     * @throws \Exception
     */
    public function statistics(Request $request)
    {
        $calc = self::TODAY;
        $project = null;
        $subheadline = '';
        if ($request->query->has('diagram')) {
            if ($request->query->get('diagram') === self::TODAY) {
                $calc = self::TODAY;
                $subheadline .= 'Results for today';
            }
            if ($request->query->get('diagram') === self::WEEK) {
                $calc = self::WEEK;
                $subheadline .= 'Results for this week';
            }
            if ($request->query->get('diagram') === self::MONTH) {
                $calc = self::MONTH;
                $subheadline .= 'Results for this month';
            }
        } else {
            $calc = self::TODAY;
            $subheadline .= 'Results for today';
        }

        if ($request->query->has('project')) {
            $projectId = $request->query->get('project');
            /** @var Project $project */
            $project = $this->getDoctrine()->getRepository(Project::class)->findOneBy(['id' => $projectId]);

            $subheadline .= ' for project ' . $project->getName();
        }

        $dataPoints = $this->calculateDiagram($calc, $project);

        $allProjects = $this->getDoctrine()->getRepository(Project::class)->findAll();

        return $this->render(
            'timelog/statistics.html.twig',
            [
                'dataPoints' => json_encode($dataPoints, JSON_THROW_ON_ERROR | JSON_NUMERIC_CHECK),
                'projects' => $allProjects,
                'subheadline' => $subheadline,
            ]
        );
    }

    /**
     * @Route("/{id}", name="timelog-delete", methods={"POST"})
     */
    public
    function deleteTimelog(
        Request $request,
        Timelog $timelog
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $timelog->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($timelog);
            $entityManager->flush();
        }

        return $this->redirectToRoute('timelog_list', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @throws \Exception
     */
    private function calcWorkingTime(array $timelogs): string
    {
        $a = $b = new DateTime('00:00');
        $interval = $a->diff($b);
        $mydateInterval = MyDateInterval::fromDateInterval($interval);

        foreach ($timelogs as $timelog) {
            $interval = $timelog->getStart()->diff($timelog->getEnd());
            $mydateInterval->add($interval);
        }

        return $mydateInterval->format("%d days\n%H h\n%I min.\n%S sec.");
    }

    /**
     * @throws \Exception
     */
    private function calcWorkingInterval(array $timelogs): MyDateInterval
    {
        $a = $b = new DateTime('00:00');
        $interval = $a->diff($b);
        $mydateInterval = MyDateInterval::fromDateInterval($interval);

        foreach ($timelogs as $timelog) {
            $interval = $timelog->getStart()->diff($timelog->getEnd());
            $mydateInterval->add($interval);
        }

        return $mydateInterval;
    }

    /**
     * @param array $queryParams
     * @return Timelog[]|int|mixed|string
     */
    private function queryAnalyzer(array $queryParams)
    {
        return $this->timelogRepository->findByCriteria($queryParams);
    }

    /**
     * @param Timelog[] $timelogs
     */
    private function downloadList(array $timelogs, string $sumTime): void
    {
        $dateDiffer = new DateDifferenceExtension();

        $csvList = [];

        $csvList[] = [
            'Project',
            'Begin',
            'End',
            'Comment',
            'Taken time',
        ];

        foreach ($timelogs as $timelog) {
            $csvList[] = [
                $timelog->getProject()->getName(),
                $timelog->getStart()->format('d.m.Y H:i:s'),
                $timelog->getEnd()->format('d.m.Y H:i:s'),
                $timelog->getComment(),
                $dateDiffer->dateDifference($timelog->getStart(), $timelog->getEnd()),
            ];
        }

        $csvList[] = [
            '',
            '',
            '',
            'Total Time:',
            $sumTime,
        ];

        $this->arrayToCsvDownload($csvList, 'timelog_' . date('YmdHis') . '.csv');
    }

    /**
     * Method for downloading csv
     *
     * @param $array
     * @param string $filename
     * @param string $delimiter
     */
    private function arrayToCsvDownload(
        $array,
        $filename = "export.csv",
        $delimiter = ";"
    ) {
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');

        // open the "output" stream
        // see http://www.php.net/manual/en/wrappers.php.php#refsect2-wrappers.php-unknown-unknown-unknown-descriptioq
        $f = fopen('php://output', 'wb');

        foreach ($array as $line) {
            fputcsv($f, $line, $delimiter);
        }
        exit;
    }

    /**
     * @param string $calc
     * @param Project $project
     * @return Timelog[]
     * @throws \Exception
     */
    private function calculateDiagram(string $calc, Project $project = null)
    {
        $dataPoints = [];

        if ($calc === self::TODAY) {
            $minutes = 0;
            foreach (range(0, 23) as $hour) {
                $startRange = new \DateTime($hour . ':0');
                $endRange = new \DateTime(($hour + 1) . ':0');
                $timelogs = $this->timelogRepository->findByRange($startRange, $endRange, $project);

                $since_start = $this->calcWorkingInterval($timelogs);

                $minutes += $since_start->days * 24 * 60;
                $minutes += $since_start->h * 60;
                $minutes += $since_start->i;
                $dataPoints[] = ['y' => $minutes, 'label' => (string)$hour];
                $minutes = 0;
            }
        }

        if ($calc === self::WEEK) {
            $minutes = 0;
            $startRange = new DateTime('sunday last week 0:0');

            foreach (range(0, 6) as $day) {
                $startRange = $startRange->add(new \DateInterval('P1D'));
                $endRange = clone $startRange;
                $endRange->add(new \DateInterval('P1D'));

                $timelogs = $this->timelogRepository->findByRange($startRange, $endRange, $project);

                $since_start = $this->calcWorkingInterval($timelogs);
                $minutes += $since_start->days * 24 * 60;
                $minutes += $since_start->h * 60;
                $minutes += $since_start->i;
                $dataPoints[] = ['y' => $minutes, 'label' => $startRange->format('l')];
                $minutes = 0;
            }
        }

        if ($calc === self::MONTH) {
            $minutes = 0;
            $startRange = new DateTime('first day of this month 0:0');
            $startRange->sub(new \DateInterval('P1D'));

            $lastday =  cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));

            foreach (range(1, $lastday) as $day) {
                $startRange = $startRange->add(new \DateInterval('P1D'));
                $endRange = clone $startRange;
                $endRange->add(new \DateInterval('P1D'));

                $timelogs = $this->timelogRepository->findByRange($startRange, $endRange, $project);

                $since_start = $this->calcWorkingInterval($timelogs);
                $minutes += $since_start->days * 24 * 60;
                $minutes += $since_start->h * 60;
                $minutes += $since_start->i;
                $dataPoints[] = ['y' => $minutes, 'label' => $day];
                $minutes = 0;
            }
        }

        return $dataPoints;
    }
}

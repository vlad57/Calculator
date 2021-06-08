<?php

namespace App\Controller;

use App\Service\CalculateService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="index")
     * @Route("/calculate", name="calculate", methods={"POST"})
     * @param Request $request
     * @param CalculateService $calculateService
     * @param RequestStack $requestStack
     * @return Response
     */
    public function index(Request $request, CalculateService $calculateService, RequestStack $requestStack): Response
    {

        $routeName = $request->attributes->get('_route');
        $inputCalculator = $request->get('inputToCalculate');

        $history = $requestStack->getSession()->get('calculate_history');

        if (!empty($routeName) && $routeName === 'calculate') {

            if (!empty($inputCalculator)) {
                $result = $calculateService->calculFinal($inputCalculator);

                if (!empty($result) && count($result) === 1) {
                    $result = reset($result);

                    if (is_numeric($result)) {
                        $history[] = [
                            'calculation' => $inputCalculator,
                            'result' => $result
                        ];

                        $requestStack->getSession()->set('calculate_history', $history);

                        return new JsonResponse($result);
                    }

                } else {
                    return new JsonResponse('Erreur');
                }

            }
        }

        return $this->render('index.html.twig', [
            'controller_name' => 'IndexController',
            'historyCalculation' => $history
        ]);
    }
}

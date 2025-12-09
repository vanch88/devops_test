<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Service\MetricsService;
use Prometheus\RenderTextFormat;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/metrics')]
class MetricsController extends AbstractController
{
    public function __construct(
        private readonly MetricsService $metricsService,
        private readonly UserRepository $userRepository,
        private readonly PostRepository $postRepository
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function index(): Response
    {
        $usersCount = count($this->userRepository->findAll());
        $postsCount = count($this->postRepository->findAll());
        
        $this->metricsService->setUsersCount($usersCount);
        $this->metricsService->setPostsCount($postsCount);

        $renderer = new RenderTextFormat();
        $result = $renderer->render($this->metricsService->getRegistry()->getMetricFamilySamples());

        return new Response($result, 200, [
            'Content-Type' => RenderTextFormat::MIME_TYPE,
        ]);
    }
}


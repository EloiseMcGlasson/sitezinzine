<?php

namespace App\Controller\Admin;

use App\Service\LibreTime\LibreTimeWeekInfoClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\LibreTime\LibreTimeScheduleBuilder;

#[Route('/admin', name: 'admin.')]
class TestLibreTimeController extends AbstractController
{
    #[Route('/test-libretime', name: 'app_test_libretime')]
public function index(
    LibreTimeWeekInfoClient $client,
    LibreTimeScheduleBuilder $builder
): Response {
    $rawData = $client->fetchWeekInfo();
    $schedule = $builder->build($rawData);

    return $this->render('admin/test_libretime.html.twig', [
        'schedule' => $schedule,
    ]);
}
}
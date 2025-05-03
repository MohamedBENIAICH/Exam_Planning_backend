<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Services\ExamNotificationService;
use Illuminate\Http\Request;

class ExamNotificationController extends Controller
{
    protected $notificationService;

    public function __construct(ExamNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function sendNotifications(Exam $exam)
    {
        try {
            $this->notificationService->generateAndSendNotifications($exam);
            return response()->json([
                'message' => 'Les convocations ont été envoyées avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de l\'envoi des convocations',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

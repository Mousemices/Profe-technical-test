<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateFootballCompetitionOutcomeRequest;
use App\Services\Internal\Competition\Football\FootballCompetitionService;

class FootballCompetitionOutcomeController extends Controller
{
    public function generate(
        GenerateFootballCompetitionOutcomeRequest $generateFootballCompetitionOutcomeRequest,
        FootballCompetitionService $footballCompetitionService
    )
    {
        $txt = $generateFootballCompetitionOutcomeRequest->validated()['txt_file'];

        $path = $footballCompetitionService->create(file: $txt)
            ->outputFilePath();

        return response()->download($path)
            ->deleteFileAfterSend();
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Services\CompetitionService;
use App\Utils\HttpResponseCode;
use Illuminate\Http\Request;

class CompetitionController extends Controller
{
    protected $competitionService;

    public function __construct(CompetitionService $competitionService)
    {
        $this->competitionService = $competitionService;
    }
    public function index()
    {
        $competitions = $this->competitionService->getCompetitions();
        return $this->success("List Competition berhasil diambil", $competitions, HttpResponseCode::HTTP_OK);
    }

    public function show(string $key)
    {
        $competition = $this->competitionService->getCompetitionByKey($key);
        return $this->success(
            'Detail Competition berhasil diambil',
            $competition,
            HttpResponseCode::HTTP_OK
        );
    }
}

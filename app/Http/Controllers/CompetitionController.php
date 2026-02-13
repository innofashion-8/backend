<?php

namespace App\Http\Controllers;

use App\Http\Requests\Competition\SaveCompetitionRequest;
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

    public function store(SaveCompetitionRequest $request)
    {
        $dto = $request->toDTO();
        $competition = $this->competitionService->store($dto);
        return $this->success(
            'Competition berhasil dibuat',
            $competition,
            HttpResponseCode::HTTP_CREATED
        );
    }

    public function update (SaveCompetitionRequest $request, string $key)
    {
        $competition = $this->competitionService->getCompetitionByKey($key);
        $dto = $request->toDTO();
        $competition = $this->competitionService->update($competition, $dto);
        return $this->success(
            'Competition berhasil diperbarui',
            $competition,
            HttpResponseCode::HTTP_OK
        );
    }

    public function destroy(string $key)
    {
        $competition = $this->competitionService->getCompetitionByKey($key);
        $this->competitionService->delete($competition);
        return $this->success(
            'Competition berhasil dihapus',
            null,
            HttpResponseCode::HTTP_OK
        );
    }
}

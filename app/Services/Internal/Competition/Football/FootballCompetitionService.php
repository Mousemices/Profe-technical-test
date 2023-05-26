<?php

namespace App\Services\Internal\Competition\Football;

use App\Services\Base;
use App\Services\Internal\Competition\Football\Generator\GeneratorContract;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FootballCompetitionService extends Base
{
    protected string $name = 'FootballCompetition';

    protected string $description = 'This is responsible for create desired output file based on the input file.';

    protected array $errors = [];

    private array $teams = [];

    private string $outputFilePath;

    private GeneratorContract $fileGenerator;

    public function __construct(GeneratorContract $fileGenerator)
    {
        $this->fileGenerator = $fileGenerator;
    }

    public function create(UploadedFile $file): self
    {
        $this->handleFileContent(file: $file);

        $this->mapFirstLevelKeyToNestedNameKey();

        $this->sortTeamsBy(key: 'P', nextKey: 'name');

        $this->parseTeamsInformationToTxt();

        return $this;
    }

    public function outputFilePath(): string
    {
        return storage_path('app/' . $this->outputFilePath);
    }

    public function handleFileContent(UploadedFile $file): void
    {
        $content = fopen($file->getPathname(), 'r');

        while (($line = fgets($content)) !== false) {
            $line = rtrim($line);
            if (!empty($line)) {
                $matchRecord = explode(';', $line);
                $this->process(matchRecord: $matchRecord);
            }
        }

        fclose($content);
    }

    private function process(array $matchRecord): void
    {
        $teams = [$matchRecord[0], $matchRecord[1]];

        $matchState = Str::upper($matchRecord[2]);

        $this->processTeams(teams: $teams);

        $this->processState(matchState: $matchState, teams: $teams);
    }

    private function processTeams(array $teams): void
    {
        for ($i = 0; $i < count($teams); $i++) {
            $name = $teams[$i];

            if (!$this->isPresent(teamName: $name)) {
                $team = $this->buildTeamStructure();
                $team['MP'] += 1;
                $this->teams[$name] = $team;
            } else {
                $this->teams[$name]['MP'] += 1;
            }
        }
    }

    private function processState(string $matchState, array $teams): void
    {
        [$firstTeamName, $secondTeamName] = [$teams[0], $teams[1]];

        switch ($matchState) {
            case MatchState::WIN->name:
                $this->firstTeamWinsAgainstSecondTeam(
                    firstTeamName: $firstTeamName,
                    secondTeamName: $secondTeamName
                );
                break;
            case MatchState::LOSS->name:
                $this->firstTeamLosesAgainstSecondTeam(
                    firstTeamName: $firstTeamName,
                    secondTeamName: $secondTeamName
                );
                break;
            case MatchState::DRAW->name:
                $this->drawBetween(
                    firstTeamName: $firstTeamName,
                    secondTeamName: $secondTeamName
                );
                break;
        }
    }

    private function isPresent(string $teamName): bool
    {
        return array_key_exists($teamName, $this->teams);
    }

    private function buildTeamStructure(): array
    {
        return $this->teamStructure();
    }

    private function teamStructure(): array
    {
        return [
            'MP' => 0,
            'W' => 0,
            'D' => 0,
            'L' => 0,
            'P' => 0,
        ];
    }

    private function firstTeamWinsAgainstSecondTeam(string $firstTeamName, string $secondTeamName): void
    {
        $this->teams[$firstTeamName]['P'] += MatchState::WIN->point();
        $this->teams[$secondTeamName]['P'] += MatchState::LOSS->point(); // it can be ignored
        $this->teams[$firstTeamName]['W'] += 1;
        $this->teams[$secondTeamName]['L'] += 1;
    }

    private function firstTeamLosesAgainstSecondTeam(string $firstTeamName, string $secondTeamName): void
    {
        $this->teams[$secondTeamName]['P'] += MatchState::WIN->point();
        $this->teams[$firstTeamName]['P'] += MatchState::LOSS->point(); // it can be ignored
        $this->teams[$secondTeamName]['W'] += 1;
        $this->teams[$firstTeamName]['L'] += 1;
    }

    private function drawBetween(string $firstTeamName, string $secondTeamName): void
    {
        $this->teams[$firstTeamName]['P'] += MatchState::DRAW->point();
        $this->teams[$secondTeamName]['P'] += MatchState::DRAW->point();
        $this->teams[$secondTeamName]['D'] += 1;
        $this->teams[$firstTeamName]['D'] += 1;
    }

    private function mapFirstLevelKeyToNestedNameKey(): void
    {
        $this->teams = array_values(array_map(function ($key, $team) {
            $team['name'] = $key;
            return $team;
        }, array_keys($this->teams), $this->teams));
    }

    private function sortTeamsBy(string $key, string $nextKey): void
    {
        usort($this->teams, function ($firstTeam, $secondTeam) use ($key, $nextKey) {
            if ($firstTeam[$key] === $secondTeam[$key]) {
                return strcmp($firstTeam[$nextKey], $secondTeam[$nextKey]);
            }

            return $secondTeam[$key] - $firstTeam[$key];
        });
    }

    private function parseTeamsInformationToTxt(): void
    {
        $this->outputFilePath = uniqid() . '_' . $this->name() . 'Outcome.txt';

        $this->setTeamNameAtBeginning();

        $content = $this->fileGenerator->generate(data: $this->teams);

        Storage::disk('local')->put($this->outputFilePath, $content);
    }

    public function setTeamNameAtBeginning(): void
    {
        foreach ($this->teams as &$team) {
            $lastKey = array_keys($team)[count($team) - 1];
            $lastElement = array_pop($team);
            $team = array_merge([$lastKey => $lastElement], $team);
        }
    }
}

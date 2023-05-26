<?php

namespace App\Services\Internal\Competition\Football\Generator;

use Illuminate\Support\Str;

class TxtFileGenerator implements GeneratorContract
{
    private array $headers = ['Team', 'MP', 'W', 'D', 'L', 'P'];

    private array $content;

    private string $formattedOutput = '';

    private array $paddingLeft = [];

    private array $paddingBoth = [];

    private array $paddingRight = [];

    public function generate(array $data): string
    {
        $this->setContent(content: $data);

        $this->preparePadding();

        $this->buildHeader()
            ->buildContent()
            ->trimContent();

        return $this->formattedOutput;
    }

    private function preparePadding(): void
    {
        $this->preparePaddingRight();
        $this->preparePaddingBoth();
        $this->preparePaddingLeft();
    }

    private function preparePaddingRight(): void
    {
        $this->paddingRight = ['Team' => $this->findLongestTeamNameLength()];

        $this->increment(targetPadding: 'paddingRight');
    }

    private function preparePaddingBoth(): void
    {
        $this->paddingBoth = $this->findLongestTeamStatusesLength(['MP']);

        $this->increment(targetPadding: 'paddingBoth');
    }

    private function preparePaddingLeft(): void
    {
        $this->paddingLeft = $this->findLongestTeamStatusesLength(['W', 'D', 'L', 'P']);

        $this->increment(targetPadding: 'paddingLeft');
    }

    private function increment(string $targetPadding)
    {
        $this->$targetPadding = collect($this->$targetPadding)->increment(4)->toArray();
    }

    private function findLongestTeamNameLength(): int
    {
        return collect($this->content)->pluck('name')
            ->toStringLength()
            ->max();
    }

    private function findLongestTeamStatusesLength(array $keys): array
    {
        return collect($this->content)->reduce(function ($carry, $team) use ($keys) {
            return collect($team)->except('name')->only($keys)->map(function ($value, $key) use ($carry) {
                $length = strlen((string)$value);
                return isset($carry[$key]) ? max($carry[$key], $length) : $length;
            });
        })
            ->toArray();
    }

    private function buildHeader(): self
    {
        collect($this->headers)->map(function ($headerName) {
            $this->buildHeaderComponent(headerName: $headerName);
        })
            ->toArray();

        $this->removeLastVerticalBarAndSpace();

        $this->formattedOutput .= PHP_EOL;

        return $this;
    }

    private function buildContent(): self
    {
        collect($this->content)->map(function ($record) {
            $this->buildContentComponent(contentRecord: $record);
        })
            ->toArray();

        return $this;
    }

    private function buildHeaderComponent(string $headerName): void
    {
        $this->applyHeaderPadding(name: $headerName);
    }

    private function buildContentComponent(array $contentRecord): void
    {
        $this->applyContentPadding(contentRecord: $contentRecord);
    }

    private function applyHeaderPadding(string $name): void
    {
        if (array_key_exists($name, $this->paddingRight())) {
            $this->formattedOutput .= Str::padRight($name, $this->paddingRight()[$name]) . '|';
            return;
        }

        if (array_key_exists($name, $this->paddingBoth())) {
            $this->formattedOutput .= Str::padBoth($name, $this->paddingBoth()[$name]) . '|';
            return;
        }

        if (array_key_exists($name, $this->paddingLeft())) {
            $largerName = $this->calculatePaddingForRight(key: $name);

            $this->formattedOutput .= Str::padLeft($largerName, $this->paddingLeft()[$name]) . '|';
        }
    }

    private function applyContentPadding(array $contentRecord): void
    {
        foreach ($contentRecord as $recordKey => $recordValue) {
            if ($recordKey === 'name') {
                $this->formattedOutput .= Str::padRight($recordValue, $this->paddingRight()['Team']) . '|';
            }

            if (array_key_exists($recordKey, $this->paddingBoth())) {
                $this->formattedOutput .= Str::padBoth($recordValue, $this->paddingBoth()[$recordKey]) . '|';
            }

            if (array_key_exists($recordKey, $this->paddingLeft())) {
                $largerRecordValue = $this->calculatePaddingForRightWith(key: $recordKey, value: $recordValue);

                $this->formattedOutput .= Str::padLeft($largerRecordValue, $this->paddingLeft()[$recordKey]) . '|';
            }
        }

        $this->removeLastVerticalBarAndSpace();

        $this->formattedOutput .= PHP_EOL;
    }

    private function calculatePaddingForRight(string $key): string
    {
        $short = max(0, $this->paddingLeft()[$key]) - mb_strlen($key);
        $shortLeft = (int)floor($short / 2);

        return Str::padRight($key, $shortLeft);
    }

    private function calculatePaddingForRightWith(string $key, string $value): string
    {
        $short = max(0, $this->paddingLeft()[$key]) - mb_strlen($value);
        $shortLeft = (int)floor($short / 2);

        return Str::padRight($value, $shortLeft);
    }


    private function removeLastVerticalBarAndSpace(): void
    {
        $this->formattedOutput = rtrim($this->formattedOutput, ' |');
    }

    private function trimContent(): void
    {
        $this->formattedOutput = rtrim($this->formattedOutput);
    }

    private function paddingRight(): array
    {
        return $this->paddingRight;
    }

    private function paddingBoth(): array
    {
        return $this->paddingBoth;
    }

    private function paddingLeft(): array
    {
        return $this->paddingLeft;
    }

    private function setContent(array $content): void
    {
        $this->content = $content;
    }
}

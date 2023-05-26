<?php

namespace App\Services\Internal\Competition\Football;

enum MatchState
{
    case WIN;
    case LOSS;
    case DRAW;

    public function point(): int
    {
        return match ($this) {
            self::WIN => 3,
            self::LOSS => 0,
            self::DRAW => 1,
        };
    }
}

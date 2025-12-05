<?php

namespace App\Domain\Quiz\FamilyFeud\Entity;

enum GamePhase: string
{
    case NEW_GAME = 'NEW_GAME';
    case NEW_ROUND = 'NEW_ROUND';
    case FACE_OFF = 'FACE_OFF';
    case PLAYING = 'PLAYING';
    case STEAL = 'STEAL';
    case END_ROUND = 'END_ROUND';
    case END_GAME = 'END_GAME';
}


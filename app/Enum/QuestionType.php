<?php

namespace App\Enum;

enum QuestionType: string
{
    case RATING = 'rating';
    case TEXT = 'text';
    case MULTIPLE_CHOICE = 'multiple_choice';
    case HEADER = 'header';
}

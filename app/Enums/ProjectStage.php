<?php

namespace App\Enums;

enum ProjectStage: string {
    case PLANNING = 'planning';
    case DEVELOPMENT = 'development';
    case TESTING = 'testing';
    case DEPLOYED = 'deployed';
}
<?php

declare(strict_types = 1);

namespace TryAgainLater\TodoApp\Environment;

enum EnvironmentType: string
{
    case Development = 'development';
    case Local = 'local';
    case Staging = 'staging';
    case Production = 'production';
}

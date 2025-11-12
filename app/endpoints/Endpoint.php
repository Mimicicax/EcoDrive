<?php

namespace EcoDrive\Endpoints;

interface Endpoint {
    public static function requiresAuth(): bool;
}
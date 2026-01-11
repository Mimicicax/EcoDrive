<?php

namespace EcoDrive\Endpoints;

interface Endpoint {
    public static function requiresAuth(): bool;
    public static function isAdminPermissible(): bool;
}
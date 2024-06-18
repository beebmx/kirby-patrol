<?php

namespace Beebmx\KirbyPatrol\Controllers;

use Beebmx\KirbyPatrol\Patrol;
use Exception;

class PermissionController extends Controller
{
    public function __invoke(string $role): array
    {
        try {
            $patrol = new Patrol($this->kirby);

            return [
                'saved' => $patrol->store(
                    role: $patrol->roles()->get($role),
                    permissions: $this->kirby->request()->get('permissions')
                )];
        } catch (Exception $e) {
            return [
                'saved' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}

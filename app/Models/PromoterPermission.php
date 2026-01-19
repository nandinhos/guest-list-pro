<?php

namespace App\Models;

/**
 * Alias de EventAssignment para manter compatibilidade com codigo existente.
 *
 * @deprecated Use EventAssignment diretamente.
 */
class PromoterPermission extends EventAssignment
{
    /**
     * A tabela original foi renomeada para event_assignments.
     */
    protected $table = 'event_assignments';
}

<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Contracts;

use YezzMedia\Foundation\Data\AuditEventDefinition;

interface DefinesAuditEvents
{
    /**
     * @return array<int, AuditEventDefinition>
     */
    public function auditEventDefinitions(): array;
}

<?php

namespace Inspector\Symfony\Bundle\Inspectable\Doctrine\DBAL\Logging;

use Doctrine\DBAL\Logging\SQLLogger;
use Doctrine\DBAL\Types\Type;
use Inspector\Inspector;

class InspectableSQLLogger implements SQLLogger
{
    const SEGMENT_TYPE = 'SQL';

    /** @var Inspector */
    protected $inspector;

    /** @var string */
    protected $lastLabel;

    /** @var \Inspector\Models\PerformanceModel|\Inspector\Models\Segment */
    private $segment;

    /** @var SQLLogger */
    private $sqlLogger;

    public function __construct(Inspector $inspector, \Doctrine\DBAL\Configuration $configuration)
    {
        $this->inspector = $inspector;
        $this->sqlLogger = $configuration->getSQLLogger();
        $configuration->setSQLLogger($this);
    }

    /**
     * Logs a SQL statement.
     *
     * @param string $sql SQL statement
     * @param array<int, mixed>|array<string, mixed>|null $params Statement parameters
     * @param array<int, Type|int|string|null>|array<string, Type|int|string|null>|null $types Parameter types
     */
    public function startQuery($sql, ?array $params = null, ?array $types = null): void
    {
        $label = 'SQL: '.$sql;
        $this->segment = $this->inspector->startSegment(self::SEGMENT_TYPE, $label);

        $this->sqlLogger->startQuery($sql, $params, $types);
    }

    /**
     * Marks the last started query segment as stopped.
     */
    public function stopQuery(): void
    {
        if (null === $this->segment) {
            throw new \LogicException('Attempt to stop a segment that has not been started');
        }

        $this->segment->end();
        $this->segment = null;

        $this->sqlLogger->stopQuery();
    }
}

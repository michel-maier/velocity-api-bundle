<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Velocity\Bundle\ApiBundle\Event\DatabaseQueryEvent;
use Symfony\Component\HttpKernel\DataCollector\DataCollector as BaseDataCollector;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class DatabaseDataCollector extends BaseDataCollector
{
    /**
     * @var array
     */
    protected $data = ['queries' => []];
    /**
     * @return array
     */
    public function getQueries()
    {
        return $this->data['queries'];
    }
    /**
     * @param DatabaseQueryEvent $event
     */
    public function onDatabaseQueryExecuted(DatabaseQueryEvent $event)
    {
        $this->data['queries'][] = [
            'exception' => null !== $event->getException() ? [
                'code' => $event->getException()->getCode(),
                'message' => $event->getException()->getMessage(),
                ] : null,
            'params' => $event->getParams(),
            'query' => $event->getQuery(),
            'result' => $event->getResult() instanceof \Iterator ? iterator_to_array($event->getResult()) : $event->getResult(),
            'type' => $event->getType(),
            'endDate' => $event->getEndTime(),
            'startDate' => $event->getStartTime(),
            'duration' =>$event->getEndTime() - $event->getStartTime(),
        ];
    }
    /**
     * @param Request    $request
     * @param Response   $response
     * @param \Exception $exception
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
    }
    /**
     * @return string
     */
    public function getName()
    {
        return 'velocity_database';
    }
}

<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\EventAction;

use Symfony\Component\EventDispatcher\GenericEvent;
use Velocity\Bundle\ApiBundle\Event;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Velocity\Bundle\ApiBundle\Service\ArchiverService;
use Velocity\Bundle\ApiBundle\EventAction\Base\AbstractEventAction;

/**
 * @author Olivier Hoareau <olivier@tomboladirecte.com>
 */
class ArchiveDocEventAction extends AbstractEventAction
{
    use ServiceAware\ArchiverServiceAwareTrait;
    /**
     * @param ArchiverService $archiverService
     */
    public function __construct(ArchiverService $archiverService)
    {
        $this->setArchiverService($archiverService);
    }
    /**
     * @Velocity\EventAction("archive_doc", ignoreOnException=true, description="archive the document")
     *
     * @return void
     *
     * @throws \Exception
     */
    public function archiveDoc()
    {
        $context = $this->getContext();
        $event   = $context->getCurrentEvent();

        if ($event instanceof Event\DocumentEvent) {
            $doc  = $event->getData();
        } elseif ($event instanceof GenericEvent) {
            $doc = $event->getSubject();
        } else {
            throw $this->createRequiredException(
                'Unable to archive, document required but not provided (event: %s)',
                get_class($event)
            );
        }

        $type = 'default';

        if ($context->hasVariable('type')) {
            $type = $context->getRequiredVariable('type');
        } elseif (null !== $doc && is_object($doc)) {
            $type = join('.', array_map(function ($v) {
                return lcfirst($v);
            }, explode('\\', get_class($doc))));
        }

        $this->getArchiverService()->archive($type, $doc);
    }
}

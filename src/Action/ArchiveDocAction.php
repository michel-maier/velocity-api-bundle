<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Action;

use Velocity\Core\Bag;
use Velocity\Bundle\ApiBundle\Event;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Velocity\Bundle\ApiBundle\Service\ArchiverService;
use Velocity\Bundle\ApiBundle\Action\Base\AbstractAction;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class ArchiveDocAction extends AbstractAction
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
     * @param Bag $params
     * @param Bag $context
     *
     * @Velocity\Action("archive_doc", ignoreOnException=true, description="archive the document")
     *
     * @return void
     *
     * @throws \Exception
     */
    public function archiveDoc(Bag $params, Bag $context)
    {
        $doc  = $context->get('doc', null);
        $type = 'default';

        if ($params->has('type')) {
            $type = $params->get('type');
        } elseif (null !== $doc && is_object($doc)) {
            $type = join('.', array_map(function ($v) {
                return lcfirst($v);
            }, explode('\\', get_class($doc))));
        }

        $this->getArchiverService()->archive($type, $doc);
    }
}

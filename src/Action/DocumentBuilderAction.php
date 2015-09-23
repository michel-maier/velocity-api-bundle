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
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Velocity\Bundle\ApiBundle\Action\Base\AbstractAction;
use Velocity\Bundle\ApiBundle\Service\DocumentBuilderService;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class DocumentBuilderAction extends AbstractAction
{
    use ServiceAware\DocumentBuilderServiceAwareTrait;
    /**
     * @param DocumentBuilderService $documentBuilderService
     */
    public function __construct(DocumentBuilderService $documentBuilderService)
    {
        $this->setDocumentBuilderService($documentBuilderService);
    }
    /**
     * @param Bag $params
     * @param Bag $context
     *
     * @Velocity\Action("build_document", description="build the document")
     */
    public function buildDocument(Bag $params, Bag $context)
    {
        $context->set(
            $params->get('var'),
            $this->getDocumentBuilderService()->build(
                $params->get('type'),
                $params->get('data', []),
                $params->get('metas', []),
                $params->get('options', [])
            )
        );
    }
}

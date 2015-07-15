<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Controller\Base;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Velocity\Bundle\ApiBundle\Service\DocumentServiceInterface;

/**
 * Sub Document Controller
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
abstract class SubDocumentController extends RestController
{
    /**
     * Returns the implicit document service (based on class name)
     *
     * @return DocumentServiceInterface
     */
    protected function getService()
    {
        return $this->get(
            'app.' . preg_replace('/Controller$/', '', basename(str_replace('\\', '/', get_class($this))))
        );
    }
    /**
     * @param Request $request
     * @param array    $options
     *
     * @return Response
     */
    protected function handleFind(Request $request, $options = [])
    {
        return $this->returnResponse(
            $this->getService()->find(
                $request->attributes->get('parentId'),
                $request->get('criteria', []),
                $request->get('fields', []),
                $request->get('limit', null),
                intval($request->get('offset', 0)),
                $request->get('sorts', []),
                $options
            ),
            200,
            [],
            ['groups' => ['Default', 'listed']]
        );
    }
    /**
     * @param Request $request
     * @param array   $options
     *
     * @return mixed
     */
    protected function handleGet(Request $request, $options = [])
    {
        return $this->returnResponse(
            $this->getService()->get(
                $request->attributes->get('parentId'),
                $request->attributes->get('id'),
                $request->get('fields', []),
                $options
            ),
            200,
            [],
            ['groups' => ['Default', 'detailed']]
        );
    }
    /**
     * @param Request $request
     * @param array   $options
     *
     * @return mixed
     */
    protected function handleDelete(Request $request, $options = [])
    {
        $this->getService()->delete(
            $request->attributes->get('parentId'),
            $request->attributes->get('id'),
            $options
        );
        return $this->returnResponse(null, 204);
    }
    /**
     * @param Request $request
     * @param array   $options
     *
     * @return mixed
     */
    protected function handlePurge(Request $request, $options = [])
    {
        $this->getService()->purge(
            $request->attributes->get('parentId'),
            $options
        );
        return $this->returnResponse(null, 204);
    }
    /**
     * @param Request $request
     * @param array   $options
     *
     * @return mixed
     */
    protected function handleUpdate(Request $request, $options = [])
    {
        return $this->returnResponse(
            $this->getService()->update(
                $request->attributes->get('parentId'),
                $request->attributes->get('id'),
                $request->request->all(),
                $options
            ),
            200,
            [],
            ['groups' => ['Default', 'updated']]
        );
    }
    /**
     * @param Request $request
     * @param array   $options
     *
     * @return mixed
     */
    protected function handleCreate(Request $request, $options = [])
    {
        return $this->returnResponse(
            $this->getService()->create(
                $request->attributes->get('parentId'),
                $request->request->all(),
                $options
            ),
            200,
            [],
            ['groups' => ['Default', 'created']]
        );
    }
}
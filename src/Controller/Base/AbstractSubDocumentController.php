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
use Velocity\Bundle\ApiBundle\Service\SubDocumentServiceInterface;

/**
 * Sub Document Controller
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
abstract class AbstractSubDocumentController extends AbstractRestController
{
    /**
     * Returns the implicit document service (based on class name)
     *
     * @return SubDocumentServiceInterface
     */
    protected function getService()
    {
        return $this->get(
            'app.'.preg_replace('/controller$/', '', join('.', array_slice(explode('.', str_replace('\\', '.', strtolower(get_class($this)))), -2)))
        );
    }
    /**
     * Retrieve the documents matching the specified criteria.
     *
     * @param Request $request
     * @param array   $options
     *
     * @return Response
     */
    protected function handleFind(Request $request, $options = [])
    {
        return $this->returnResponse(
            $this->getService()->find(
                $this->getRequestService()->fetchRouteParameter($request, 'parentId'),
                $this->getRequestService()->fetchQueryCriteria($request),
                $this->getRequestService()->fetchQueryFields($request),
                $this->getRequestService()->fetchQueryLimit($request),
                $this->getRequestService()->fetchQueryOffset($request),
                $this->getRequestService()->fetchQuerySorts($request),
                $options
            ),
            200,
            [],
            ['groups' => ['Default', 'listed']],
            $request
        );
    }
    /**
     * Retrieve the documents matching the specified criteria.
     *
     * @param Request $request
     * @param string  $field
     * @param array   $options
     *
     * @return Response
     */
    protected function handleFindBy(Request $request, $field, $options = [])
    {
        return $this->returnResponse(
            $this->getService()->findBy(
                $field,
                $this->getRequestService()->fetchRouteParameter($request, $field),
                $this->getRequestService()->fetchQueryCriteria($request),
                $this->getRequestService()->fetchQueryFields($request),
                $this->getRequestService()->fetchQueryLimit($request),
                $this->getRequestService()->fetchQueryOffset($request),
                $this->getRequestService()->fetchQuerySorts($request),
                $options
            ),
            200,
            [],
            ['groups' => ['Default', 'listed']],
            $request
        );
    }
    /**
     * Return the specified document.
     *
     * @param Request $request
     * @param array   $options
     *
     * @return Response
     */
    protected function handleGet(Request $request, $options = [])
    {
        return $this->returnResponse(
            $this->getService()->get(
                $this->getRequestService()->fetchRouteParameter($request, 'parentId'),
                $this->getRequestService()->fetchRouteParameter($request, 'id'),
                $this->getRequestService()->fetchQueryFields($request),
                $options
            ),
            200,
            [],
            ['groups' => ['Default', 'detailed']],
            $request
        );
    }
    /**
     * Delete the specified document.
     *
     * @param Request $request
     * @param array   $options
     *
     * @return Response
     */
    protected function handleDelete(Request $request, $options = [])
    {
        $this->getService()->delete(
            $this->getRequestService()->fetchRouteParameter($request, 'parentId'),
            $this->getRequestService()->fetchRouteParameter($request, 'id'),
            $options
        );

        return $this->returnResponse(null, 204, [], [], $request);
    }
    /**
     * Purge (delete) all the documents.
     *
     * @param Request $request
     * @param array   $options
     *
     * @return Response
     */
    protected function handlePurge(Request $request, $options = [])
    {
        $this->getService()->purge(
            $this->getRequestService()->fetchRouteParameter($request, 'parentId'),
            $options
        );

        return $this->returnResponse(null, 204, [], [], $request);
    }
    /**
     * Update the specified document.
     *
     * @param Request $request
     * @param array   $options
     *
     * @return Response
     */
    protected function handleUpdate(Request $request, $options = [])
    {
        return $this->returnResponse(
            $this->getService()->update(
                $this->getRequestService()->fetchRouteParameter($request, 'parentId'),
                $this->getRequestService()->fetchRouteParameter($request, 'id'),
                $this->getRequestService()->fetchRequestData($request),
                $options
            ),
            200,
            [],
            ['groups' => ['Default', 'updated']],
            $request
        );
    }
    /**
     * Update the specified document property.
     *
     * @param Request $request
     * @param string  $property
     * @param array   $options
     *
     * @return Response
     */
    protected function handleUpdateProperty(Request $request, $property, $options = [])
    {
        $service = $this->getService();
        $method  = 'update'.ucfirst($property);

        if (!method_exists($service, $method)) {
            throw $this->createAccessDeniedException();
        }

        return $this->returnResponse(
            $service->$method(
                $this->getRequestService()->fetchRouteParameter($request, 'parentId'),
                $this->getRequestService()->fetchRouteParameter($request, 'id'),
                $this->getRequestService()->fetchRequestData($request),
                $options
            ),
            200,
            [],
            ['groups' => ['Default', 'updated']],
            $request
        );
    }
    /**
     * Create a new document.
     *
     * @param Request $request
     * @param array   $options
     *
     * @return Response
     */
    protected function handleCreate(Request $request, $options = [])
    {
        return $this->returnResponse(
            $this->getService()->create(
                $this->getRequestService()->fetchRouteParameter($request, 'parentId'),
                $this->getRequestService()->fetchRequestData($request),
                $options
            ),
            200,
            [],
            ['groups' => ['Default', 'created']],
            $request
        );
    }
    /**
     * Create a new document.
     *
     * @param Request $request
     * @param string  $field
     * @param array   $options
     *
     * @return Response
     */
    protected function handleCreateBy(Request $request, $field, $options = [])
    {
        return $this->returnResponse(
            $this->getService()->createBy(
                $field,
                $this->getRequestService()->fetchRouteParameter($request, $field),
                $this->getRequestService()->fetchRequestData($request),
                $options
            ),
            200,
            [],
            ['groups' => ['Default', 'created']],
            $request
        );
    }
}

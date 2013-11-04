<?php

namespace Bangpound\Bundle\TwitterStreamingBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as BaseController;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Bangpound\Bundle\TwitterStreamingBundle\Entity\FilterInterface;

class CRUDController extends BaseController
{
    /**
     * @param FilterInterface $object
     */
    protected function disableFilter(FilterInterface $object)
    {
        $object->setIsActive(false);

        $this->admin->getModelManager()->update($object);
    }

    /**
     * @param ProxyQueryInterface $query
     *
     * @return RedirectResponse
     */
    public function batchActionDisable(ProxyQueryInterface $query)
    {
        if (false === $this->admin->isGranted('EDIT')) {
            throw new AccessDeniedException();
        }

        foreach ($query->execute() as $object) {
            $this->disableFilter($object);
        }

        return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
    }

    /**
     * @param FilterInterface $object
     */
    protected function enableFilter(FilterInterface $object)
    {
        $object->setIsActive(true);

        $this->admin->getModelManager()->update($object);
    }

    /**
     * @param ProxyQueryInterface $query
     *
     * @return RedirectResponse
     */
    public function batchActionEnable(ProxyQueryInterface $query)
    {
        if (false === $this->admin->isGranted('EDIT')) {
            throw new AccessDeniedException();
        }

        foreach ($query->execute() as $object) {
            $this->enableFilter($object);
        }

        return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
    }
}

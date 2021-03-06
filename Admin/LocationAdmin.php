<?php

namespace Bangpound\Bundle\TwitterStreamingBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class LocationAdmin extends FilterAdmin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('south')
            ->add('west')
            ->add('north')
            ->add('east')
            ->add('isActive', 'checkbox', array('required' => false))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('isActive')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('south')
            ->add('west')
            ->add('north')
            ->add('east')
            ->add('isActive', null, array('editable' => true))
        ;
    }
}

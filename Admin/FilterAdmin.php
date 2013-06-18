<?php

namespace Bangpound\Twitter\StreamingBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;

abstract class FilterAdmin extends Admin
{
    /**
     * {@inheritdoc}
     */
    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        $actions['enable'] = array(
            'label'            => $this->trans($this->getLabelTranslatorStrategy()->getLabel('enable', 'batch', 'message')),
            'ask_confirmation' => false,
        );

        $actions['disable'] = array(
            'label'            => $this->trans($this->getLabelTranslatorStrategy()->getLabel('disable', 'batch', 'message')),
            'ask_confirmation' => false,
        );

        return $actions;
    }
}
<?php

namespace Bangpound\Bundle\TwitterStreamingBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class BangpoundTwitterStreamingBundle extends Bundle
{
    public function build(\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new NotificationAdminCompilerPass());
    }
}

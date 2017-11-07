<?php
namespace Granam\Tests\GpWebPay\Flat\Sections;

use Granam\Tests\ExceptionsHierarchy\Exceptions\AbstractExceptionsHierarchyTest;

class GpWebPaySectionsExceptionsHierarchyTest extends AbstractExceptionsHierarchyTest
{
    /**
     * @return string
     */
    protected function getTestedNamespace(): string
    {
        return str_replace('\\Tests', '', __NAMESPACE__);
    }

    /**
     * @return string
     */
    protected function getRootNamespace(): string
    {
        return str_replace(['\\Tests', '\\Sections'], '', __NAMESPACE__);
    }

}
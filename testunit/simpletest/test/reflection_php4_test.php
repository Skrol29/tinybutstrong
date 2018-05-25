<?php
// $Id: reflection_php4_test.php,v 1.1 2009-06-21 17:26:52 fabrice Exp $
require_once(dirname(__FILE__) . '/../autorun.php');

class AnyOldThing
{
    public function aMethod()
    {
    }
}

class AnyOldChildThing extends AnyOldThing
{
}

class TestOfReflection extends UnitTestCase
{
    public function testClassExistence()
    {
        $reflection = new SimpleReflection('AnyOldThing');
        $this->assertTrue($reflection->classOrInterfaceExists());
        $this->assertTrue($reflection->classOrInterfaceExistsSansAutoload());
    }

    public function testClassNonExistence()
    {
        $reflection = new SimpleReflection('UnknownThing');
        $this->assertFalse($reflection->classOrInterfaceExists());
        $this->assertFalse($reflection->classOrInterfaceExistsSansAutoload());
    }

    public function testDetectionOfInterfacesAlwaysFalse()
    {
        $reflection = new SimpleReflection('AnyOldThing');
        $this->assertFalse($reflection->isAbstract());
        $this->assertFalse($reflection->isInterface());
    }

    public function testFindingParentClass()
    {
        $reflection = new SimpleReflection('AnyOldChildThing');
        $this->assertEqual(strtolower($reflection->getParent()), 'anyoldthing');
    }

    public function testMethodsListFromClass()
    {
        $reflection = new SimpleReflection('AnyOldThing');
        $methods = $reflection->getMethods();
        $this->assertEqualIgnoringCase($methods[0], 'aMethod');
    }

    public function testNoInterfacesForPHP4()
    {
        $reflection = new SimpleReflection('AnyOldThing');
        $this->assertEqual(
                $reflection->getInterfaces(),
                array()
        );
    }

    public function testMostGeneralPossibleSignature()
    {
        $reflection = new SimpleReflection('AnyOldThing');
        $this->assertEqualIgnoringCase(
                $reflection->getSignature('aMethod'),
                'function &aMethod()'
        );
    }

    public function assertEqualIgnoringCase($a, $b)
    {
        return $this->assertEqual(strtolower($a), strtolower($b));
    }
}

<?php
// $Id: interfaces_test.php,v 1.1 2009-06-21 17:26:52 fabrice Exp $
require_once(dirname(__FILE__) . '/../autorun.php');
if (function_exists('spl_classes')) {
    include(dirname(__FILE__) . '/support/spl_examples.php');
}

interface DummyInterface
{
    public function aMethod();
    public function anotherMethod($a);
    public function &referenceMethod(&$a);
}

Mock::generate('DummyInterface');
Mock::generatePartial('DummyInterface', 'PartialDummyInterface', array());

class TestOfMockInterfaces extends UnitTestCase
{
    public function testCanMockAnInterface()
    {
        $mock = new MockDummyInterface();
        $this->assertIsA($mock, 'SimpleMock');
        $this->assertIsA($mock, 'MockDummyInterface');
        $this->assertTrue(method_exists($mock, 'aMethod'));
        $this->assertTrue(method_exists($mock, 'anotherMethod'));
        $this->assertNull($mock->aMethod());
    }

    public function testMockedInterfaceExpectsParameters()
    {
        $mock = new MockDummyInterface();
        $mock->anotherMethod();
        $this->assertError();
    }

    public function testCannotPartiallyMockAnInterface()
    {
        $this->assertFalse(class_exists('PartialDummyInterface'));
    }
}

class TestOfSpl extends UnitTestCase
{
    public function skip()
    {
        $this->skipUnless(function_exists('spl_classes'), 'No SPL module loaded');
    }

    public function testCanMockAllSplClasses()
    {
        if (! function_exists('spl_classes')) {
            return;
        }
        foreach (spl_classes() as $class) {
            if ($class == 'SplHeap') {
                continue;
            }
            $mock_class = "Mock$class";
            Mock::generate($class);
            $this->assertIsA(new $mock_class(), $mock_class);
        }
    }

    public function testExtensionOfCommonSplClasses()
    {
        Mock::generate('IteratorImplementation');
        $this->assertIsA(
                new IteratorImplementation(),
                'IteratorImplementation'
        );
        Mock::generate('IteratorAggregateImplementation');
        $this->assertIsA(
                new IteratorAggregateImplementation(),
                'IteratorAggregateImplementation'
        );
    }
}

class WithHint
{
    public function hinted(DummyInterface $object)
    {
    }
}

class ImplementsDummy implements DummyInterface
{
    public function aMethod()
    {
    }
    public function anotherMethod($a)
    {
    }
    public function &referenceMethod(&$a)
    {
    }
    public function extraMethod($a = false)
    {
    }
}
Mock::generate('ImplementsDummy');

class TestOfImplementations extends UnitTestCase
{
    public function testMockedInterfaceCanPassThroughTypeHint()
    {
        $mock = new MockDummyInterface();
        $hinter = new WithHint();
        $hinter->hinted($mock);
    }

    public function testImplementedInterfacesAreCarried()
    {
        $mock = new MockImplementsDummy();
        $hinter = new WithHint();
        $hinter->hinted($mock);
    }
    
    public function testNoSpuriousWarningsWhenSkippingDefaultedParameter()
    {
        $mock = new MockImplementsDummy();
        $mock->extraMethod();
    }
}

interface SampleClassWithConstruct
{
    public function __construct($something);
}

class TestOfInterfaceMocksWithConstruct extends UnitTestCase
{
    public function testBasicConstructOfAnInterface()
    {
        Mock::generate('SampleClassWithConstruct');
        $this->assertNoErrors();
    }
}

interface SampleInterfaceWithHintInSignature
{
    public function method(array $hinted);
}

class TestOfInterfaceMocksWithHintInSignature extends UnitTestCase
{
    public function testBasicConstructOfAnInterfaceWithHintInSignature()
    {
        Mock::generate('SampleInterfaceWithHintInSignature');
        $this->assertNoErrors();
        $mock = new MockSampleInterfaceWithHintInSignature();
        $this->assertIsA($mock, 'SampleInterfaceWithHintInSignature');
    }
}

interface SampleInterfaceWithClone
{
    public function __clone();
}

class TestOfSampleInterfaceWithClone extends UnitTestCase
{
    public function testCanMockWithoutErrors()
    {
        Mock::generate('SampleInterfaceWithClone');
        $this->assertNoErrors();
    }
}

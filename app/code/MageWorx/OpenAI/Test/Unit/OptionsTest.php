<?php

namespace MageWorx\OpenAI\Test\Unit;

use MageWorx\OpenAI\Model\Options;
use MageWorx\OpenAI\Helper\Data as Helper;
use PHPUnit\Framework\TestCase;

class OptionsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->helperMock = $this->getMockBuilder(Helper::class)
                                 ->disableOriginalConstructor()
                                 ->getMock();

        $this->options = new Options(
            $this->helperMock
        );
    }

    /**
     * Test covers the converting object to array and back to object
     *
     * @covers \MageWorx\OpenAI\Model\Options::fromArray
     * @covers \MageWorx\OpenAI\Model\Options::toArray
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     */
    public function testToArrayAndFromArray(): void
    {
        $dummyApiKey = '1a2f3d4g5h6j7k8l9z0x';
        $this->helperMock->method('getApiKey')->willReturn($dummyApiKey);

        // Set up some initial data
        $headers     = ['Content-Type: application/json', 'Authorization' => 'Authorization: Bearer ' . $dummyApiKey];
        $initialData = [
            'headers'     => $headers,
            'model'       => 'gpt-3.5-turbo',
            'path'        => '/some/path',
            'temperature' => 0.7,
            'max_tokens'  => 150,
            'n'           => 2
        ];

        // Populate the object with initial data
        $this->options->fromArray($initialData);

        // Convert object to array
        $arrayData = $this->options->toArray();

        // Assertions to ensure data integrity
        $this->assertNotEquals($initialData['headers'], $arrayData['headers']);
        $this->assertArrayNotHasKey('Authorization', $arrayData['headers']);
        $this->assertEquals($initialData['model'], $arrayData['model']);
        $this->assertEquals($initialData['path'], $arrayData['path']);
        $this->assertEquals($initialData['temperature'], $arrayData['temperature']);
        $this->assertEquals($initialData['max_tokens'], $arrayData['max_tokens']);
        $this->assertEquals($initialData['n'], $arrayData['n']);

        // Convert array back to object
        $newOptions = new Options($this->helperMock);
        $newOptions->fromArray($arrayData);

        // Assertions to ensure the object is correctly populated from the array
        $this->assertEquals($initialData['headers'], $newOptions->getHeaders());
        $this->assertEquals($initialData['model'], $newOptions->getModel());
        $this->assertEquals($initialData['path'], $newOptions->getPath());
        $this->assertEquals($initialData['temperature'], $newOptions->getTemperature());
        $this->assertEquals($initialData['max_tokens'], $newOptions->getMaxTokens());
        $this->assertEquals($initialData['n'], $newOptions->getNumberOfResultOptions());
    }

    /**
     * Test covers the toArray method calls. Only registered getters must be called.
     * When you are adding some new property to the Options class, you must update this test case too.
     *
     * @covers \MageWorx\OpenAI\Model\Options::toArray
     * @return void
     */
    public function testToArrayMethodCalls(): void
    {
        // You need to add the methods you want to the toArray method, but them should be registered
        $registeredMethods = [
            'getHeaders',
            'getModel',
            'getPath',
            'getTemperature',
            'getMaxTokens',
            'getNumberOfResultOptions'
        ];

        $optionsMock = $this->getMockBuilder(Options::class)
                            ->onlyMethods($registeredMethods)
                            ->setConstructorArgs([$this->helperMock])
                            ->getMock();

        // Define expectations for the method calls
        $optionsMock->expects($this->once())->method('getHeaders');
        $optionsMock->expects($this->once())->method('getModel');
        $optionsMock->expects($this->once())->method('getPath');
        $optionsMock->expects($this->once())->method('getTemperature');
        $optionsMock->expects($this->once())->method('getMaxTokens');
        $optionsMock->expects($this->once())->method('getNumberOfResultOptions');

        $optionsMock->toArray();
    }

    /**
     * Test covers the fromArray method calls. Only registered setters must be called.
     * When you are adding some new property to the Options class, you must update this test case too.
     *
     * @covers \MageWorx\OpenAI\Model\Options::fromArray
     * @return void
     */
    public function testFromArrayMethodCalls(): void
    {
        // You need to add the methods you want to the fromArray method, but them should be registered
        $registeredMethods = [
            'setHeaders',
            'setModel',
            'setPath',
            'setTemperature',
            'setMaxTokens',
            'setNumberOfResultOptions'
        ];
        // Initial data
        $data = [
            'headers'     => ['Content-Type: application/json'],
            'model'       => 'gpt-3.5-turbo',
            'path'        => '/some/path',
            'temperature' => 0.7,
            'max_tokens'  => 150,
            'n'           => 2
        ];

        $optionsMock = $this->getMockBuilder(Options::class)
                            ->onlyMethods($registeredMethods)
                            ->setConstructorArgs([$this->helperMock])
                            ->getMock();

        // Define expectations for the method calls
        $optionsMock->expects($this->any())->method('setHeaders');
        $optionsMock->expects($this->once())->method('setModel');
        $optionsMock->expects($this->once())->method('setPath');
        $optionsMock->expects($this->once())->method('setTemperature');
        $optionsMock->expects($this->once())->method('setMaxTokens');
        $optionsMock->expects($this->once())->method('setNumberOfResultOptions');

        $optionsMock->fromArray($data);
    }
}

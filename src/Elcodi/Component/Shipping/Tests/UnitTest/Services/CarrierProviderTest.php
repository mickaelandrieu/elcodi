<?php

/**
 * This file is part of the Elcodi package.
 *
 * Copyright (c) 2014 Elcodi.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 * @author Aldo Chiecchia <zimage@tiscali.it>
 */

namespace Elcodi\Component\Shipping\Tests\UnitTest\Services;

use Elcodi\Component\Currency\Entity\Interfaces\CurrencyInterface;
use Elcodi\Component\Currency\Entity\Money;
use Elcodi\Component\Currency\Services\CurrencyConverter;
use Elcodi\Component\Shipping\Entity\Interfaces\CarrierInterface;
use Elcodi\Component\Shipping\Entity\Interfaces\CarrierWeightRangeInterface;
use Elcodi\Component\Shipping\Repository\CarrierRepository;
use Elcodi\Component\Shipping\Services\CarrierProvider;
use PHPUnit_Framework_TestCase;
use Elcodi\Component\Cart\Entity\Interfaces\CartInterface;

/**
 * Class CarrierProviderTest
 */
class CarrierProviderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var CartInterface
     *
     * Cart
     */
    private $cart;

    /**
     * @var CarrierRepository
     *
     * Carrier repository
     */
    private $carrierRepository;

    /**
     * @var CurrencyConverter
     *
     * Currency converter
     */
    private $currencyConverter;

    /**
     * @var CurrencyInterface
     *
     * Currency
     */
    private $currency;

    /**
     * @var CarrierProvider
     *
     * Carrier Provider
     */
    private $carrierProvider;

    /**
     * Setup
     */
    public function setUp()
    {
        $this->cart = $this->getMock('Elcodi\Component\Cart\Entity\Interfaces\CartInterface');
        $this->carrierRepository = $this->getMock('Elcodi\Component\Shipping\Repository\CarrierRepository', [], [], '', false);
        $this->currencyConverter = $this->getMock('Elcodi\Component\Currency\Services\CurrencyConverter', [], [], '', false);

        $this
            ->currencyConverter
            ->expects($this->any())
            ->method('convertMoney')
            ->will($this->returnArgument(0));

        $this->carrierProvider = new CarrierProvider(
            $this->carrierRepository,
            $this->currencyConverter
        );

        $this->currency = $this->getMock('Elcodi\Component\Currency\Entity\Interfaces\CurrencyInterface');

        $this->currency
            ->expects($this->any())
            ->method('getSymbol')
            ->will($this->returnValue('$'));

        $this->currency
            ->expects($this->any())
            ->method('getIso')
            ->will($this->returnValue('USD'));

        $amount = Money::create(100, $this->currency);

        $this
            ->cart
            ->expects($this->any())
            ->method('getWeight')
            ->will($this->returnValue(10));

        $this
            ->cart
            ->expects($this->any())
            ->method('getAmount')
            ->will($this->returnValue($amount));
    }

    /**
     * Tests isCarrierWeightRangeSatisfiedByCart
     *
     * @dataProvider dataIsCarrierWeightRangeSatisfiedByCartOk
     */
    public function testIsCarrierWeightRangeSatisfiedByCart(
        $fromWeight,
        $toWeight,
        $isSatisfied
    )
    {
        $carrierRange = $this->getCarrierWeightRangeMock(
            $fromWeight,
            $toWeight
        );

        $this->assertEquals(
            $isSatisfied,
            $this
                ->carrierProvider
                ->isCarrierWeightRangeSatisfiedByCart(
                    $this->cart,
                    $carrierRange
                )
        );
    }

    /**
     * Data for testIsCarrierWeightRangeSatisfiedByCart
     *
     * @return array
     */
    public function dataIsCarrierWeightRangeSatisfiedByCartOk()
    {
        return [
            [5, 15, true],
            [5, 10, true],
            [10, 15, true],
            [10, 10, true],
            [5, 9, false],
            [11, 15, false],
            [10, null, false],
            [10, true, false],
            [10, false, false],
            [null, null, false],
            [true, true, false],
        ];
    }

    /**
     * Tests isCarrierPriceRangeSatisfiedByCart with same currency
     *
     * @dataProvider dataIsCarrierPriceRangeSatisfiedByCartSameCurrency
     */
    public function testIsCarrierPriceRangeSatisfiedByCartSameCurrency(
        $fromPrice,
        $toPrice,
        $isSatisfied
    )
    {
        $priceRange = $this->getCarrierPriceRangeMock(
            $fromPrice,
            $toPrice
        );

        $this->assertEquals(
            $isSatisfied,
            $this
                ->carrierProvider
                ->isCarrierPriceRangeSatisfiedByCart(
                    $this->cart,
                    $priceRange
                )
        );
    }

    /**
     * Data for testIsCarrierPriceRangeSatisfiedByCartSameCurrency
     *
     * @return array
     */
    public function dataIsCarrierPriceRangeSatisfiedByCartSameCurrency()
    {
        return [
            [50, 150, true],
            [50, 100, true],
            [100, 150, true],
            [100, 100, true],
            [50, 90, false],
            [110, 150, false],
        ];
    }

    /**
     * Test getCarrierRangeSatisfiedByCartOk
     */
    public function testGetCarrierRangeSatisfiedByCart()
    {
        $carrier = $this->getCarrierMock(50, 100, 10, 15);

        $this->assertInstanceOf(
            'Elcodi\Component\Shipping\Entity\Interfaces\CarrierRangeInterface',
            $this->carrierProvider->getCarrierRangeSatisfiedByCart(
                $this->cart,
                $carrier
            )
        );
    }

    /**
     * Test getCarrierRangeSatisfiedByCartFail
     */
    public function testGetCarrierRangeSatisfiedByCartFail()
    {
        $carrier = $this->getCarrierMock(10, 20, 50, 55);

        $this->assertFalse($this->carrierProvider->getCarrierRangeSatisfiedByCart(
                $this->cart,
                $carrier
            )
        );
    }

    /**
     * Test provideCarrierRangesSatisfiedWithCart
     */
    public function testProvideCarrierRangesSatisfiedWithCart()
    {
        $this
            ->carrierRepository
            ->expects($this->any())
            ->method('findBy')
            ->will($this->returnValue(array(
                $this->getCarrierMock(100, 110, 40, 50),
                $this->getCarrierMock(120, 300, 500, 1000),
                $this->getCarrierMock(50, 101, 10, 10),
            )));

        $carrierRanges = $this
            ->carrierProvider
            ->provideCarrierRangesSatisfiedWithCart($this->cart);

        $this->assertCount(2, $carrierRanges);

        foreach ($carrierRanges as $carrierRange) {

            $this->assertInstanceOf(
                'Elcodi\Component\Shipping\Entity\Interfaces\CarrierRangeInterface',
                $carrierRange
            );
        }
    }

    /**
     * Get Carrier mock
     *
     * @param string $fromPrice  From price
     * @param string $toPrice    To price
     * @param string $fromWeight From weight
     * @param string $toWeight   To weight
     *
     * @return CarrierInterface
     */
    private function getCarrierMock(
        $fromPrice,
        $toPrice,
        $fromWeight,
        $toWeight
    )
    {
        $carrier = $this->getMock('Elcodi\Component\Shipping\Entity\Interfaces\CarrierInterface');

        $carrierRanges = array(
            $this->getCarrierPriceRangeMock($fromPrice, $toPrice),
            $this->getCarrierWeightRangeMock($fromWeight, $toWeight),
        );

        $carrier
            ->expects($this->any())
            ->method('getRanges')
            ->will($this->returnValue($carrierRanges));

        return $carrier;
    }

    /**
     * Get CarrierPriceRange mock
     *
     * @param string $fromPrice From price
     * @param string $toPrice   To price
     *
     * @return CarrierWeightRangeInterface
     */
    private function getCarrierPriceRangeMock(
        $fromPrice,
        $toPrice
    )
    {
        $priceRange = $this->getMock('Elcodi\Component\Shipping\Entity\Interfaces\CarrierPriceRangeInterface');

        $priceRange
            ->expects($this->any())
            ->method('getFromPrice')
            ->will($this->returnValue(
                Money::create($fromPrice, $this->currency)
            ));

        $priceRange
            ->expects($this->any())
            ->method('getToPrice')
            ->will($this->returnValue(
                Money::create($toPrice, $this->currency)
            ));

        return $priceRange;
    }

    /**
     * Get CarrierWeightRange mock
     *
     * @param string $fromWeight From weight
     * @param string $toWeight   To weight
     *
     * @return CarrierWeightRangeInterface
     */
    private function getCarrierWeightRangeMock(
        $fromWeight,
        $toWeight
    )
    {
        $carrierRange = $this->getMock('Elcodi\Component\Shipping\Entity\Interfaces\CarrierWeightRangeInterface');

        $carrierRange
            ->expects($this->any())
            ->method('getFromWeight')
            ->will($this->returnValue($fromWeight));

        $carrierRange
            ->expects($this->any())
            ->method('getToWeight')
            ->will($this->returnValue($toWeight));

        return $carrierRange;
    }
}
 
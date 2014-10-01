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

namespace Elcodi\Component\Shipping\Services;

use Elcodi\Component\Cart\Entity\Interfaces\CartInterface;
use Elcodi\Component\Currency\Services\CurrencyConverter;
use Elcodi\Component\Geo\Services\ZoneManager;
use Elcodi\Component\Geo\Services\ZoneMatcher;
use Elcodi\Component\Shipping\Entity\Abstracts\AbstractCarrierRange;
use Elcodi\Component\Shipping\Entity\Interfaces\CarrierInterface;
use Elcodi\Component\Shipping\Entity\Interfaces\CarrierPriceRangeInterface;
use Elcodi\Component\Shipping\Entity\Interfaces\CarrierRangeInterface;
use Elcodi\Component\Shipping\Entity\Interfaces\CarrierWeightRangeInterface;
use Elcodi\Component\Shipping\Repository\CarrierRepository;

/**
 * Class CarrierProvider
 */
class CarrierProvider
{
    /**
     * @var CarrierRepository
     *
     * carrierRepository
     */
    protected $carrierRepository;

    /**
     * @var CurrencyConverter
     *
     * currencyConverter
     */
    protected $currencyConverter;

    /**
     * @var ZoneMatcher
     *
     * ZoneMatcher
     */
    protected $zoneMatcher;

    /**
     * Construct method
     *
     * @param CarrierRepository $carrierRepository Carrier Repository
     * @param CurrencyConverter $currencyConverter Currency Converter
     */
    public function __construct(
        CarrierRepository $carrierRepository,
        CurrencyConverter $currencyConverter
    )
    {
        $this->carrierRepository = $carrierRepository;
        $this->currencyConverter = $currencyConverter;
    }

    /**
     * Given a Cart, return a set of CarrierRanges satisfied.
     *
     * @param CartInterface $cart Cart
     *
     * @return CarrierRangeInterface[] Set of carriers ranges satisfied
     */
    public function provideCarrierRangesSatisfiedWithCart(CartInterface $cart)
    {
        $availableCarriers = $this
            ->carrierRepository
            ->findBy([
                'enabled' => true
            ]);

        $satisfiedCarriers = array();

        foreach ($availableCarriers as $carrier) {

            $carrierRange = $this->getCarrierRangeSatisfiedByCart(
                $cart,
                $carrier
            );

            if ($carrierRange instanceof CarrierRangeInterface) {

                $satisfiedCarriers[] = $carrierRange;
            }
        }

        return $satisfiedCarriers;
    }

    /**
     * Return the first Carrier's CarrierRange satisfied by a Cart.
     *
     * If none is found, return false
     *
     * @param CartInterface    $cart
     * @param CarrierInterface $carrier
     *
     * @return CarrierRangeInterface|false CarrierRange satisfied by Cart
     */
    public function getCarrierRangeSatisfiedByCart(
        CartInterface $cart,
        CarrierInterface $carrier
    )
    {
        $carrierRanges = $carrier->getRanges();

        foreach ($carrierRanges as $carrierRange) {

            $carrierRangeSatisfied = $this->isCarrierRangeSatisfiedByCart(
                $cart,
                $carrierRange
            );

            if ($carrierRangeSatisfied) {

                return $carrierRange;
            }
        }

        return false;
    }

    /**
     * Return if Carrier Range is satisfied by cart
     *
     * @param CartInterface         $cart
     * @param CarrierRangeInterface $carrierRange
     *
     * @return boolean Carrier Range is satisfied by cart
     */
    public function isCarrierRangeSatisfiedByCart(
        CartInterface $cart,
        CarrierRangeInterface $carrierRange
    )
    {
        if ($carrierRange instanceof CarrierPriceRangeInterface) {

            return $this->isCarrierPriceRangeSatisfiedByCart($cart, $carrierRange);
        } elseif ($carrierRange instanceof CarrierWeightRangeInterface) {

            return $this->isCarrierWeightRangeSatisfiedByCart($cart, $carrierRange);
        }

        return false;
    }

    /**
     * Given CarrierPriceRange is satisfied by a cart
     *
     * @param CartInterface              $cart         Cart
     * @param CarrierPriceRangeInterface $carrierRange Carrier Range
     *
     * @return bool CarrierRange is satisfied by cart
     */
    public function isCarrierPriceRangeSatisfiedByCart(
        CartInterface $cart,
        CarrierPriceRangeInterface $carrierRange
    )
    {
        $cartPrice = $cart->getAmount();
        $cartPriceCurrency = $cartPrice->getCurrency();
        $carrierRangeFromPrice = $carrierRange->getFromPrice();
        $carrierRangeToPrice = $carrierRange->getToPrice();

        return
            $this->isCarrierRangeZonesSatisfiedByCart($cart, $carrierRange) &&
            (
                $this
                    ->currencyConverter
                    ->convertMoney($carrierRangeFromPrice, $cartPriceCurrency)
                    ->compareTo($cartPrice) <= 0
            ) &&
            (
                $this
                    ->currencyConverter
                    ->convertMoney($carrierRangeToPrice, $cartPriceCurrency)
                    ->compareTo($cartPrice) >= 0
            );
    }

    /**
     * Given CarrierWeightRange is satisfied by a cart
     *
     * @param CartInterface               $cart         Cart
     * @param CarrierWeightRangeInterface $carrierRange Carrier Range
     *
     * @return bool CarrierRange is satisfied by cart
     */
    public function isCarrierWeightRangeSatisfiedByCart(
        CartInterface $cart,
        CarrierWeightRangeInterface $carrierRange
    )
    {
        $cartWeight = $cart->getWeight();
        $cartRangeFromWeight = $carrierRange->getFromWeight();
        $cartRangeToWeight = $carrierRange->getToWeight();

        return
            $this->isCarrierRangeZonesSatisfiedByCart($cart, $carrierRange) &&
            is_numeric($cartRangeFromWeight) &&
            is_numeric($cartRangeToWeight) &&
            $cartRangeFromWeight >= 0 &&
            $cartRangeToWeight >= 0 &&
            $cartWeight >= $cartRangeFromWeight &&
            $cartWeight <= $cartRangeToWeight;
    }

    /**
     * Given CarrierRange zones are satisfied by a cart
     *
     * @param CartInterface         $cart         Cart
     * @param CarrierRangeInterface $carrierRange Carrier Range
     *
     * @return bool CarrierRange is satisfied by cart
     */
    public function isCarrierRangeZonesSatisfiedByCart(
        CartInterface $cart,
        CarrierRangeInterface $carrierRange
    )
    {
        return true;
    }
}
 
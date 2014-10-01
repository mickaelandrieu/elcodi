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
use Elcodi\Component\Shipping\Resolver\CarrierResolver;
use Symfony\Component\Validator\Constraints\Collection;

/**
 * Class ShippingProvider
 */
class ShippingProvider
{
    /**
     * @var CarrierProvider
     *
     * Carrier Provider
     */
    protected $carrierProvider;

    /**
     * @var CarrierResolver
     *
     * Carrier Resolver
     */
    protected $carrierResolver;

    /**
     * Construct method
     *
     * @param CarrierProvider $carrierProvider Carrier Provider
     * @param CarrierResolver $carrierResolver Carrier Resolver
     */
    public function __construct(
        CarrierProvider $carrierProvider,
        CarrierResolver $carrierResolver
    )
    {
        $this->carrierProvider = $carrierProvider;
        $this->carrierResolver = $carrierResolver;
    }

    /**
     * Return all valid CarrierRanges satisfied by a Cart
     *
     * @param CartInterface $cart Cart
     *
     * @return array Valid CarrierRanges satisfied by the cart
     */
    public function getValidCarrierRangesFromCart(CartInterface $cart)
    {
        $carrierRanges = $this->getAllCarrierRangesFromCart($cart);

        return $this
            ->carrierResolver
            ->resolveCarrierRanges($carrierRanges);
    }

    /**
     * Return all CarrierRanges satisfied by a Cart
     *
     * @param CartInterface $cart Cart
     *
     * @return array CarrierRanges satisfied by the cart
     */
    public function getAllCarrierRangesFromCart(CartInterface $cart)
    {
        return $this
            ->carrierProvider
            ->provideCarrierRangesSatisfiedWithCart($cart);
    }
}
 
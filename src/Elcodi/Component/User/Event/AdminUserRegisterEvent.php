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

namespace Elcodi\Component\User\Event;

use Symfony\Component\EventDispatcher\Event;

use Elcodi\Component\User\Entity\Interfaces\AdminUserInterface;

/**
 * Class AdminUserRegisterEvent
 */
class AdminUserRegisterEvent extends Event
{
    /**
     * @var AdminUserInterface
     *
     * AdminUser
     */
    protected $adminUser;

    /**
     * Construct method
     *
     * @param AdminUserInterface $adminUser Admin User
     */
    public function __construct(AdminUserInterface $adminUser)
    {
        $this->adminUser = $adminUser;
    }

    /**
     * @return AdminUserInterface Admin User
     */
    public function getAdminUser()
    {
        return $this->adminUser;
    }
}

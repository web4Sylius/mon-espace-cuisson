<?php
/*
 * This file is part of the fnacMarketPlace APi Client.
 * (c) 2011 Fnac
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FnacApiClient\Type;

/**
 * SellerType
 *
 * @author     Fnac
 * @version    1.0.0
 */
class SellerType extends AbstractType
{
    /**
     * All sellers including you
     */
    const ALL = "all";

    /**
     * All sellers expecting you
     */
    const OTHERS = "others";

    protected static $validType = array(
        self::ALL, self::OTHERS
    );
}

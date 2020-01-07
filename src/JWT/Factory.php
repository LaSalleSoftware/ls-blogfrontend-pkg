<?php

/**
 * This file is part of the Lasalle Software blog front-end package
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright  (c) 2019-2020 The South LaSalle Trading Corporation
 * @license    http://opensource.org/licenses/MIT
 * @author     Bob Bloom
 * @email      bob.bloom@lasallesoftware.ca
 * @link       https://lasallesoftware.ca
 * @link       https://packagist.org/packages/lasallesoftware/lsv2-blogfrontend-pkg
 * @link       https://github.com/LaSalleSoftware/lsv2-blogfrontend-pkg
 *
 */

namespace Lasallesoftware\Blogfrontend\JWT;

// LaSalle Software
use Lasallesoftware\Library\UniversallyUniqueIDentifiers\UuidGenerator;

// Third party classes
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;


class Factory
{
    /**
     * @var Lasallesoftware\Library\UniversallyUniqueIDentifiers\UuidGenerator
     */
    protected $uuidGenerator;

    /**
     * @var string
     */
    protected $uuidComment;


    /**
     * @param  UuidGenerator  $uuidGenerator
     */
    public function __construct(UuidGenerator  $uuidGenerator)
    {
        $this->uuidGenerator = $uuidGenerator;
    }

    /**
     * Create a JWT
     *
     * https://auth0.com/docs/tokens/jwt-claims
     * https://tools.ietf.org/html/rfc7519#section-4
     *
     * The JWT specification defines seven reserved claims that are not required, but are recommended to allow interoperability with third-party applications. These are:
     *
     *  iss (issuer): Issuer of the JWT
     *  sub (subject): Subject of the JWT (the user)
     *  aud (audience): Recipient for which the JWT is intended
     *  exp (expiration time): Time after which the JWT expires
     *  nbf (not before time): Time before which the JWT must not be accepted for processing
     *  iat (issued at time): Time at which the JWT was issued; can be used to determine age of the JWT
     *  jti (JWT ID): Unique identifier; can be used to prevent the JWT from being replayed (allows a token to be used only once)
     *
     * @return string
     */
    public function createJWT($comment = null)
    {
        $signer    = new Sha256();
        $key       = config('lasallesoftware-frontendapp.lasalle_jwt_key');
        $time      = time();

        $issClaim  = app('config')->get('lasallesoftware-library.lasalle_app_domain_name');
        $audClaim  = env('LASALLE_JWT_AUD_CLAIM');
        $jtiClaim  = $this->makeUuid($comment);
        $iatClaim  = $time;
        $nbfClaim  = $time + 60;  // not used, but left as a placeholder
        $expClaim  = $time + config('lasallesoftware-library.lasalle_jwt_exp_claim_seconds_to_expiration');;

        $token = (new Builder())
            ->issuedBy($issClaim)                // Configures the issuer (iss claim)
            ->permittedFor($audClaim)            // Configures the audience (aud claim)
            ->identifiedBy($jtiClaim, true)      // Configures the id (jti claim), replicating as a header item
            ->issuedAt($iatClaim)                // Configures the time that the token was issue (iat claim)
            ->canOnlyBeUsedAfter($nbfClaim)      // Configures the time that the token can be used (nbf claim)
            ->expiresAt($expClaim)               // Configures the expiration time of the token (exp claim)
            ->getToken($signer, new Key($key))   // Retrieves the generated token
        ;

        return $token;
    }

    public function makeUuid($comment)
    {
        return $this->uuidGenerator->createUuid(9, $comment, 1);
    }
}

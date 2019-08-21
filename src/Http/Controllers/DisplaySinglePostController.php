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
 * @copyright  (c) 2019 The South LaSalle Trading Corporation
 * @license    http://opensource.org/licenses/MIT
 * @author     Bob Bloom
 * @email      bob.bloom@lasallesoftware.ca
 * @link       https://lasallesoftware.ca
 * @link       https://packagist.org/packages/lasallesoftware/lsv2-blogfrontend-pkg
 * @link       https://github.com/LaSalleSoftware/lsv2-blogfrontend-pkg
 *
 */

namespace Lasallesoftware\Blogfrontend\Http\Controllers;

// LaSalle Software
use Lasallesoftware\Library\Common\Http\Controllers\CommonController;
use Lasallesoftware\Library\UniversallyUniqueIDentifiers\UuidGenerator;


// Third party classes
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;

class DisplaySinglePostController extends CommonController
{
    public function DisplaySinglePost()
    {
        $token = $this->createJWT();

        $headers = [
            'Authorization'   => 'Bearer ' . $token,
            'InstalledDomain' => 1,
            'Accept'          => 'application/json',
        ];

        $client = new Client();

        //$getUrl = "http://temp.api.com:8888/api";
        $getUrl = "http://hackintosh.lsv2-adminbackend-app.com:8888/singlearticleblog";

        try {

            $response = $client->request('GET', $getUrl, [
                'headers'         => $headers,
                'connect_timeout' => 10,
            ]);

            // Here the code for successful request
            $body = json_decode($response->getBody());

            echo "<h1>" . $getUrl . "</h1>";
            echo "<h1>" . $response->getStatusCode() . "</h1>";

            $this->viewPost($body->post, $body->tags);

            $this->viewPostupdates($body->postupdates);

            echo "<br><br>---- end of post! -----<br>";

            echo "<h1>token = "  . $body->token;
            echo "<br>domain = " . $body->domain;




        } catch (RequestException $e) {

            // BAD REQUEST
            // The server cannot or will not process the request due to something that is perceived to be a client error
            // (e.g., malformed request syntax, invalid request message framing, or deceptive request routing).
            // https://httpstatuses.com/400
            if ($e->getResponse()->getStatusCode() == '400') {
                echo "Got response 400 - Bad Request";
            }

            // UNAUTHORIZED
            // The request has not been applied because it lacks valid authentication credentials for the target resource.
            // https://httpstatuses.com/401
            if ($e->getResponse()->getStatusCode() == '401') {
                echo "Got response 401 - Unauthorized";
            }

            // FORBIDDEN
            // The server understood the request but refuses to authorize it.
            // https://httpstatuses.com/403
            if ($e->getResponse()->getStatusCode() == '403') {
                echo "Got response 404 - Forbidden";
            }

            // NOT FOUND
            // The origin server did not find a current representation for the target resource or
            // is not willing to disclose that one exists.
            // https://httpstatuses.com/404
            if ($e->getResponse()->getStatusCode() == '404') {
                echo "Got response 404 - Not Found";
            }

        } catch (\Exception $e) {

            // There was another exception.
            echo "No response was received. No status code nor any diagnostic information was given to us.";

        }
    }

    public function viewPost($post, $tags)
    {
        echo (is_null($post->featured_image)) ? "<br>(there is no featured_image)" : "<br>'('.featured_image: ".$post->featured_image.')';
        echo "<h1>" . $post->title . "</h1>";
        echo "(slug = " . $post->slug . ")";
        echo "<br>by " . $post->author;
        echo "<br>"  .$post->date;
        echo (is_null($post->category_name)) ? '' : "<br>category: " . $post->category_name;
        echo (is_null($tags)) ? '' : $this->viewTags($tags) ;
        echo "<br><br>";
        echo "(excerpt: " . $post->excerpt . ")";
        echo "<br><br>(meta_description: " . $post->meta_description . ")";
        echo "<br><br>" .  $post->content;

    }

    public function viewTags($tags)
    {
        $counter = 1;
        $numberOfTags = count($tags);
        echo "<br>tags: ";
        foreach ($tags as $tag) {

            echo $tag->title;
            if ($counter < $numberOfTags) {
                echo ", ";
            }
            $counter++;
        }
    }

    public function viewPostupdates($postupdates)
    {
        if (!is_null($postupdates)) {

            (count($postupdates) == 1) ? $word = "is" : $word = "are";

            echo "<br><h2>There " . $word . " " . count($postupdates) ." Updates For This Post!</h2>";

            foreach ($postupdates as $postupdate) {
                echo "<h3>" . $postupdate->title . "</h3>";
                echo $postupdate->date;
                echo "<br>(excerpt: " . $postupdate->excerpt . ")";
                echo "<br>" . $postupdate->content;
            }
        }
    }


    public function createJWT()
    {
        $signer           = new Sha256();
        $time             = time();
        $installed_domain = app('config')->get('lasallesoftware-library.lasalle_app_domain_name');


        // https://auth0.com/docs/tokens/jwt-claims
        // The JWT specification defines seven reserved claims that are not required, but are recommended to allow interoperability with third-party applications. These are:
        //
        // iss (issuer): Issuer of the JWT
        // sub (subject): Subject of the JWT (the user)
        // aud (audience): Recipient for which the JWT is intended
        // exp (expiration time): Time after which the JWT expires
        // nbf (not before time): Time before which the JWT must not be accepted for processing
        // iat (issued at time): Time at which the JWT was issued; can be used to determine age of the JWT
        // jti (JWT ID): Unique identifier; can be used to prevent the JWT from being replayed (allows a token to be used only once)
        $token = (new Builder())
            ->issuedBy($installed_domain)              // Configures the issuer (iss claim)
            //->permittedFor('admin')                  // Configures the audience (aud claim)
            ->identifiedBy($this->makeUuid(), true)    // Configures the id (jti claim), replicating as a header item
            ->issuedAt($time)                          // Configures the time that the token was issue (iat claim)
            ->canOnlyBeUsedAfter($time + 60)           // Configures the time that the token can be used (nbf claim)
            ->expiresAt($time + 3600)                  // Configures the expiration time of the token (exp claim)
            ->getToken($signer, new Key('testing'))    // Retrieves the generated token
        ;

        //var_dump($token->verify($signer, 'testing 1'));  // false, because the key is different
        //var_dump($token->verify($signer, 'testing'));    // true, because the key is the same

        return $token;
    }

    public function makeUuid(UuidGenerator $uuidGenerator)
    {
        return $uuidGenerator->newUuid();
    }
}

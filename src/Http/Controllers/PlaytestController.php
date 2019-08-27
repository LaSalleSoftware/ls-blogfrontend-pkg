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

use Lasallesoftware\Blogfrontend\JWT\Factory;


// Third party classes
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;

class PlaytestController extends CommonController
{
    protected $factory;

    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }
    public function Index()
    {
        $token = $this->factory->createJWT();

        echo "<br>generated token = " . $token;

        $headers = [
            'Authorization'   => 'Bearer ' . $token,
            'Accept'          => 'application/json',
        ];

        $client = new Client();

        //$getUrl = "http://temp.api.com:8888/api";
        $getUrl = "http://hackintosh.lsv2-adminbackend-app.com:8888/api/v1/testapi";

        try {

            $response = $client->request('GET', $getUrl, [
                'headers'         => $headers,
                'connect_timeout' => 10,
            ]);

            // Here the code for successful request
            $body = json_decode($response->getBody());

            echo "<h1>" . $getUrl . "</h1>";
            echo "<h1>" . $response->getStatusCode() . "</h1>";

            echo "response = " . $body->message;

            $LASALLE_JWT_AUD_CLAIM = env('LASALLE_JWT_AUD_CLAIM');
            echo "<br>LASALLE_JWT_AUD_CLAIM = " . $LASALLE_JWT_AUD_CLAIM;



            echo "<br><br>---------<br>";

            echo "<h1>token = "  . $body->token;
            echo "<br>domain = " . $body->domain;




        } catch (RequestException $e) {

            $body = json_decode($e->getResponse()->getBody());

            echo "<h2>".$e->getResponse()->getStatusCode()."</h2>";
            echo "<h2> message = ".$body->message."</h2>";

            if (isset($body->message)) echo "<h2> message = " . $body->message . "</h2>";
            if (isset($body->errors))  echo "<h2> errors = " . $body->errors . "</h2>";

            echo "<br>xxx = " . $e->getMessage();

        } catch (\Exception $e) {

            // There was another exception.
            echo "No response was received. No status code nor any diagnostic information was given to us.";
        }

    }


    public function PingBackendBlogplay()
    {
        $token = $this->factory->createJWT();

        echo "<br>generated token = " . $token;

        $headers = [
            'Authorization'   => 'Bearer ' . $token,
            'Accept'          => 'application/json',
        ];

        $client = new Client();

        //$getUrl = "http://temp.api.com:8888/api";
        $getUrl = "http://hackintosh.lsv2-adminbackend-app.com:8888/api/v1/blogplay";

        try {

            $response = $client->request('GET', $getUrl, [
                'headers'         => $headers,
                'connect_timeout' => 10,
            ]);

            // Here the code for successful request
            $body = json_decode($response->getBody());

            echo "<h1>" . $getUrl . "</h1>";
            echo "<h1>" . $response->getStatusCode() . "</h1>";

            echo "<pre>";
            print_r($response);
            echo "</pre>";

            echo "response from blogplay = " . $body->message;

            $LASALLE_JWT_AUD_CLAIM = env('LASALLE_JWT_AUD_CLAIM');
            echo "<br>LASALLE_JWT_AUD_CLAIM = " . $LASALLE_JWT_AUD_CLAIM;



            echo "<br><br>---------<br>";






        } catch (RequestException $e) {

            $body = json_decode($e->getResponse()->getBody());

            echo "<h2>".$e->getResponse()->getStatusCode()."</h2>";
            echo "<h2> message = ".$body->message."</h2>";

            if (isset($body->message)) echo "<h2> message = " . $body->message . "</h2>";
            if (isset($body->errors))  echo "<h2> errors = " . $body->errors . "</h2>";

            echo "<br>xxx = " . $e->getMessage();

        } catch (\Exception $e) {

            // There was another exception.
            echo "No response was received. No status code nor any diagnostic information was given to us.";
            echo "<br>xxx = " . $e->getMessage();
        }
    }







}

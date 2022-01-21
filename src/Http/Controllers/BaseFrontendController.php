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
 * @copyright  (c) 2019-2022 The South LaSalle Trading Corporation
 * @license    http://opensource.org/licenses/MIT
 * @author     Bob Bloom
 * @email      bob.bloom@lasallesoftware.ca
 * @link       https://lasallesoftware.ca
 * @link       https://packagist.org/packages/lasallesoftware/ls-blogfrontend-pkg
 * @link       https://github.com/LaSalleSoftware/ls-blogfrontend-pkg
 *
 */

namespace Lasallesoftware\Blogfrontend\Http\Controllers;

// LaSalle Software
use Lasallesoftware\Libraryfrontend\Common\Http\Controllers\CommonController;

class BaseFrontendController extends CommonController
{
    /**
     * Map blog front-end controller and job classes to their admin back-end endpoints.
     * 
     * I thought it would be handy to associate controller and job classes directly to back-end
     * endpoints in one place like this so I can reference this list. As well, having a real
     * array means that as I have to keep this list current when I do future development.
     *
     * @return array
     */
    public function mapFrontendClassesToEndpoints()
    {
        return [
            'DisplayHomepageBlogPostsController'      => '/api/v1/homepageblogposts',
            'blogrssfeed'            => '/api/v1/blogrssfeed',
            'allblogposts'           => '/api/v1/allblogposts',
            'allcategoryblogposts'   => '/api/v1/allcategoryblogposts',
            'alltagblogposts'        => '/api/v1/alltagblogposts',
            'allauthorblogposts'     => '/api/v1/allauthorblogposts',
            'singleblogpost'         => '/api/v1/singleblogpost',
        ];
    }

    /**
     * Get the API (back-end) endpoint path for a specific frontend class.
     *
     * @param  string  $frontendClass       Front-end class seeking an enpoint
     * @return string
     */
    public function getEndpointPath($frontendClass)
    {
        $apiEndpointList = $this->mapFrontendClassesToEndpoints();
        return $apiEndpointList[$frontendClass];
    }
}



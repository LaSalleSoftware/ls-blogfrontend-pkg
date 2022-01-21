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
use Lasallesoftware\Libraryfrontend\APIRequestsToTheBackend\HttpRequestToAdminBackend;


// Laravel Framework
use Illuminate\Support\MessageBag;

// Third party classes
use GuzzleHttp\Exception\RequestException;


class DisplayBlogPostsForAnAuthorController extends BaseFrontendController
{
    use HttpRequestToAdminBackend;

    /**
     * The message bag instance.
     *
     * @var \Illuminate\Support\MessageBag
     */
    protected $messages;


    public function DisplayBlogPostsForAnAuthor($authorNamecalculated)
    {
        $endpointPath = $this->getEndpointPath('allauthorblogposts');
        $httpRequest  = 'GET';
        $slug         = $authorNamecalculated; 

        $response = $this->sendRequestToLasalleBackend($endpointPath, $httpRequest, $slug);

        //if ($response instanceof \GuzzleHttp\Psr7\Response) {
        if (!isset($this->messages)) {

            $body = json_decode($response->getBody());

            $transformedPosts = $this->getTransformedPosts($body->posts);

            return view(config('lasallesoftware-libraryfrontend.lasalle_path_to_front_end_view_path') . '.blog.pages.allblogposts', [
                'pageTitle'           => 'Posts by ' . $authorNamecalculated,
                'posts'               => $transformedPosts,
                'numberOfPosts'       => count($transformedPosts),
                'prev_page_url'       => $this->getPageURLForPagination('author/'.str_replace(' ', '%20', $authorNamecalculated), $body->prev_page_url),
                'next_page_url'       => $this->getPageURLForPagination('author/'.str_replace(' ', '%20', $authorNamecalculated), $body->next_page_url),
                'copyright'           => env('LASALLE_COPYRIGHT_IN_FOOTER'),
                'socialMediaMetaTags' => $this->getSocialMediaMetaTags(),
                'featured_image_social_media_meta_tag' => config('lasallesoftware-libraryfrontend.lasalle_social_media_meta_tag_default_image'),
            ]);

        } else {
            return $this->displayErrorView();
        }
    }

    /**
     * Get the social media meta tags.
     *
     * @return array
     */
    private function getSocialMediaMetaTags()
    {
        // ...put all the social media meta tag info together
        // https://developer.twitter.com/en/docs/tweets/optimize-with-cards/guides/getting-started
        // https://developer.twitter.com/en/docs/tweets/optimize-with-cards/overview/summary-card-with-large-image
        // https://developer.twitter.com/en/docs/tweets/optimize-with-cards/overview/markup
        // https://ogp.me/
        return [
            'twitter_card' => 'summary_large_image',
            'og_type'      => 'website',
            'title'        => config('app.name'),
            'description'  => '',
            'url'          => url()->full(),
            'site'         => $this->getSocialMediaMetaTagSite(),
            'creator'      => $this->getSocialMediaMetaTagCreator(),
            'image'        => config('lasallesoftware-libraryfrontend.lasalle_social_media_meta_tag_default_image'),
        ];
    }
}

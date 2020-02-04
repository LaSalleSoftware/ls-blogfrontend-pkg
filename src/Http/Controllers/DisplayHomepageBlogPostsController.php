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

namespace Lasallesoftware\Blogfrontend\Http\Controllers;

// LaSalle Software
use Lasallesoftware\Library\Common\Http\Controllers\CommonControllerForClients;

// Laravel Framework
use Illuminate\Support\MessageBag;

// Third party classes
use GuzzleHttp\Exception\RequestException;


class DisplayHomepageBlogPostsController extends CommonControllerForClients
{
    /**
     * The message bag instance.
     *
     * @var \Illuminate\Support\MessageBag
     */
    protected $messages;


    public function DisplayHomepageBlogPosts()
    {
        // comment is for the UUID database table
        $comment = 'Created by ' .
            config('lasallesoftware-library.lasalle_app_domain_name') .
            "'s Lasallesoftware\Blogfrontend\Http\Controllers\DisplayHomepageBlogPostsController"
        ;

        $path = $this->getApiPath('homepageblogposts');

        $response = $this->sendRequestToLasalleBackend($comment, $path);

        if (!isset($this->messages)) {

            $body = json_decode($response->getBody());

            $transformedPosts = [];
            foreach ($body->posts as $post) {
                $transformedPost = [
                    'title'               => $post->title,
                    'slug'                => $post->slug,
                    'author'              => $post->author,
                    'excerpt'             => $post->excerpt,
                    'featured_image'      => $this->getFeaturedImage($post->featured_image),
                    'featured_image_type' => $this->getFeaturedImageType($post->featured_image_type),
                    'publish_on'          => $this->formatDate($post->publish_on),
                    'datetime'            => $this->formatDateForHTMLTimeTag($post->publish_on),
                ];

                $transformedPosts[] = $transformedPost;
            }

        } else {
            $transformedPosts = false;
        }

        return view(config('lasallesoftware-frontendapp.lasalle_path_to_front_end_view_path') . '.home', [
            'posts'                                => $transformedPosts,
            'numberOfPosts'                        => ($transformedPosts) ? count($transformedPosts) : 0,
            'copyright'                            => env('LASALLE_COPYRIGHT_IN_FOOTER'),
            'socialMediaMetaTags'                  => $this->getSocialMediaMetaTags(),
            'featured_image_social_media_meta_tag' => config('lasallesoftware-frontendapp.lasalle_social_media_meta_tag_default_image'),
        ]);
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
            'title'        => env('APP_NAME'),
            'description'  => env('APP_NAME') . ' home page',
            'url'          => url()->full(),
            'site'         => $this->getSocialMediaMetaTagSite(),
            'creator'      => $this->getSocialMediaMetaTagCreator(),
            'image'        => config('lasallesoftware-frontendapp.lasalle_social_media_meta_tag_default_image'),
        ];
    }
}

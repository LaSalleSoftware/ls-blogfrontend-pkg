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
 * @link       https://packagist.org/packages/lasallesoftware/ls-blogfrontend-pkg
 * @link       https://github.com/LaSalleSoftware/ls-blogfrontend-pkg
 *
 */

namespace Lasallesoftware\Blogfrontend\Http\Controllers;

// LaSalle Software
use Lasallesoftware\Libraryfrontend\APIRequestsToTheBackend\HttpRequestToAdminBackend;


// Laravel Framework
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\MessageBag;

// Third party classes
use GuzzleHttp\Exception\RequestException;

/**
 * Class DisplayBlogRSSFeedController
 *
 * @package Lasallesoftware\Blogfrontend\Http\Controllers
 */
class DisplayBlogRSSFeedController extends BaseFrontendController
{
    use HttpRequestToAdminBackend;

    /**
     * The message bag instance.
     *
     * @var \Illuminate\Support\MessageBag
     */
    protected $messages;

    /**
     * Display the RSS feed.
     */
    public function DisplayBlogRSSFeed()
    {
        // comment is for the UUID database table
        $endpointPath = $this->getEndpointPath('blogrssfeed');
        $httpRequest  = 'GET';
        $slug         = null;

        $response = $this->sendRequestToLasalleBackend($endpointPath, $httpRequest, $slug);

        //if ($response instanceof \GuzzleHttp\Psr7\Response) {
        if (!isset($this->messages)) {

            $body = json_decode($response->getBody());

            $transformedPosts = $this->getTransformedPostsForRSSFeed($body->posts);
            $metaRSSData      = $this->getMetaRSSData();

            // Thank you https://github.com/spatie/laravel-feed/blob/master/resources/views/rss.blade.php
            //           https://github.com/spatie/laravel-feed/blob/master/src/Feed.php
            $contents = view(config('lasallesoftware-frontendapp.lasalle_path_to_front_end_view_path') . '.blog.feeds.atom', [
                'posts'     => $transformedPosts,
                'meta'      => $metaRSSData,
                'copyright' => env('LASALLE_COPYRIGHT_IN_FOOTER'),
            ]);

            return new Response($contents, 200, [
                'Content-Type' => 'application/xml;charset=UTF-8',
            ]);

        } else {
            return $this->displayErrorView();
        }
    }

    /**
     * Get the transformed posts specifically structured for the RSS feed.
     *
     * @param  array    $posts
     * @return array
     */
    private function getTransformedPostsForRSSFeed($posts)
    {
        $transformedPosts = [];

        foreach ($posts as $post) {

            $transformedPost = [
                'title'       => $post->title,
                'link'        => env('APP_URL') .'/' . $post->slug,
                'id'          => env('APP_URL') .'/' . $post->slug,
                'description' => $post->excerpt,
                'summary'     => $post->excerpt,
                'author'      => $post->author,
                'guid'        => env('APP_URL') .'/' . $post->slug,
                'pubDate'     => $post->publish_on,
                'updated'     => $post->publish_on,
            ];

            $transformedPosts[] = $transformedPost;
        }

        return $transformedPosts;
    }

    /**
     * Get the meta data specifically for the RSS feed.
     *
     * This is the data that is at the top of the RSS feed that describes the site producing the feed.
     *
     * @return array
     */
    private function getMetaRSSData()
    {
        return [
            'id'          => config('app.url') . '/blog/feed',
            'title'       => config('app.name'),
            'link'        => config('app.url') . '/blog/feed',
            //'description' => '',
            //'language'    => '',
            'pubDate'     => Carbon::now(),
        ];
    }
}

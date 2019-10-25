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
use http\Exception\BadQueryStringException;
use Illuminate\Support\MessageBag;
use Lasallesoftware\Library\Common\Http\Controllers\CommonControllerForClients;

use GuzzleHttp\Exception\RequestException;


class DisplaySinglePostController extends CommonControllerForClients
{
    /**
     * The message bag instance.
     *
     * @var \Illuminate\Support\MessageBag
     */
    protected $messages;


    public function DisplaySinglePost($slug)
    {
        $comment = 'Created by ' .
            config('lasallesoftware-library.lasalle_app_domain_name') .
            "'s Lasallesoftware\Blogfrontend\Http\Controllers\DisplaySinglePostController"
        ;

        $path = $this->getApiPath('singlearticleblog');

        $response = $this->sendRequestToLasalleBackend($comment, $path, $slug);

        //if ($response instanceof \GuzzleHttp\Psr7\Response) {
        if (!isset($this->messages)) {

            $body = json_decode($response->getBody());


            /*
            echo "<h1>featured image!</h1>";
            echo "image = " . $body->post->featured_image;
            echo "<br><br>type = " . $body->post->featured_image_type;
            echo "<br><br>social media = " . $body->post->featured_image_social_meta_tag;
            return;
            */


            return view(config('lasallesoftware-frontendapp.lasalle_path_to_front_end_view_path') . '.blog.pages.singleblogpost', [
                'title'               => $body->post->title,
                'author'              => $body->post->author,
                'publish_on'          => $this->formatDate($body->post->publish_on),
                'category'            => $this->getCategoryLinkHtml($body->post->category),
                'content'             => $body->post->content,
                'featured_image'      => $body->post->featured_image,
                'featured_image_type' => $body->post->featured_image_type,
                'copyright'           => env('LASALLE_COPYRIGHT_IN_FOOTER'),
                'socialMediaMetaTags' => $this->getSocialMediaMetaTags($body->post),
                'numberOfTags'        => $this->getTheNumberOfTags($body->tags),
                'tags'                => $this->transformTags($body->tags),
                'numberOfPostupdates' => $this->getTheNumberOfPostupdates($body->postupdates),
                'postupdates'         => $this->transformPostupdates($body->postupdates),
            ]);

        } else {

            return view(config('lasallesoftware-frontendapp.lasalle_path_to_front_end_view_path') . '.errors.main', [
                'status_code'         => $this->messages->first('StatusCode'),
                'error'               => $this->messages->first('Error'),
                'reason'              => $this->messages->first('Reason'),
                'copyright'           => env('LASALLE_COPYRIGHT_IN_FOOTER'),
            ]);
        }
    }

    /**
     * Format date from a date string.
     *
     * @param  string  $date     2019-09-29T04:00:00.000000Z
     * @return string
     */
    private function formatDate($date)
    {
        return date(config('lasallesoftware-frontendapp.lasalle_date_format'),strtotime($date));
    }

    /**
     * Get the link to the category listing.
     *
     * Return the full html <img> tag.
     *
     * @param  string  $category   The category name.
     * @return string
     */
    private function getCategoryLinkHtml($category)
    {
        return '<a href="' . env('APP_URL') . '/category/' . strtolower($category) . '">' . strtolower($category) . '</a>';
    }

    /**
     * Get the social media meta tags.
     *
     * @param  object  $post
     * @return array
     */
    private function getSocialMediaMetaTags($post)
    {
        // ...put all the social media meta tag info together
        // https://developer.twitter.com/en/docs/tweets/optimize-with-cards/guides/getting-started
        // https://developer.twitter.com/en/docs/tweets/optimize-with-cards/overview/summary-card-with-large-image
        // https://developer.twitter.com/en/docs/tweets/optimize-with-cards/overview/markup
        // https://ogp.me/
        return [
            'twitter_card' => 'summary_large_image',
            'og_type'      => 'article',
            'title'        => $post->title,
            'description'  => $post->excerpt,
            'url'          => env('APP_URL') . '/' . $post->slug,
            'site'         => $this->getSocialMediaMetaTagSite(),
            'creator'      => $this->getSocialMediaMetaTagCreator(),
            'image'        => $post->featured_image_social_meta_tag,
        ];
    }

    /**
     * Get the value of the twitter:site social media meta tag.
     *
     * https://developer.twitter.com/en/docs/tweets/optimize-with-cards/overview/markup
     *
     * @return string
     */
    private function getSocialMediaMetaTagSite()
    {
        return config('lasallesoftware-frontendapp.lasalle_social_media_meta_tag_site');
    }

    /**
     * Get the value of the twitter:creator social media meta tag.
     *
     * https://developer.twitter.com/en/docs/tweets/optimize-with-cards/overview/markup
     *
     * @return string
     */
    private function getSocialMediaMetaTagCreator()
    {
        if (config('lasallesoftware-frontendapp.lasalle_social_media_meta_tag_creator') == '') {
            return config('lasallesoftware-frontendapp.lasalle_social_media_meta_tag_site');
        }

        return config('lasallesoftware-frontendapp.lasalle_social_media_meta_tag_creator');
    }

    /**
     * Get the number of tags.
     *
     * @param  collection  $tags
     * @return int
     */
    private function getTheNumberOfTags($tags)
    {
        return count($tags);
    }

    /**
     * Prepare the post updates for the view.
     *
     * @param  object  $tags
     * @return Illuminate\Support\Collection | null
     */
    private function transformTags($tags)
    {
        if ($this->getTheNumberOfTags($tags) == 0) return null;

        $transformedTags = [];
        foreach ($tags as $tag)
        {
            $transformedPostupdates[] = [
                'title' => $tag->title,
                'link'  => $this->getTagLinkHtml($tag->title) ];
        }

        $collection = collect($transformedPostupdates);

        //return $collection->sortByDesc('title');
        return $collection->sortBy('title');
    }

    /**
     * Get the link to the tag listing.
     *
     * Return the full html <img> tag.
     *
     * @param  string  $tag   The title field in the tag record.
     * @return string
     */
    private function getTagLinkHtml($tag)
    {
        return '<a href="' . env('APP_URL') . '/tags/' . strtolower($tag) . '">' . strtolower($tag) . '</a>';
    }

    /**
     * Get the number of post updates.
     *
     * @param  object  $postupdates
     * @return int
     */
    private function getTheNumberOfPostupdates($postupdates)
    {
        return count($postupdates);
    }

    /**
     * Prepare the post updates for the view.
     *
     * @param object  $postupdates
     * @return Illuminate\Support\Collection | null
     */
    private function transformPostupdates($postupdates)
    {
        if ($this->getTheNumberOfPostupdates($postupdates) == 0) return null;

        $transformedPostupdates = [];
        foreach ($postupdates as $postupdate)
        {
            $transformedPostupdates[] = [
              'title'      => $postupdate->title,
              'publish_on' => $this->formatDate($postupdate->publish_on),
              'excerpt'    => $postupdate->excerpt,
              'content'    => $postupdate->content,
            ];
        }

        $collection = collect($transformedPostupdates);

        //return $collection->sortByDesc('publish_on');
        return $collection->sortBy('publish_on');
    }
}

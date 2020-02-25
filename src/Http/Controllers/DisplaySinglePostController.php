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
        // comment is for the UUID database table
        $comment = 'Created by ' .
            config('lasallesoftware-library.lasalle_app_domain_name') .
            "'s Lasallesoftware\Blogfrontend\Http\Controllers\DisplaySinglePostController"
        ;

        $uuid = $this->makeUuid($comment, 9);

        $path = $this->getApiPath('singleblogpost');

        $response = $this->sendRequestToLasalleBackend($uuid, $path, $slug);

        //if ($response instanceof \GuzzleHttp\Psr7\Response) {
        if (!isset($this->messages)) {

            $body = json_decode($response->getBody());

            return view(config('lasallesoftware-frontendapp.lasalle_path_to_front_end_view_path') . '.blog.pages.singleblogpost', [
                'title'               => $body->post->title,
                'author'              => $this->getAuthorLinkHtml($body->post->author),
                'publish_on'          => $this->formatDate($body->post->publish_on),
                'datetime'            => $this->formatDateForHTMLTimeTag($body->post->publish_on),
                'category'            => $this->getCategoryLinkHtml($body->post->category),
                'content'             => $body->post->content,
                'featured_image'      => $this->getFeaturedImage($body->post->featured_image),
                'featured_image_type' => $this->getFeaturedImageType($body->post->featured_image_type),
                'featured_image_social_media_meta_tag' => $this->getFeaturedImageSocialMediaMetaTag($body->post->featured_image_social_meta_tag),
                'copyright'           => env('LASALLE_COPYRIGHT_IN_FOOTER'),
                'socialMediaMetaTags' => $this->getSocialMediaMetaTags($body->post),
                'numberOfTags'        => $this->getTheNumberOfTags($body->tags),
                'tags'                => $this->transformTags($body->tags),
                'numberOfPostupdates' => $this->getTheNumberOfPostupdates($body->postupdates),
                'postupdates'         => $this->transformPostupdates($body->postupdates),
            ]);

        } else {
            return $this->displayErrorView();
        }
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
        return '<a class="link-custom1" href="' . env('APP_URL') . '/category/' . strtolower($category) . '?page=1">' . strtolower($category) . '</a>';
    }

    /**
     * Get the link to the author listing.
     *
     * Return the full html <img> tag.
     *
     * @param  string  $author   The author's name, which must exactly match the 'name_calculated' field in the personsbydomains db table.
     * @return string
     */
    private function getAuthorLinkHtml($author)
    {
        return '<a class="link-custom1" href="' . env('APP_URL') . '/author/' . str_replace(' ', '%20', $author) . '?page=1">' . $author . '</a>';
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
            'image'        => $this->getFeaturedImageSocialMediaMetaTag($post->featured_image_social_meta_tag),
        ];
    }

    /**
     * Get the number of tags.
     *
     * @param  collection  $tags
     * @return int
     */
    private function getTheNumberOfTags($tags)
    {
        return (isset($tags)) ? count($tags) : 0;
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
        return '<a href="' . env('APP_URL') . '/tag/' . ucwords($tag) . '">' . strtolower($tag) . '</a>';
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
